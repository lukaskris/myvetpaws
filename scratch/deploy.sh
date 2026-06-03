#!/bin/bash
set -e

SERVER_IP="72.61.143.83"
SSH_PASS="${SSH_PASS:-Luk4sk10tki3@}"
REMOTE_PATH="/var/www/myvetpaws"
DOMAIN="myvetpaws.my.id"
EMAIL="edwin.kurniawan@balog.co.id"

echo "=== 1. Compiling Tailwind CSS assets locally ==="
npm run build

echo "=== 2. Backing up old Laravel database and files on remote server ==="
sshpass -p "$SSH_PASS" ssh -o StrictHostKeyChecking=no root@$SERVER_IP "
  mysqldump myvetpaws > /root/myvetpaws_old_laravel.sql 2>/dev/null || true
  if [ -d $REMOTE_PATH ]; then
    tar -czf /root/myvetpaws_old_laravel.tar.gz -C /var/www myvetpaws 2>/dev/null || true
    rm -rf $REMOTE_PATH
  fi
"

echo "=== 3. Creating remote directory structure ==="
sshpass -p "$SSH_PASS" ssh -o StrictHostKeyChecking=no root@$SERVER_IP "
  mkdir -p $REMOTE_PATH/writable/cache \
           $REMOTE_PATH/writable/sessions \
           $REMOTE_PATH/writable/logs \
           $REMOTE_PATH/writable/uploads \
           $REMOTE_PATH/writable/debugbar
"

echo "=== 4. Re-creating clean database and granting permissions ==="
sshpass -p "$SSH_PASS" ssh -o StrictHostKeyChecking=no root@$SERVER_IP "
  mysql -e 'DROP DATABASE IF EXISTS myvetpaws; CREATE DATABASE myvetpaws;'
  mysql -e \"GRANT ALL PRIVILEGES ON myvetpaws.* TO 'remote_user'@'localhost';\"
  mysql -e 'FLUSH PRIVILEGES;'
"

echo "=== 5. Syncing files via Rsync ==="
rsync -avz --delete \
  --exclude=".git" \
  --exclude="node_modules" \
  --exclude="tests" \
  --exclude="scratch" \
  --exclude=".env" \
  --exclude="database/data" \
  --exclude="writable/cache/*" \
  --exclude="writable/logs/*" \
  --exclude="writable/sessions/*" \
  --exclude="writable/debugbar/*" \
  --exclude="writable/uploads/*" \
  --exclude="vendor" \
  -e "sshpass -p $SSH_PASS ssh -o StrictHostKeyChecking=no" \
  ./ root@$SERVER_IP:$REMOTE_PATH/

echo "=== 6. Uploading production .env file ==="
cat << 'EOF' > .env.production
#--------------------------------------------------------------------
# MyVetPaws Production Configuration
#--------------------------------------------------------------------

CI_ENVIRONMENT = production

# App Configurations
app.baseURL = 'https://myvetpaws.my.id/'
app.indexPage = ''
app.forceGlobalSecureRequests = true

# Database Settings
database.default.hostname = 127.0.0.1
database.default.database = myvetpaws
database.default.username = remote_user
database.default.password = "9xKq#P7mN$L2vR5w"
database.default.DBDriver = MySQLi
database.default.port = 3306
database.default.DBPrefix = ""

# Encryption Configuration
encryption.key = hex2bin:ad37d6e6f47dfb2e811c0f058097d81a95e78bc8beaa48e02d6b38c238b9d0ea1a
encryption.driver = OpenSSL
encryption.digest = SHA512

# Session config
session.driver = 'CodeIgniter\Session\Handlers\FileHandler'
session.savePath = '/var/www/myvetpaws/writable/sessions'

# Security CSRF
security.CSRFProtection = 'active'
security.tokenName = 'csrf_test_name'
security.headerName = 'X-CSRF-TOKEN'
security.cookieName = 'csrf_cookie_name'
security.expires = 7200
security.regenerate = true
security.redirect = true
EOF

sshpass -p "$SSH_PASS" scp -o StrictHostKeyChecking=no .env.production root@$SERVER_IP:$REMOTE_PATH/.env
rm -f .env.production

echo "=== 7. Running Composer Install on remote server ==="
sshpass -p "$SSH_PASS" ssh -o StrictHostKeyChecking=no root@$SERVER_IP "cd $REMOTE_PATH && composer install --no-dev --optimize-autoloader"

echo "=== 8. Setting up remote directory ownership and permissions ==="
sshpass -p "$SSH_PASS" ssh -o StrictHostKeyChecking=no root@$SERVER_IP "
  chown -R www-data:www-data $REMOTE_PATH
  chmod -R 775 $REMOTE_PATH/writable
"

echo "=== 9. Running database migrations & seeders ==="
sshpass -p "$SSH_PASS" ssh -o StrictHostKeyChecking=no root@$SERVER_IP "
  cd $REMOTE_PATH
  php spark migrate
  php spark db:seed SampleDataSeeder
"

echo "=== 10. Setting up Nginx virtual host configuration ==="
sshpass -p "$SSH_PASS" ssh -o StrictHostKeyChecking=no root@$SERVER_IP "cat << 'NGINX_EOF' > /etc/nginx/sites-available/$DOMAIN
server {
    listen 80;
    server_name $DOMAIN www.$DOMAIN;

    root $REMOTE_PATH/public;
    index index.php index.html index.htm;

    client_max_body_size 20M;

    location / {
        try_files \$uri \$uri/ /index.php\$is_args\$args;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
NGINX_EOF
"

echo "=== 11. Enabling Nginx site, disabling old config, and reloading Nginx ==="
sshpass -p "$SSH_PASS" ssh -o StrictHostKeyChecking=no root@$SERVER_IP "
  rm -f /etc/nginx/sites-enabled/myvetpaws
  ln -sf /etc/nginx/sites-available/$DOMAIN /etc/nginx/sites-enabled/$DOMAIN
  nginx -t
  systemctl reload nginx
"

echo "=== 12. Running Certbot for Let's Encrypt SSL ==="
if sshpass -p "$SSH_PASS" ssh -o StrictHostKeyChecking=no root@$SERVER_IP "host $DOMAIN" >/dev/null 2>&1; then
  echo "DNS resolved! Starting SSL generation..."
  sshpass -p "$SSH_PASS" ssh -o StrictHostKeyChecking=no root@$SERVER_IP "certbot --nginx -d $DOMAIN -d www.$DOMAIN --non-interactive --agree-tos -m $EMAIL --redirect"
  echo "Certbot SSL certificate successfully installed!"
else
  echo "WARNING: DNS for $DOMAIN has not propagated yet. Skipping Certbot SSL."
fi

echo "=== Production Deployment Successfully Completed ==="

<?php
namespace Deployer;

require 'recipe/laravel.php';

set('application', 'myvetpaws');
set('repository', 'git@github.com:lukaskris/myvetpaws.git');

host('production')
    ->set('remote_user', 'root')
    ->set('hostname', '72.61.143.83')
    ->set('deploy_path', '/var/www/myvetpaws');

after('deploy:success', 'artisan:migrate');
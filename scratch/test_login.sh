#!/bin/bash
set -e

COOKIE_FILE="/tmp/myvetpaws_cookies.txt"
rm -f "$COOKIE_FILE"

echo "=== Step 1: Getting login page and CSRF cookie ==="
RESPONSE=$(curl -s -k -c "$COOKIE_FILE" -i --resolve myvetpaws.my.id:443:72.61.143.83 https://myvetpaws.my.id/login)

# Extract CSRF token from cookie file
CSRF_TOKEN=$(grep "csrf_cookie_name" "$COOKIE_FILE" | awk '{print $NF}')
echo "CSRF Token obtained: $CSRF_TOKEN"

echo "=== Step 2: Attempting login with credentials ==="
# Send POST request
LOGIN_RESPONSE=$(curl -s -k -b "$COOKIE_FILE" -c "$COOKIE_FILE" -i \
  --resolve myvetpaws.my.id:443:72.61.143.83 \
  -d "csrf_test_name=$CSRF_TOKEN" \
  -d "email=admin@clinic.com" \
  -d "password=password" \
  -d "remember=on" \
  https://myvetpaws.my.id/login)

# Print headers from login attempt
echo "$LOGIN_RESPONSE" | head -n 25

echo "=== Step 3: Checking visits page and calendar ==="
VISITS_RESPONSE=$(curl -s -k -b "$COOKIE_FILE" -i \
  --resolve myvetpaws.my.id:443:72.61.143.83 \
  https://myvetpaws.my.id/visits)

echo "$VISITS_RESPONSE" | head -n 25

if echo "$VISITS_RESPONSE" | grep -q "Calendar View"; then
  echo "SUCCESS: Calendar View tab is present on the visits page!"
else
  echo "ERROR: Calendar View tab is missing!"
  exit 1
fi

if echo "$VISITS_RESPONSE" | grep -q "calendarWeeks"; then
  echo "SUCCESS: Alpine.js calendar initialization logic is present!"
else
  echo "ERROR: Alpine.js calendar logic is missing!"
  exit 1
fi

echo "=== Step 4: Checking visits/create with date parameter ==="
CREATE_RESPONSE=$(curl -s -k -b "$COOKIE_FILE" -i \
  --resolve myvetpaws.my.id:443:72.61.143.83 \
  "https://myvetpaws.my.id/visits/create?date=2026-06-03")

echo "$CREATE_RESPONSE" | head -n 25

echo "=== Step 5: Checking dashboard ==="
DASHBOARD_RESPONSE=$(curl -s -k -b "$COOKIE_FILE" -i \
  --resolve myvetpaws.my.id:443:72.61.143.83 \
  https://myvetpaws.my.id/dashboard)

echo "$DASHBOARD_RESPONSE" | head -n 25




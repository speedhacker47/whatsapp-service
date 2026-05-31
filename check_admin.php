#!/bin/bash
# Full login test with correct CSRF token extraction

rm -f /tmp/cookies2.txt

# Step 1: GET login page with cookies
echo "=== Step 1: GET Login page ==="
curl -s -c /tmp/cookies2.txt -b /tmp/cookies2.txt http://localhost/login -o /tmp/login_page2.html
echo "Login page fetched"

# Try different grep patterns for CSRF token
TOKEN=$(grep -oE 'name="_token" value="[^"]+"' /tmp/login_page2.html | grep -oE 'value="[^"]+"' | sed 's/value="//;s/"//')
if [ -z "$TOKEN" ]; then
    TOKEN=$(grep -oE '"_token","[^"]+"' /tmp/login_page2.html | sed 's/"_token","//;s/"//')
fi
if [ -z "$TOKEN" ]; then
    TOKEN=$(grep -oP 'csrf-token" content="\K[^"]+' /tmp/login_page2.html | head -1)
fi

echo "TOKEN: $TOKEN"

# Step 2: POST login
echo -e "\n=== Step 2: POST Login ==="
RESPONSE=$(curl -si -c /tmp/cookies2.txt -b /tmp/cookies2.txt \
    -X POST http://localhost/login \
    --data-urlencode "email=admin@whatsmark.com" \
    --data-urlencode "password=Admin@123456" \
    --data-urlencode "_token=$TOKEN" \
    -H "Referer: http://localhost/login")

echo "$RESPONSE" | grep -E "HTTP|Location"

echo -e "\n=== Cookies ==="
cat /tmp/cookies2.txt

# Step 3: GET /admin
echo -e "\n=== Step 3: GET /admin ==="
curl -si -c /tmp/cookies2.txt -b /tmp/cookies2.txt http://localhost/admin 2>&1 | grep -E "HTTP|Location|<title"

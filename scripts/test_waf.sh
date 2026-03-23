#!/usr/bin/env bash
# Simple test script to trigger WAF rules by sending repeated sqlmap-like requests
# Usage: ./scripts/test_waf.sh [HOST]

HOST=${1:-http://localhost:8090}
URL="$HOST/Scenario3/profile.php?user_id=1%20UNION%20SELECT%201,username,password%20FROM%20users--"

echo "Testing WAF at: $HOST"
for i in {1..6}; do
  STATUS=$(curl -s -o /dev/null -w "%{http_code}" -A "sqlmap/1.0" "$URL")
  echo "Request #$i -> HTTP $STATUS"
  sleep 1
done

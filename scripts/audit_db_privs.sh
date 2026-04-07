#!/usr/bin/env bash
# audit_db_privs.sh
# Wrapper to run the SQL audit against the DB container using docker compose.
# Adjust the service name if your compose file uses a different name for the DB (default in this repo is likely 'db').

set -euo pipefail

DB_SERVICE=${DB_SERVICE:-db}
MYSQL_USER=${MYSQL_USER:-root}
MYSQL_PASS=${MYSQL_PASS:-root}

echo "Running DB privileges audit against container: $DB_SERVICE"

docker compose exec -T $DB_SERVICE mysql -u${MYSQL_USER} -p${MYSQL_PASS} < /app/scripts/audit_db_privs.sql

echo "Audit complete. Review the output above for risky privileges."

echo "If running outside compose, you can run: mysql -u root -p < scripts/audit_db_privs.sql"

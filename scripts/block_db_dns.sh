#!/usr/bin/env bash
# block_db_dns.sh
# Example host-level firewall rules to block DNS egress from the DB process.
# DRY_RUN=1 (default) prints commands instead of applying them.

set -euo pipefail

DRY_RUN=${DRY_RUN:-1}
DB_USER=${DB_USER:-mysql}

echo "This script shows example iptables commands to block DNS (port 53) for processes owned by user: $DB_USER"

RULES=(
  "iptables -A OUTPUT -p udp --dport 53 -m owner --uid-owner $DB_USER -j DROP"
  "iptables -A OUTPUT -p tcp --dport 53 -m owner --uid-owner $DB_USER -j DROP"
)

for cmd in "${RULES[@]}"; do
  if [ "$DRY_RUN" -eq 1 ]; then
    echo "DRY-RUN: $cmd"
  else
    echo "Running: $cmd"
    eval "$cmd"
  fi
done

echo "Notes:"
echo " - For containerized DBs apply egress rules on the host or via orchestration (Docker network, k8s NetworkPolicy)."
echo " - Test carefully; ensure you don't block legitimate DNS for the host or other services."

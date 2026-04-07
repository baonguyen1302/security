# Hardening for OOB (DNS) SQLi exfiltration

This guide shows practical steps to harden a MySQL/MariaDB server and the host network to reduce the risk of Out-Of-Band (OOB) data exfiltration via DNS or other network channels.

---

Important notes
- Test changes in staging before applying to production.
- Some operations require root on the DB host and may disrupt service (restarts, removing UDFs).

Files added in `scripts/`:
- `harden_mysql.sql` — SQL examples to revoke dangerous privileges and inspect UDFs.
- `audit_db_privs.sql` — SQL to list users with risky privileges.
- `audit_db_privs.sh` — helper to run the audit inside the DB container (uses docker compose).
- `block_db_dns.sh` — host-level iptables example to block DNS egress for the DB process (DRY-RUN by default).

Quick audit (recommended)
1) Run the privileges audit inside the DB container (this repo's default credentials are root/root in README):

```bash
# run inside repo root
docker compose exec -T db mysql -uroot -proot < /app/scripts/audit_db_privs.sql
```

Or use the helper (non-interactive):

```bash
./scripts/audit_db_privs.sh
```

2) Review output for users with FILE, SUPER, PROCESS, CREATE ROUTINE, or other high privileges. If found, follow the hardening steps below.

Hardening steps

1) Principle: Least Privilege
- Remove FILE, SUPER, PROCESS, and similar global privileges from application accounts. Use specific-granularity grants (e.g., SELECT on required tables only).

2) Disable local file import and persistent dangerous options
- Disable LOCAL INFILE (SET GLOBAL local_infile = 0) and persist with my.cnf.

3) Inspect and remove UDFs / stored routines that perform network I/O
- List `mysql.func` and any stored procedures/functions that may call system code. Remove untrusted UDFs and delete their shared object files from disk (requires root and restart).

4) Network egress controls
- Prevent the DB host from resolving to external attacker-controlled names:
  - Apply host-level firewall rules to block outbound DNS/TCP port 53 for the DB process (see `block_db_dns.sh`).
  - In cloud environments, remove outbound internet egress for DB subnets or use firewall rules/security groups.
  - In Kubernetes, use NetworkPolicy to restrict egress from DB pods.

5) Monitoring
- Enable DNS query logging at your resolver and alert on unusual domains or frequent queries with data-like labels (long hex/base32 subdomains).

6) Safe rollback & testing
- Backup grants (SHOW GRANTS FOR 'user'@'host') before changing. Revoke privileges incrementally and test.

Applying the SQL hardening (example)
1) Inspect `scripts/harden_mysql.sql` and replace `'appuser'@'host'` placeholders with your real app user/host.
2) Run as root inside the DB container (example using docker compose):

```bash
docker compose exec -T db mysql -uroot -proot < /app/scripts/harden_mysql.sql
```

Network egress example (host-level)
1) Review `scripts/block_db_dns.sh` (DRY_RUN=1 prints commands). To apply rules set DRY_RUN=0 and run as root.

```bash
# dry run
DRY_RUN=1 ./scripts/block_db_dns.sh

# apply (run as root)
DRY_RUN=0 ./scripts/block_db_dns.sh
```

Final notes
- These measures reduce risk but do not replace proper secure coding and prepared statements in the application (which you already use in secure mode in `app/Scenario4/oob.php`).
- If you tell me which DB engine/version is running in your container (MySQL, MariaDB, or MSSQL), I can produce exact commands for that engine and a non-interactive one-liner to apply hardening using docker compose.

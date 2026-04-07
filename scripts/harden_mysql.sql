-- harden_mysql.sql
-- Example SQL commands to harden a MySQL/MariaDB instance against OOB exfiltration
-- Run these as a privileged MySQL user (e.g. root) after reviewing and adapting to your environment.

-- 1) Disable LOCAL INFILE at runtime (may require my.cnf change + restart to persist)
SET GLOBAL local_infile = 0;

-- 2) Revoke FILE privilege from application users (FILE allows reading server files)
-- Replace 'appuser'@'host' with your application's DB username and host
REVOKE FILE ON *.* FROM 'appuser'@'host';

-- 3) Remove dangerous global privileges from application accounts
REVOKE SUPER, PROCESS, SHOW DATABASES, RELOAD ON *.* FROM 'appuser'@'host';

-- 4) Revoke CREATE ROUTINE / ALTER ROUTINE if not needed
REVOKE CREATE ROUTINE, ALTER ROUTINE ON *.* FROM 'appuser'@'host';

-- 5) Use dedicated least-privilege accounts. Example: create a read-only account
-- (replace host and password appropriately)
-- CREATE USER 'app_ro'@'host' IDENTIFIED BY 'strong_password_here';
-- GRANT SELECT ON sqli_demo.* TO 'app_ro'@'host';

-- 6) List user-defined functions (UDFs) to inspect for network-capable code
SELECT * FROM mysql.func;

-- If you find suspicious UDF entries, investigate and remove them from disk and
-- from mysql.func (requires root on DB host) and restart the server.

-- 7) Optional: audit current grants for the target user
SHOW GRANTS FOR 'appuser'@'host';

-- NOTE: adapt and test in staging. Some changes require config file edits and server restarts.

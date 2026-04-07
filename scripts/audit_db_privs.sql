-- audit_db_privs.sql
-- Lists users with potentially risky privileges that can enable OOB/network access.

-- Show grants for each user (information_schema.user_privileges lists per-user privileges)
SELECT GRANTEE, PRIVILEGE_TYPE
FROM information_schema.user_privileges
WHERE PRIVILEGE_TYPE IN ('FILE','SUPER','PROCESS','CREATE ROUTINE','CREATE USER','RELOAD')
ORDER BY GRANTEE, PRIVILEGE_TYPE;

-- Alternative check using mysql.user (if available):
SELECT User, Host, File_priv, Super_priv, Process_priv, Create_routine_priv
FROM mysql.user
WHERE File_priv = 'Y' OR Super_priv = 'Y' OR Process_priv = 'Y' OR Create_routine_priv = 'Y';

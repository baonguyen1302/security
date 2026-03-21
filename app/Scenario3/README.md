# Scenario 3 — Blind SQL Injection (time-based) and WAF defense

Goal
- Demonstrate blind (time-based) SQL injection where the attacker uses true/false queries and measures response times to extract data (e.g., admin password). Then show how a ModSecurity WAF can detect SQLMap scanning and block the attacker after repeated suspicious requests.

Vulnerable endpoint
- `app/Scenario3/profile.php` reads `user_id` from the query string and injects it directly into SQL.
- Example vulnerable URL:

```
http://localhost:8080/Scenario3/profile.php?user_id=1
```

Time-based Blind SQLi with sqlmap
- A typical sqlmap command to run a time-based blind scan looks like:

```
sqlmap -u "http://localhost:8080/Scenario3/profile.php?user_id=1" --technique=T --time-sec=5 --batch --level=5 --risk=3
```

- Without a WAF, sqlmap can enumerate the database by sending many true/false/time-based requests; for a non-trivial database this may take minutes (the example claim: ~2 minutes).

WAF (ModSecurity) defense
- The Compose stack includes a `waf` service (OWASP ModSecurity CRS) mapped to host port `8090`.
- I added a custom rules file at `./waf/rules/10-sqlmap.conf` that:
  - Detects `User-Agent` containing `sqlmap` and common SQLi patterns like `UNION SELECT`, `SLEEP(`, `BENCHMARK(` in request parameters or body.
  - Increments a per-IP counter (`ip.sqlmap_counter`) on suspicious requests and denies the IP after 3 suspicious hits.

How to test (safe, local lab)
1. Start the stack:

```bash
docker compose up -d
```

2. Populate test tables (via phpMyAdmin or SQL init). Example SQL to create `profiles` and `users` tables and sample rows:

```sql
CREATE TABLE IF NOT EXISTS profiles (id INT PRIMARY KEY, username VARCHAR(100));
INSERT INTO profiles (id, username) VALUES (1, 'victim');

CREATE TABLE IF NOT EXISTS users (id INT PRIMARY KEY AUTO_INCREMENT, username VARCHAR(100), password VARCHAR(100));
INSERT INTO users (username, password) VALUES ('admin','supersecret');
```

3. Run sqlmap *without* WAF (target port 8080) to see how it can extract data:

```
sqlmap -u "http://localhost:8080/Scenario3/profile.php?user_id=1" --technique=T --time-sec=5 --batch --level=5 --risk=3
```

4. Run sqlmap *through the WAF* (target port 8090) — the custom ModSecurity rules will detect sqlmap patterns (User-Agent and injection payloads) and increment the per-IP counter. After 3 suspicious requests the WAF will block the attacker and sqlmap will fail to extract data.

```
sqlmap -u "http://localhost:8090/Scenario3/profile.php?user_id=1" --technique=T --time-sec=5 --batch --level=5 --risk=3
```

Notes and caveats
- The custom ModSecurity rules provided are a demo; rule effectiveness depends on correct ModSecurity/CRS configuration in the container image. The provided rules increment a per-IP counter and deny when the counter exceeds 2 (i.e., after 3 suspicious hits).
- The `waf` container must load rules from `./waf/rules` — docker-compose mounts that directory into the container. If your WAF image expects a different path, you may need to adjust the mount path.
- For more robust defenses, consider:
  - Using more comprehensive CRS rules and tuning false positive handling.
  - Rate limiting and connection throttling at the proxy level.
  - Blocking based on multiple signals (UA, request frequency, payload patterns), and using a blacklist store.

Security reminder
- This scenario is intentionally vulnerable for learning. Do not run against production systems or expose to the public internet.


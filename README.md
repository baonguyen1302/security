## security — English / Vietnamese

This repository is a small, local lab for learning SQL injection (SQLi) techniques and mitigations. It contains intentionally vulnerable PHP examples (three scenarios), a Docker Compose stack to run the app + MySQL + phpMyAdmin, and an optional WAF (ModSecurity/CRS) for defensive demonstrations.

Below you'll find the project structure and simple run instructions in English followed by a Vietnamese translation.

## Quick facts

- Purpose: learning and demos for SQLi (login bypass, UNION-based, blind/time-based) and mitigations (prepared statements, ModSecurity rules, rate-limiting).
- Do NOT run this on public infrastructure. Use an isolated VM or lab network.

## Repository structure (top-level)

- `Dockerfile`            - PHP/Apache image build used for the `web` service.
- `docker-compose.yml`   - Orchestrates containers: `web`, `db`, `waf`, `phpmyadmin`.
- `init/`                - `init.sql` seeds the `sqli_demo` database on first MySQL startup.
- `app/`                 - PHP application code and scenario folders.
- `waf/`                 - Custom ModSecurity rules and nginx limit config used by the WAF container.
- `scripts/`             - Helpful utilities (WAF tester, blind extractor script).
- `README.md`            - This file.

## `app/` (important files)

- `app/config.php`                   - Database connection settings used by the PHP pages.
- `app/Scenario1/login.php`          - Scenario 1: Login bypass demo (vulnerable + secure prepared-statement example).
- `app/Scenario2/product.php`        - Scenario 2: Product listing with UNION-based SQLi demo + secure branch.
- `app/Scenario3/profile.php`        - Scenario 3: Blind (time-based) demo plus a safe simulation API and secure branch.

There may also be per-scenario README files under `app/Scenario*/README.md` with additional details.

## WAF / rules

- `waf/rules/10-sqlmap.conf`  - Example ModSecurity rules (scanner UA detection, per-IP counters, deny rules).
- `waf/conf.d/limit.conf`     - nginx rate-limiting config applied by the WAF proxy.

## Ports & credentials (default)

- Web app (direct): http://localhost:8080
- Web app (through WAF proxy): http://localhost:8090
- phpMyAdmin: http://localhost:8081 (user: `root`, password: `root`)
- MySQL (inside container): root / root; database: `sqli_demo` (created from `init/init.sql`).

These port mappings are from `docker-compose.yml` and may be adjusted there.

## How to run (quick)

Prerequisites: Docker and Docker Compose available on your machine.

Start the stack (build if needed):

```bash
docker compose up -d --build
```

Check running services:

```bash
docker compose ps
```

Stop and remove containers and volumes (useful to re-run initial DB seeding):

```bash
docker compose down -v
```

Restart after removing volumes:

```bash
docker compose up -d
```

Open in a browser:

- App (direct): http://localhost:8080
- App (through WAF): http://localhost:8090
- phpMyAdmin: http://localhost:8081

## Short scenario guides

- Scenario 1 — Login bypass (SQLi)
  - File: `app/Scenario1/login.php`
  - What: shows a vulnerable login (string concatenation) and a secure prepared-statement version.
  - Quick test: on the vulnerable form try `username: ' OR '1'='1` and an empty password to see bypass behavior.

- Scenario 2 — UNION-based SQLi
  - File: `app/Scenario2/product.php`
  - What: demonstrates how a `product_id` parameter can be abused with a UNION to leak other table columns. Also includes a parameterized/secure branch.
  - Example (vulnerable): `http://localhost:8080/Scenario2/product.php?product_id=-1 UNION SELECT 1,username,password FROM users--`

- Scenario 3 — Blind (time-based) SQLi (safe simulation)
  - File: `app/Scenario3/profile.php`
  - What: for safety this repository exposes a simulation endpoint (no attacker-supplied SQL execution). The simulation uses prepared statements and sleeps when a probe condition matches to mimic time-based blind SQLi.
  - Simulation probe pattern: `/Scenario3/profile.php?simulate=1&probe=table|column|id|pos|ascii`
  - Example: `/Scenario3/profile.php?simulate=1&probe=users|password|1|1|97` — server sleeps if first char of `users.password` for id=1 is ASCII 97.
  - Use `scripts/blind_extractor.py` to practice timed probes safely.

## Testing the WAF

- `scripts/test_waf.sh` sends sqlmap-like requests (User-Agent: sqlmap) to demonstrate detection and blocking.
- The WAF container runs OWASP CRS and custom rules; it may return 403 or 429 depending on rule thresholds.

## Security notes

- This code intentionally contains unsafe examples for educational purposes.
- Never expose this stack to the public internet. Run inside an isolated lab or VM.
- The `simulate` API is designed to be a safe teaching aid and does not execute raw, attacker-supplied SQL.

## Next steps (optional)

- I can add per-scenario step-by-step lab guides in `app/Scenario*/README.md` (happy to start with Scenario 1).
- I can add additional seed data in `init/init.sql` or tune WAF thresholds in `waf/rules/`.

---

Vietnamese (Tiếng Việt)

## Tổng quan

Kho mã này là một phòng lab nhỏ để học các kỹ thuật SQL injection (SQLi) và cách phòng chống. Nó chứa các ví dụ PHP có lỗi bảo mật (3 kịch bản), một ngăn xếp Docker Compose để chạy ứng dụng + MySQL + phpMyAdmin, và một WAF (ModSecurity/CRS) để minh họa phòng thủ.

## Cấu trúc dự án (tổng quát)

- `Dockerfile`            - Dùng để build image PHP/Apache cho service `web`.
- `docker-compose.yml`   - Điều phối các container: `web`, `db`, `waf`, `phpmyadmin`.
- `init/`                - `init.sql` dùng để tạo cơ sở dữ liệu `sqli_demo` và dữ liệu mẫu.
- `app/`                 - Mã PHP và các thư mục kịch bản.
- `waf/`                 - Luật ModSecurity mẫu và cấu hình giới hạn của nginx.
- `scripts/`             - Các script tiện ích (test WAF, extractor blind).

## Một số tệp quan trọng trong `app/`

- `app/config.php`                   - Cấu hình kết nối DB.
- `app/Scenario1/login.php`          - Kịch bản 1: bypass login (vulnerable) và phiên bản an toàn.
- `app/Scenario2/product.php`        - Kịch bản 2: UNION-based SQLi demo và phiên bản an toàn.
- `app/Scenario3/profile.php`        - Kịch bản 3: Blind/time-based demo và API mô phỏng an toàn.

## Cổng (ports) & thông tin đăng nhập mặc định

- Ứng dụng (direct): http://localhost:8080
- Ứng dụng (qua WAF): http://localhost:8090
- phpMyAdmin: http://localhost:8081 (user: `root`, pass: `root`)
- MySQL: root / root, database: `sqli_demo`.

## Chạy nhanh

Yêu cầu: Docker và Docker Compose.

Khởi động:

```bash
docker compose up -d --build
```

Dừng và xóa volumes (để seed lại DB):

```bash
docker compose down -v
```

## Hướng dẫn ngắn cho các kịch bản

- Kịch bản 1 — Login bypass
  - Tệp: `app/Scenario1/login.php`
  - Thử payload: `username: ' OR '1'='1` (để trống password) trên phần dễ bị tấn công.

- Kịch bản 2 — UNION-based
  - Tệp: `app/Scenario2/product.php`
  - Ví dụ (vulnerable): `?product_id=-1 UNION SELECT 1,username,password FROM users--`

- Kịch bản 3 — Blind (time-based) — mô phỏng an toàn
  - Tệp: `app/Scenario3/profile.php`
  - API mô phỏng: `?simulate=1&probe=table|column|id|pos|ascii`

## Ghi chú bảo mật

- Các ví dụ này có mục đích học tập. Không chạy trên mạng công cộng.

---

Nếu bạn muốn, tôi có thể thêm hướng dẫn chi tiết cho từng kịch bản trong `app/Scenario*/README.md` — bạn muốn bắt đầu với kịch bản nào?
# security

A small demo repository containing intentionally vulnerable PHP examples (SQL injection scenarios) and a Docker Compose setup to run them locally for learning and testing.

## Repository structure

Top-level files and directories:

- `docker-compose.yml`  - Compose stack: `web` (PHP/Apache), `db` (MySQL 5.7), `phpmyadmin`.
- `README.md`          - This file.
- `init/`              - SQL init scripts injected into the MySQL container (`init.sql`).
- `app/`               - PHP application code and scenario directories.

Contents of `app/`:

- `Scenario1/`  - First vulnerable scenario (example inputs/logic inside).
- `Scenario2/` - Second scenario (note the space in the folder name).
- `Scenario3/` - Third scenario.
- `login.php`   - Example/login page (present at `app/login.php`).

Note: folder names are as provided — e.g. `Scenario2/` contains a space.

## What the Docker Compose stack provides

- `web` (PHP 8.2 + Apache) served on host port 8080 -> container 80. The `app/` directory is mounted into `/var/www/html`.
- `db` (MySQL 5.7) served on host port 3306 -> container 3306. The image uses `/docker-entrypoint-initdb.d` to run SQL scripts found in `init/` on first startup. It creates a database named `sqli_demo`.
- `phpmyadmin` accessible on host port 8081 for easy DB inspection.

Default DB credentials (from `docker-compose.yml`):

- MySQL root user: `root`
- MySQL root password: `root`
- Database: `sqli_demo`

## How to run (quick)

Prerequisites:

- Docker and Docker Compose installed. On many Linux systems the commands are `docker` and `docker compose`. If you have the older standalone binary you may use `docker-compose` instead.

Start the stack in detached mode:

```bash
# security

This repository is a small, local lab for learning SQL injection (SQLi) and mitigation techniques. It contains intentionally vulnerable PHP examples (three scenarios), a Docker Compose stack that runs the web app + MySQL + phpMyAdmin, and a ModSecurity WAF container with example rules for defenses.

Quick overview
- Purpose: teach SQLi attack techniques (union-based, blind/time-based, login bypass) and basic defenses (prepared statements, WAF rules, rate-limiting).
- Not for production. Run only in an isolated lab environment.

Repository structure

Top-level files and folders:

- `Dockerfile`          - PHP/Apache image build steps.
- `docker-compose.yml`  - Compose orchestration (web, db, waf, phpmyadmin).
- `init/`               - `init.sql` with example database + sample data (runs on first MySQL startup).
- `app/`                - PHP application and scenarios.
- `waf/`                - Custom ModSecurity rules and WAF helper files.
- `scripts/`            - Small helper scripts (WAF tester, blind extractor).
- `README.md`           - This file.

`app/` layout:

- `app/config.php`      - DB connection used by PHP pages.
- `app/Scenario1/login.php`    - Scenario 1: Login bypass (SQLi) demo and secure prepared-statement example.
- `app/Scenario2/product.php`  - Scenario 2: Product listing, union-based SQLi demonstration and secure branch.
- `app/Scenario3/profile.php`  - Scenario 3: Blind (time-based) SQLi demo + safe simulation API and secure branch.

WAF and rules
- `waf/rules/10-sqlmap.conf` - Custom ModSecurity rules (detect sqlmap UA/payloads, per-IP counters, deny rules).
- `waf/conf.d/limit.conf`    - nginx rate-limiting configuration (applies request throttling at the proxy).

Useful scripts
- `scripts/test_waf.sh`       - Sends repeated sqlmap-like requests to demonstrate the WAF blocking behavior.
- `scripts/blind_extractor.py`- Safe demo extractor that uses `profile.php?simulate=1&probe=...` to practice blind/time-based extraction without creating a real SQLi hole.

Ports and credentials

- Web app (PHP/Apache): http://localhost:8080
- WAF proxy (ModSecurity/CRS): http://localhost:8090 (proxy to web)
- phpMyAdmin: http://localhost:8081 (user: `root`, password: `root`)
- MySQL (inside container): root / root, default DB `sqli_demo` (the `init/init.sql` creates tables and sample rows)

How to run (quick)

Prerequisites: Docker and Docker Compose available on the host.

Start the stack:

```bash
docker compose up -d
```

Check services:

```bash
docker compose ps
```

Reset DB (reread `init/`):

```bash
docker compose down -v
docker compose up -d
```

Access the app and tools in your browser:

- App (direct): http://localhost:8080
- App (through WAF): http://localhost:8090
- phpMyAdmin: http://localhost:8081

Scenario summaries and how to exercise them

- Scenario 1 — Login bypass (SQLi)
	- File: `app/Scenario1/login.php`.
	- Vulnerable form (concatenated SQL) demonstrates classic login bypass; the page also includes a secure prepared-statement example to compare behavior.
	- Test payload (vulnerable form): `username: ' OR '1'='1` (leave password blank).

- Scenario 2 — UNION-based SQLi
	- File: `app/Scenario2/product.php`.
	- Vulnerable branch injects `product_id` directly into SQL; example payload:
		`?product_id=-1 UNION SELECT 1,username,password FROM users--`
	- The scenario also includes a secure/parameterized branch to compare results.

- Scenario 3 — Blind (time-based) SQLi and WAF
	- File: `app/Scenario3/profile.php`.
	- For safe classroom practice this repo provides a server-side simulation API (no real attacker-supplied SQL execution):
		- `?simulate=1&probe=table|column|id|pos|ascii` — the server uses prepared statements to check the probe and sleeps 5s if true (OK), otherwise returns NO.
		- Example: `?simulate=1&probe=users|password|1|1|97` checks if the first char of `users.password` for id=1 is ASCII 97.
	- Use `scripts/blind_extractor.py` to automate timed probes and reconstruct values for lab demos.

WAF behavior and testing

- The repository includes a WAF container (`waf`) running OWASP ModSecurity CRS with custom rules in `waf/rules/`.
- Rules detect sqlmap-like User-Agent and SQLi payload patterns and block or rate-limit suspicious clients. Use `scripts/test_waf.sh` to simulate scanning and observe 403/429 responses.

Security notes

- All scenarios are intentionally insecure for learning. Never run this stack on a public network. Use an isolated lab or VM.
- The `simulate` API is a safe learning helper and does not execute attacker-supplied SQL; it only reads server-side data using prepared statements and intentionally delays responses to mimic time-based blind SQLi.

Troubleshooting & tips

- If services don't start: run `docker compose logs <service>` to see errors (for example `docker compose logs sqli_waf`).
- If the DB looks empty after starting, remove volumes and restart: `docker compose down -v` then `docker compose up -d`.
- Tune WAF thresholds in `waf/rules/10-sqlmap.conf` and `waf/conf.d/limit.conf` to match your lab needs.

Want me to do more?

- I can add per-scenario READMEs inside each `app/Scenario*` with step-by-step exercises and expected results.
- I can add an `init` change to seed more test accounts or secrets, or add faster extractor algorithms (binary search) to make the blind extractor finish faster in demos.

---

If you want, I can now write per-scenario step-by-step lab guides into each `app/Scenario*/README.md` — tell me which scenario to start with.



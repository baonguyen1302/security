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
  - Example: 
    + Attack:  sqlmap -u "http://localhost:8080/Scenario3/profile.php?user_id=1" --dump
    + Defense: sqlmap -u "http://localhost:8090/Scenario3/profile.php?user_id=1" --dump

- Scenario 4 — OOB (DNS) SQLi
  - File: `app/Scenario4/oob.php`
  - What: Out-of-band (OOB) SQL injection demonstration using DNS exfiltration. The vulnerable page shows how an attacker can coerce the database server to perform DNS lookups to an attacker-controlled domain and embed sensitive data into the DNS query (for example: password.attacker.com).
  - Example (vulnerable): `http://localhost:8080/Scenario4/oob.php?id=1 UNION SELECT database()--`
  - Defense: apply Least Privilege — remove or restrict database account ability to execute system/network functions (examples: revoke or disable xp_dirtree, xp_cmdshell, LOAD_FILE, or user-defined functions that perform network requests). Also use network egress filtering to block DB server DNS requests to untrusted hosts and monitor for unusual DNS queries.
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

## Hardening (OOB DNS) — how to run

I've added a small hardening/audit toolset under `scripts/` to help mitigate out-of-band (OOB) SQLi exfiltration via DNS. Review `scripts/HARDENING.md` before running anything.

Quick steps (English):

- Audit database privileges (recommended first):

```bash
# run the SQL audit in the DB container (uses root/root per README)
docker compose exec -T db mysql -uroot -proot < ./scripts/audit_db_privs.sq

# or use the helper script (non-interactive)
./scripts/audit_db_privs.sh
```

- Apply SQL hardening (edit `scripts/harden_mysql.sql` and replace 'appuser'@'host'):

```bash
docker compose exec -T db mysql -uroot -proot < ./scripts/harden_mysql.sql\

```

- Block DNS egress (host-level example) — dry-run first:

```bash
# dry-run (prints iptables commands)
DRY_RUN=1 ./scripts/block_db_dns.sh

# apply (run as root on the DB host; be careful)
DRY_RUN=0 sudo ./scripts/block_db_dns.sh
```

See `scripts/HARDENING.md` for details, caveats, and translation (VN/EN).

Ngắn gọn (Tiếng Việt):

- Kiểm tra quyền: chạy audit để tìm user có quyền nguy hiểm (FILE, SUPER, PROCESS, CREATE ROUTINE):

```bash
docker compose exec -T db mysql -uroot -proot < /app/scripts/audit_db_privs.sql
```

- Áp dụng hardening SQL (sửa `scripts/harden_mysql.sql` thay giá trị placeholder):

```bash
docker compose exec -T db mysql -uroot -proot < /app/scripts/harden_mysql.sql
```

- Chặn egress/DNS từ host DB (ví dụ iptables) — thử dry-run trước, sau đó áp dụng:

```bash
DRY_RUN=1 ./scripts/block_db_dns.sh
DRY_RUN=0 sudo ./scripts/block_db_dns.sh
```

Lưu ý: Thử trên môi trường staging trước khi áp dụng; một số thay đổi yêu cầu quyền root và/hoặc khởi động lại DB.

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
  - Example: 
    + Attack:  sqlmap -u "http://localhost:8080/Scenario3/profile.php?user_id=1" --dump
    + Defense: sqlmap -u "http://localhost:8090/Scenario3/profile.php?user_id=1" --dump
- Kịch bản 4 — OOB (DNS) SQLi
  - Tệp: `app/Scenario4/oob.php`
  - Nội dung: Minh họa Out-of-band (OOB) SQL injection dùng DNS để rò rỉ dữ liệu. Trang dễ bị tấn công cho thấy kẻ tấn công có thể khiến máy chủ cơ sở dữ liệu thực hiện tra cứu DNS tới tên miền do họ kiểm soát và nhúng dữ liệu nhạy cảm vào truy vấn DNS (ví dụ: password.attacker.com).
  - Ví dụ (vulnerable): `http://localhost:8080/Scenario4/oob.php?id=1 UNION SELECT database()--`
  - Phòng thủ: Áp dụng nguyên tắc Least Privilege — thu hồi hoặc giới hạn quyền của tài khoản DB thực hiện các hàm hệ thống/mạng (ví dụ: tắt hoặc thu hồi xp_dirtree, xp_cmdshell, LOAD_FILE, hoặc các UDF cho phép yêu cầu mạng). Bảo vệ mạng (chặn egress/DNS từ DB tới host không tin cậy) và giám sát các truy vấn DNS bất thường cũng giúp giảm rủi ro OOB exfiltration.
## Ghi chú bảo mật

- Các ví dụ này có mục đích học tập. Không chạy trên mạng công cộng.

---



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
- `Scenario 2/` - Second scenario (note the space in the folder name).
- `Scenario 3/` - Third scenario.
- `login.php`   - Example/login page (present at `app/login.php`).

Note: folder names are as provided — e.g. `Scenario 2/` contains a space.

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
# Using Docker Compose v2 (recommended)
docker compose up -d

# Or, if your system uses the older docker-compose binary:
docker-compose up -d
```

Verify services are running:

```bash
docker compose ps
```

Open the web app in your browser:

- Web app: http://localhost:8080
- phpMyAdmin: http://localhost:8081 (user: `root`, password: `root`)

The MySQL image will automatically run SQL files found in `init/` on first initialization. If you need to re-run the init scripts, remove the MySQL volume (see `docker compose down -v` below) and start the stack again.

Stop and remove containers and volumes (useful to reset DB state):

```bash
docker compose down -v
```

Tail logs (example for the PHP web container):

```bash
docker compose logs -f web
```

## Verifying the setup

1. Start the stack with `docker compose up -d`.
2. Visit http://localhost:8081 and log into phpMyAdmin using `root`/`root` to confirm the `sqli_demo` database exists and that any tables from `init/init.sql` were created.
3. Visit http://localhost:8080 to reach the vulnerable PHP pages (for educational testing only).

## Troubleshooting

- Port collisions: If ports 8080, 8081 or 3306 are already in use, stop the conflicting service or edit `docker-compose.yml` to change host ports.
- Permission issues with mounting `app/`: On some systems SELinux or Docker permission rules may prevent Apache from reading mounted files. Check container logs and adjust mount options or permissions.
- If the database looks empty after startup, you may need to remove the MySQL volume and restart so the init scripts run: `docker compose down -v` then `docker compose up -d`.

## Security notice

This repository contains intentionally insecure code for learning about SQL injection and related vulnerabilities. Do NOT run this exposed to the public internet. Only run locally in an isolated environment.

## Next steps / Suggestions

- Add a `README` inside each `Scenario*/` folder that explains what vulnerability or lesson that scenario demonstrates.
- Add a `.env.example` if you plan to parameterize credentials or ports.

If you'd like, I can also:

- Add small README files inside each `app/Scenario*` explaining what to test.
- Add a short script to wait for DB readiness before running tests.

-- End of file

## Tiếng Việt (Vietnamese)

Một kho chứa nhỏ với các ví dụ PHP có lỗ hổng (các kịch bản SQL injection) và cấu hình Docker Compose để chạy cục bộ phục vụ mục đích học tập và thực hành.

### Cấu trúc repository

Các file và thư mục cấp cao:

- `docker-compose.yml`  - Stack: `web` (PHP/Apache), `db` (MySQL 5.7), `phpmyadmin`.
- `README.md`          - File này.
- `init/`              - Các script SQL được đưa vào container MySQL (`init.sql`).
- `app/`               - Mã ứng dụng PHP và các thư mục kịch bản.

Trong `app/`:

- `Scenario1/`, `Scenario 2/`, `Scenario 3/` - các kịch bản dễ bị tổn thương để học về SQL injection.
- `login.php` - ví dụ trang đăng nhập.

Lưu ý: Một số tên thư mục có khoảng trắng (ví dụ `Scenario 2/`) — giữ nguyên như cấu trúc gốc.

### Những gì Docker Compose cung cấp

- `web`: PHP 8.2 + Apache, ánh xạ port host 8080 -> container 80. Thư mục `app/` được mount vào `/var/www/html`.
- `db`: MySQL 5.7, ánh xạ port host 3306 -> container 3306. Các file trong `init/` sẽ được chạy khi container khởi tạo lần đầu. Cơ sở dữ liệu mặc định: `sqli_demo`.
- `phpmyadmin`: giao diện quản trị MySQL trên port host 8081.

Thông tin đăng nhập mặc định (theo `docker-compose.yml`):

- Người dùng root: `root`
- Mật khẩu root: `root`
- Database: `sqli_demo`

### Cách chạy (tóm tắt)

Yêu cầu trước:

- Cài Docker và Docker Compose. Trên nhiều hệ Linux lệnh là `docker` và `docker compose`. Nếu bạn dùng cũ hơn, dùng `docker-compose`.

Khởi động stack dưới nền:

```bash
docker compose up -d
# hoặc
docker-compose up -d
```

Kiểm tra trạng thái:

```bash
docker compose ps
```

Truy cập:

- Ứng dụng web: http://localhost:8080
- phpMyAdmin: http://localhost:8081 (user: `root`, pass: `root`)

Để đặt lại trạng thái DB và chạy lại các script init, dừng stack và xóa volume MySQL rồi khởi động lại:

```bash
docker compose down -v
docker compose up -d
```

### Kiểm tra và khắc phục

1. Khởi động với `docker compose up -d`.
2. Mở phpMyAdmin trên http://localhost:8081 và đăng nhập `root`/`root` để kiểm tra `sqli_demo`.
3. Mở http://localhost:8080 để thử các trang PHP (chỉ phục vụ mục đích học tập).

Vấn đề thường gặp:

- Trùng port: nếu 8080/8081/3306 đã sử dụng, tắt dịch vụ đó hoặc sửa `docker-compose.yml` để đổi port.
- Quyền truy cập file khi mount `app/`: một số hệ có SELinux hoặc quyền file gây lỗi. Kiểm tra log container.
- Nếu DB rỗng sau khi khởi động, xóa volume MySQL rồi khởi động lại để init scripts chạy.

### Lưu ý bảo mật

Kho này chứa mã có lỗ hổng cố ý để học SQL injection. Không để chạy trên mạng công cộng.

### Bước tiếp theo (đề xuất)

- Thêm `README` cho từng `app/Scenario*` giải thích bài học của mỗi kịch bản.
- Thêm `.env.example` nếu muốn cấu hình biến môi trường (port, mật khẩu).

-- Phần kết


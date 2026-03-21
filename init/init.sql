-- Tạo database
CREATE DATABASE IF NOT EXISTS sqli_demo;
USE sqli_demo;

-- =========================
-- TABLE 1: USERS
-- =========================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    password VARCHAR(50),
    email VARCHAR(100),
    full_name VARCHAR(100),
    phone VARCHAR(20),
    address VARCHAR(255),
    role VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Dữ liệu mẫu (đa dạng + có admin)
INSERT INTO users (username, password, email, full_name, phone, address, role) VALUES
('admin', '123456', 'admin@gmail.com', 'Nguyen Van Admin', '0900000001', 'Ha Noi', 'admin'),
('user1', 'password', 'user1@gmail.com', 'Tran Thi A', '0900000002', 'Ho Chi Minh', 'user'),
('user2', '123456', 'user2@gmail.com', 'Le Van B', '0900000003', 'Da Nang', 'user'),
('user3', 'abc123', 'user3@gmail.com', 'Pham Van C', '0900000004', 'Can Tho', 'user'),
('manager', 'manager123', 'manager@gmail.com', 'Hoang Thi D', '0900000005', 'Hai Phong', 'manager'),
('test', 'test123', 'test@gmail.com', 'Test User', '0900000006', 'Hue', 'user');

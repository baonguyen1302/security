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

-- =========================
-- TABLE 2: PRODUCTS
-- =========================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    description TEXT,
    category VARCHAR(50),
    price INT,
    stock INT
);

INSERT INTO products (name, description, category, price, stock) VALUES
('Laptop Dell XPS', 'High-end laptop', 'Laptop', 2000, 10),
('iPhone 14', 'Apple smartphone', 'Phone', 1200, 20),
('Samsung Galaxy S23', 'Android flagship', 'Phone', 1000, 15),
('iPad Pro', 'Apple tablet', 'Tablet', 900, 12),
('Gaming PC', 'Custom gaming PC', 'PC', 2500, 5),
('Headphones Sony', 'Noise cancelling', 'Accessory', 300, 50),
('Mechanical Keyboard', 'RGB keyboard', 'Accessory', 150, 40);

-- =========================
-- TABLE 3: ORDERS
-- =========================
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    quantity INT,
    total_price INT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO orders (user_id, product_id, quantity, total_price) VALUES
(1, 1, 1, 2000),
(2, 2, 2, 2400),
(3, 3, 1, 1000),
(4, 4, 1, 900),
(5, 5, 1, 2500);

-- =========================
-- TABLE 4: SECRET (dữ liệu nhạy cảm để demo hack)
-- =========================
CREATE TABLE secrets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    secret_name VARCHAR(100),
    secret_value VARCHAR(255)
);

INSERT INTO secrets (secret_name, secret_value) VALUES
('admin_password_backup', 'admin@123'),
('api_key', 'ABC123-SECRET-KEY'),
('jwt_secret', 'super_secret_jwt_key'),
('database_backup', 'backup_2025.sql');

CREATE TABLE secrets (
id INT AUTO_INCREMENT PRIMARY KEY,
secret_data VARCHAR(100)
);

INSERT INTO secrets(secret_data) VALUES
('admin_password_123'),
('db_backup_key'),
('jwt_secret_token');
-- Inventory Management System - schema + seed data
-- Import: mysql -u root -p < database/schema.sql
CREATE DATABASE IF NOT EXISTS inventory_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventory_db;

DROP TABLE IF EXISTS stock_movements, product_suppliers, products, suppliers, categories, user_profiles, users;

CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','staff') NOT NULL DEFAULT 'staff',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- One-to-one: satu user memiliki satu profile (FK + UNIQUE)
CREATE TABLE user_profiles (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL UNIQUE,
  full_name VARCHAR(100) NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE categories (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE suppliers (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  phone VARCHAR(30) NULL
) ENGINE=InnoDB;

-- One-to-many: categories -> products
CREATE TABLE products (
  id INT PRIMARY KEY AUTO_INCREMENT,
  category_id INT NOT NULL,
  sku VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  price DECIMAL(12,2) NOT NULL CHECK (price >= 0),
  stock INT NOT NULL DEFAULT 0 CHECK (stock >= 0),
  minimum_stock INT NOT NULL DEFAULT 5 CHECK (minimum_stock >= 0),
  FOREIGN KEY (category_id) REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Many-to-many: products <-> suppliers (junction table, composite PK)
CREATE TABLE product_suppliers (
  product_id INT NOT NULL,
  supplier_id INT NOT NULL,
  supplier_price DECIMAL(12,2) NULL,
  PRIMARY KEY (product_id, supplier_id),
  FOREIGN KEY (product_id) REFERENCES products(id) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- Histori transaksi: RESTRICT agar histori tidak hilang
CREATE TABLE stock_movements (
  id INT PRIMARY KEY AUTO_INCREMENT,
  product_id INT NOT NULL,
  user_id INT NOT NULL,
  type ENUM('IN','OUT') NOT NULL,
  quantity INT NOT NULL CHECK (quantity > 0),
  note VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ===== Seed data =====
-- Password: admin123 (admin) dan staff123 (staff); hash dibuat dengan password_hash()
INSERT INTO users (username, password_hash, role) VALUES
('admin', '$2y$10$dZqTosw/UnnxETheOJ9bw.Lvkxzphv00ROy4hINNZht30K6uei8Z2', 'admin'),
('staff', '$2y$10$QnZBBC0q8N45SaAmX89aNeww37MjNMXnL.fFuZ9hooUFTrNzhKgcS', 'staff');
INSERT INTO user_profiles (user_id, full_name) VALUES (1,'Administrator Toko'),(2,'Staff Gudang');

INSERT INTO categories (name) VALUES ('Keyboard'),('Mouse'),('Monitor'),('Aksesoris');
INSERT INTO suppliers (name, phone) VALUES ('PT Maju Jaya','021-555-0101'),('CV Sinar Teknologi','022-555-0202');
INSERT INTO products (category_id, sku, name, price, stock, minimum_stock) VALUES
(1,'KB-001','Keyboard Mechanical RGB',450000,20,5),
(1,'KB-002','Keyboard Wireless Slim',250000,3,5),
(2,'MS-001','Mouse Gaming 7200 DPI',200000,15,5),
(2,'MS-002','Mouse Wireless Office',90000,2,5),
(3,'MN-001','Monitor 24 inch IPS',1800000,8,3),
(4,'AC-001','Mousepad XL',75000,40,10),
(4,'AC-002','Kabel HDMI 2m',45000,12,10);
INSERT INTO product_suppliers VALUES (1,1,380000),(1,2,390000),(3,1,160000),(5,2,1600000);
INSERT INTO stock_movements (product_id, user_id, type, quantity, note) VALUES
(1,1,'IN',20,'Stok awal'),(2,1,'IN',3,'Stok awal'),(3,1,'IN',15,'Stok awal'),
(4,1,'IN',2,'Stok awal'),(5,1,'IN',8,'Stok awal'),(6,1,'IN',40,'Stok awal'),(7,1,'IN',12,'Stok awal');

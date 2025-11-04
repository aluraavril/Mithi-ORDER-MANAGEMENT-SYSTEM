CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    is_admin TINYINT(1) NOT NULL DEFAULT 0,
    password VARCHAR(255) NOT NULL,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_is_admin (is_admin)
) 

ALTER TABLE users ADD COLUMN is_suspended TINYINT(1) DEFAULT 0;

CREATE TABLE IF NOT EXISTS products (
  id INT(11) AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  added_by_name VARCHAR(255) NOT NULL,
  added_by_role ENUM('Admin', 'Cashier') NOT NULL,
  date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE products 
ADD COLUMN image VARCHAR(255) DEFAULT NULL,
ADD COLUMN category VARCHAR(50) DEFAULT 'Food';

CREATE TABLE IF NOT EXISTS orders (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    items TEXT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    cashier_name VARCHAR(255) NOT NULL,
    date_ordered TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE orders ADD COLUMN voided TINYINT(1) DEFAULT 0;


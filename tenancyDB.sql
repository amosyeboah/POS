CREATE DATABASE IF NOT EXISTS mobpos;
USE mobpos;

-- Create tenants table if not already created
CREATE TABLE IF NOT EXISTS tenants (
    tenant_id INT AUTO_INCREMENT PRIMARY KEY,
    business_name VARCHAR(100) NOT NULL,
    contact_name VARCHAR(100),
    contact_email VARCHAR(100),
    contact_phone VARCHAR(20),
    address TEXT,
    industry VARCHAR(100),
    plan_type ENUM('free', 'basic', 'pro') DEFAULT 'free',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
ALTER TABLE tenants ADD COLUMN currency_type VARCHAR(10) DEFAULT 'GHS';
ALTER TABLE tenants ADD COLUMN setup_complete BOOLEAN DEFAULT FALSE;



-- Users Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    username VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('cashier', 'admin') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (tenant_id, username),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id)
);

-- Categories Table
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    parent_id INT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(category_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id)
);

-- Products Table
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    barcode VARCHAR(50),
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category_id INT,
    price DECIMAL(10,2) NOT NULL,
    cost DECIMAL(10,2),
    stock INT NOT NULL DEFAULT 0,
    min_stock INT DEFAULT 5,
    image_path VARCHAR(255),
    tax_rate DECIMAL(5,2) DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (tenant_id, barcode),
    FOREIGN KEY (category_id) REFERENCES categories(category_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id)
);

-- Customers Table
CREATE TABLE customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    loyalty_points INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id)
);


-- Sales Table
CREATE TABLE sales (
    sale_id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    user_id INT NOT NULL,
    customer_id INT,
    transaction_code VARCHAR(20) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'card', 'mobile', 'mixed') NOT NULL,
    payment_details TEXT,
    status ENUM('completed', 'refunded', 'cancelled') DEFAULT 'completed',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (tenant_id, transaction_code),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id)
);

-- Sale Items Table
CREATE TABLE sale_items (
    sale_item_id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0,
    tax_rate DECIMAL(5,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(sale_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Inventory Logs Table
CREATE TABLE inventory_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    quantity_change INT NOT NULL,
    previous_stock INT NOT NULL,
    new_stock INT NOT NULL,
    type ENUM('purchase', 'sale', 'return', 'adjustment', 'damage') NOT NULL,
    reference_id INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id)
);

-- Shifts Table
CREATE TABLE shifts (
    shift_id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    user_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME,
    starting_cash DECIMAL(10,2) NOT NULL,
    ending_cash DECIMAL(10,2),
    status ENUM('open', 'closed') DEFAULT 'open',
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id)
);

-- Settings Table
CREATE TABLE settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT NOT NULL,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (tenant_id, setting_key),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id)
);



-- DATA FOR DATABASE

INSERT INTO tenants (business_name, contact_name, contact_email, contact_phone, address, industry, plan_type, status)
VALUES
('FreshMart Supermarket', 'Kwame Mensah', 'kwame@freshmart.com', '0541002001', 'Kumasi - Adum', 'Retail - Grocery', 'pro', 'active'),
('GadgetHub Electronics', 'Ama Owusu', 'ama@gadgethub.com', '0249876543', 'Accra - Osu', 'Retail - Electronics', 'basic', 'active'),
('SweetBites Bakery', 'Akosua Dede', 'akosua@sweetbites.com', '0501122334', 'Tema - Community 1', 'Food & Beverage', 'free', 'active');


INSERT INTO users (username, password_hash, role, full_name, email, phone, tenant_id)
VALUES
-- FreshMart Supermarket Users
('freshadmin', '$2y$10$IUu5e3LZPESXJVKqDQo.QOXz25NswkSfOk1FTWK6bx7KlO9VkJVVG', 'admin', 'Kwame Mensah', 'kwame@freshmart.com', '0541002001', 1),
('freshcashier1', '$2y$10$IUu5e3LZPESXJVKqDQo.QOXz25NswkSfOk1FTWK6bx7KlO9VkJVVG', 'cashier', 'Emmanuel Asare', 'emma@freshmart.com', '0541234567', 1),

-- GadgetHub Electronics Users
('gadgetadmin', '$2y$10$IUu5e3LZPESXJVKqDQo.QOXz25NswkSfOk1FTWK6bx7KlO9VkJVVG', 'admin', 'Ama Owusu', 'ama@gadgethub.com', '0249876543', 2),

-- SweetBites Bakery Users
('sweetadmin', '$2y$10$IUu5e3LZPESXJVKqDQo.QOXz25NswkSfOk1FTWK6bx7KlO9VkJVVG', 'admin', 'Akosua Dede', 'akosua@sweetbites.com', '0501122334', 3);



-- Categories for FreshMart (tenant_id = 1)
INSERT INTO categories (name, description, tenant_id) VALUES
('Fruits', 'Fresh fruits', 1),
('Dairy', 'Milk, cheese, yogurt', 1);

-- Products for FreshMart
INSERT INTO products (barcode, name, category_id, price, cost, stock, tenant_id) VALUES
('FM123456', 'Bananas (1lb)', 1, 0.99, 0.50, 150, 1),
('FM654321', 'Whole Milk 1L', 2, 2.99, 1.80, 100, 1);


-- Categories for GadgetHub (tenant_id = 2)
INSERT INTO categories (name, description, tenant_id) VALUES
('Phones', 'Smartphones', 2),
('Accessories', 'Phone accessories', 2);

-- Products for GadgetHub
INSERT INTO products (barcode, name, category_id, price, cost, stock, tenant_id) VALUES
('GH987654', 'Samsung Galaxy A34', 3, 399.00, 310.00, 50, 2),
('GH123789', 'iPhone 13 Case', 4, 19.99, 10.00, 120, 2);


-- Categories for SweetBites (tenant_id = 3)
INSERT INTO categories (name, description, tenant_id) VALUES
('Breads', 'Fresh baked breads', 3),
('Cakes', 'Special cakes', 3);

-- Products for SweetBites
INSERT INTO products (barcode, name, category_id, price, cost, stock, tenant_id) VALUES
('SB111222', 'Whole Wheat Bread', 5, 4.50, 2.20, 40, 3),
('SB333444', 'Birthday Cake 1kg', 6, 20.00, 12.00, 15, 3);


-- Settings
INSERT INTO settings (setting_key, setting_value, description, tenant_id)
VALUES
-- FreshMart
('store_name', 'FreshMart Supermarket', 'Display name of the store', 1),
('currency', 'GHS', 'Ghana Cedi', 1),

-- GadgetHub
('store_name', 'GadgetHub Electronics', 'Display name of the store', 2),
('currency', 'GHS', 'Ghana Cedi', 2),

-- SweetBites
('store_name', 'SweetBites Bakery', 'Display name of the store', 3),
('currency', 'GHS', 'Ghana Cedi', 3);

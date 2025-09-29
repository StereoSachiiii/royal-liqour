-- ===================================================================
-- COMPLETE LIQUOR E-COMMERCE DATABASE SCHEMA
-- Features: Multi-address support, Views, Stored Procedures, Transactions, 
--           AJAX queries, Soft/Hard deletes, Full CRUD operations
-- Stack: Legacy PHP/HTML/CSS/MySQL
-- ===================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 1;
SET time_zone = "+00:00";

-- ===================================================================
-- USERS TABLE
-- ===================================================================
CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(254) NOT NULL,
    phone VARCHAR(15) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    profile_image_url VARCHAR(500) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_admin TINYINT(1) NOT NULL DEFAULT 0,
    is_anonymized TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL DEFAULT NULL,
    anonymized_at DATETIME NULL DEFAULT NULL,
    last_login_at DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_users_email (email),
    KEY idx_users_active (is_active, deleted_at),
    KEY idx_users_login (email, password_hash, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- USER ADDRESSES TABLE (Multiple addresses per user)
-- ===================================================================
CREATE TABLE user_addresses (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    address_type ENUM('billing','shipping','both') NOT NULL DEFAULT 'both',
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255) DEFAULT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) DEFAULT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL DEFAULT 'Sri Lanka',
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_addresses_user (user_id, is_active),
    KEY idx_addresses_default (user_id, is_default, address_type),
    KEY idx_addresses_active (is_active, deleted_at),
    FOREIGN KEY fk_addresses_user (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- SUPPLIERS TABLE
-- ===================================================================
CREATE TABLE suppliers (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(254) DEFAULT NULL,
    phone VARCHAR(15) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_suppliers_name (name),
    KEY idx_suppliers_active (is_active, deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- CATEGORIES TABLE
-- ===================================================================
CREATE TABLE categories (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    image_url VARCHAR(500) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_categories_name (name),
    KEY idx_categories_active (is_active, deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- WAREHOUSES TABLE
-- ===================================================================
CREATE TABLE warehouses (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    address TEXT DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_warehouses_name (name),
    KEY idx_warehouses_active (is_active, deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- PRODUCTS TABLE
-- ===================================================================
CREATE TABLE products (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    price DECIMAL(12,2) NOT NULL CHECK (price > 0),
    image_url VARCHAR(500) DEFAULT NULL,
    category_id INT(11) NOT NULL,
    supplier_id INT(11) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_products_active (is_active, deleted_at),
    KEY idx_products_category (category_id, is_active),
    KEY idx_products_supplier (supplier_id),
    KEY idx_products_price (price, is_active),
    KEY idx_products_name (name, is_active),
    FOREIGN KEY fk_products_category (category_id) REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY fk_products_supplier (supplier_id) REFERENCES suppliers(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- STOCK TABLE
CREATE TABLE stock (
    id INT(11) NOT NULL AUTO_INCREMENT,
    product_id INT(11) NOT NULL,
    warehouse_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL DEFAULT 0 CHECK (quantity >= 0),
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_stock_product_warehouse (product_id, warehouse_id),
    KEY idx_stock_product (product_id, is_active),
    KEY idx_stock_warehouse (warehouse_id, is_active),
    KEY idx_stock_quantity (quantity, is_active),
    FOREIGN KEY fk_stock_product (product_id) REFERENCES products(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY fk_stock_warehouse (warehouse_id) REFERENCES warehouses(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- ORDERS TABLE
-- ===================================================================
CREATE TABLE orders (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    shipping_address_id INT(11) DEFAULT NULL,
    billing_address_id INT(11) DEFAULT NULL,
    status ENUM('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending',
    total DECIMAL(12,2) NOT NULL CHECK (total >= 0),
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_anonymized TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL DEFAULT NULL,
    anonymized_at DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_orders_user (user_id, created_at),
    KEY idx_orders_status (status, created_at),
    KEY idx_orders_active (is_active, deleted_at),
    KEY idx_orders_shipping (shipping_address_id),
    KEY idx_orders_billing (billing_address_id),
    FOREIGN KEY fk_orders_user (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY fk_orders_shipping (shipping_address_id) REFERENCES user_addresses(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY fk_orders_billing (billing_address_id) REFERENCES user_addresses(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- ORDER ITEMS TABLE
-- ===================================================================
CREATE TABLE order_items (
    id INT(11) NOT NULL AUTO_INCREMENT,
    order_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL CHECK (quantity > 0),
    price DECIMAL(12,2) NOT NULL CHECK (price > 0),
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_order_items (order_id, product_id),
    KEY idx_order_items_order (order_id),
    KEY idx_order_items_product (product_id),
    FOREIGN KEY fk_order_items_order (order_id) REFERENCES orders(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY fk_order_items_product (product_id) REFERENCES products(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;










CREATE TABLE rate_limiting (
       id INT(11) AUTO_INCREMENT PRIMARY KEY,
       identifier VARCHAR(255) NOT NULL,
       attempt_time DATETIME NOT NULL,
       KEY idx_identifier_time (identifier, attempt_time)
   );
-- ===================================================================
-- VIEWS FOR AJAX QUERIES
-- ===================================================================

-- Active Products with Category and Stock Info (AJAX Ready)
CREATE OR REPLACE VIEW vw_active_products AS
SELECT 
    p.id,
    p.name,
    p.description,
    p.price,
    p.image_url,
    p.category_id,
    c.name AS category_name,
    p.supplier_id,
    s.name AS supplier_name,
    COALESCE(SUM(st.quantity), 0) AS total_stock,
    p.is_active,
    p.created_at,
    p.updated_at
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN suppliers s ON p.supplier_id = s.id
LEFT JOIN stock st ON p.id = st.product_id AND st.is_active = 1
WHERE p.deleted_at IS NULL 
    AND p.is_active = 1
    AND c.deleted_at IS NULL
    AND c.is_active = 1
GROUP BY p.id, p.name, p.description, p.price, p.image_url, 
         p.category_id, c.name, p.supplier_id, s.name, 
         p.is_active, p.created_at, p.updated_at;

-- Products Search View (for AJAX autocomplete)
CREATE OR REPLACE VIEW vw_products_search AS
SELECT 
    p.id,
    p.name AS product_name,
    c.name AS category_name,
    p.price,
    COALESCE(SUM(st.quantity), 0) AS stock,
    p.image_url
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN stock st ON p.id = st.product_id AND st.is_active = 1
WHERE p.deleted_at IS NULL 
    AND p.is_active = 1
    AND c.deleted_at IS NULL
    AND c.is_active = 1
GROUP BY p.id, p.name, c.name, p.price, p.image_url;

-- User Orders Summary View
CREATE OR REPLACE VIEW vw_user_orders AS
SELECT 
    o.id AS order_id,
    o.user_id,
    u.name AS user_name,
    u.email AS user_email,
    o.status,
    o.total,
    o.created_at AS order_date,
    COUNT(oi.id) AS item_count,
    CONCAT(sa.address_line1, ', ', sa.city, ' ', sa.postal_code) AS shipping_address
FROM orders o
JOIN users u ON o.user_id = u.id
LEFT JOIN order_items oi ON o.id = oi.order_id
LEFT JOIN user_addresses sa ON o.shipping_address_id = sa.id
WHERE o.deleted_at IS NULL 
    AND o.is_active = 1
    AND u.deleted_at IS NULL
GROUP BY o.id, o.user_id, u.name, u.email, o.status, 
         o.total, o.created_at, sa.address_line1, sa.city, sa.postal_code;

-- Order Details View
CREATE OR REPLACE VIEW vw_order_details AS
SELECT 
    o.id AS order_id,
    o.user_id,
    u.name AS user_name,
    u.email AS user_email,
    u.phone AS user_phone,
    oi.id AS order_item_id,
    oi.product_id,
    p.name AS product_name,
    oi.quantity,
    oi.price,
    (oi.quantity * oi.price) AS item_total,
    o.status,
    o.total AS order_total,
    o.created_at AS order_date
FROM orders o
JOIN users u ON o.user_id = u.id
JOIN order_items oi ON o.id = oi.order_id
JOIN products p ON oi.product_id = p.id
WHERE o.deleted_at IS NULL 
    AND o.is_active = 1
    AND u.deleted_at IS NULL
    AND p.deleted_at IS NULL;

-- Category Products Count View
CREATE OR REPLACE VIEW vw_category_stats AS
SELECT 
    c.id AS category_id,
    c.name AS category_name,
    c.description,
    c.image_url,
    COUNT(p.id) AS product_count,
    COALESCE(MIN(p.price), 0) AS min_price,
    COALESCE(MAX(p.price), 0) AS max_price,
    COALESCE(AVG(p.price), 0) AS avg_price
FROM categories c
LEFT JOIN products p ON c.id = p.category_id 
    AND p.deleted_at IS NULL 
    AND p.is_active = 1
WHERE c.deleted_at IS NULL 
    AND c.is_active = 1
GROUP BY c.id, c.name, c.description, c.image_url;

-- Low Stock Alert View
CREATE OR REPLACE VIEW vw_low_stock_products AS
SELECT 
    p.id,
    p.name,
    c.name AS category_name,
    w.name AS warehouse_name,
    st.quantity,
    p.price
FROM stock st
JOIN products p ON st.product_id = p.id
JOIN categories c ON p.category_id = c.id
JOIN warehouses w ON st.warehouse_id = w.id
WHERE st.quantity < 10
    AND st.is_active = 1
    AND p.deleted_at IS NULL 
    AND p.is_active = 1
    AND w.deleted_at IS NULL
    AND w.is_active = 1
ORDER BY st.quantity ASC;

-- User Addresses View
CREATE OR REPLACE VIEW vw_user_addresses AS
SELECT 
    ua.id,
    ua.user_id,
    u.name AS user_name,
    u.email AS user_email,
    ua.address_type,
    CONCAT(ua.address_line1, 
           CASE WHEN ua.address_line2 IS NOT NULL THEN CONCAT(', ', ua.address_line2) ELSE '' END,
           ', ', ua.city,
           CASE WHEN ua.state IS NOT NULL THEN CONCAT(', ', ua.state) ELSE '' END,
           ' ', ua.postal_code,
           ', ', ua.country) AS full_address,
    ua.is_default,
    ua.is_active,
    ua.created_at
FROM user_addresses ua
JOIN users u ON ua.user_id = u.id
WHERE ua.deleted_at IS NULL
    AND u.deleted_at IS NULL;

-- ===================================================================
-- STORED PROCEDURES
-- ===================================================================

DELIMITER $$

-- ===================================================================
-- PRODUCT PROCEDURES
-- ===================================================================

-- Create Product with Transaction
CREATE PROCEDURE sp_create_product(
    IN p_name VARCHAR(200),
    IN p_description TEXT,
    IN p_price DECIMAL(12,2),
    IN p_image_url VARCHAR(500),
    IN p_category_id INT,
    IN p_supplier_id INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    INSERT INTO products (name, description, price, image_url, category_id, supplier_id)
    VALUES (p_name, p_description, p_price, p_image_url, p_category_id, p_supplier_id);
    
    COMMIT;
    
    SELECT LAST_INSERT_ID() AS product_id;
END$$

-- Update Product
CREATE PROCEDURE sp_update_product(
    IN p_id INT,
    IN p_name VARCHAR(200),
    IN p_description TEXT,
    IN p_price DECIMAL(12,2),
    IN p_image_url VARCHAR(500),
    IN p_category_id INT,
    IN p_supplier_id INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    UPDATE products 
    SET name = p_name,
        description = p_description,
        price = p_price,
        image_url = p_image_url,
        category_id = p_category_id,
        supplier_id = p_supplier_id
    WHERE id = p_id 
        AND deleted_at IS NULL;
    
    COMMIT;
    
    SELECT ROW_COUNT() AS affected_rows;
END$$

-- Soft Delete Product
CREATE PROCEDURE sp_soft_delete_product(IN p_id INT)
BEGIN
    UPDATE products 
    SET deleted_at = CURRENT_TIMESTAMP,
        is_active = 0
    WHERE id = p_id 
        AND deleted_at IS NULL;
    
    SELECT ROW_COUNT() AS affected_rows;
END$$

-- Hard Delete Product (Permanent)
CREATE PROCEDURE sp_hard_delete_product(IN p_id INT)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Delete related order items first
    DELETE FROM order_items WHERE product_id = p_id;
    
    -- Delete stock records
    DELETE FROM stock WHERE product_id = p_id;
    
    -- Finally delete product
    DELETE FROM products WHERE id = p_id;
    
    COMMIT;
    
    SELECT ROW_COUNT() AS affected_rows;
END$$

-- Restore Soft Deleted Product
CREATE PROCEDURE sp_restore_product(IN p_id INT)
BEGIN
    UPDATE products 
    SET deleted_at = NULL,
        is_active = 1
    WHERE id = p_id 
        AND deleted_at IS NOT NULL;
    
    SELECT ROW_COUNT() AS affected_rows;
END$$

-- Search Products (AJAX Ready)
CREATE PROCEDURE sp_search_products(
    IN p_search_term VARCHAR(200),
    IN p_category_id INT,
    IN p_min_price DECIMAL(12,2),
    IN p_max_price DECIMAL(12,2),
    IN p_sort_by VARCHAR(20),
    IN p_sort_order VARCHAR(4),
    IN p_limit INT,
    IN p_offset INT
)
BEGIN
    SET @sql = 'SELECT * FROM vw_active_products WHERE 1=1';
    
    IF p_search_term IS NOT NULL AND p_search_term != '' THEN
        SET @sql = CONCAT(@sql, ' AND (name LIKE "%', p_search_term, '%" OR description LIKE "%', p_search_term, '%")');
    END IF;
    
    IF p_category_id IS NOT NULL AND p_category_id > 0 THEN
        SET @sql = CONCAT(@sql, ' AND category_id = ', p_category_id);
    END IF;
    
    IF p_min_price IS NOT NULL AND p_min_price > 0 THEN
        SET @sql = CONCAT(@sql, ' AND price >= ', p_min_price);
    END IF;
    
    IF p_max_price IS NOT NULL AND p_max_price > 0 THEN
        SET @sql = CONCAT(@sql, ' AND price <= ', p_max_price);
    END IF;
    
    SET @sort_column = COALESCE(p_sort_by, 'created_at');
    SET @sort_direction = COALESCE(p_sort_order, 'DESC');
    
    SET @sql = CONCAT(@sql, ' ORDER BY ', @sort_column, ' ', @sort_direction);
    
    IF p_limit IS NOT NULL AND p_limit > 0 THEN
        SET @sql = CONCAT(@sql, ' LIMIT ', p_limit);
        
        IF p_offset IS NOT NULL AND p_offset >= 0 THEN
            SET @sql = CONCAT(@sql, ' OFFSET ', p_offset);
        END IF;
    END IF;
    
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END$$

-- Get Products by Price Range (AJAX Filter)
CREATE PROCEDURE sp_filter_products_by_price(
    IN p_min_price DECIMAL(12,2),
    IN p_max_price DECIMAL(12,2)
)
BEGIN
    SELECT * FROM vw_active_products
    WHERE price BETWEEN p_min_price AND p_max_price
    ORDER BY price ASC;
END$$

-- Get Products A-Z (AJAX Filter)
CREATE PROCEDURE sp_filter_products_alphabetical(
    IN p_order VARCHAR(4)
)
BEGIN
    IF p_order = 'DESC' THEN
        SELECT * FROM vw_active_products ORDER BY name DESC;
    ELSE
        SELECT * FROM vw_active_products ORDER BY name ASC;
    END IF;
END$$

-- ===================================================================
-- ORDER PROCEDURES
-- ===================================================================

-- Create Order with Items (Transaction)
CREATE PROCEDURE sp_create_order(
    IN p_user_id INT,
    IN p_shipping_address_id INT,
    IN p_billing_address_id INT,
    IN p_items JSON
)
BEGIN
    DECLARE v_order_id INT;
    DECLARE v_total DECIMAL(12,2) DEFAULT 0;
    DECLARE v_product_id INT;
    DECLARE v_quantity INT;
    DECLARE v_price DECIMAL(12,2);
    DECLARE v_idx INT DEFAULT 0;
    DECLARE v_count INT;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Create order
    INSERT INTO orders (user_id, shipping_address_id, billing_address_id, total)
    VALUES (p_user_id, p_shipping_address_id, p_billing_address_id, 0);
    
    SET v_order_id = LAST_INSERT_ID();
    SET v_count = JSON_LENGTH(p_items);
    
    -- Insert order items
    WHILE v_idx < v_count DO
        SET v_product_id = JSON_UNQUOTE(JSON_EXTRACT(p_items, CONCAT('$[', v_idx, '].product_id')));
        SET v_quantity = JSON_UNQUOTE(JSON_EXTRACT(p_items, CONCAT('$[', v_idx, '].quantity')));
        
        -- Get current product price
        SELECT price INTO v_price FROM products WHERE id = v_product_id AND deleted_at IS NULL;
        
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (v_order_id, v_product_id, v_quantity, v_price);
        
        SET v_total = v_total + (v_price * v_quantity);
        SET v_idx = v_idx + 1;
    END WHILE;
    
    -- Update order total
    UPDATE orders SET total = v_total WHERE id = v_order_id;
    
    COMMIT;
    
    SELECT v_order_id AS order_id, v_total AS total;
END$$

-- Update Order Status
CREATE PROCEDURE sp_update_order_status(
    IN p_order_id INT,
    IN p_status ENUM('pending','processing','completed','cancelled')
)
BEGIN
    UPDATE orders 
    SET status = p_status
    WHERE id = p_order_id 
        AND deleted_at IS NULL;
    
    SELECT ROW_COUNT() AS affected_rows;
END$$

-- Soft Delete Order
CREATE PROCEDURE sp_soft_delete_order(IN p_order_id INT)
BEGIN
    UPDATE orders 
    SET deleted_at = CURRENT_TIMESTAMP,
        is_active = 0
    WHERE id = p_order_id 
        AND deleted_at IS NULL;
    
    SELECT ROW_COUNT() AS affected_rows;
END$$

-- Hard Delete Order
CREATE PROCEDURE sp_hard_delete_order(IN p_order_id INT)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Delete order items
    DELETE FROM order_items WHERE order_id = p_order_id;
    
    -- Delete order
    DELETE FROM orders WHERE id = p_order_id;
    
    COMMIT;
    
    SELECT ROW_COUNT() AS affected_rows;
END$$

-- Get User Orders (AJAX Ready)
CREATE PROCEDURE sp_get_user_orders(
    IN p_user_id INT,
    IN p_status VARCHAR(20),
    IN p_limit INT,
    IN p_offset INT
)
BEGIN
    IF p_status IS NOT NULL AND p_status != '' THEN
        SELECT * FROM vw_user_orders
        WHERE user_id = p_user_id 
            AND status = p_status
        ORDER BY order_date DESC
        LIMIT p_limit OFFSET p_offset;
    ELSE
        SELECT * FROM vw_user_orders
        WHERE user_id = p_user_id
        ORDER BY order_date DESC
        LIMIT p_limit OFFSET p_offset;
    END IF;
END$$

-- ===================================================================
-- STOCK PROCEDURES
-- ===================================================================

-- Update Stock (Transaction)
CREATE PROCEDURE sp_update_stock(
    IN p_product_id INT,
    IN p_warehouse_id INT,
    IN p_quantity INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    INSERT INTO stock (product_id, warehouse_id, quantity)
    VALUES (p_product_id, p_warehouse_id, p_quantity)
    ON DUPLICATE KEY UPDATE 
        quantity = p_quantity,
        updated_at = CURRENT_TIMESTAMP;
    
    COMMIT;
    
    SELECT ROW_COUNT() AS affected_rows;
END$$

-- Adjust Stock (Add/Subtract)
CREATE PROCEDURE sp_adjust_stock(
    IN p_product_id INT,
    IN p_warehouse_id INT,
    IN p_adjustment INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    UPDATE stock 
    SET quantity = quantity + p_adjustment
    WHERE product_id = p_product_id 
        AND warehouse_id = p_warehouse_id
        AND is_active = 1;
    
    COMMIT;
    
    SELECT ROW_COUNT() AS affected_rows;
END$$

-- Get Stock by Product
CREATE PROCEDURE sp_get_product_stock(IN p_product_id INT)
BEGIN
    SELECT 
        s.id,
        s.product_id,
        p.name AS product_name,
        s.warehouse_id,
        w.name AS warehouse_name,
        s.quantity,
        s.updated_at
    FROM stock s
    JOIN products p ON s.product_id = p.id
    JOIN warehouses w ON s.warehouse_id = w.id
    WHERE s.product_id = p_product_id 
        AND s.is_active = 1
        AND p.deleted_at IS NULL
        AND w.deleted_at IS NULL;
END$$

-- ===================================================================
-- USER PROCEDURES
-- ===================================================================

-- Create User
CREATE PROCEDURE sp_create_user(
    IN p_name VARCHAR(100),
    IN p_email VARCHAR(254),
    IN p_phone VARCHAR(15),
    IN p_password_hash VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    INSERT INTO users (name, email, phone, password_hash)
    VALUES (p_name, p_email, p_phone, p_password_hash);
    
    COMMIT;
    
    SELECT LAST_INSERT_ID() AS user_id;
END$$

-- Update User
CREATE PROCEDURE sp_update_user(
    IN p_user_id INT,
    IN p_name VARCHAR(100),
    IN p_email VARCHAR(254),
    IN p_phone VARCHAR(15),
    IN p_profile_image_url VARCHAR(500)
)
BEGIN
    UPDATE users 
    SET name = p_name,
        email = p_email,
        phone = p_phone,
        profile_image_url = p_profile_image_url
    WHERE id = p_user_id 
        AND deleted_at IS NULL;
    
    SELECT ROW_COUNT() AS affected_rows;
END$$

-- Soft Delete User
CREATE PROCEDURE sp_soft_delete_user(IN p_user_id INT)
BEGIN
    UPDATE users 
    SET deleted_at = CURRENT_TIMESTAMP,
        is_active = 0
    WHERE id = p_user_id 
        AND deleted_at IS NULL;
    
    SELECT ROW_COUNT() AS affected_rows;
END$

-- Hard Delete User (Anonymize first recommended)
CREATE PROCEDURE sp_hard_delete_user(IN p_user_id INT)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Delete user addresses
    DELETE FROM user_addresses WHERE user_id = p_user_id;
    
    -- Delete order items related to user orders
    DELETE oi FROM order_items oi
    INNER JOIN orders o ON oi.order_id = o.id
    WHERE o.user_id = p_user_id;
    
    -- Delete orders
    DELETE FROM orders WHERE user_id = p_user_id;
    
    -- Finally delete user
    DELETE FROM users WHERE id = p_user_id;
    
    COMMIT;
    
    SELECT ROW_COUNT() AS affected_rows;
END$

-- Anonymize User (GDPR Compliance)
CREATE PROCEDURE sp_anonymize_user(IN p_user_id INT)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    UPDATE users 
    SET name = CONCAT('Deleted User ', id),
        email = CONCAT('deleted_', id, '@anonymized.local'),
        phone = NULL,
        address = NULL,
        profile_image_url = NULL,
        is_anonymized = 1,
        anonymized_at = CURRENT_TIMESTAMP,
        is_active = 0
    WHERE id = p_user_id;
    
    -- Anonymize addresses
    UPDATE user_addresses
    SET address_line1 = 'Anonymized',
        address_line2 = NULL,
        city = 'Anonymized',
        state = NULL,
        postal_code = '00000',
        is_active = 0
    WHERE user_id = p_user_id;
    
    -- Mark orders as anonymized
    UPDATE orders
    SET is_anonymized = 1,
        anonymized_at = CURRENT_TIMESTAMP
    WHERE user_id = p_user_id;
    
    COMMIT;
    
    SELECT ROW_COUNT() AS affected_rows;
END$

-- Update Last Login
CREATE PROCEDURE sp_update_last_login(IN p_user_id INT)
BEGIN
    UPDATE users 
    SET last_login_at = CURRENT_TIMESTAMP
    WHERE id = p_user_id 
        AND deleted_at IS NULL;
    
    SELECT ROW_COUNT() AS affected_rows;
END$

-- ===================================================================
-- USER ADDRESS PROCEDURES
-- ===================================================================

-- Create User Address
CREATE PROCEDURE sp_create_address(
    IN p_user_id INT,
    IN p_address_type ENUM('billing','shipping','both'),
    IN p_address_line1 VARCHAR(255),
    IN p_address_line2 VARCHAR(255),
    IN p_city VARCHAR(100),
    IN p_state VARCHAR(100),
    IN p_postal_code VARCHAR(20),
    IN p_country VARCHAR(100),
    IN p_is_default TINYINT(1)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- If this is default, unset other defaults
    IF p_is_default = 1 THEN
        UPDATE user_addresses 
        SET is_default = 0 
        WHERE user_id = p_user_id 
            AND address_type = p_address_type
            AND deleted_at IS NULL;
    END IF;
    
    INSERT INTO user_addresses (
        user_id, address_type, address_line1, address_line2,
        city, state, postal_code, country, is_default
    )
    VALUES (
        p_user_id, p_address_type, p_address_line1, p_address_line2,
        p_city, p_state, p_postal_code, p_country, p_is_default
    );
    
    COMMIT;
    
    SELECT LAST_INSERT_ID() AS address_id;
END$

-- Update User Address
CREATE PROCEDURE sp_update_address(
    IN p_address_id INT,
    IN p_address_type ENUM('billing','shipping','both'),
    IN p_address_line1 VARCHAR(255),
    IN p_address_line2 VARCHAR(255),
    IN p_city VARCHAR(100),
    IN p_state VARCHAR(100),
    IN p_postal_code VARCHAR(20),
    IN p_country VARCHAR(100),
    IN p_is_default TINYINT(1)
)
BEGIN
    DECLARE v_user_id INT;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Get user_id for this address
    SELECT user_id INTO v_user_id FROM user_addresses WHERE id = p_address_id;
    
    -- If setting as default, unset other defaults
    IF p_is_default = 1 THEN
        UPDATE user_addresses 
        SET is_default = 0 
        WHERE user_id = v_user_id 
            AND address_type = p_address_type
            AND id != p_address_id
            AND deleted_at IS NULL;
    END IF;
    
    UPDATE user_addresses 
    SET address_type = p_address_type,
        address_line1 = p_address_line1,
        address_line2 = p_address_line2,
        city = p_city,
        state = p_state,
        postal_code = p_postal_code,
        country = p_country,
        is_default = p_is_default
    WHERE id = p_address_id 
        AND deleted_at IS NULL;
    
    COMMIT;
    
    SELECT ROW_COUNT() AS affected_rows;
END$

-- Soft Delete Address
CREATE PROCEDURE sp_soft_delete_address(IN p_address_id INT)
BEGIN
    UPDATE user_addresses 
    SET deleted_at = CURRENT_TIMESTAMP,
        is_active = 0
    WHERE id = p_address_id 
        AND deleted_at IS NULL;
    
    SELECT ROW_COUNT() AS affected_rows;
END$

-- Hard Delete Address
CREATE PROCEDURE sp_hard_delete_address(IN p_address_id INT)
BEGIN
    DELETE FROM user_addresses WHERE id = p_address_id;
    
    SELECT ROW_COUNT() AS affected_rows;
END$

-- Get User Addresses
CREATE PROCEDURE sp_get_user_addresses(
    IN p_user_id INT,
    IN p_address_type VARCHAR(20)
)
BEGIN
    IF p_address_type IS NOT NULL AND p_address_type != '' THEN
        SELECT * FROM vw_user_addresses
        WHERE user_id = p_user_id 
            AND address_type = p_address_type
        ORDER BY is_default DESC, created_at DESC;
    ELSE
        SELECT * FROM vw_user_addresses
        WHERE user_id = p_user_id
        ORDER BY is_default DESC, created_at DESC;
    END IF;
END$

-- ===================================================================
-- CATEGORY PROCEDURES
-- ===================================================================

-- Create Category
CREATE PROCEDURE sp_create_category(
    IN p_name VARCHAR(100),
    IN p_description TEXT,
    IN p_image_url VARCHAR(500)
)
BEGIN
    INSERT INTO categories (name, description, image_url)
    VALUES (p_name, p_description, p_image_url);
    
    SELECT LAST_INSERT_ID() AS category_id;
END$

-- Update Category
CREATE PROCEDURE sp_update_category(
    IN p_category_id INT,
    IN p_name VARCHAR(100),
    IN p_description TEXT,
    IN p_image_url VARCHAR(500)
)
BEGIN
    UPDATE categories 
    SET name = p_name,
        description = p_description,
        image_url = p_image_url
    WHERE id = p_category_id 
        AND deleted_at IS NULL;
    
    SELECT ROW_COUNT() AS affected_rows;
END$

-- Soft Delete Category
CREATE PROCEDURE sp_soft_delete_category(IN p_category_id INT)
BEGIN
    UPDATE categories 
    SET deleted_at = CURRENT_TIMESTAMP,
        is_active = 0
    WHERE id = p_category_id 
        AND deleted_at IS NULL;
    
    SELECT ROW_COUNT() AS affected_rows;
END$

-- Hard Delete Category
CREATE PROCEDURE sp_hard_delete_category(IN p_category_id INT)
BEGIN
    DELETE FROM categories WHERE id = p_category_id;
    
    SELECT ROW_COUNT() AS affected_rows;
END$

-- Get Active Categories (AJAX Ready)
CREATE PROCEDURE sp_get_active_categories()
BEGIN
    SELECT * FROM vw_category_stats
    ORDER BY category_name ASC;
END$

-- 
-- SUPPLIER PROCEDURES
-- 

-- Create Supplier
CREATE PROCEDURE sp_create_supplier(
    IN p_name VARCHAR(100),
    IN p_email VARCHAR(254),
    IN p_phone VARCHAR(15),
    IN p_address TEXT
)
BEGIN
    INSERT INTO suppliers (name, email, phone, address)
    VALUES (p_name, p_email, p_phone, p_address);
    
    SELECT LAST_INSERT_ID() AS supplier_id;
END$

-- Update Supplier
CREATE PROCEDURE sp_update_supplier(
    IN p_supplier_id INT,
    IN p_name VARCHAR(100),
    IN p_email VARCHAR(254),
    IN p_phone VARCHAR(15),
    IN p_address TEXT
)
BEGIN
    UPDATE suppliers 
    SET name = p_name,
        email = p_email,
        phone = p_phone,
        address = p_address
    WHERE id = p_supplier_id 
        AND deleted_at IS NULL;
    
    SELECT ROW_COUNT() AS affected_rows;
END$

-- Soft Delete Supplier
CREATE PROCEDURE sp_soft_delete_supplier(IN p_supplier_id INT)
BEGIN
    UPDATE suppliers 
    SET deleted_at = CURRENT_TIMESTAMP,
        is_active = 0
    WHERE id = p_supplier_id 
        AND deleted_at IS NULL;
    
    SELECT ROW_COUNT() AS affected_rows;
END$

-- Hard Delete Supplier
CREATE PROCEDURE sp_hard_delete_supplier(IN p_supplier_id INT)
BEGIN
    DELETE FROM suppliers WHERE id = p_supplier_id;
    
    SELECT ROW_COUNT() AS affected_rows;
END$

-- 
-- WAREHOUSE PROCEDURES
-- 

-- Create Warehouse
CREATE PROCEDURE sp_create_warehouse(
    IN p_name VARCHAR(100),
    IN p_address TEXT
)
BEGIN
    INSERT INTO warehouses (name, address)
    VALUES (p_name, p_address);
    
    SELECT LAST_INSERT_ID() AS warehouse_id;
END$

-- Update Warehouse
CREATE PROCEDURE sp_update_warehouse(
    IN p_warehouse_id INT,
    IN p_name VARCHAR(100),
    IN p_address TEXT
)
BEGIN
    UPDATE warehouses 
    SET name = p_name,
        address = p_address
    WHERE id = p_warehouse_id 
        AND deleted_at IS NULL;
    
    SELECT ROW_COUNT() AS affected_rows;
END$

-- Soft Delete Warehouse
CREATE PROCEDURE sp_soft_delete_warehouse(IN p_warehouse_id INT)
BEGIN
    UPDATE warehouses 
    SET deleted_at = CURRENT_TIMESTAMP,
        is_active = 0
    WHERE id = p_warehouse_id 
        AND deleted_at IS NULL;
    
    SELECT ROW_COUNT() AS affected_rows;
END$

-- Hard Delete Warehouse
CREATE PROCEDURE sp_hard_delete_warehouse(IN p_warehouse_id INT)
BEGIN
    DELETE FROM warehouses WHERE id = p_warehouse_id;
    
    SELECT ROW_COUNT() AS affected_rows;
END$

-- 
-- ANALYTICS & REPORTS PROCEDURES
-- 

-- Sales Report by Date Range
CREATE PROCEDURE sp_sales_report(
    IN p_start_date DATE,
    IN p_end_date DATE
)
BEGIN
    SELECT 
        DATE(o.created_at) AS sale_date,
        COUNT(DISTINCT o.id) AS total_orders,
        COUNT(oi.id) AS total_items,
        SUM(oi.quantity) AS total_quantity,
        SUM(o.total) AS total_revenue
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.created_at BETWEEN p_start_date AND p_end_date
        AND o.deleted_at IS NULL
        AND o.status != 'cancelled'
    GROUP BY DATE(o.created_at)
    ORDER BY sale_date DESC;
END$

-- Top Selling Products
CREATE PROCEDURE sp_top_selling_products(IN p_limit INT)
BEGIN
    SELECT 
        p.id,
        p.name,
        c.name AS category_name,
        COUNT(oi.id) AS order_count,
        SUM(oi.quantity) AS total_sold,
        SUM(oi.quantity * oi.price) AS total_revenue,
        p.price AS current_price
    FROM products p
    JOIN order_items oi ON p.id = oi.product_id
    JOIN orders o ON oi.order_id = o.id
    JOIN categories c ON p.category_id = c.id
    WHERE p.deleted_at IS NULL
        AND o.deleted_at IS NULL
        AND o.status = 'completed'
    GROUP BY p.id, p.name, c.name, p.price
    ORDER BY total_sold DESC
    LIMIT p_limit;
END$

-- Customer Orders Statistics
CREATE PROCEDURE sp_customer_stats(IN p_user_id INT)
BEGIN
    SELECT 
        u.id,
        u.name,
        u.email,
        COUNT(o.id) AS total_orders,
        SUM(CASE WHEN o.status = 'completed' THEN 1 ELSE 0 END) AS completed_orders,
        SUM(CASE WHEN o.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_orders,
        SUM(CASE WHEN o.status = 'completed' THEN o.total ELSE 0 END) AS total_spent,
        MAX(o.created_at) AS last_order_date
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id AND o.deleted_at IS NULL
    WHERE u.id = p_user_id
        AND u.deleted_at IS NULL
    GROUP BY u.id, u.name, u.email;
END$

-- Low Stock Alert
CREATE PROCEDURE sp_get_low_stock_alerts(IN p_threshold INT)
BEGIN
    SELECT 
        p.id,
        p.name AS product_name,
        c.name AS category_name,
        w.name AS warehouse_name,
        st.quantity AS current_stock,
        p.price
    FROM stock st
    JOIN products p ON st.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    JOIN warehouses w ON st.warehouse_id = w.id
    WHERE st.quantity < p_threshold
        AND st.is_active = 1
        AND p.deleted_at IS NULL
        AND p.is_active = 1
        AND w.deleted_at IS NULL
        AND w.is_active = 1
    ORDER BY st.quantity ASC, p.name ASC;
END$

-- Products Without Stock
CREATE PROCEDURE sp_products_without_stock()
BEGIN
    SELECT 
        p.id,
        p.name,
        c.name AS category_name,
        p.price,
        p.created_at
    FROM products p
    JOIN categories c ON p.category_id = c.id
    LEFT JOIN stock st ON p.id = st.product_id AND st.is_active = 1
    WHERE p.deleted_at IS NULL
        AND p.is_active = 1
        AND (st.id IS NULL OR st.quantity = 0)
    ORDER BY p.name ASC;
END$

DELIMITER ;

-- SAMPLE DATA INSERT (Optional - for testing)

-- Insert sample categories
INSERT INTO categories (name, description) VALUES
('Whiskey', 'Premium whiskey collection'),
('Vodka', 'Finest vodka selection'),
('Rum', 'Caribbean and spiced rums'),
('Wine', 'Red, white and sparkling wines'),
('Beer', 'Local and imported beers'),
('Gin', 'Classic and craft gins');

-- Insert sample warehouses
INSERT INTO warehouses (name, address) VALUES
('Main Warehouse', '123 Storage St, Colombo, Sri Lanka'),
('North Branch', '456 Branch Rd, Jaffna, Sri Lanka'),
('South Depot', '789 Depot Ave, Galle, Sri Lanka');

-- Insert sample suppliers
INSERT INTO suppliers (name, email, phone, address) VALUES
('Premium Spirits Ltd', 'contact@premiumspirits.lk', '+94112345678', 'Colombo 03, Sri Lanka'),
('Island Beverages', 'info@islandbev.lk', '+94112345679', 'Colombo 05, Sri Lanka'),
('Global Imports Co', 'sales@globalimports.lk', '+94112345680', 'Colombo 07, Sri Lanka');

-- Insert admin user (password: admin123 - hashed with PASSWORD())
INSERT INTO users (name, email, phone, password_hash, is_admin) VALUES
('Admin User', 'admin@liquorshop.lk', '+94771234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Insert sample customer
INSERT INTO users (name, email, phone, password_hash) VALUES
('John Doe', 'john@example.com', '+94771234568', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert sample address for customer
INSERT INTO user_addresses (user_id, address_type, address_line1, city, postal_code, country, is_default) VALUES
(2, 'both', '45 Main Street', 'Colombo', '00100', 'Sri Lanka', 1);



/*
-- Search products by name (AJAX autocomplete)
CALL sp_search_products('jack', NULL, NULL, NULL, 'name', 'ASC', 10, 0);

-- Filter products by price range
CALL sp_filter_products_by_price(1000.00, 5000.00);

-- Get products A-Z
CALL sp_filter_products_alphabetical('ASC');

-- Get products Z-A
CALL sp_filter_products_alphabetical('DESC');

-- Search with multiple filters
CALL sp_search_products('whiskey', 1, 2000, 10000, 'price', 'DESC', 20, 0);

-- Get user orders
CALL sp_get_user_orders(2, NULL, 10, 0);

-- Get user orders by status
CALL sp_get_user_orders(2, 'completed', 10, 0);

-- Get low stock alerts
CALL sp_get_low_stock_alerts(10);

-- Get top selling products
CALL sp_top_selling_products(10);

-- Sales report for date range
CALL sp_sales_report('2025-01-01', '2025-12-31');
*/

-- PHP API USAGE EXAMPLES

/*
Example PHP code for using these stored procedures:

<?php
// Database connection
$conn = new mysqli("localhost", "username", "password", "liquor_db");

// 1. AJAX Product Search
$search = $_GET['search'] ?? '';
$stmt = $conn->prepare("CALL sp_search_products(?, NULL, NULL, NULL, 'name', 'ASC', 10, 0)");
$stmt->bind_param("s", $search);
$stmt->execute();
$result = $stmt->get_result();
echo json_encode($result->fetch_all(MYSQLI_ASSOC));

// 2. Create Order with Transaction
$user_id = 2;
$shipping_id = 1;
$billing_id = 1;
$items = json_encode([
    ['product_id' => 1, 'quantity' => 2],
    ['product_id' => 3, 'quantity' => 1]
]);
$stmt = $conn->prepare("CALL sp_create_order(?, ?, ?, ?)");
$stmt->bind_param("iiis", $user_id, $shipping_id, $billing_id, $items);
$stmt->execute();

// 3. Filter Products by Price (AJAX)
$min = $_GET['min'] ?? 0;
$max = $_GET['max'] ?? 999999;
$stmt = $conn->prepare("CALL sp_filter_products_by_price(?, ?)");
$stmt->bind_param("dd", $min, $max);
$stmt->execute();
$result = $stmt->get_result();
echo json_encode($result->fetch_all(MYSQLI_ASSOC));

// 4. Soft Delete Product
$product_id = 5;
$stmt = $conn->prepare("CALL sp_soft_delete_product(?)");
$stmt->bind_param("i", $product_id);
$stmt->execute();

// 5. Get Active Products (Simple View Query)
$result = $conn->query("SELECT * FROM vw_active_products ORDER BY created_at DESC LIMIT 20");
echo json_encode($result->fetch_all(MYSQLI_ASSOC));

// 6. Update Stock
$product_id = 1;
$warehouse_id = 1;
$quantity = 100;
$stmt = $conn->prepare("CALL sp_update_stock(?, ?, ?)");
$stmt->bind_param("iii", $product_id, $warehouse_id, $quantity);
$stmt->execute();

$conn->close();
?>
*/



-- Additional indexes for faster AJAX queries (already included in table definitions)
-- These are already created above but listed here for reference:
-- idx_products_name on products(name, is_active) - for autocomplete
-- idx_products_price on products(price, is_active) - for price filters
-- idx_products_category on products(category_id, is_active) - for category filters
-- idx_orders_user on orders(user_id, created_at) - for user order history
-- idx_orders_status on orders(status, created_at) - for order status filters



/*
1. Always use prepared statements in PHP to prevent SQL injection
2. Hash passwords with password_hash() in PHP before storing
3. Use password_verify() to check passwords
4. Implement rate limiting for AJAX requests
5. Add CSRF tokens to forms
6. Use HTTPS for all API endpoints
7. Implement proper session management
8. Sanitize all user inputs
9. Use parameterized queries for all database operations
10. Implement proper error handling (don't expose database errors to users)
*/



CREATE TYPE order_status AS ENUM ('pending', 'processing', 'completed', 'cancelled');
CREATE TYPE address_type AS ENUM ('billing', 'shipping', 'both');


CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(254) NOT NULL UNIQUE,
    phone VARCHAR(15) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    profile_image_url VARCHAR(500) DEFAULT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    is_admin BOOLEAN NOT NULL DEFAULT FALSE,
    is_anonymized BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    anonymized_at TIMESTAMP NULL DEFAULT NULL,
    last_login_at TIMESTAMP NULL DEFAULT NULL
);

-- Indexes
CREATE INDEX idx_users_active ON users(is_active, deleted_at);
CREATE INDEX idx_users_login ON users(email, password_hash, is_active);

-- Trigger for updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER users_updated_at
BEFORE UPDATE ON users
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

CREATE TABLE user_addresses (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    address_type address_type NOT NULL DEFAULT 'both',
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255) DEFAULT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) DEFAULT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL DEFAULT 'Sri Lanka',
    is_default BOOLEAN NOT NULL DEFAULT FALSE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    
    CONSTRAINT fk_addresses_user FOREIGN KEY (user_id) 
        REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
);

-- Indexes
CREATE INDEX idx_addresses_user ON user_addresses(user_id, is_active);
CREATE INDEX idx_addresses_default ON user_addresses(user_id, is_default, address_type);
CREATE INDEX idx_addresses_active ON user_addresses(is_active, deleted_at);

-- Trigger for updated_at
CREATE TRIGGER user_addresses_updated_at
BEFORE UPDATE ON user_addresses
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

CREATE TABLE suppliers (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(254) DEFAULT NULL,
    phone VARCHAR(15) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

-- Indexes
CREATE INDEX idx_suppliers_active ON suppliers(is_active, deleted_at);

-- Trigger for updated_at
CREATE TRIGGER suppliers_updated_at
BEFORE UPDATE ON suppliers
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();


CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    image_url VARCHAR(500) DEFAULT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

-- Indexes
CREATE INDEX idx_categories_active ON categories(is_active, deleted_at);

-- Trigger for updated_at
CREATE TRIGGER categories_updated_at
BEFORE UPDATE ON categories
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();


CREATE TABLE warehouses (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    address TEXT DEFAULT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL
);

-- Indexes
CREATE INDEX idx_warehouses_active ON warehouses(is_active, deleted_at);

-- Trigger for updated_at
CREATE TRIGGER warehouses_updated_at
BEFORE UPDATE ON warehouses
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    price DECIMAL(12,2) NOT NULL CHECK (price > 0),
    image_url VARCHAR(500) DEFAULT NULL,
    category_id INTEGER NOT NULL,
    supplier_id INTEGER DEFAULT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) 
        REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_products_supplier FOREIGN KEY (supplier_id) 
        REFERENCES suppliers(id) ON UPDATE CASCADE ON DELETE RESTRICT
);

-- Indexes
CREATE INDEX idx_products_active ON products(is_active, deleted_at);
CREATE INDEX idx_products_category ON products(category_id, is_active);
CREATE INDEX idx_products_supplier ON products(supplier_id);
CREATE INDEX idx_products_price ON products(price, is_active);
CREATE INDEX idx_products_name ON products(name, is_active);

-- Trigger for updated_at
CREATE TRIGGER products_updated_at
BEFORE UPDATE ON products
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

CREATE TABLE stock (
    id SERIAL PRIMARY KEY,
    product_id INTEGER NOT NULL,
    warehouse_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL DEFAULT 0 CHECK (quantity >= 0),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT uk_stock_product_warehouse UNIQUE (product_id, warehouse_id),
    CONSTRAINT fk_stock_product FOREIGN KEY (product_id) 
        REFERENCES products(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_stock_warehouse FOREIGN KEY (warehouse_id) 
        REFERENCES warehouses(id) ON UPDATE CASCADE ON DELETE RESTRICT
);

-- Indexes
CREATE INDEX idx_stock_product ON stock(product_id, is_active);
CREATE INDEX idx_stock_warehouse ON stock(warehouse_id, is_active);
CREATE INDEX idx_stock_quantity ON stock(quantity, is_active);

-- Trigger for updated_at
CREATE TRIGGER stock_updated_at
BEFORE UPDATE ON stock
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

CREATE TABLE orders (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    shipping_address_id INTEGER DEFAULT NULL,
    billing_address_id INTEGER DEFAULT NULL,
    status order_status NOT NULL DEFAULT 'pending',
    total DECIMAL(12,2) NOT NULL CHECK (total >= 0),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    is_anonymized BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    anonymized_at TIMESTAMP NULL DEFAULT NULL,
    
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) 
        REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_orders_shipping FOREIGN KEY (shipping_address_id) 
        REFERENCES user_addresses(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_orders_billing FOREIGN KEY (billing_address_id) 
        REFERENCES user_addresses(id) ON UPDATE CASCADE ON DELETE RESTRICT
);

-- Indexes
CREATE INDEX idx_orders_user ON orders(user_id, created_at);
CREATE INDEX idx_orders_status ON orders(status, created_at);
CREATE INDEX idx_orders_active ON orders(is_active, deleted_at);
CREATE INDEX idx_orders_shipping ON orders(shipping_address_id);
CREATE INDEX idx_orders_billing ON orders(billing_address_id);

-- Trigger for updated_at
CREATE TRIGGER orders_updated_at
BEFORE UPDATE ON orders
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

CREATE TABLE order_items (
    id SERIAL PRIMARY KEY,
    order_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL CHECK (quantity > 0),
    price DECIMAL(12,2) NOT NULL CHECK (price > 0),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT uk_order_items UNIQUE (order_id, product_id),
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) 
        REFERENCES orders(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) 
        REFERENCES products(id) ON UPDATE CASCADE ON DELETE RESTRICT
);

-- Indexes
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_order_items_product ON order_items(product_id);

-- Trigger for updated_at
CREATE TRIGGER order_items_updated_at
BEFORE UPDATE ON order_items
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

CREATE TABLE rate_limiting (
    id SERIAL PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL,
    attempt_time TIMESTAMP NOT NULL
);

CREATE INDEX idx_identifier_time ON rate_limiting(identifier, attempt_time);


-- Active Products with Category and Stock Info
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
LEFT JOIN stock st ON p.id = st.product_id AND st.is_active = TRUE
WHERE p.deleted_at IS NULL 
    AND p.is_active = TRUE
    AND c.deleted_at IS NULL
    AND c.is_active = TRUE
GROUP BY p.id, p.name, p.description, p.price, p.image_url, 
         p.category_id, c.name, p.supplier_id, s.name, 
         p.is_active, p.created_at, p.updated_at;

-- Products Search View
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
LEFT JOIN stock st ON p.id = st.product_id AND st.is_active = TRUE
WHERE p.deleted_at IS NULL 
    AND p.is_active = TRUE
    AND c.deleted_at IS NULL
    AND c.is_active = TRUE
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
    AND o.is_active = TRUE
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
    AND o.is_active = TRUE
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
    AND p.is_active = TRUE
WHERE c.deleted_at IS NULL 
    AND c.is_active = TRUE
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
    AND st.is_active = TRUE
    AND p.deleted_at IS NULL 
    AND p.is_active = TRUE
    AND w.deleted_at IS NULL
    AND w.is_active = TRUE
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


-- Create Product with Transaction
CREATE OR REPLACE FUNCTION sp_create_product(
    p_name VARCHAR(200),
    p_description TEXT,
    p_price DECIMAL(12,2),
    p_image_url VARCHAR(500),
    p_category_id INTEGER,
    p_supplier_id INTEGER
) RETURNS TABLE(product_id INTEGER) AS $$
DECLARE
    v_product_id INTEGER;
BEGIN
    INSERT INTO products (name, description, price, image_url, category_id, supplier_id)
    VALUES (p_name, p_description, p_price, p_image_url, p_category_id, p_supplier_id)
    RETURNING id INTO v_product_id;
    
    RETURN QUERY SELECT v_product_id;
END;
$$ LANGUAGE plpgsql;

-- Update Product
CREATE OR REPLACE FUNCTION sp_update_product(
    p_id INTEGER,
    p_name VARCHAR(200),
    p_description TEXT,
    p_price DECIMAL(12,2),
    p_image_url VARCHAR(500),
    p_category_id INTEGER,
    p_supplier_id INTEGER
) RETURNS TABLE(affected_rows INTEGER) AS $$
DECLARE
    v_affected INTEGER;
BEGIN
    UPDATE products 
    SET name = p_name,
        description = p_description,
        price = p_price,
        image_url = p_image_url,
        category_id = p_category_id,
        supplier_id = p_supplier_id
    WHERE id = p_id 
        AND deleted_at IS NULL;
    
    GET DIAGNOSTICS v_affected = ROW_COUNT;
    RETURN QUERY SELECT v_affected;
END;
$$ LANGUAGE plpgsql;

-- Soft Delete Product
CREATE OR REPLACE FUNCTION sp_soft_delete_product(p_id INTEGER) 
RETURNS TABLE(affected_rows INTEGER) AS $$
DECLARE
    v_affected INTEGER;
BEGIN
    UPDATE products 
    SET deleted_at = CURRENT_TIMESTAMP,
        is_active = FALSE
    WHERE id = p_id 
        AND deleted_at IS NULL;
    
    GET DIAGNOSTICS v_affected = ROW_COUNT;
    RETURN QUERY SELECT v_affected;
END;
$$ LANGUAGE plpgsql;

-- Hard Delete Product (Permanent)
CREATE OR REPLACE FUNCTION sp_hard_delete_product(p_id INTEGER) 
RETURNS TABLE(affected_rows INTEGER) AS $$
DECLARE
    v_affected INTEGER;
BEGIN
    -- Delete related order items first
    DELETE FROM order_items WHERE product_id = p_id;
    
    -- Delete stock records
    DELETE FROM stock WHERE product_id = p_id;
    
    -- Finally delete product
    DELETE FROM products WHERE id = p_id;
    
    GET DIAGNOSTICS v_affected = ROW_COUNT;
    RETURN QUERY SELECT v_affected;
END;
$$ LANGUAGE plpgsql;

-- Restore Soft Deleted Product
CREATE OR REPLACE FUNCTION sp_restore_product(p_id INTEGER) 
RETURNS TABLE(affected_rows INTEGER) AS $$
DECLARE
    v_affected INTEGER;
BEGIN
    UPDATE products 
    SET deleted_at = NULL,
        is_active = TRUE
    WHERE id = p_id 
        AND deleted_at IS NOT NULL;
    
    GET DIAGNOSTICS v_affected = ROW_COUNT;
    RETURN QUERY SELECT v_affected;
END;
$$ LANGUAGE plpgsql;

-- Search Products (AJAX Ready)
CREATE OR REPLACE FUNCTION sp_search_products(
    p_search_term VARCHAR(200) DEFAULT NULL,
    p_category_id INTEGER DEFAULT NULL,
    p_min_price DECIMAL(12,2) DEFAULT NULL,
    p_max_price DECIMAL(12,2) DEFAULT NULL,
    p_limit INTEGER DEFAULT 50,
    p_offset INTEGER DEFAULT 0
) RETURNS TABLE(
    id INTEGER,
    name VARCHAR(200),
    description TEXT,
    price DECIMAL(12,2),
    image_url VARCHAR(500),
    category_id INTEGER,
    category_name VARCHAR(100),
    supplier_id INTEGER,
    supplier_name VARCHAR(100),
    total_stock BIGINT,
    is_active BOOLEAN,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        p.id,
        p.name,
        p.description,
        p.price,
        p.image_url,
        p.category_id,
        p.category_name,
        p.supplier_id,
        p.supplier_name,
        p.total_stock,
        p.is_active,
        p.created_at,
        p.updated_at
    FROM vw_active_products p
    WHERE (p_search_term IS NULL OR 
           p.name ILIKE '%' || p_search_term || '%' OR 
           p.description ILIKE '%' || p_search_term || '%')
      AND (p_category_id IS NULL OR p.category_id = p_category_id)
      AND (p_min_price IS NULL OR p.price >= p_min_price)
      AND (p_max_price IS NULL OR p.price <= p_max_price)
    ORDER BY p.created_at DESC
    LIMIT p_limit OFFSET p_offset;
END;
$$ LANGUAGE plpgsql;


-- Create Order with Items (Transaction)
CREATE OR REPLACE FUNCTION sp_create_order(
    p_user_id INTEGER,
    p_shipping_address_id INTEGER,
    p_billing_address_id INTEGER,
    p_items JSONB
) RETURNS TABLE(order_id INTEGER, total DECIMAL(12,2)) AS $$
DECLARE
    v_order_id INTEGER;
    v_total DECIMAL(12,2) := 0;
    v_item JSONB;
    v_product_id INTEGER;
    v_quantity INTEGER;
    v_price DECIMAL(12,2);
BEGIN
    -- Create order
    INSERT INTO orders (user_id, shipping_address_id, billing_address_id, total)
    VALUES (p_user_id, p_shipping_address_id, p_billing_address_id, 0)
    RETURNING id INTO v_order_id;
    
    -- Loop through JSON items
    FOR v_item IN SELECT * FROM jsonb_array_elements(p_items)
    LOOP
        v_product_id := (v_item->>'product_id')::INTEGER;
        v_quantity := (v_item->>'quantity')::INTEGER;
        
        -- Get current product price
        SELECT price INTO v_price FROM products 
        WHERE id = v_product_id AND deleted_at IS NULL;
        
        -- Insert order item
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (v_order_id, v_product_id, v_quantity, v_price);
        
        -- Add to total
        v_total := v_total + (v_price * v_quantity);
    END LOOP;
    
    -- Update order total
    UPDATE orders SET total = v_total WHERE id = v_order_id;
    
    RETURN QUERY SELECT v_order_id, v_total;
END;
$$ LANGUAGE plpgsql;

-- Update Order Status
CREATE OR REPLACE FUNCTION sp_update_order_status(
    p_order_id INTEGER,
    p_status order_status
) RETURNS TABLE(affected_rows INTEGER) AS $$
DECLARE
    v_affected INTEGER;
BEGIN
    UPDATE orders 
    SET status = p_status
    WHERE id = p_order_id 
        AND deleted_at IS NULL;
    
    GET DIAGNOSTICS v_affected = ROW_COUNT;
    RETURN QUERY SELECT v_affected;
END;
$$ LANGUAGE plpgsql;

-- Soft Delete Order
CREATE OR REPLACE FUNCTION sp_soft_delete_order(p_order_id INTEGER) 
RETURNS TABLE(affected_rows INTEGER) AS $$
DECLARE
    v_affected INTEGER;
BEGIN
    UPDATE orders 
    SET deleted_at = CURRENT_TIMESTAMP,
        is_active = FALSE
    WHERE id = p_order_id 
        AND deleted_at IS NULL;
    
    GET DIAGNOSTICS v_affected = ROW_COUNT;
    RETURN QUERY SELECT v_affected;
END;
$$ LANGUAGE plpgsql;

-- Get User Orders (AJAX Ready)
CREATE OR REPLACE FUNCTION sp_get_user_orders(
    p_user_id INTEGER,
    p_status order_status DEFAULT NULL,
    p_limit INTEGER DEFAULT 50,
    p_offset INTEGER DEFAULT 0
) RETURNS TABLE(
    order_id INTEGER,
    user_id INTEGER,
    user_name VARCHAR(100),
    user_email VARCHAR(254),
    status order_status,
    total DECIMAL(12,2),
    order_date TIMESTAMP,
    item_count BIGINT,
    shipping_address TEXT
) AS $$
BEGIN
    IF p_status IS NOT NULL THEN
        RETURN QUERY
        SELECT 
            o.order_id,
            o.user_id,
            o.user_name,
            o.user_email,
            o.status,
            o.total,
            o.order_date,
            o.item_count,
            o.shipping_address
        FROM vw_user_orders o
        WHERE o.user_id = p_user_id 
            AND o.status = p_status
        ORDER BY o.order_date DESC
        LIMIT p_limit OFFSET p_offset;
    ELSE
        RETURN QUERY
        SELECT 
            o.order_id,
            o.user_id,
            o.user_name,
            o.user_email,
            o.status,
            o.total,
            o.order_date,
            o.item_count,
            o.shipping_address
        FROM vw_user_orders o
        WHERE o.user_id = p_user_id
        ORDER BY o.order_date DESC
        LIMIT p_limit OFFSET p_offset;
    END IF;
END;
$$ LANGUAGE plpgsql;

-- Update Stock (with Upsert)
CREATE OR REPLACE FUNCTION sp_update_stock(
    p_product_id INTEGER,
    p_warehouse_id INTEGER,
    p_quantity INTEGER
) RETURNS TABLE(affected_rows INTEGER) AS $$
DECLARE
    v_affected INTEGER;
BEGIN
    INSERT INTO stock (product_id, warehouse_id, quantity)
    VALUES (p_product_id, p_warehouse_id, p_quantity)
    ON CONFLICT (product_id, warehouse_id) 
    DO UPDATE SET 
        quantity = p_quantity,
        updated_at = CURRENT_TIMESTAMP;
    
    GET DIAGNOSTICS v_affected = ROW_COUNT;
    RETURN QUERY SELECT v_affected;
END;
$$ LANGUAGE plpgsql;

-- Adjust Stock (Add/Subtract)
CREATE OR REPLACE FUNCTION sp_adjust_stock(
    p_product_id INTEGER,
    p_warehouse_id INTEGER,
    p_adjustment INTEGER
) RETURNS TABLE(affected_rows INTEGER) AS $$
DECLARE
    v_affected INTEGER;
BEGIN
    UPDATE stock 
    SET quantity = quantity + p_adjustment
    WHERE product_id = p_product_id 
        AND warehouse_id = p_warehouse_id
        AND is_active = TRUE;
    
    GET DIAGNOSTICS v_affected = ROW_COUNT;
    RETURN QUERY SELECT v_affected;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION sp_create_user(
    p_name VARCHAR(100),
    p_email VARCHAR(254),
    p_phone VARCHAR(15),
    p_password_hash VARCHAR(255)
) RETURNS TABLE(
    id INTEGER,
    name VARCHAR(100),
    email VARCHAR(254),
    phone VARCHAR(15),
    password_hash VARCHAR(255),
    profile_image_url VARCHAR(255),
    is_active BOOLEAN,
    is_admin BOOLEAN,
    is_anonymized BOOLEAN,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    anonymized_at TIMESTAMP,
    last_login_at TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
    INSERT INTO users (name, email, phone, password_hash)
    VALUES (p_name, p_email, p_phone, p_password_hash)
    RETURNING id, name, email, phone, password_hash, profile_image_url,
              is_active, is_admin, is_anonymized, created_at, updated_at,
              deleted_at, anonymized_at, last_login_at;
END;
$$ LANGUAGE plpgsql;



-- Anonymize User (GDPR Compliance)
CREATE OR REPLACE FUNCTION sp_anonymize_user(p_user_id INTEGER) 
RETURNS TABLE(affected_rows INTEGER) AS $$
DECLARE
    v_affected INTEGER;
BEGIN
    UPDATE users 
    SET name = 'Deleted User ' || id::TEXT,
        email = 'deleted_' || id::TEXT || '@anonymized.local',
        phone = NULL,
        profile_image_url = NULL,
        is_anonymized = TRUE,
        anonymized_at = CURRENT_TIMESTAMP,
        is_active = FALSE
    WHERE id = p_user_id;
    
    -- Anonymize addresses
    UPDATE user_addresses
    SET address_line1 = 'Anonymized',
        address_line2 = NULL,
        city = 'Anonymized',
        state = NULL,
        postal_code = '00000',
        is_active = FALSE
    WHERE user_id = p_user_id;
    
    -- Mark orders as anonymized
    UPDATE orders
    SET is_anonymized = TRUE,
        anonymized_at = CURRENT_TIMESTAMP
    WHERE user_id = p_user_id;
    
    GET DIAGNOSTICS v_affected = ROW_COUNT;
    RETURN QUERY SELECT v_affected;
END;
$$ LANGUAGE plpgsql;


-- Create User Address
CREATE OR REPLACE FUNCTION sp_create_address(
    p_user_id INTEGER,
    p_address_type address_type,
    p_address_line1 VARCHAR(255),
    p_address_line2 VARCHAR(255),
    p_city VARCHAR(100),
    p_state VARCHAR(100),
    p_postal_code VARCHAR(20),
    p_country VARCHAR(100),
    p_is_default BOOLEAN
) RETURNS TABLE(address_id INTEGER) AS $$
DECLARE
    v_address_id INTEGER;
BEGIN
    -- If this is default, unset other defaults
    IF p_is_default = TRUE THEN
        UPDATE user_addresses 
        SET is_default = FALSE 
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
    )
    RETURNING id INTO v_address_id;
    
    RETURN QUERY SELECT v_address_id;
END;
$$ LANGUAGE plpgsql;

-- Update User Address
CREATE OR REPLACE FUNCTION sp_update_address(
    p_address_id INTEGER,
    p_address_type address_type,
    p_address_line1 VARCHAR(255),
    p_address_line2 VARCHAR(255),
    p_city VARCHAR(100),
    p_state VARCHAR(100),
    p_postal_code VARCHAR(20),
    p_country VARCHAR(100),
    p_is_default BOOLEAN
) RETURNS TABLE(affected_rows INTEGER) AS $$
DECLARE
    v_user_id INTEGER;
    v_affected INTEGER;
BEGIN
    -- Get user_id for this address
    SELECT user_id INTO v_user_id FROM user_addresses WHERE id = p_address_id;
    
    -- If setting as default, unset other defaults
    IF p_is_default = TRUE THEN
        UPDATE user_addresses 
        SET is_default = FALSE 
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
    
    GET DIAGNOSTICS v_affected = ROW_COUNT;
    RETURN QUERY SELECT v_affected;
END;
$$ LANGUAGE plpgsql;

-- Soft Delete Address
CREATE OR REPLACE FUNCTION sp_soft_delete_address(p_address_id INTEGER) 
RETURNS TABLE(affected_rows INTEGER) AS $$
DECLARE
    v_affected INTEGER;
BEGIN
    UPDATE user_addresses 
    SET deleted_at = CURRENT_TIMESTAMP,
        is_active = FALSE
    WHERE id = p_address_id 
        AND deleted_at IS NULL;
    
    GET DIAGNOSTICS v_affected = ROW_COUNT;
    RETURN QUERY SELECT v_affected;
END;
$$ LANGUAGE plpgsql;

-- Get User Addresses
CREATE OR REPLACE FUNCTION sp_get_user_addresses(
    p_user_id INTEGER,
    p_address_type address_type DEFAULT NULL
) RETURNS TABLE(
    id INTEGER,
    user_id INTEGER,
    user_name VARCHAR(100),
    user_email VARCHAR(254),
    address_type address_type,
    full_address TEXT,
    is_default BOOLEAN,
    is_active BOOLEAN,
    created_at TIMESTAMP
) AS $$
BEGIN
    IF p_address_type IS NOT NULL THEN
        RETURN QUERY
        SELECT 
            ua.id,
            ua.user_id,
            ua.user_name,
            ua.user_email,
            ua.address_type,
            ua.full_address,
            ua.is_default,
            ua.is_active,
            ua.created_at
        FROM vw_user_addresses ua
        WHERE ua.user_id = p_user_id 
            AND ua.address_type = p_address_type
        ORDER BY ua.is_default DESC, ua.created_at DESC;
    ELSE
        RETURN QUERY
        SELECT 
            ua.id,
            ua.user_id,
            ua.user_name,
            ua.user_email,
            ua.address_type,
            ua.full_address,
            ua.is_default,
            ua.is_active,
            ua.created_at
        FROM vw_user_addresses ua
        WHERE ua.user_id = p_user_id
        ORDER BY ua.is_default DESC, ua.created_at DESC;
    END IF;
END;
$$ LANGUAGE plpgsql;


-- Create Category (returns full row)
CREATE OR REPLACE FUNCTION sp_create_category(
    p_name VARCHAR(100),
    p_description TEXT,
    p_image_url VARCHAR(500)
) 
RETURNS categories AS $$
DECLARE
    v_category categories;
BEGIN
    INSERT INTO categories (name, description, image_url)
    VALUES (p_name, p_description, p_image_url)
    RETURNING * INTO v_category;

    RETURN v_category;
END;
$$ LANGUAGE plpgsql;


-- Update Category
CREATE OR REPLACE FUNCTION sp_update_category(
    p_category_id INTEGER,
    p_name VARCHAR(100),
    p_description TEXT,
    p_image_url VARCHAR(500)
) RETURNS TABLE(affected_rows INTEGER) AS $$
DECLARE
    v_affected INTEGER;
BEGIN
    UPDATE categories 
    SET name = p_name,
        description = p_description,
        image_url = p_image_url
    WHERE id = p_category_id 
        AND deleted_at IS NULL;
    
    GET DIAGNOSTICS v_affected = ROW_COUNT;
    RETURN QUERY SELECT v_affected;
END;
$$ LANGUAGE plpgsql;

-- Soft Delete Category
CREATE OR REPLACE FUNCTION sp_soft_delete_category(p_category_id INTEGER) 
RETURNS TABLE(affected_rows INTEGER) AS $$
DECLARE
    v_affected INTEGER;
BEGIN
    UPDATE categories 
    SET deleted_at = CURRENT_TIMESTAMP,
        is_active = FALSE
    WHERE id = p_category_id 
        AND deleted_at IS NULL;
    
    GET DIAGNOSTICS v_affected = ROW_COUNT;
    RETURN QUERY SELECT v_affected;
END;
$$ LANGUAGE plpgsql;

-- Get Active Categories (AJAX Ready)
CREATE OR REPLACE FUNCTION sp_get_active_categories()
RETURNS TABLE(
    category_id INTEGER,
    category_name VARCHAR(100),
    description TEXT,
    image_url VARCHAR(500),
    product_count BIGINT,
    min_price NUMERIC,
    max_price NUMERIC,
    avg_price NUMERIC
) AS $$
BEGIN
    RETURN QUERY
    SELECT * FROM vw_category_stats
    ORDER BY category_name ASC;
END;
$$ LANGUAGE plpgsql;


-- Create Supplier
CREATE OR REPLACE FUNCTION sp_create_supplier(
    p_name VARCHAR(100),
    p_email VARCHAR(254),
    p_phone VARCHAR(15),
    p_address TEXT
) RETURNS TABLE(supplier_id INTEGER) AS $$
DECLARE
    v_supplier_id INTEGER;
BEGIN
    INSERT INTO suppliers (name, email, phone, address)
    VALUES (p_name, p_email, p_phone, p_address)
    RETURNING id INTO v_supplier_id;
    
    RETURN QUERY SELECT v_supplier_id;
END;
$$ LANGUAGE plpgsql;

-- Update Supplier
CREATE OR REPLACE FUNCTION sp_update_supplier(
    p_supplier_id INTEGER,
    p_name VARCHAR(100),
    p_email VARCHAR(254),
    p_phone VARCHAR(15),
    p_address TEXT
) RETURNS TABLE(affected_rows INTEGER) AS $$
DECLARE
    v_affected INTEGER;
BEGIN
    UPDATE suppliers 
    SET name = p_name,
        email = p_email,
        phone = p_phone,
        address = p_address
    WHERE id = p_supplier_id 
        AND deleted_at IS NULL;
    
    GET DIAGNOSTICS v_affected = ROW_COUNT;
    RETURN QUERY SELECT v_affected;
END;
$$ LANGUAGE plpgsql;

-- Soft Delete Supplier
CREATE OR REPLACE FUNCTION sp_soft_delete_supplier(p_supplier_id INTEGER) 
RETURNS TABLE(affected_rows INTEGER) AS $$
DECLARE
    v_affected INTEGER;
BEGIN
    UPDATE suppliers 
    SET deleted_at = CURRENT_TIMESTAMP,
        is_active = FALSE
    WHERE id = p_supplier_id 
        AND deleted_at IS NULL;
    
    GET DIAGNOSTICS v_affected = ROW_COUNT;
    RETURN QUERY SELECT v_affected;
END;
$$ LANGUAGE plpgsql;


-- Create Warehouse
CREATE OR REPLACE FUNCTION sp_create_warehouse(
    p_name VARCHAR(100),
    p_address TEXT
) RETURNS TABLE(warehouse_id INTEGER) AS $$
DECLARE
    v_warehouse_id INTEGER;
BEGIN
    INSERT INTO warehouses (name, address)
    VALUES (p_name, p_address)
    RETURNING id INTO v_warehouse_id;
    
    RETURN QUERY SELECT v_warehouse_id;
END;
$$ LANGUAGE plpgsql;

-- Update Warehouse
CREATE OR REPLACE FUNCTION sp_update_warehouse(
    p_warehouse_id INTEGER,
    p_name VARCHAR(100),
    p_address TEXT
) RETURNS TABLE(affected_rows INTEGER) AS $$
DECLARE
    v_affected INTEGER;
BEGIN
    UPDATE warehouses 
    SET name = p_name,
        address = p_address
    WHERE id = p_warehouse_id 
        AND deleted_at IS NULL;
    
    GET DIAGNOSTICS v_affected = ROW_COUNT;
    RETURN QUERY SELECT v_affected;
END;
$$ LANGUAGE plpgsql;

-- Soft Delete Warehouse
CREATE OR REPLACE FUNCTION sp_soft_delete_warehouse(p_warehouse_id INTEGER) 
RETURNS TABLE(affected_rows INTEGER) AS $$
DECLARE
    v_affected INTEGER;
BEGIN
    UPDATE warehouses 
    SET deleted_at = CURRENT_TIMESTAMP,
        is_active = FALSE
    WHERE id = p_warehouse_id 
        AND deleted_at IS NULL;
    
    GET DIAGNOSTICS v_affected = ROW_COUNT;
    RETURN QUERY SELECT v_affected;
END;
$$ LANGUAGE plpgsql;


-- Sales Report by Date Range
CREATE OR REPLACE FUNCTION sp_sales_report(
    p_start_date DATE,
    p_end_date DATE
) RETURNS TABLE(
    sale_date DATE,
    total_orders BIGINT,
    total_items BIGINT,
    total_quantity BIGINT,
    total_revenue NUMERIC
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        o.created_at::DATE AS sale_date,
        COUNT(DISTINCT o.id) AS total_orders,
        COUNT(oi.id) AS total_items,
        SUM(oi.quantity) AS total_quantity,
        SUM(o.total) AS total_revenue
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.created_at::DATE BETWEEN p_start_date AND p_end_date
        AND o.deleted_at IS NULL
        AND o.status != 'cancelled'
    GROUP BY o.created_at::DATE
    ORDER BY sale_date DESC;
END;
$$ LANGUAGE plpgsql;

-- Top Selling Products
CREATE OR REPLACE FUNCTION sp_top_selling_products(p_limit INTEGER)
RETURNS TABLE(
    id INTEGER,
    name VARCHAR(200),
    category_name VARCHAR(100),
    order_count BIGINT,
    total_sold BIGINT,
    total_revenue NUMERIC,
    current_price DECIMAL(12,2)
) AS $$
BEGIN
    RETURN QUERY
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
END;
$$ LANGUAGE plpgsql;

-- Customer Orders Statistics
CREATE OR REPLACE FUNCTION sp_customer_stats(p_user_id INTEGER)
RETURNS TABLE(
    id INTEGER,
    name VARCHAR(100),
    email VARCHAR(254),
    total_orders BIGINT,
    completed_orders BIGINT,
    cancelled_orders BIGINT,
    total_spent NUMERIC,
    last_order_date TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
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
END;
$$ LANGUAGE plpgsql;

-- Low Stock Alert
CREATE OR REPLACE FUNCTION sp_get_low_stock_alerts(p_threshold INTEGER)
RETURNS TABLE(
    id INTEGER,
    product_name VARCHAR(200),
    category_name VARCHAR(100),
    warehouse_name VARCHAR(100),
    current_stock INTEGER,
    price DECIMAL(12,2)
) AS $$
BEGIN
    RETURN QUERY
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
        AND st.is_active = TRUE
        AND p.deleted_at IS NULL
        AND p.is_active = TRUE
        AND w.deleted_at IS NULL
        AND w.is_active = TRUE
    ORDER BY st.quantity ASC, p.name ASC;
END;
$$ LANGUAGE plpgsql;

-- Products Without Stock
CREATE OR REPLACE FUNCTION sp_products_without_stock()
RETURNS TABLE(
    id INTEGER,
    name VARCHAR(200),
    category_name VARCHAR(100),
    price DECIMAL(12,2),
    created_at TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        p.id,
        p.name,
        c.name AS category_name,
        p.price,
        p.created_at
    FROM products p
    JOIN categories c ON p.category_id = c.id
    LEFT JOIN stock st ON p.id = st.product_id AND st.is_active = TRUE
    WHERE p.deleted_at IS NULL
        AND p.is_active = TRUE
        AND (st.id IS NULL OR st.quantity = 0)
    ORDER BY p.name ASC;
END;
$$ LANGUAGE plpgsql;

-- Get Product Stock by Product ID
CREATE OR REPLACE FUNCTION sp_get_product_stock(p_product_id INTEGER)
RETURNS TABLE(
    id INTEGER,
    product_id INTEGER,
    product_name VARCHAR(200),
    warehouse_id INTEGER,
    warehouse_name VARCHAR(100),
    quantity INTEGER,
    updated_at TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
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
        AND s.is_active = TRUE
        AND p.deleted_at IS NULL
        AND w.deleted_at IS NULL;
END;
$$ LANGUAGE plpgsql;

-- Update Last Login
CREATE OR REPLACE FUNCTION sp_update_last_login(p_user_id INTEGER)
RETURNS TABLE(affected_rows INTEGER) AS $$
DECLARE
    v_affected INTEGER;
BEGIN
    UPDATE users 
    SET last_login_at = CURRENT_TIMESTAMP
    WHERE id = p_user_id 
        AND deleted_at IS NULL;
    
    GET DIAGNOSTICS v_affected = ROW_COUNT;
    RETURN QUERY SELECT v_affected;
END;
$$ LANGUAGE plpgsql;


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

-- Insert admin user (password should be hashed with bcrypt in application)
INSERT INTO users (name, email, phone, password_hash, is_admin) VALUES
('Admin User', 'admin@liquorshop.lk', '+94771234567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);

-- Insert sample customer
INSERT INTO users (name, email, phone, password_hash) VALUES
('John Doe', 'john@example.com', '+94771234568', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert sample address for customer (user_id 2)
INSERT INTO user_addresses (user_id, address_type, address_line1, city, postal_code, country, is_default) VALUES
(2, 'both', '45 Main Street', 'Colombo', '00100', 'Sri Lanka', TRUE);


DROP SCHEMA public CASCADE;
CREATE SCHEMA public;
GRANT ALL ON SCHEMA public TO public;

-- ENUMS
CREATE TYPE cart_status    AS ENUM ('active', 'converted', 'abandoned', 'expired');
CREATE TYPE order_status   AS ENUM ('pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded', 'failed');
CREATE TYPE payment_status AS ENUM ('pending', 'captured', 'failed', 'refunded', 'voided');
CREATE TYPE address_type   AS ENUM ('billing', 'shipping', 'both');

-- TABLES (exact order — safe for sequences)
CREATE TABLE users (
    id                BIGSERIAL PRIMARY KEY,
    name              VARCHAR(100)     NOT NULL,
    email             VARCHAR(254)     UNIQUE NOT NULL,
    phone             VARCHAR(20),
    password_hash     VARCHAR(255)     NOT NULL,
    profile_image_url VARCHAR(500),
    is_admin          BOOLEAN          DEFAULT FALSE,
    is_active         BOOLEAN          DEFAULT TRUE,
    is_anonymized     BOOLEAN          DEFAULT FALSE,
    created_at        TIMESTAMPTZ      DEFAULT NOW(),
    updated_at        TIMESTAMPTZ      DEFAULT NOW(),
    deleted_at        TIMESTAMPTZ,
    anonymized_at     TIMESTAMPTZ,
    last_login_at     TIMESTAMPTZ
);

CREATE TABLE user_addresses (
    id              BIGSERIAL PRIMARY KEY,
    user_id         BIGINT           NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    address_type    address_type     NOT NULL DEFAULT 'both',
    recipient_name  VARCHAR(100),
    phone           VARCHAR(20),
    address_line1   VARCHAR(255)     NOT NULL,
    address_line2   VARCHAR(255),
    city            VARCHAR(100)     NOT NULL,
    state           VARCHAR(100),
    postal_code     VARCHAR(20)      NOT NULL,
    country         VARCHAR(100)     DEFAULT 'Sri Lanka',
    is_default      BOOLEAN          DEFAULT FALSE,
    created_at      TIMESTAMPTZ      DEFAULT NOW(),
    updated_at      TIMESTAMPTZ      DEFAULT NOW(),
    deleted_at      TIMESTAMPTZ,
    UNIQUE(user_id, address_type, is_default)
);

CREATE TABLE categories (
    id          BIGSERIAL PRIMARY KEY,
    name        VARCHAR(100) UNIQUE NOT NULL,
    slug        VARCHAR(120) UNIQUE NOT NULL,
    description TEXT,
    image_url   VARCHAR(500),
    is_active   BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMPTZ DEFAULT NOW(),
    updated_at  TIMESTAMPTZ DEFAULT NOW(),
    deleted_at  TIMESTAMPTZ
);

CREATE TABLE suppliers (
    id         BIGSERIAL PRIMARY KEY,
    name       VARCHAR(100) UNIQUE NOT NULL,
    email      VARCHAR(254),
    phone      VARCHAR(20),
    address    TEXT,
    is_active  BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW(),
    deleted_at TIMESTAMPTZ
);

CREATE TABLE warehouses (
    id         BIGSERIAL PRIMARY KEY,
    name       VARCHAR(100) UNIQUE NOT NULL,
    address    TEXT,
    phone      VARCHAR(20),
    image_url  VARCHAR(500),
    is_active  BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW(),
    deleted_at TIMESTAMPTZ
);

CREATE TABLE products (
    id            BIGSERIAL PRIMARY KEY,
    name          VARCHAR(200) NOT NULL,
    slug          VARCHAR(220) UNIQUE NOT NULL,
    description   TEXT,
    price_cents   INTEGER      NOT NULL CHECK (price_cents > 0),
    image_url     VARCHAR(500),
    category_id   BIGINT       NOT NULL REFERENCES categories(id),
    supplier_id   BIGINT                REFERENCES suppliers(id),
    is_active     BOOLEAN      DEFAULT TRUE,
    created_at    TIMESTAMPTZ  DEFAULT NOW(),
    updated_at    TIMESTAMPTZ  DEFAULT NOW(),
    deleted_at    TIMESTAMPTZ
);

CREATE TABLE stock (
    id           BIGSERIAL PRIMARY KEY,
    product_id   BIGINT NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    warehouse_id BIGINT NOT NULL REFERENCES warehouses(id),
    quantity     INTEGER NOT NULL DEFAULT 0 CHECK (quantity >= 0),
    reserved     INTEGER NOT NULL DEFAULT 0 CHECK (reserved >= 0),
    created_at   TIMESTAMPTZ DEFAULT NOW(),
    updated_at   TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(product_id, warehouse_id)
);

CREATE TABLE carts (
    id           BIGSERIAL PRIMARY KEY,
    user_id      BIGINT REFERENCES users(id) ON DELETE SET NULL,
    session_id   VARCHAR(64) NOT NULL,
    status       cart_status DEFAULT 'active',
    total_cents  INTEGER     DEFAULT 0,
    item_count   INTEGER     DEFAULT 0,
    created_at   TIMESTAMPTZ DEFAULT NOW(),
    updated_at   TIMESTAMPTZ DEFAULT NOW(),
    converted_at TIMESTAMPTZ,
    abandoned_at TIMESTAMPTZ
);

CREATE TABLE cart_items (
    id                 BIGSERIAL PRIMARY KEY,
    cart_id            BIGINT NOT NULL REFERENCES carts(id) ON DELETE CASCADE,
    product_id         BIGINT NOT NULL REFERENCES products(id),
    quantity           INTEGER NOT NULL CHECK (quantity > 0),
    price_at_add_cents INTEGER NOT NULL,
    created_at         TIMESTAMPTZ DEFAULT NOW(),
    updated_at         TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(cart_id, product_id)
);

-- ORDERS TABLE + SEQUENCE (MUST BE IN THIS ORDER)
CREATE TABLE orders (
    id                  BIGSERIAL PRIMARY KEY,
    order_number        VARCHAR(20)  UNIQUE NOT NULL DEFAULT '',
    cart_id             BIGINT       NOT NULL REFERENCES carts(id),
    user_id             BIGINT                REFERENCES users(id) ON DELETE SET NULL,
    status              order_status DEFAULT 'pending',
    total_cents         INTEGER      NOT NULL,
    shipping_address_id BIGINT                REFERENCES user_addresses(id),
    billing_address_id  BIGINT                REFERENCES user_addresses(id),
    notes               TEXT,
    created_at          TIMESTAMPTZ  DEFAULT NOW(),
    updated_at          TIMESTAMPTZ  DEFAULT NOW(),
    paid_at             TIMESTAMPTZ,
    shipped_at          TIMESTAMPTZ,
    delivered_at        TIMESTAMPTZ,
    cancelled_at        TIMESTAMPTZ
);

-- SEQUENCE AFTER orders TABLE EXISTS
CREATE SEQUENCE order_number_seq OWNED BY NONE;

CREATE TABLE order_items (
    id                BIGSERIAL PRIMARY KEY,
    order_id          BIGINT NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
    product_id        BIGINT NOT NULL REFERENCES products(id),
    product_name      TEXT NOT NULL,
    product_image_url TEXT,
    price_cents       INTEGER NOT NULL,
    quantity          INTEGER NOT NULL CHECK (quantity > 0),
    created_at        TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE payments (
    id               BIGSERIAL PRIMARY KEY,
    order_id         BIGINT NOT NULL REFERENCES orders(id) ON DELETE RESTRICT,
    amount_cents     INTEGER NOT NULL,
    currency         CHAR(3) DEFAULT 'LKR',
    gateway          VARCHAR(50) NOT NULL,
    gateway_order_id VARCHAR(255),
    transaction_id   VARCHAR(255),
    status           payment_status DEFAULT 'pending',
    payload          JSONB,
    created_at       TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE flavor_profiles (
    product_id BIGINT PRIMARY KEY REFERENCES products(id) ON DELETE CASCADE,
    sweetness  INTEGER DEFAULT 5 CHECK (sweetness BETWEEN 0 AND 10),
    bitterness INTEGER DEFAULT 5 CHECK (bitterness BETWEEN 0 AND 10),
    strength   INTEGER DEFAULT 5 CHECK (strength BETWEEN 0 AND 10),
    smokiness  INTEGER DEFAULT 5 CHECK (smokiness BETWEEN 0 AND 10),
    fruitiness INTEGER DEFAULT 5 CHECK (fruitiness BETWEEN 0 AND 10),
    spiciness  INTEGER DEFAULT 5 CHECK (spiciness BETWEEN 0 AND 10),
    tags       TEXT[]
);

CREATE TABLE feedback (
    id                   BIGSERIAL PRIMARY KEY,
    user_id              BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    product_id           BIGINT NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    rating               INTEGER NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment              TEXT,
    is_verified_purchase BOOLEAN DEFAULT FALSE,
    is_active            BOOLEAN DEFAULT TRUE,
    created_at           TIMESTAMPTZ DEFAULT NOW(),
    updated_at           TIMESTAMPTZ DEFAULT NOW(),
    deleted_at           TIMESTAMPTZ
);

CREATE TABLE cocktail_recipes (
    id                BIGSERIAL PRIMARY KEY,
    name              VARCHAR(200) NOT NULL,
    description       TEXT,
    instructions      TEXT NOT NULL,
    image_url         VARCHAR(500),
    difficulty        VARCHAR(20) DEFAULT 'easy',
    preparation_time  INTEGER,
    serves            INTEGER DEFAULT 1,
    is_active         BOOLEAN DEFAULT TRUE,
    created_at        TIMESTAMPTZ DEFAULT NOW(),
    updated_at        TIMESTAMPTZ DEFAULT NOW(),
    deleted_at        TIMESTAMPTZ
);

CREATE TABLE recipe_ingredients (
    id         BIGSERIAL PRIMARY KEY,
    recipe_id  BIGINT NOT NULL REFERENCES cocktail_recipes(id) ON DELETE CASCADE,
    product_id BIGINT NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    quantity   DECIMAL(10,2) NOT NULL CHECK (quantity > 0),
    unit       VARCHAR(50) NOT NULL,
    is_optional BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE(recipe_id, product_id)
);

CREATE TABLE user_preferences (
    id                   BIGSERIAL PRIMARY KEY,
    user_id              BIGINT NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    preferred_sweetness  INTEGER DEFAULT 5 CHECK (preferred_sweetness BETWEEN 0 AND 10),
    preferred_bitterness INTEGER DEFAULT 5 CHECK (preferred_bitterness BETWEEN 0 AND 10),
    preferred_strength   INTEGER DEFAULT 5 CHECK (preferred_strength BETWEEN 0 AND 10),
    preferred_smokiness  INTEGER DEFAULT 5 CHECK (preferred_smokiness BETWEEN 0 AND 10),
    preferred_fruitiness INTEGER DEFAULT 5 CHECK (preferred_fruitiness BETWEEN 0 AND 10),
    preferred_spiciness  INTEGER DEFAULT 5 CHECK (preferred_spiciness BETWEEN 0 AND 10),
    favorite_categories  INTEGER[],
    created_at           TIMESTAMPTZ DEFAULT NOW(),
    updated_at           TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE product_recognition (
    id                 BIGSERIAL PRIMARY KEY,
    user_id            BIGINT REFERENCES users(id) ON DELETE SET NULL,
    session_id         VARCHAR(255) NOT NULL,
    image_url          VARCHAR(500) NOT NULL,
    recognized_text    TEXT,
    recognized_labels  TEXT[],
    matched_product_id BIGINT REFERENCES products(id),
    confidence_score   DECIMAL(5,2),
    api_provider       VARCHAR(50),
    created_at         TIMESTAMPTZ DEFAULT NOW()
);

-- INDEXES
CREATE INDEX idx_users_email_active            ON users(email) WHERE is_active = TRUE AND deleted_at IS NULL;
CREATE INDEX idx_products_active               ON products(id) WHERE is_active = TRUE AND deleted_at IS NULL;
CREATE INDEX idx_products_category             ON products(category_id) WHERE is_active = TRUE;
CREATE INDEX idx_products_price                ON products(price_cents);
CREATE INDEX idx_products_slug_active          ON products(slug) WHERE is_active = TRUE;
CREATE INDEX idx_products_name                 ON products(name);
CREATE INDEX idx_stock_available              ON stock(product_id) WHERE quantity > reserved;
CREATE INDEX idx_stock_low                     ON stock(quantity ASC) WHERE quantity < 50;
CREATE UNIQUE INDEX idx_carts_active_user      ON carts(user_id) WHERE status = 'active';
CREATE UNIQUE INDEX idx_carts_active_session   ON carts(session_id) WHERE status = 'active';
CREATE INDEX idx_orders_user_date              ON orders(user_id, created_at DESC);
CREATE INDEX idx_orders_status_date            ON orders(status, created_at DESC);
CREATE INDEX idx_order_items_product           ON order_items(product_id);
CREATE INDEX idx_flavor_sweetness              ON flavor_profiles(sweetness);
CREATE INDEX idx_flavor_strength               ON flavor_profiles(strength);
CREATE INDEX idx_feedback_product_active       ON feedback(product_id) WHERE is_active = TRUE;
-- Drop all existing admin views
DROP VIEW IF EXISTS admin_view_users CASCADE;
DROP VIEW IF EXISTS admin_view_user_addresses CASCADE;
DROP VIEW IF EXISTS admin_view_categories CASCADE;
DROP VIEW IF EXISTS admin_view_suppliers CASCADE;
DROP VIEW IF EXISTS admin_view_warehouses CASCADE;
DROP VIEW IF EXISTS admin_view_products CASCADE;
DROP VIEW IF EXISTS admin_view_stock CASCADE;
DROP VIEW IF EXISTS admin_view_carts CASCADE;
DROP VIEW IF EXISTS admin_view_cart_items CASCADE;
DROP VIEW IF EXISTS admin_view_orders CASCADE;
DROP VIEW IF EXISTS admin_view_order_items CASCADE;
DROP VIEW IF EXISTS admin_view_payments CASCADE;
DROP VIEW IF EXISTS admin_view_flavor_profiles CASCADE;
DROP VIEW IF EXISTS admin_view_feedback CASCADE;
DROP VIEW IF EXISTS admin_view_cocktail_recipes CASCADE;
DROP VIEW IF EXISTS admin_view_recipe_ingredients CASCADE;
DROP VIEW IF EXISTS admin_view_user_preferences CASCADE;
DROP VIEW IF EXISTS admin_view_product_recognition CASCADE;

-- 1. Users - List View (Fast, paginated)
CREATE VIEW admin_list_users AS
SELECT 
    u.id,
    u.name,
    u.email,
    u.phone,
    u.is_admin,
    u.is_active,
    u.created_at,
    u.last_login_at,
    (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count,
    (SELECT COALESCE(SUM(total_cents), 0) FROM orders WHERE user_id = u.id AND status IN ('paid', 'delivered')) as lifetime_value_cents
FROM users u
WHERE u.deleted_at IS NULL AND u.is_anonymized = FALSE
ORDER BY u.created_at DESC;

-- Users - Detail View (Rich data for modal)
CREATE VIEW admin_detail_users AS
SELECT 
    u.id, u.name, u.email, u.phone, u.profile_image_url,
    u.is_admin, u.is_active, u.is_anonymized,
    u.created_at, u.updated_at, u.last_login_at, u.anonymized_at,
    
    -- Order stats
    (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as total_orders,
    (SELECT COUNT(*) FROM orders WHERE user_id = u.id AND status = 'delivered') as completed_orders,
    (SELECT COUNT(*) FROM orders WHERE user_id = u.id AND status IN ('pending', 'processing')) as pending_orders,
    (SELECT COUNT(*) FROM orders WHERE user_id = u.id AND status = 'cancelled') as cancelled_orders,
    
    -- Financial
    (SELECT COALESCE(SUM(total_cents), 0) FROM orders WHERE user_id = u.id AND status IN ('paid', 'delivered')) as lifetime_value_cents,
    (SELECT COALESCE(AVG(total_cents), 0) FROM orders WHERE user_id = u.id AND status IN ('paid', 'delivered')) as avg_order_value_cents,
    (SELECT MAX(created_at) FROM orders WHERE user_id = u.id) as last_order_date,
    
    -- Activity
    (SELECT COUNT(*) FROM feedback WHERE user_id = u.id) as feedback_count,
    (SELECT COUNT(*) FROM carts WHERE user_id = u.id AND status = 'active') as active_carts,
    (SELECT COUNT(*) FROM carts WHERE user_id = u.id AND status = 'abandoned') as abandoned_carts,
    (SELECT COUNT(*) FROM user_addresses WHERE user_id = u.id AND deleted_at IS NULL) as address_count,
    
    -- Payment methods used
    (SELECT STRING_AGG(DISTINCT gateway, ', ') FROM payments p JOIN orders o ON p.order_id = o.id WHERE o.user_id = u.id) as payment_gateways_used,
    
    -- Recent orders (JSON array of last 5)
    (SELECT JSON_AGG(row_to_json(t)) FROM (
        SELECT order_number, status, total_cents, created_at 
        FROM orders 
        WHERE user_id = u.id 
        ORDER BY created_at DESC 
        LIMIT 5
    ) t) as recent_orders
    
FROM users u
WHERE u.deleted_at IS NULL AND u.is_anonymized = FALSE;

-- 2. Categories
CREATE VIEW admin_list_categories AS
SELECT 
    c.id, c.name, c.slug, c.is_active, c.created_at,
    (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_active = TRUE) AS product_count,
    (SELECT COALESCE(SUM(s.quantity - s.reserved), 0) FROM stock s JOIN products p ON s.product_id = p.id WHERE p.category_id = c.id) AS total_stock
FROM categories c
WHERE c.deleted_at IS NULL
ORDER BY c.name;

CREATE VIEW admin_detail_categories AS
SELECT 
    c.id, c.name, c.slug, c.description, c.image_url, c.is_active,
    c.created_at, c.updated_at,
    
    (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) as total_products,
    (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_active = TRUE) as active_products,
    (SELECT AVG(p.price_cents) FROM products p WHERE p.category_id = c.id) as avg_price_cents,
    (SELECT MIN(p.price_cents) FROM products p WHERE p.category_id = c.id) as min_price_cents,
    (SELECT MAX(p.price_cents) FROM products p WHERE p.category_id = c.id) as max_price_cents,
    
    (SELECT COALESCE(SUM(s.quantity), 0) FROM stock s JOIN products p ON s.product_id = p.id WHERE p.category_id = c.id) as total_inventory,
    (SELECT COALESCE(SUM(s.reserved), 0) FROM stock s JOIN products p ON s.product_id = p.id WHERE p.category_id = c.id) as total_reserved,
    
    (SELECT COUNT(*) FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE p.category_id = c.id) as total_sales,
    
    (SELECT JSON_AGG(row_to_json(t)) FROM (
        SELECT id, name, slug, price_cents, is_active 
        FROM products 
        WHERE category_id = c.id 
        ORDER BY name 
        LIMIT 10
    ) t) as top_products
    
FROM categories c
WHERE c.deleted_at IS NULL;

-- 3. Products
CREATE VIEW admin_list_products AS
SELECT 
    p.id, p.name, p.slug, p.price_cents,
    cat.name as category_name,
    sup.name as supplier_name,
    p.is_active, p.created_at,
    (SELECT COALESCE(SUM(s.quantity - s.reserved), 0) FROM stock s WHERE s.product_id = p.id) as available_stock,
    (SELECT AVG(rating) FROM feedback f WHERE f.product_id = p.id AND f.is_active = TRUE) as avg_rating
FROM products p
LEFT JOIN categories cat ON p.category_id = cat.id
LEFT JOIN suppliers sup ON p.supplier_id = sup.id
WHERE p.deleted_at IS NULL
ORDER BY p.created_at DESC;

CREATE VIEW admin_detail_products AS
SELECT 
    p.id, p.name, p.slug, p.description, p.price_cents, p.image_url,
    p.category_id, cat.name as category_name, cat.slug as category_slug,
    p.supplier_id, sup.name as supplier_name, sup.email as supplier_email, sup.phone as supplier_phone,
    p.is_active, p.created_at, p.updated_at,
    
    -- Stock across all warehouses
    (SELECT JSON_AGG(row_to_json(t)) FROM (
        SELECT w.name as warehouse_name, s.quantity, s.reserved, (s.quantity - s.reserved) as available
        FROM stock s
        JOIN warehouses w ON s.warehouse_id = w.id
        WHERE s.product_id = p.id
    ) t) as stock_by_warehouse,
    
    (SELECT COALESCE(SUM(s.quantity), 0) FROM stock s WHERE s.product_id = p.id) as total_quantity,
    (SELECT COALESCE(SUM(s.reserved), 0) FROM stock s WHERE s.product_id = p.id) as total_reserved,
    (SELECT COALESCE(SUM(s.quantity - s.reserved), 0) FROM stock s WHERE s.product_id = p.id) as total_available,
    
    -- Sales stats
    (SELECT COUNT(*) FROM order_items WHERE product_id = p.id) as times_ordered,
    (SELECT COALESCE(SUM(quantity), 0) FROM order_items WHERE product_id = p.id) as total_sold,
    (SELECT COALESCE(SUM(quantity * price_cents), 0) FROM order_items WHERE product_id = p.id) as total_revenue_cents,
    
    -- Feedback
    (SELECT COUNT(*) FROM feedback WHERE product_id = p.id AND is_active = TRUE) as feedback_count,
    (SELECT AVG(rating) FROM feedback WHERE product_id = p.id AND is_active = TRUE) as avg_rating,
    
    -- Flavor profile
    (SELECT row_to_json(fp) FROM flavor_profiles fp WHERE fp.product_id = p.id) as flavor_profile,
    
    -- Recent orders
    (SELECT JSON_AGG(row_to_json(t)) FROM (
        SELECT o.order_number, oi.quantity, oi.price_cents, o.created_at
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        WHERE oi.product_id = p.id
        ORDER BY o.created_at DESC
        LIMIT 10
    ) t) as recent_orders
    
FROM products p
LEFT JOIN categories cat ON p.category_id = cat.id
LEFT JOIN suppliers sup ON p.supplier_id = sup.id
WHERE p.deleted_at IS NULL;

-- 4. Orders
CREATE VIEW admin_list_orders AS
SELECT 
    o.id, o.order_number, o.status, o.total_cents, o.created_at,
    u.name as user_name, u.email as user_email,
    (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
FROM orders o
LEFT JOIN users u ON o.user_id = u.id
ORDER BY o.created_at DESC;

CREATE VIEW admin_detail_orders AS
SELECT 
    o.id, o.order_number, o.status, o.total_cents, o.notes,
    o.created_at, o.updated_at, o.paid_at, o.shipped_at, o.delivered_at, o.cancelled_at,
    
    -- User info
    o.user_id, u.name as user_name, u.email as user_email, u.phone as user_phone,
    
    -- Addresses
    o.shipping_address_id,
    (SELECT row_to_json(sa) FROM (
        SELECT recipient_name, phone, address_line1, address_line2, city, state, postal_code, country
        FROM user_addresses WHERE id = o.shipping_address_id
    ) sa) as shipping_address,
    
    o.billing_address_id,
    (SELECT row_to_json(ba) FROM (
        SELECT recipient_name, phone, address_line1, address_line2, city, state, postal_code, country
        FROM user_addresses WHERE id = o.billing_address_id
    ) ba) as billing_address,
    
    -- Items
    (SELECT JSON_AGG(row_to_json(t)) FROM (
        SELECT oi.id, oi.product_id, oi.product_name, oi.product_image_url, 
               oi.quantity, oi.price_cents, oi.warehouse_id, w.name as warehouse_name
        FROM order_items oi
        LEFT JOIN warehouses w ON oi.warehouse_id = w.id
        WHERE oi.order_id = o.id
    ) t) as items,
    
    (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count,
    
    -- Payments
    (SELECT JSON_AGG(row_to_json(t)) FROM (
        SELECT id, amount_cents, currency, gateway, transaction_id, status, created_at
        FROM payments
        WHERE order_id = o.id
    ) t) as payments,
    
    -- Cart info
    o.cart_id,
    (SELECT session_id FROM carts WHERE id = o.cart_id) as cart_session_id
    
FROM orders o
LEFT JOIN users u ON o.user_id = u.id;

-- 5. Suppliers
CREATE VIEW admin_list_suppliers AS
SELECT 
    s.id, s.name, s.email, s.phone, s.is_active, s.created_at,
    (SELECT COUNT(*) FROM products WHERE supplier_id = s.id AND is_active = TRUE) as product_count
FROM suppliers s
WHERE s.deleted_at IS NULL
ORDER BY s.name;

CREATE VIEW admin_detail_suppliers AS
SELECT 
    s.id, s.name, s.email, s.phone, s.address, s.is_active,
    s.created_at, s.updated_at,
    
    (SELECT COUNT(*) FROM products WHERE supplier_id = s.id) as total_products,
    (SELECT COUNT(*) FROM products WHERE supplier_id = s.id AND is_active = TRUE) as active_products,
    (SELECT AVG(price_cents) FROM products WHERE supplier_id = s.id) as avg_product_price_cents,
    
    (SELECT COALESCE(SUM(st.quantity), 0) FROM stock st JOIN products p ON st.product_id = p.id WHERE p.supplier_id = s.id) as total_inventory,
    
    (SELECT JSON_AGG(row_to_json(t)) FROM (
        SELECT id, name, slug, price_cents, is_active
        FROM products
        WHERE supplier_id = s.id
        ORDER BY name
        LIMIT 20
    ) t) as products
    
FROM suppliers s
WHERE s.deleted_at IS NULL;

-- 6. Warehouses
CREATE VIEW admin_list_warehouses AS
SELECT 
    w.id, w.name, w.phone, w.is_active, w.created_at,
    (SELECT COALESCE(SUM(quantity - reserved), 0) FROM stock WHERE warehouse_id = w.id) as available_stock,
    (SELECT COUNT(DISTINCT product_id) FROM stock WHERE warehouse_id = w.id) as unique_products
FROM warehouses w
WHERE w.deleted_at IS NULL
ORDER BY w.name;

CREATE VIEW admin_detail_warehouses AS
SELECT 
    w.id, w.name, w.address, w.phone, w.image_url, w.is_active,
    w.created_at, w.updated_at,
    
    (SELECT COUNT(*) FROM stock WHERE warehouse_id = w.id) as total_stock_entries,
    (SELECT COUNT(DISTINCT product_id) FROM stock WHERE warehouse_id = w.id) as unique_products,
    (SELECT COALESCE(SUM(quantity), 0) FROM stock WHERE warehouse_id = w.id) as total_quantity,
    (SELECT COALESCE(SUM(reserved), 0) FROM stock WHERE warehouse_id = w.id) as total_reserved,
    (SELECT COALESCE(SUM(quantity - reserved), 0) FROM stock WHERE warehouse_id = w.id) as total_available,
    
    -- Low stock items
    (SELECT JSON_AGG(row_to_json(t)) FROM (
        SELECT p.name as product_name, s.quantity, s.reserved, (s.quantity - s.reserved) as available
        FROM stock s
        JOIN products p ON s.product_id = p.id
        WHERE s.warehouse_id = w.id AND s.quantity < 20
        ORDER BY s.quantity ASC
        LIMIT 10
    ) t) as low_stock_items,
    
    -- Recent shipments (orders with items from this warehouse)
    (SELECT JSON_AGG(row_to_json(t)) FROM (
        SELECT DISTINCT o.order_number, o.status, o.created_at
        FROM orders o
        JOIN order_items oi ON oi.order_id = o.id
        WHERE oi.warehouse_id = w.id
        ORDER BY o.created_at DESC
        LIMIT 10
    ) t) as recent_shipments
    
FROM warehouses w
WHERE w.deleted_at IS NULL;

-- 7. Stock
CREATE VIEW admin_list_stock AS
SELECT 
    s.id, s.product_id, p.name as product_name,
    s.warehouse_id, w.name as warehouse_name,
    s.quantity, s.reserved, (s.quantity - s.reserved) as available,
    s.updated_at
FROM stock s
JOIN products p ON s.product_id = p.id
JOIN warehouses w ON s.warehouse_id = w.id
WHERE p.deleted_at IS NULL AND w.deleted_at IS NULL
ORDER BY s.updated_at DESC;

CREATE VIEW admin_detail_stock AS
SELECT 
    s.id, s.product_id, p.name as product_name, p.slug as product_slug, p.price_cents,
    s.warehouse_id, w.name as warehouse_name, w.address as warehouse_address,
    s.quantity, s.reserved, (s.quantity - s.reserved) as available,
    s.created_at, s.updated_at,
    
    (p.price_cents * (s.quantity - s.reserved) / 100.0) as inventory_value,
    
    -- Recent movements (from order_items)
    (SELECT JSON_AGG(row_to_json(t)) FROM (
        SELECT o.order_number, oi.quantity, o.status, o.created_at
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        WHERE oi.product_id = s.product_id AND oi.warehouse_id = s.warehouse_id
        ORDER BY o.created_at DESC
        LIMIT 10
    ) t) as recent_movements
    
FROM stock s
JOIN products p ON s.product_id = p.id
JOIN warehouses w ON s.warehouse_id = w.id
WHERE p.deleted_at IS NULL AND w.deleted_at IS NULL;

-- 8. Feedback
CREATE VIEW admin_list_feedback AS
SELECT 
    f.id, f.rating, f.is_active, f.created_at,
    u.name as user_name, p.name as product_name,
    f.is_verified_purchase
FROM feedback f
JOIN users u ON f.user_id = u.id
JOIN products p ON f.product_id = p.id
WHERE f.deleted_at IS NULL
ORDER BY f.created_at DESC;

CREATE VIEW admin_detail_feedback AS
SELECT 
    f.id, f.rating, f.comment, f.is_verified_purchase, f.is_active,
    f.created_at, f.updated_at,
    
    f.user_id, u.name as user_name, u.email as user_email,
    f.product_id, p.name as product_name, p.slug as product_slug,
    
    -- Did they actually buy it?
    (SELECT COUNT(*) FROM order_items oi 
     JOIN orders o ON oi.order_id = o.id 
     WHERE oi.product_id = f.product_id AND o.user_id = f.user_id AND o.status IN ('paid', 'delivered')) as purchase_count
    
FROM feedback f
JOIN users u ON f.user_id = u.id
JOIN products p ON f.product_id = p.id
WHERE f.deleted_at IS NULL;

-- 9. Cocktail Recipes
CREATE VIEW admin_list_cocktail_recipes AS
SELECT 
    cr.id, cr.name, cr.difficulty, cr.preparation_time, cr.serves, cr.is_active, cr.created_at,
    (SELECT COUNT(*) FROM recipe_ingredients WHERE recipe_id = cr.id) as ingredient_count
FROM cocktail_recipes cr
WHERE cr.deleted_at IS NULL
ORDER BY cr.created_at DESC;

CREATE VIEW admin_detail_cocktail_recipes AS
SELECT 
    cr.id, cr.name, cr.description, cr.instructions, cr.image_url,
    cr.difficulty, cr.preparation_time, cr.serves, cr.is_active,
    cr.created_at, cr.updated_at,
    
    (SELECT JSON_AGG(row_to_json(t)) FROM (
        SELECT ri.id, ri.product_id, p.name as product_name, ri.quantity, ri.unit, ri.is_optional
        FROM recipe_ingredients ri
        JOIN products p ON ri.product_id = p.id
        WHERE ri.recipe_id = cr.id
    ) t) as ingredients,
    
    (SELECT COUNT(*) FROM recipe_ingredients WHERE recipe_id = cr.id) as ingredient_count,
    
    -- Total cost to make
    (SELECT COALESCE(SUM(p.price_cents * ri.quantity), 0) 
     FROM recipe_ingredients ri 
     JOIN products p ON ri.product_id = p.id 
     WHERE ri.recipe_id = cr.id AND ri.is_optional = FALSE) as estimated_cost_cents
    
FROM cocktail_recipes cr
WHERE cr.deleted_at IS NULL;

-- 10. Carts
CREATE VIEW admin_list_carts AS
SELECT 
    c.id, c.session_id, c.status, c.total_cents, c.item_count,
    c.created_at, c.updated_at,
    u.name as user_name, u.email as user_email
FROM carts c
LEFT JOIN users u ON c.user_id = u.id
ORDER BY c.updated_at DESC;

CREATE VIEW admin_detail_carts AS
SELECT 
    c.id, c.session_id, c.status, c.total_cents, c.item_count,
    c.created_at, c.updated_at, c.converted_at, c.abandoned_at,
    
    c.user_id, u.name as user_name, u.email as user_email,
    
    (SELECT JSON_AGG(row_to_json(t)) FROM (
        SELECT ci.id, ci.product_id, p.name as product_name, ci.quantity, ci.price_at_add_cents
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.cart_id = c.id
    ) t) as items,
    
    -- If converted, show order
    (SELECT order_number FROM orders WHERE cart_id = c.id LIMIT 1) as converted_order_number
    
FROM carts c
LEFT JOIN users u ON c.user_id = u.id;

-- 11. Payments
CREATE VIEW admin_list_payments AS
SELECT 
    p.id, p.order_id, o.order_number, p.amount_cents, p.gateway,
    p.status, p.created_at
FROM payments p
JOIN orders o ON p.order_id = o.id
ORDER BY p.created_at DESC;

CREATE VIEW admin_detail_payments AS
SELECT 
    p.id, p.order_id, o.order_number, o.status as order_status,
    p.amount_cents, p.currency, p.gateway, p.gateway_order_id, p.transaction_id,
    p.status, p.payload, p.created_at,
    
    o.user_id, u.name as user_name, u.email as user_email,
    o.total_cents as order_total_cents
    
FROM payments p
JOIN orders o ON p.order_id = o.id
LEFT JOIN users u ON o.user_id = u.id;

-- 12. User Addresses
CREATE VIEW admin_list_user_addresses AS
SELECT 
    ua.id, ua.user_id, u.name as user_name, u.email as user_email,
    ua.address_type, ua.city, ua.country, ua.is_default, ua.created_at
FROM user_addresses ua
JOIN users u ON ua.user_id = u.id
WHERE ua.deleted_at IS NULL
ORDER BY ua.created_at DESC;

CREATE VIEW admin_detail_user_addresses AS
SELECT 
    ua.id, ua.user_id, u.name as user_name, u.email as user_email,
    ua.address_type, ua.recipient_name, ua.phone,
    ua.address_line1, ua.address_line2, ua.city, ua.state, ua.postal_code, ua.country,
    ua.is_default, ua.created_at, ua.updated_at,
    
    -- Usage
    (SELECT COUNT(*) FROM orders WHERE shipping_address_id = ua.id) as used_as_shipping,
    (SELECT COUNT(*) FROM orders WHERE billing_address_id = ua.id) as used_as_billing
    
FROM user_addresses ua
JOIN users u ON ua.user_id = u.id
WHERE ua.deleted_at IS NULL;

-- 13. User Preferences
CREATE VIEW admin_list_user_preferences AS
SELECT 
    up.id, up.user_id, u.name as user_name, u.email as user_email,
    up.created_at, up.updated_at
FROM user_preferences up
JOIN users u ON up.user_id = u.id;

CREATE VIEW admin_detail_user_preferences AS
SELECT 
    up.id, up.user_id, u.name as user_name, u.email as user_email,
    up.preferred_sweetness, up.preferred_bitterness, up.preferred_strength,
    up.preferred_smokiness, up.preferred_fruitiness, up.preferred_spiciness,
    up.favorite_categories,
    (SELECT STRING_AGG(c.name, ', ') FROM categories c WHERE c.id = ANY(up.favorite_categories)) as favorite_category_names,
    up.created_at, up.updated_at
FROM user_preferences up
JOIN users u ON up.user_id = u.id;

-- 14. Flavor Profiles
CREATE VIEW admin_list_flavor_profiles AS
SELECT 
    fp.product_id, p.name as product_name, p.slug as product_slug,
    fp.sweetness, fp.bitterness, fp.strength
FROM flavor_profiles fp
JOIN products p ON fp.product_id = p.id
WHERE p.deleted_at IS NULL;

CREATE VIEW admin_detail_flavor_profiles AS
SELECT 
    fp.product_id, p.name as product_name, p.slug as product_slug,
    fp.sweetness, fp.bitterness, fp.strength, fp.smokiness, fp.fruitiness, fp.spiciness,
    fp.tags,
    
    (SELECT AVG(rating) FROM feedback WHERE product_id = fp.product_id AND is_active = TRUE) as avg_rating,
    (SELECT COUNT(*) FROM feedback WHERE product_id = fp.product_id AND is_active = TRUE) as feedback_count
    
FROM flavor_profiles fp
JOIN products p ON fp.product_id = p.id
WHERE p.deleted_at IS NULL;

-- 15. Product Recognition
CREATE VIEW admin_list_product_recognition AS
SELECT 
    pr.id, pr.session_id, pr.matched_product_id, p.name as matched_product_name,
    pr.confidence_score, pr.api_provider, pr.created_at,
    u.name as user_name
FROM product_recognition pr
LEFT JOIN users u ON pr.user_id = u.id
LEFT JOIN products p ON pr.matched_product_id = p.id
ORDER BY pr.created_at DESC;

CREATE VIEW admin_detail_product_recognition AS
SELECT 
    pr.id, pr.user_id, u.name as user_name, u.email as user_email,
    pr.session_id, pr.image_url, pr.recognized_text, pr.recognized_labels,
    pr.matched_product_id, p.name as matched_product_name, p.slug as matched_product_slug,
    pr.confidence_score, pr.api_provider, pr.created_at
FROM product_recognition pr
LEFT JOIN users u ON pr.user_id = u.id
LEFT JOIN products p ON pr.matched_product_id = p.id;

-- 16. Order Items
CREATE VIEW admin_list_order_items AS
SELECT 
    oi.id, oi.order_id, o.order_number, oi.product_name, oi.quantity, oi.price_cents,
    oi.created_at, w.name as warehouse_name
FROM order_items oi
JOIN orders o ON oi.order_id = o.id
LEFT JOIN warehouses w ON oi.warehouse_id = w.id
ORDER BY oi.created_at DESC;

CREATE VIEW admin_detail_order_items AS
SELECT 
    oi.id, oi.order_id, o.order_number, o.status as order_status,
    oi.product_id, oi.product_name, oi.product_image_url,
    oi.quantity, oi.price_cents, (oi.quantity * oi.price_cents) as subtotal_cents,
    oi.warehouse_id, w.name as warehouse_name, w.address as warehouse_address,
    oi.created_at,
    
    -- Current product info
    (SELECT row_to_json(p) FROM (
        SELECT id, name, slug, price_cents, is_active
        FROM products WHERE id = oi.product_id
    ) p) as current_product_info
    
FROM order_items oi
JOIN orders o ON oi.order_id = o.id
LEFT JOIN warehouses w ON oi.warehouse_id = w.id;

-- 17. Recipe Ingredients
CREATE VIEW admin_list_recipe_ingredients AS
SELECT 
    ri.id, ri.recipe_id, cr.name as recipe_name,
    ri.product_id, p.name as product_name,
    ri.quantity, ri.unit, ri.is_optional
FROM recipe_ingredients ri
JOIN cocktail_recipes cr ON ri.recipe_id = cr.id
JOIN products p ON ri.product_id = p.id
WHERE cr.deleted_at IS NULL AND p.deleted_at IS NULL;

CREATE VIEW admin_detail_recipe_ingredients AS
SELECT 
    ri.id, ri.recipe_id, cr.name as recipe_name, cr.difficulty,
    ri.product_id, p.name as product_name, p.price_cents as product_price_cents,
    ri.quantity, ri.unit, ri.is_optional, ri.created_at,
    
    (p.price_cents * ri.quantity) as ingredient_cost_cents
    
FROM recipe_ingredients ri
JOIN cocktail_recipes cr ON ri.recipe_id = cr.id
JOIN products p ON ri.product_id = p.id
WHERE cr.deleted_at IS NULL AND p.deleted_at IS NULL;

-- 18. Cart Items
CREATE VIEW admin_list_cart_items AS
SELECT 
    ci.id, ci.cart_id, c.session_id, ci.product_id, p.name as product_name,
    ci.quantity, ci.price_at_add_cents, ci.created_at
FROM cart_items ci
JOIN carts c ON ci.cart_id = c.id
JOIN products p ON ci.product_id = p.id
WHERE p.deleted_at IS NULL
ORDER BY ci.created_at DESC;

CREATE VIEW admin_detail_cart_items AS
SELECT 
    ci.id, ci.cart_id, c.session_id, c.status as cart_status,
    ci.product_id, p.name as product_name, p.price_cents as current_price_cents,
    ci.quantity, ci.price_at_add_cents,
    (ci.quantity * ci.price_at_add_cents) as subtotal_cents,
    ci.created_at, ci.updated_at,
    
    -- Price difference
    (p.price_cents - ci.price_at_add_cents) as price_difference_cents
    
FROM cart_items ci
JOIN carts c ON ci.cart_id = c.id
JOIN products p ON ci.product_id = p.id
WHERE p.deleted_at IS NULL;

ALTER TABLE order_items 
ADD COLUMN warehouse_id BIGINT REFERENCES warehouses(id);
ALTER TABLE recipe_ingredients
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP;
-- set default for future inserts/updates (optional)
ALTER TABLE recipe_ingredients
ALTER COLUMN updated_at SET DEFAULT NOW();

-- set existing nulls to now (optional)
UPDATE recipe_ingredients
SET updated_at = NOW()
WHERE updated_at IS NULL;
ALTER TABLE payments 
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMPTZ 
DEFAULT NOW() NOT NULL;
ALTER TABLE your_table DROP CONSTRAINT your_table_matched_product_id_fkey;
-- Drop the foreign key
ALTER TABLE product_recognition
DROP CONSTRAINT product_recognition_matched_product_id_fkey;

-- Optionally, allow it to be NULL
ALTER TABLE product_recognition
ALTER COLUMN matched_product_id DROP NOT NULL

ALTER TABLE product_recognition
ADD COLUMN updated_at TIMESTAMPTZ DEFAULT NOW();



ALTER TABLE flavor_profiles
ADD COLUMN created_at TIMESTAMPTZ DEFAULT NOW(),
ADD COLUMN updated_at TIMESTAMPTZ DEFAULT NOW();

-- Reset all sequences to max(id) + 1 to fix auto-increment issues
-- Run this after seeding data or if you get "duplicate key" errors
SELECT setval('users_id_seq', COALESCE((SELECT MAX(id) FROM users), 0) + 1, false);
SELECT setval('user_addresses_id_seq', COALESCE((SELECT MAX(id) FROM user_addresses), 0) + 1, false);
SELECT setval('categories_id_seq', COALESCE((SELECT MAX(id) FROM categories), 0) + 1, false);
SELECT setval('suppliers_id_seq', COALESCE((SELECT MAX(id) FROM suppliers), 0) + 1, false);
SELECT setval('warehouses_id_seq', COALESCE((SELECT MAX(id) FROM warehouses), 0) + 1, false);
SELECT setval('products_id_seq', COALESCE((SELECT MAX(id) FROM products), 0) + 1, false);
SELECT setval('stock_id_seq', COALESCE((SELECT MAX(id) FROM stock), 0) + 1, false);
SELECT setval('carts_id_seq', COALESCE((SELECT MAX(id) FROM carts), 0) + 1, false);
SELECT setval('cart_items_id_seq', COALESCE((SELECT MAX(id) FROM cart_items), 0) + 1, false);
SELECT setval('orders_id_seq', COALESCE((SELECT MAX(id) FROM orders), 0) + 1, false);
SELECT setval('order_items_id_seq', COALESCE((SELECT MAX(id) FROM order_items), 0) + 1, false);
SELECT setval('payments_id_seq', COALESCE((SELECT MAX(id) FROM payments), 0) + 1, false);
SELECT setval('feedback_id_seq', COALESCE((SELECT MAX(id) FROM feedback), 0) + 1, false);
SELECT setval('cocktail_recipes_id_seq', COALESCE((SELECT MAX(id) FROM cocktail_recipes), 0) + 1, false);
SELECT setval('recipe_ingredients_id_seq', COALESCE((SELECT MAX(id) FROM recipe_ingredients), 0) + 1, false);
SELECT setval('user_preferences_id_seq', COALESCE((SELECT MAX(id) FROM user_preferences), 0) + 1, false);
SELECT setval('product_recognition_id_seq', COALESCE((SELECT MAX(id) FROM product_recognition), 0) + 1, false);
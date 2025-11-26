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

-- TRIGGERS & FUNCTIONS
CREATE OR REPLACE FUNCTION trigger_set_timestamp()
RETURNS TRIGGER AS $$ BEGIN NEW.updated_at = NOW(); RETURN NEW; END; $$ LANGUAGE plpgsql;

DO $$
DECLARE t text;
    tables text[] := ARRAY['users','user_addresses','categories','suppliers','warehouses','products','stock','carts','cart_items','orders','order_items','payments','flavor_profiles','feedback','cocktail_recipes','recipe_ingredients','user_preferences','product_recognition'];
BEGIN
    FOREACH t IN ARRAY tables LOOP
        EXECUTE format('CREATE TRIGGER trg_%I_updated BEFORE UPDATE ON %I FOR EACH ROW EXECUTE FUNCTION trigger_set_timestamp()', t, t);
    END LOOP;
END $$;

CREATE OR REPLACE FUNCTION generate_order_number()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.order_number = '' OR NEW.order_number IS NULL THEN
        NEW.order_number := 'ORD-' || EXTRACT(YEAR FROM NOW()) || '-' || LPAD(nextval('order_number_seq')::TEXT, 6, '0');
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER trg_generate_order_number BEFORE INSERT ON orders FOR EACH ROW EXECUTE FUNCTION generate_order_number();

CREATE OR REPLACE FUNCTION reserve_stock() RETURNS TRIGGER AS $$
BEGIN
    UPDATE stock s SET reserved = reserved + oi.quantity
    FROM order_items oi WHERE oi.order_id = NEW.id AND oi.product_id = s.product_id;
    IF EXISTS (SELECT 1 FROM stock s JOIN order_items oi ON oi.product_id = s.product_id WHERE oi.order_id = NEW.id AND s.quantity - s.reserved < 0) THEN
        RAISE EXCEPTION 'Insufficient stock';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER trg_reserve_stock AFTER INSERT ON orders FOR EACH ROW EXECUTE FUNCTION reserve_stock();

CREATE OR REPLACE FUNCTION deduct_stock_on_paid() RETURNS TRIGGER AS $$
BEGIN
    IF NEW.status = 'paid' AND OLD.status IS DISTINCT FROM 'paid' THEN
        UPDATE stock s SET quantity = quantity - oi.quantity, reserved = reserved - oi.quantity
        FROM order_items oi WHERE oi.order_id = NEW.id AND oi.product_id = s.product_id;
        NEW.paid_at := NOW();
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER trg_deduct_stock AFTER UPDATE ON orders FOR EACH ROW WHEN (NEW.status = 'paid' AND OLD.status IS DISTINCT FROM 'paid') EXECUTE FUNCTION deduct_stock_on_paid();

CREATE OR REPLACE FUNCTION return_stock_on_cancel() RETURNS TRIGGER AS $$
BEGIN
    IF NEW.status IN ('cancelled','refunded') AND OLD.status NOT IN ('cancelled','refunded') THEN
        UPDATE stock s SET quantity = quantity + oi.quantity, reserved = reserved - oi.quantity
        FROM order_items oi WHERE oi.order_id = NEW.id AND oi.product_id = s.product_id;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER trg_return_stock AFTER UPDATE ON orders FOR EACH ROW WHEN (NEW.status IN ('cancelled','refunded') AND OLD.status NOT IN ('cancelled','refunded')) EXECUTE FUNCTION return_stock_on_cancel();

CREATE OR REPLACE FUNCTION mark_cart_converted() RETURNS TRIGGER AS $$
BEGIN
    UPDATE carts SET status='converted', converted_at=NOW() WHERE id=NEW.cart_id;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER trg_mark_cart_converted AFTER INSERT ON orders FOR EACH ROW EXECUTE FUNCTION mark_cart_converted();

CREATE OR REPLACE FUNCTION recalc_cart_totals() RETURNS TRIGGER AS $$
BEGIN
    UPDATE carts c SET
        total_cents = (SELECT COALESCE(SUM(quantity * price_at_add_cents),0) FROM cart_items ci WHERE ci.cart_id = c.id),
        item_count = (SELECT COUNT(*) FROM cart_items ci WHERE ci.cart_id = c.id)
    WHERE c.id = COALESCE(NEW.cart_id, OLD.cart_id);
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER trg_recalc_cart AFTER INSERT OR UPDATE OR DELETE ON cart_items FOR EACH ROW EXECUTE FUNCTION recalc_cart_totals();

CREATE OR REPLACE FUNCTION enforce_default_address() RETURNS TRIGGER AS $$
BEGIN
    IF NEW.is_default THEN
        UPDATE user_addresses SET is_default = FALSE
        WHERE user_id = NEW.user_id AND address_type = NEW.address_type AND id != NEW.id;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER trg_default_address BEFORE INSERT OR UPDATE ON user_addresses FOR EACH ROW WHEN (NEW.is_default = TRUE) EXECUTE FUNCTION enforce_default_address();


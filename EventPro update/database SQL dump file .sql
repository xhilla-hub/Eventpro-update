
--  EventPro PostgreSQL Database Setup


-- ── USERS TABLE
CREATE TABLE IF NOT EXISTS users (
    id          SERIAL PRIMARY KEY,
    fullname    VARCHAR(150) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    phone       VARCHAR(20)  NOT NULL,
    password    VARCHAR(255) NOT NULL,
    avatar      VARCHAR(255) DEFAULT NULL,
    role        VARCHAR(50)  DEFAULT 'user',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed admin user
INSERT INTO users (fullname, email, phone, password, role)
VALUES ('Admin', 'admin@gmail.com', '0700000000', '$2y$10$A0AS6h6ZaDuKWb2HY/hu3ujkxdKVXfLJmrJXl3twtcOFKwvC8Ic4e', 'admin')
ON CONFLICT (email) DO NOTHING;

-- ── PACKAGES TABLE
CREATE TABLE IF NOT EXISTS packages (
    id          SERIAL PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    badge       VARCHAR(50)  DEFAULT NULL,
    price       DECIMAL(10,2) NOT NULL,
    description TEXT,
    features    TEXT,
    icon        VARCHAR(10)  DEFAULT '🎉',
    color       VARCHAR(20)  DEFAULT '#cc3300',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── BOOKINGS TABLE
CREATE TABLE IF NOT EXISTS bookings (
    id              SERIAL PRIMARY KEY,
    user_id         INTEGER NOT NULL,
    package_id      INTEGER NOT NULL,
    event_name      VARCHAR(200) NOT NULL,
    event_type      VARCHAR(100) NOT NULL,
    event_date      DATE NOT NULL,
    event_location  VARCHAR(255) NOT NULL,
    guests          INTEGER NOT NULL DEFAULT 0,
    special_notes   TEXT,
    total_amount    DECIMAL(10,2) NOT NULL,
    status          VARCHAR(50) DEFAULT 'pending' CHECK (status IN ('pending', 'confirmed', 'cancelled', 'completed')),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
);

-- ── PAYMENTS TABLE
CREATE TABLE IF NOT EXISTS payments (
    id              SERIAL PRIMARY KEY,
    booking_id      INTEGER NOT NULL,
    user_id         INTEGER NOT NULL,
    amount          DECIMAL(10,2) NOT NULL,
    phone           VARCHAR(20)   NOT NULL,
    mpesa_code      VARCHAR(50)   DEFAULT NULL,
    checkout_id     VARCHAR(100)  DEFAULT NULL,
    method          VARCHAR(50) DEFAULT 'mpesa' CHECK (method IN ('mpesa', 'tigopesa', 'airtelmoney', 'halopesa', 'card', 'cash')),
    status          VARCHAR(50) DEFAULT 'pending' CHECK (status IN ('pending', 'completed', 'failed')),
    paid_at         TIMESTAMP DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
);

-- ── SEED PACKAGES
-- Clear existing to avoid duplicates if run multiple times
TRUNCATE TABLE packages CASCADE;

INSERT INTO packages (name, badge, price, description, features, icon, color) VALUES
('Starter',  'STARTER',      45000.00, 'Perfect for small gatherings and private parties up to 100 guests.',
 '1 Sound Vendor|1 Photography Package|Basic Decor Setup|Event Timeline Tool|Email Support', '🎈', '#cc3300'),
('Pro',      'PRO',         120000.00, 'Ideal for corporate events, conferences, and functions up to 500 guests.',
 'Full PA Sound System|Professional Photography + Video|Premium Tent & Decor|Catering Coordination|Dedicated Event Manager|Real-Time Dashboard', '⭐', '#cc3300'),
('Elite',    'ELITE',       350000.00, 'Full-scale productions — concerts, large conferences, 1000+ attendees.',
 'Concert-Grade Sound Rigs|Multi-Camera Production|Custom Stage & Lighting Design|Full Catering & Hospitality|On-Site Event Director|M-Pesa Integrated Payments|24/7 Priority Support', '👑', '#cc3300');

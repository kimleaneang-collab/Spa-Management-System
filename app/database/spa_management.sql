-- ============================================================
-- RELAX SPA MANAGEMENT SYSTEM
-- MySQL 8.0+
-- Complete database foundation for all 16 functional areas.
-- Login is the first implemented module in this code package.
-- ============================================================

CREATE DATABASE IF NOT EXISTS spa_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE spa_management;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS employee_schedules;
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS employees;
DROP TABLE IF EXISTS refunds;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS invoice_items;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS purchase_order_items;
DROP TABLE IF EXISTS purchase_orders;
DROP TABLE IF EXISTS stock_transactions;
DROP TABLE IF EXISTS supplier_products;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS treatment_rooms;
DROP TABLE IF EXISTS therapist_schedules;
DROP TABLE IF EXISTS therapist_services;
DROP TABLE IF EXISTS therapists;
DROP TABLE IF EXISTS service_products;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS service_categories;
DROP TABLE IF EXISTS memberships;
DROP TABLE IF EXISTS membership_levels;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. USER AUTHENTICATION / RBAC
CREATE TABLE roles (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id TINYINT UNSIGNED NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NULL UNIQUE,
    phone VARCHAR(30) NULL,
    status ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
    last_login_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_role
        FOREIGN KEY (role_id) REFERENCES roles(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_users_status (status)
) ENGINE=InnoDB;

CREATE TABLE login_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NOT NULL,
    was_successful BOOLEAN NOT NULL DEFAULT FALSE,
    attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_login_attempt_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL,
    INDEX idx_login_attempts_username_time (username, attempted_at),
    INDEX idx_login_attempts_ip_time (ip_address, attempted_at)
) ENGINE=InnoDB;

-- 2. CUSTOMERS
CREATE TABLE customers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_code VARCHAR(30) NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    gender ENUM('male','female','other','prefer_not_to_say') NULL,
    date_of_birth DATE NULL,
    phone VARCHAR(30) NOT NULL,
    email VARCHAR(150) NULL,
    address TEXT NULL,
    emergency_contact_name VARCHAR(150) NULL,
    emergency_contact_phone VARCHAR(30) NULL,
    notes TEXT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customer_name (full_name),
    INDEX idx_customer_phone (phone)
) ENGINE=InnoDB;

-- 3. MEMBERSHIP
CREATE TABLE membership_levels (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
    loyalty_points_multiplier DECIMAL(5,2) NOT NULL DEFAULT 1,
    annual_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE memberships (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    level_id TINYINT UNSIGNED NOT NULL,
    membership_no VARCHAR(50) NOT NULL UNIQUE,
    start_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    loyalty_points INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('active','expired','cancelled') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_membership_customer FOREIGN KEY (customer_id)
        REFERENCES customers(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_membership_level FOREIGN KEY (level_id)
        REFERENCES membership_levels(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_membership_expiry (expiry_date, status)
) ENGINE=InnoDB;

-- 4-5. SERVICE + CATEGORY
CREATE TABLE service_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NOT NULL,
    service_code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    duration_minutes SMALLINT UNSIGNED NOT NULL,
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    description TEXT NULL,
    image_path VARCHAR(255) NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_service_category FOREIGN KEY (category_id)
        REFERENCES service_categories(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_service_name (name),
    INDEX idx_service_status (status)
) ENGINE=InnoDB;

-- Required products for services
CREATE TABLE service_products (
    service_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(12,3) NOT NULL DEFAULT 1,
    PRIMARY KEY (service_id, product_id)
) ENGINE=InnoDB;

-- 6. THERAPISTS
CREATE TABLE therapists (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    therapist_code VARCHAR(30) NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    gender ENUM('male','female','other') NULL,
    phone VARCHAR(30) NOT NULL,
    specialization VARCHAR(150) NULL,
    experience_years DECIMAL(4,1) NOT NULL DEFAULT 0,
    employment_status ENUM('active','inactive','on_leave') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_therapist_name (full_name),
    INDEX idx_therapist_status (employment_status)
) ENGINE=InnoDB;

CREATE TABLE therapist_services (
    therapist_id BIGINT UNSIGNED NOT NULL,
    service_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (therapist_id, service_id),
    CONSTRAINT fk_ts_therapist FOREIGN KEY (therapist_id)
        REFERENCES therapists(id) ON DELETE CASCADE,
    CONSTRAINT fk_ts_service FOREIGN KEY (service_id)
        REFERENCES services(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE therapist_schedules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    therapist_id BIGINT UNSIGNED NOT NULL,
    day_of_week TINYINT UNSIGNED NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_day_off BOOLEAN NOT NULL DEFAULT FALSE,
    CONSTRAINT fk_schedule_therapist FOREIGN KEY (therapist_id)
        REFERENCES therapists(id) ON DELETE CASCADE,
    UNIQUE KEY uq_therapist_day (therapist_id, day_of_week),
    CONSTRAINT chk_schedule_day CHECK (day_of_week BETWEEN 1 AND 7)
) ENGINE=InnoDB;

-- 7. APPOINTMENTS
CREATE TABLE appointments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_no VARCHAR(40) NOT NULL UNIQUE,
    customer_id BIGINT UNSIGNED NOT NULL,
    service_id BIGINT UNSIGNED NOT NULL,
    therapist_id BIGINT UNSIGNED NULL,
    room_id BIGINT UNSIGNED NULL,
    appointment_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('pending','confirmed','in_progress','completed','cancelled')
        NOT NULL DEFAULT 'pending',
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_appointment_customer FOREIGN KEY (customer_id)
        REFERENCES customers(id) ON DELETE RESTRICT,
    CONSTRAINT fk_appointment_service FOREIGN KEY (service_id)
        REFERENCES services(id) ON DELETE RESTRICT,
    CONSTRAINT fk_appointment_therapist FOREIGN KEY (therapist_id)
        REFERENCES therapists(id) ON DELETE SET NULL,
    CONSTRAINT fk_appointment_user FOREIGN KEY (created_by)
        REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_appointment_date_status (appointment_date, status),
    INDEX idx_appointment_therapist_time (therapist_id, appointment_date, start_time),
    INDEX idx_appointment_room_time (room_id, appointment_date, start_time)
) ENGINE=InnoDB;

-- 8. TREATMENT ROOMS
CREATE TABLE treatment_rooms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_code VARCHAR(30) NOT NULL UNIQUE,
    room_name VARCHAR(100) NOT NULL,
    room_type VARCHAR(100) NULL,
    capacity SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    status ENUM('available','occupied','cleaning','under_maintenance')
        NOT NULL DEFAULT 'available',
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 9. PRODUCTS / INVENTORY
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_code VARCHAR(40) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(100) NULL,
    unit VARCHAR(30) NOT NULL DEFAULT 'pcs',
    cost_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    selling_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    stock_quantity DECIMAL(14,3) NOT NULL DEFAULT 0,
    reorder_level DECIMAL(14,3) NOT NULL DEFAULT 0,
    expiry_date DATE NULL,
    image_path VARCHAR(255) NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_product_name (name),
    INDEX idx_product_stock (stock_quantity, reorder_level),
    INDEX idx_product_expiry (expiry_date)
) ENGINE=InnoDB;

CREATE TABLE suppliers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_code VARCHAR(40) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(150) NULL,
    phone VARCHAR(30) NULL,
    email VARCHAR(150) NULL,
    address TEXT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE supplier_products (
    supplier_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (supplier_id, product_id),
    CONSTRAINT fk_supplier_product_supplier FOREIGN KEY (supplier_id)
        REFERENCES suppliers(id) ON DELETE CASCADE,
    CONSTRAINT fk_supplier_product_product FOREIGN KEY (product_id)
        REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE stock_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    transaction_type ENUM('stock_in','stock_out','adjustment') NOT NULL,
    quantity DECIMAL(14,3) NOT NULL,
    unit_cost DECIMAL(12,2) NULL,
    reference_type VARCHAR(50) NULL,
    reference_id BIGINT UNSIGNED NULL,
    reason VARCHAR(255) NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_stock_product FOREIGN KEY (product_id)
        REFERENCES products(id) ON DELETE RESTRICT,
    CONSTRAINT fk_stock_user FOREIGN KEY (created_by)
        REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_stock_product_date (product_id, created_at)
) ENGINE=InnoDB;

-- 10. PURCHASE ORDERS
CREATE TABLE purchase_orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    po_number VARCHAR(50) NOT NULL UNIQUE,
    supplier_id BIGINT UNSIGNED NOT NULL,
    order_date DATE NOT NULL,
    expected_date DATE NULL,
    status ENUM('draft','ordered','received','cancelled') NOT NULL DEFAULT 'draft',
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_po_supplier FOREIGN KEY (supplier_id)
        REFERENCES suppliers(id) ON DELETE RESTRICT,
    CONSTRAINT fk_po_user FOREIGN KEY (created_by)
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE purchase_order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    purchase_order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(14,3) NOT NULL,
    unit_cost DECIMAL(12,2) NOT NULL,
    total_cost DECIMAL(12,2) AS (quantity * unit_cost) STORED,
    CONSTRAINT fk_poi_po FOREIGN KEY (purchase_order_id)
        REFERENCES purchase_orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_poi_product FOREIGN KEY (product_id)
        REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 12. BILLING
CREATE TABLE invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_no VARCHAR(50) NOT NULL UNIQUE,
    customer_id BIGINT UNSIGNED NOT NULL,
    appointment_id BIGINT UNSIGNED NULL,
    membership_id BIGINT UNSIGNED NULL,
    invoice_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    status ENUM('draft','unpaid','partial','paid','void','refunded')
        NOT NULL DEFAULT 'unpaid',
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_invoice_customer FOREIGN KEY (customer_id)
        REFERENCES customers(id) ON DELETE RESTRICT,
    CONSTRAINT fk_invoice_appointment FOREIGN KEY (appointment_id)
        REFERENCES appointments(id) ON DELETE SET NULL,
    CONSTRAINT fk_invoice_membership FOREIGN KEY (membership_id)
        REFERENCES memberships(id) ON DELETE SET NULL,
    CONSTRAINT fk_invoice_user FOREIGN KEY (created_by)
        REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_invoice_date (invoice_date),
    INDEX idx_invoice_status (status)
) ENGINE=InnoDB;

CREATE TABLE invoice_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id BIGINT UNSIGNED NOT NULL,
    service_id BIGINT UNSIGNED NULL,
    product_id BIGINT UNSIGNED NULL,
    description VARCHAR(255) NOT NULL,
    quantity DECIMAL(12,3) NOT NULL DEFAULT 1,
    unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_invoice_item_invoice FOREIGN KEY (invoice_id)
        REFERENCES invoices(id) ON DELETE CASCADE,
    CONSTRAINT fk_invoice_item_service FOREIGN KEY (service_id)
        REFERENCES services(id) ON DELETE SET NULL,
    CONSTRAINT fk_invoice_item_product FOREIGN KEY (product_id)
        REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id BIGINT UNSIGNED NOT NULL,
    payment_no VARCHAR(50) NOT NULL UNIQUE,
    amount DECIMAL(12,2) NOT NULL,
    payment_method ENUM('cash','card','bank_transfer','qr_payment') NOT NULL,
    transaction_reference VARCHAR(150) NULL,
    paid_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    received_by BIGINT UNSIGNED NULL,
    notes VARCHAR(255) NULL,
    CONSTRAINT fk_payment_invoice FOREIGN KEY (invoice_id)
        REFERENCES invoices(id) ON DELETE RESTRICT,
    CONSTRAINT fk_payment_user FOREIGN KEY (received_by)
        REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_payment_date (paid_at)
) ENGINE=InnoDB;

CREATE TABLE refunds (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_id BIGINT UNSIGNED NOT NULL,
    refund_no VARCHAR(50) NOT NULL UNIQUE,
    amount DECIMAL(12,2) NOT NULL,
    reason VARCHAR(255) NOT NULL,
    refunded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    processed_by BIGINT UNSIGNED NULL,
    CONSTRAINT fk_refund_payment FOREIGN KEY (payment_id)
        REFERENCES payments(id) ON DELETE RESTRICT,
    CONSTRAINT fk_refund_user FOREIGN KEY (processed_by)
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 13. EMPLOYEES / ATTENDANCE
CREATE TABLE employees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_code VARCHAR(40) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    role_name ENUM('manager','receptionist','therapist','cashier','cleaner') NOT NULL,
    phone VARCHAR(30) NULL,
    email VARCHAR(150) NULL,
    salary DECIMAL(12,2) NOT NULL DEFAULT 0,
    hire_date DATE NULL,
    status ENUM('active','inactive','on_leave') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_employee_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE attendance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    attendance_date DATE NOT NULL,
    check_in DATETIME NULL,
    check_out DATETIME NULL,
    status ENUM('present','late','absent','leave','half_day') NOT NULL,
    notes VARCHAR(255) NULL,
    UNIQUE KEY uq_employee_attendance (employee_id, attendance_date),
    CONSTRAINT fk_attendance_employee FOREIGN KEY (employee_id)
        REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE employee_schedules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    day_of_week TINYINT UNSIGNED NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    is_day_off BOOLEAN NOT NULL DEFAULT FALSE,
    CONSTRAINT fk_employee_schedule_employee FOREIGN KEY (employee_id)
        REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY uq_employee_schedule_day (employee_id, day_of_week)
) ENGINE=InnoDB;

-- 15. NOTIFICATIONS
CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    type ENUM(
        'appointment_reminder',
        'membership_expiry',
        'low_stock',
        'therapist_schedule',
        'promotion'
    ) NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    reference_type VARCHAR(50) NULL,
    reference_id BIGINT UNSIGNED NULL,
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    scheduled_at DATETIME NULL,
    sent_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notification_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notification_user_read (user_id, is_read)
) ENGINE=InnoDB;

-- 16. SETTINGS
CREATE TABLE settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    setting_type ENUM('text','number','boolean','json') NOT NULL DEFAULT 'text',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- SYSTEM AUDIT LOG
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(100) NULL,
    record_id BIGINT UNSIGNED NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_audit_created (created_at),
    INDEX idx_audit_user (user_id)
) ENGINE=InnoDB;

-- ============================================================
-- INITIAL DATA
-- ============================================================

INSERT INTO roles (name, description) VALUES
('admin', 'Full system access'),
('staff', 'Operational spa management access'),
('receptionist', 'Customer and appointment access');

INSERT INTO membership_levels
    (name, discount_percent, loyalty_points_multiplier, annual_fee)
VALUES
    ('Silver', 5.00, 1.00, 0.00),
    ('Gold', 10.00, 1.50, 0.00),
    ('Platinum', 15.00, 2.00, 0.00);

INSERT INTO service_categories (name, description) VALUES
('Massage', 'Massage services'),
('Facial Care', 'Facial and skin care'),
('Body Treatment', 'Body scrub and body care'),
('Hair Care', 'Hair spa and hair treatments'),
('Nail Care', 'Manicure and pedicure'),
('Wellness Therapy', 'Sauna and wellness');

INSERT INTO settings (setting_key, setting_value, setting_type) VALUES
('spa_name', 'Relax Spa', 'text'),
('currency', 'USD', 'text'),
('tax_percent', '0', 'number'),
('business_hours', '{"monday":"09:00-21:00","tuesday":"09:00-21:00","wednesday":"09:00-21:00","thursday":"09:00-21:00","friday":"09:00-21:00","saturday":"09:00-21:00","sunday":"09:00-21:00"}', 'json');

-- Passwords are bcrypt hashes. Generate each hash with password_hash(), never store plaintext passwords.
INSERT INTO users
    (role_id, username, password_hash, full_name, email, status)
VALUES
    ((SELECT id FROM roles WHERE name = 'admin'), 'admin', '$2y$12$SocGAVxnqWwHKbTmvFTbhucdcVcC.TF9T6qh8xLw8Cfmsuhi3YVHy', 'System Administrator', 'admin@relaxspa.local', 'active'),
    ((SELECT id FROM roles WHERE name = 'staff'), 'staff', '$2y$12$SocGAVxnqWwHKbTmvFTbhucdcVcC.TF9Tqh8xLw8Cfmsuhi3YVHy', 'Spa Staff', 'staff@relaxspa.local', 'active'),
    ((SELECT id FROM roles WHERE name = 'receptionist'), 'receptionist', '$2y$12$SocGAVxnqWwHKbTmvFTbhucdcVcC.TF9Tqh8xLw8Cfmsuhi3YVHy', 'Spa Receptionist', 'receptionist@relaxspa.local', 'active');

-- Example services from the specification
INSERT INTO services
    (category_id, service_code, name, duration_minutes, price, description)
VALUES
((SELECT id FROM service_categories WHERE name='Massage'), 'SVC001', 'Full Body Massage', 60, 30.00, 'Full body relaxation massage'),
((SELECT id FROM service_categories WHERE name='Massage'), 'SVC002', 'Aromatherapy', 60, 35.00, 'Essential oil aromatherapy treatment'),
((SELECT id FROM service_categories WHERE name='Facial Care'), 'SVC003', 'Facial Treatment', 60, 40.00, 'Professional facial treatment'),
((SELECT id FROM service_categories WHERE name='Body Treatment'), 'SVC004', 'Body Scrub', 60, 35.00, 'Body exfoliation treatment'),
((SELECT id FROM service_categories WHERE name='Massage'), 'SVC005', 'Hot Stone Massage', 75, 45.00, 'Hot stone relaxation massage'),
((SELECT id FROM service_categories WHERE name='Massage'), 'SVC006', 'Foot Massage', 45, 25.00, 'Relaxing foot massage'),
((SELECT id FROM service_categories WHERE name='Nail Care'), 'SVC007', 'Manicure', 45, 20.00, 'Professional manicure'),
((SELECT id FROM service_categories WHERE name='Nail Care'), 'SVC008', 'Pedicure', 60, 25.00, 'Professional pedicure'),
((SELECT id FROM service_categories WHERE name='Hair Care'), 'SVC009', 'Hair Spa', 60, 35.00, 'Relaxing hair spa'),
((SELECT id FROM service_categories WHERE name='Wellness Therapy'), 'SVC010', 'Sauna', 30, 20.00, 'Wellness sauna session');

-- Example products
INSERT INTO products
    (product_code, name, category, unit, cost_price, selling_price, stock_quantity, reorder_level)
VALUES
('PRD001', 'Massage Oil', 'Spa Supplies', 'bottle', 8.00, 15.00, 20, 5),
('PRD002', 'Essential Oil', 'Spa Supplies', 'bottle', 10.00, 18.00, 15, 5),
('PRD003', 'Facial Cream', 'Skin Care', 'jar', 12.00, 22.00, 12, 4),
('PRD004', 'Body Lotion', 'Body Care', 'bottle', 7.00, 14.00, 18, 5),
('PRD005', 'Shampoo', 'Hair Care', 'bottle', 6.00, 12.00, 15, 5),
('PRD006', 'Towels', 'Supplies', 'pcs', 4.00, 8.00, 30, 10),
('PRD007', 'Candles', 'Supplies', 'pcs', 3.00, 7.00, 20, 5),
('PRD008', 'Spa Equipment', 'Equipment', 'pcs', 50.00, 90.00, 5, 1);

-- Example rooms
INSERT INTO treatment_rooms
    (room_code, room_name, room_type, capacity)
VALUES
('RM001', 'Treatment Room 1', 'Massage', 1),
('RM002', 'Treatment Room 2', 'Massage', 1),
('RM003', 'Facial Room', 'Facial', 1),
('RM004', 'Wellness Room', 'Sauna', 2);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- Payment System Tables
-- Additional tables for the certificate platform
-- ============================================

USE certificate_platform;

-- ============================================
-- جدول المدفوعات (Payments)
-- ============================================
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    certificate_id VARCHAR(100),
    payment_type ENUM('certificate_fee', 'verification_fee', 'subscription') DEFAULT 'certificate_fee',
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'USD',
    payment_method ENUM('stripe', 'paypal', 'crypto', 'bank_transfer') NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    transaction_id VARCHAR(255) UNIQUE,
    payment_gateway_response TEXT,
    paid_at TIMESTAMP NULL,
    refunded_at TIMESTAMP NULL,
    refund_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (payment_status),
    INDEX idx_transaction (transaction_id),
    INDEX idx_certificate (certificate_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- جدول طلبات التوثيق (Verification Requests)
-- ============================================
CREATE TABLE IF NOT EXISTS verification_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    requester_id INT NOT NULL,
    certificate_id VARCHAR(100) NOT NULL,
    request_type ENUM('employment', 'educational', 'legal', 'general') DEFAULT 'general',
    purpose TEXT,
    company_name VARCHAR(255),
    contact_person VARCHAR(255),
    contact_email VARCHAR(255),
    contact_phone VARCHAR(20),
    status ENUM('pending', 'approved', 'rejected', 'in_review') DEFAULT 'pending',
    admin_notes TEXT,
    rejection_reason TEXT,
    fee_amount DECIMAL(10, 2) DEFAULT 10.00,
    payment_id INT NULL,
    verified_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    
    FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_requester (requester_id),
    INDEX idx_certificate (certificate_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- جدول سجل الأنشطة (Activity Log)
-- ============================================
CREATE TABLE IF NOT EXISTS activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type ENUM('certificate', 'user', 'payment', 'verification_request', 'system') NOT NULL,
    entity_id INT,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- جدول الإشعارات (Notifications)
-- ============================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    action_url VARCHAR(500),
    related_entity_type VARCHAR(50),
    related_entity_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_read (is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- جدول الاشتراكات (Subscriptions)
-- ============================================
CREATE TABLE IF NOT EXISTS subscriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    plan_name VARCHAR(100) NOT NULL,
    plan_type ENUM('basic', 'professional', 'enterprise') NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    billing_cycle ENUM('monthly', 'yearly') NOT NULL,
    certificates_limit INT DEFAULT 10,
    status ENUM('active', 'cancelled', 'expired', 'suspended') DEFAULT 'active',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    auto_renew BOOLEAN DEFAULT TRUE,
    payment_method VARCHAR(50),
    last_payment_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (last_payment_id) REFERENCES payments(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_end_date (end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- جدول الكوبونات/الخصومات (Coupons)
-- ============================================
CREATE TABLE IF NOT EXISTS coupons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(10, 2) NOT NULL,
    min_amount DECIMAL(10, 2) DEFAULT 0,
    max_uses INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    valid_from DATE NOT NULL,
    valid_until DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_active (is_active),
    INDEX idx_valid (valid_from, valid_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- جدول استخدام الكوبونات (Coupon Usage)
-- ============================================
CREATE TABLE IF NOT EXISTS coupon_usage (
    id INT PRIMARY KEY AUTO_INCREMENT,
    coupon_id INT NOT NULL,
    user_id INT NOT NULL,
    payment_id INT,
    discount_amount DECIMAL(10, 2) NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL,
    INDEX idx_coupon (coupon_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Views للتقارير والإحصائيات
-- ============================================

-- عرض الإحصائيات المالية
CREATE OR REPLACE VIEW financial_stats AS
SELECT 
    COUNT(*) as total_payments,
    SUM(CASE WHEN payment_status = 'completed' THEN amount ELSE 0 END) as total_revenue,
    SUM(CASE WHEN payment_status = 'pending' THEN amount ELSE 0 END) as pending_amount,
    SUM(CASE WHEN payment_status = 'refunded' THEN amount ELSE 0 END) as refunded_amount,
    AVG(CASE WHEN payment_status = 'completed' THEN amount ELSE NULL END) as avg_transaction
FROM payments;

-- عرض أداء المصدرين
CREATE OR REPLACE VIEW issuer_performance AS
SELECT 
    u.id,
    u.name,
    u.email,
    COUNT(c.id) as total_certificates,
    SUM(CASE WHEN c.verification_status = 'verified' THEN 1 ELSE 0 END) as verified_certificates,
    SUM(CASE WHEN c.status = 'revoked' THEN 1 ELSE 0 END) as revoked_certificates,
    u.created_at as member_since
FROM users u
LEFT JOIN certificates c ON u.id = c.issuer_id
WHERE u.role = 'issuer'
GROUP BY u.id, u.name, u.email, u.created_at;

-- ============================================
-- Stored Procedures
-- ============================================

-- إجراء لحساب رسوم الشهادة
DELIMITER //
CREATE PROCEDURE calculate_certificate_fee(
    IN p_certificate_type VARCHAR(50),
    IN p_user_id INT,
    OUT p_fee DECIMAL(10,2)
)
BEGIN
    DECLARE user_subscription VARCHAR(20);
    
    -- Check if user has active subscription
    SELECT plan_type INTO user_subscription
    FROM subscriptions
    WHERE user_id = p_user_id 
    AND status = 'active'
    AND end_date >= CURDATE()
    LIMIT 1;
    
    -- Calculate fee based on subscription
    IF user_subscription = 'enterprise' THEN
        SET p_fee = 0.00; -- Free for enterprise
    ELSEIF user_subscription = 'professional' THEN
        SET p_fee = 2.50; -- Discounted
    ELSE
        SET p_fee = 5.00; -- Standard fee
    END IF;
END //
DELIMITER ;

-- إجراء لإنشاء إشعار
DELIMITER //
CREATE PROCEDURE create_notification(
    IN p_user_id INT,
    IN p_title VARCHAR(255),
    IN p_message TEXT,
    IN p_type VARCHAR(20),
    IN p_action_url VARCHAR(500)
)
BEGIN
    INSERT INTO notifications (user_id, title, message, type, action_url)
    VALUES (p_user_id, p_title, p_message, p_type, p_action_url);
END //
DELIMITER ;

-- ============================================
-- Sample Data (Optional)
-- ============================================

-- إضافة كوبون تجريبي
INSERT INTO coupons (code, description, discount_type, discount_value, valid_from, valid_until, created_by)
VALUES 
('WELCOME2025', 'Welcome discount for new users', 'percentage', 20.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 1),
('FIRSTCERT', 'First certificate free', 'fixed', 5.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 6 MONTH), 1);

-- ============================================
-- Indexes for Performance
-- ============================================

-- إضافة فهرس مركب للبحث السريع
ALTER TABLE certificates 
ADD INDEX idx_status_verification (status, verification_status);

ALTER TABLE certificates 
ADD INDEX idx_issuer_date (issuer_id, created_at);

-- ============================================
-- Comments
-- ============================================

-- إضافة تعليقات على الجداول
ALTER TABLE payments COMMENT = 'جدول المدفوعات والمعاملات المالية';
ALTER TABLE verification_requests COMMENT = 'طلبات التحقق من الشهادات';
ALTER TABLE activity_log COMMENT = 'سجل جميع الأنشطة على المنصة';
ALTER TABLE notifications COMMENT = 'إشعارات المستخدمين';
ALTER TABLE subscriptions COMMENT = 'اشتراكات المستخدمين الشهرية/السنوية';
ALTER TABLE coupons COMMENT = 'كوبونات الخصم';

COMMIT;

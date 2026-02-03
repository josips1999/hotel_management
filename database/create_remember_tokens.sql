-- Remember Me Tokens Table
-- Stores secure authentication tokens for persistent login

CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    selector VARCHAR(64) NOT NULL UNIQUE,
    hashed_validator VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_used_at TIMESTAMP NULL DEFAULT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    
    -- Foreign key constraint
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Indexes for performance
    INDEX idx_selector (selector),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: This table implements the split-token approach for security:
-- - selector: Public identifier stored in cookie and database (plain text)
-- - hashed_validator: Secret token hashed with password_hash() in database
-- - Cookie stores: selector:validator (plain)
-- 
-- Security benefits:
-- 1. Even if database is compromised, attacker cannot create valid cookie
-- 2. Tokens can be individually revoked by deleting from database
-- 3. Protects against timing attacks
-- 4. Fast lookups using selector index

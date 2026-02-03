-- ============================================================================
-- AUDIT LOG TABLE
-- Tracks all data changes with Unix timestamp
-- ============================================================================

CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(100) NOT NULL COMMENT 'Naziv tablice gdje je nastala promjena',
    record_id INT NOT NULL COMMENT 'ID zapisa koji je promijenjen',
    action VARCHAR(20) NOT NULL COMMENT 'Vrsta akcije: INSERT, UPDATE, DELETE',
    old_data TEXT NULL COMMENT 'Stari podaci (JSON format)',
    new_data TEXT NULL COMMENT 'Novi podaci (JSON format)',
    changed_by INT NULL COMMENT 'ID korisnika koji je napravio promjenu',
    changed_at INT NOT NULL COMMENT 'Unix timestamp kada je nastala promjena',
    ip_address VARCHAR(45) NULL COMMENT 'IP adresa korisnika',
    user_agent TEXT NULL COMMENT 'Browser/User agent',
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_changed_at (changed_at),
    INDEX idx_action (action),
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Audit log za praćenje svih promjena u bazi podataka';

-- ============================================================================
-- EXAMPLE QUERIES
-- ============================================================================

-- Sve promjene za određeni hotel
-- SELECT * FROM audit_log WHERE table_name = 'hotels' AND record_id = 1 ORDER BY changed_at DESC;

-- Promjene u zadnjih 24 sata
-- SELECT * FROM audit_log WHERE changed_at >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 24 HOUR));

-- Promjene po korisniku
-- SELECT * FROM audit_log WHERE changed_by = 1 ORDER BY changed_at DESC;

-- Sve DELETE akcije
-- SELECT * FROM audit_log WHERE action = 'DELETE' ORDER BY changed_at DESC;

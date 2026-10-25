CREATE TABLE IF NOT EXISTS etl_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    process_name VARCHAR(255) NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME,
    status VARCHAR(50),
    rows_processed INT DEFAULT 0,
    error_message TEXT,
    duration_seconds DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
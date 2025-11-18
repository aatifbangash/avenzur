-- Create missing sma_user_logs table
-- This table is used to track user access logs and is causing 404 errors when missing

CREATE TABLE IF NOT EXISTS `sma_user_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `location` varchar(255) DEFAULT '',
  `is_bot` tinyint(1) DEFAULT 0,
  `user_agent` text,
  `landing_url` varchar(500) DEFAULT '',
  `access_time` datetime NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_access_time` (`access_time`),
  KEY `idx_ip_address` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
-- Database: url_shortener

-- Table: urls
CREATE TABLE urls (
                      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                      user_id INT UNSIGNED NOT NULL,
                      original_url VARCHAR(2048) NOT NULL,
                      short_code VARCHAR(10) NOT NULL UNIQUE,
                      click_count INT UNSIGNED DEFAULT 0,
                      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                      INDEX idx_short_code (short_code),
                      INDEX idx_created_at (created_at),
                      INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: clicks
CREATE TABLE clicks (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        url_id INT UNSIGNED NOT NULL,
                        ip_address VARCHAR(45),
                        user_agent TEXT,
                        referer VARCHAR(500),
                        clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (url_id) REFERENCES urls(id) ON DELETE CASCADE,
                        INDEX idx_url_id (url_id),
                        INDEX idx_clicked_at (clicked_at),
                        INDEX idx_ip_address (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: api_tokens
CREATE TABLE api_tokens (
                            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                            user_id INT UNSIGNED NOT NULL,
                            token VARCHAR(64) NOT NULL UNIQUE,
                            expires_at TIMESTAMP NULL,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            INDEX idx_token (token),
                            INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: rate_limits
CREATE TABLE rate_limits (
                             id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                             ip_address VARCHAR(45) NOT NULL,
                             requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                             INDEX idx_ip_requested (ip_address, requested_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

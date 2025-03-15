-- Lisans bildirimleri için veritabanı tablosu
CREATE TABLE IF NOT EXISTS dismissed_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    notification_key VARCHAR(100) NOT NULL,
    dismissed_until DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX (user_id),
    INDEX (notification_key),
    UNIQUE KEY (user_id, notification_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

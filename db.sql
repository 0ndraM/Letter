-- 1. Tabulka pro uživatele
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` VARCHAR(10) DEFAULT 'user',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabulka pro dopisy (obsah)
-- ID je typu VARCHAR(12), aby pojalo náhodný alfanumerický token
CREATE TABLE IF NOT EXISTS `content_table` (
  `id` VARCHAR(12) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `letter_text` LONGTEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_author` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabulka pro logování zobrazení sdílených zpráv
CREATE TABLE IF NOT EXISTS `view_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `letter_id` VARCHAR(12) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT NOT NULL,
  `viewed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_view_letter` FOREIGN KEY (`letter_id`) REFERENCES `content_table`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Migration: 2025-11-30
-- Her kullanıcı için bir profil tablosu: firma, adres ve kargo bilgileri
-- Bir kullanıcı için tek kayıt olacak şekilde user_id UNIQUE ayarladım.

CREATE TABLE IF NOT EXISTS `user_profiles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `company_name` VARCHAR(255) DEFAULT NULL,
  `address_line1` VARCHAR(255) DEFAULT NULL,
  `address_line2` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(120) DEFAULT NULL,
  `state` VARCHAR(120) DEFAULT NULL,
  `postal_code` VARCHAR(32) DEFAULT NULL,
  `country` VARCHAR(120) DEFAULT NULL,
  `phone` VARCHAR(60) DEFAULT NULL,
  `shipping_instructions` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user` (`user_id`),
  INDEX (`user_id`)
);

-- Opsiyonel: users.email için UNIQUE constraint eklemek istiyorsanız bunu prod'da dikkatlice uygulayın.
-- ALTER TABLE `users` ADD CONSTRAINT uniq_email UNIQUE (email);

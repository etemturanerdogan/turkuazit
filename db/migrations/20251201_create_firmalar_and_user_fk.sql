-- Migration: 2025-12-01
-- 1) Firma tablosu oluşturulur
-- 2) users tablosuna firma_id alanı eklenir ve mümkünse foreign key ilişkilendirilir
-- DİKKAT: Canlı sistemde çalıştırmadan önce yedek alın.

CREATE TABLE IF NOT EXISTS `firmalar` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `firma_adi` VARCHAR(255) NOT NULL,
  `firma_tipi` VARCHAR(64) DEFAULT NULL,
  `adres` TEXT DEFAULT NULL,
  `telefon` VARCHAR(64) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `vergi_no` VARCHAR(128) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`firma_adi`)
);

-- users tablosuna firma_id ekleme
ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `firma_id` INT UNSIGNED DEFAULT NULL AFTER `id`;

-- Eğer destekleniyorsa foreign key ekle (önce users ve firmalar tablolarının engine ve verileri uyumlu olduğundan emin olun)
-- ALTER TABLE `users` ADD CONSTRAINT fk_users_firma FOREIGN KEY (firma_id) REFERENCES firmalar(id) ON DELETE SET NULL ON UPDATE CASCADE;

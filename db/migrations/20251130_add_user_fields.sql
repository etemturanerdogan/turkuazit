-- Migration: 2025-11-30
-- Bu dosya mevcut users tablosunu ad/soyad ve rol alanlarıyla genişletmek için örnek SQL içerir.
-- Çalıştırmadan önce veritabanınızın yedeğini alın!

-- 1) Yeni alanlar ekleniyor (varsa bir kez çalıştırın)
ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `first_name` VARCHAR(100) DEFAULT '' AFTER `id`,
  ADD COLUMN IF NOT EXISTS `last_name` VARCHAR(100) DEFAULT '' AFTER `first_name`,
  ADD COLUMN IF NOT EXISTS `role` VARCHAR(32) NOT NULL DEFAULT 'client' AFTER `email`,
  ADD COLUMN IF NOT EXISTS `is_dealer` TINYINT(1) NOT NULL DEFAULT 0 AFTER `role`,
  ADD COLUMN IF NOT EXISTS `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `is_dealer`,
  ADD COLUMN IF NOT EXISTS `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `is_active`,
  ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- 2) (Opsiyonel) Mevcut full_name sütununu migrate et (basit bir bölme)
-- Bu yöntem tüm isimleri doğru şekilde ayıramayabilir, karmaşık isimler için elle kontrol gereklidir.
UPDATE `users` SET
  first_name = TRIM(SUBSTRING_INDEX(full_name, ' ', 1)),
  last_name = TRIM(SUBSTRING_INDEX(full_name, ' ', -1))
WHERE (first_name = '' OR last_name = '') AND full_name <> '';

-- 3) Örnek roller: admin, client, staff1, staff2, staff3
-- Varsayılan kayıt rolü: client

-- 4) Mevcut veritabanı motorunuz ve sürümünüze göre SQL uyarlaması gerekebilir.

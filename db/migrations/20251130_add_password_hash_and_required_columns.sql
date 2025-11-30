-- Migration: 2025-11-30
-- Bu migration eksik sütunları ekler: password_hash, first_name, last_name, role, is_active, created_at
-- NOT: Önce veritabanı yedeği alın.

ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `password_hash` VARCHAR(255) NULL AFTER `email`,
  ADD COLUMN IF NOT EXISTS `first_name` VARCHAR(100) DEFAULT '' AFTER `id`,
  ADD COLUMN IF NOT EXISTS `last_name` VARCHAR(100) DEFAULT '' AFTER `first_name`,
  ADD COLUMN IF NOT EXISTS `role` VARCHAR(32) NOT NULL DEFAULT 'client' AFTER `email`,
  ADD COLUMN IF NOT EXISTS `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `role`,
  ADD COLUMN IF NOT EXISTS `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `is_active`;

-- Eğer MySQL sürümünüz ADD COLUMN IF NOT EXISTS desteklemiyorsa, aşağıdaki örneği kullanın (önce kontrol yapın):
-- SET @exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
--   WHERE table_schema = DATABASE() AND table_name = 'users' AND column_name = 'password_hash');
-- SELECT @exists;
-- THEN run ALTER only if needed.

-- Migration to add awaiting_petugas_validation column if not exists
USE db_aduan;

SET @column_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = 'db_aduan'
    AND TABLE_NAME = 'pengaduan'
    AND COLUMN_NAME = 'awaiting_petugas_validation'
);

SET @sql = IF(@column_exists = 0, 'ALTER TABLE pengaduan ADD COLUMN awaiting_petugas_validation TINYINT(1) DEFAULT 0', 'SELECT "Column already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
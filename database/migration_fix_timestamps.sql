-- Migration to fix all timestamp issues
-- Change date columns to DATETIME and update legacy data

USE db_aduan;

-- Change tanggapan column type from DATE to DATETIME
ALTER TABLE tanggapan MODIFY COLUMN tgl_tanggapan DATETIME NOT NULL;

-- Change pengaduan column type from DATE to DATETIME
ALTER TABLE pengaduan MODIFY COLUMN tgl_pengaduan DATETIME NOT NULL;

-- Update existing tanggapan records that have 00:00:00 time to current time
UPDATE tanggapan SET tgl_tanggapan = CONCAT(DATE(tgl_tanggapan), ' ', TIME(NOW())) WHERE TIME(tgl_tanggapan) = '00:00:00' OR TIME(tgl_tanggapan) = '08:00:00';

-- Update existing pengaduan records that have 00:00:00 time to current time
UPDATE pengaduan SET tgl_pengaduan = CONCAT(DATE(tgl_pengaduan), ' ', TIME(NOW())) WHERE TIME(tgl_pengaduan) = '00:00:00' OR TIME(tgl_pengaduan) = '08:00:00' OR TIME(tgl_pengaduan) IS NULL;

-- Update all other datetime columns in pengaduan table
UPDATE pengaduan SET opened_at = CONCAT(DATE(opened_at), ' 08:00:00') WHERE opened_at IS NOT NULL AND TIME(opened_at) = '00:00:00';
UPDATE pengaduan SET approved_at = CONCAT(DATE(approved_at), ' 08:00:00') WHERE approved_at IS NOT NULL AND TIME(approved_at) = '00:00:00';
UPDATE pengaduan SET rejected_at = CONCAT(DATE(rejected_at), ' 08:00:00') WHERE rejected_at IS NOT NULL AND TIME(rejected_at) = '00:00:00';
UPDATE pengaduan SET assigned_at = CONCAT(DATE(assigned_at), ' 08:00:00') WHERE assigned_at IS NOT NULL AND TIME(assigned_at) = '00:00:00';
UPDATE pengaduan SET last_progress_update = CONCAT(DATE(last_progress_update), ' 08:00:00') WHERE last_progress_update IS NOT NULL AND TIME(last_progress_update) = '00:00:00';
UPDATE pengaduan SET petugas_validated_at = CONCAT(DATE(petugas_validated_at), ' 08:00:00') WHERE petugas_validated_at IS NOT NULL AND TIME(petugas_validated_at) = '00:00:00';
UPDATE pengaduan SET closed_at = CONCAT(DATE(closed_at), ' 08:00:00') WHERE closed_at IS NOT NULL AND TIME(closed_at) = '00:00:00';

-- Fix status for records with rejection info
UPDATE pengaduan SET status = 'rejected' WHERE rejected_by IS NOT NULL AND status != 'rejected';
-- Migration script to add new columns to pengaduan table
-- Run this in phpMyAdmin or MySQL command line after importing db_aduan.sql

USE db_aduan;

ALTER TABLE pengaduan
ADD COLUMN opened_at DATETIME DEFAULT NULL,
ADD COLUMN opened_by INT(11) DEFAULT NULL,
ADD COLUMN is_approved TINYINT(1) DEFAULT 0,
ADD COLUMN approved_by INT(11) DEFAULT NULL,
ADD COLUMN approved_at DATETIME DEFAULT NULL,
ADD COLUMN approval_notes TEXT DEFAULT NULL,
ADD COLUMN can_unapprove TINYINT(1) DEFAULT 1,
ADD COLUMN is_rejected TINYINT(1) DEFAULT 0,
ADD COLUMN rejected_by INT(11) DEFAULT NULL,
ADD COLUMN rejection_reason TEXT DEFAULT NULL,
ADD COLUMN rejected_at DATETIME DEFAULT NULL,
ADD COLUMN is_assigned TINYINT(1) DEFAULT 0,
ADD COLUMN assigned_by INT(11) DEFAULT NULL,
ADD COLUMN assigned_petugas_id INT(11) DEFAULT NULL,
ADD COLUMN assigned_at DATETIME DEFAULT NULL,
ADD COLUMN disposition_notes TEXT DEFAULT NULL,
ADD COLUMN progress_status ENUM('pending','on_progress','awaiting_completion') DEFAULT 'pending',
ADD COLUMN progress_notes TEXT DEFAULT NULL,
ADD COLUMN last_progress_update DATETIME DEFAULT NULL,
ADD COLUMN updated_progress_by INT(11) DEFAULT NULL,
ADD COLUMN validated_by_petugas TINYINT(1) DEFAULT 0,
ADD COLUMN petugas_validation_notes TEXT DEFAULT NULL,
ADD COLUMN petugas_validated_at DATETIME DEFAULT NULL,
ADD COLUMN can_close TINYINT(1) DEFAULT 0,
ADD COLUMN closed_by INT(11) DEFAULT NULL,
ADD COLUMN closed_at DATETIME DEFAULT NULL,
ADD COLUMN completion_proof VARCHAR(255) DEFAULT NULL,
ADD COLUMN completion_notes TEXT DEFAULT NULL,
ADD COLUMN is_workable TINYINT(1) DEFAULT 1,
ADD COLUMN non_workable_reason TEXT DEFAULT NULL,
ADD COLUMN id_lurah INT(11) DEFAULT NULL,
ADD COLUMN id_staff INT(11) DEFAULT NULL,
ADD COLUMN wilayah VARCHAR(50) DEFAULT NULL,
ADD COLUMN progress_log TEXT DEFAULT NULL;

-- Update existing rows to set wilayah from masyarakat
UPDATE pengaduan p
INNER JOIN masyarakat m ON p.nik = m.nik
SET p.wilayah = m.wilayah
WHERE p.wilayah IS NULL;

-- Create notifications table if not exists
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nik CHAR(16) NOT NULL,
    type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
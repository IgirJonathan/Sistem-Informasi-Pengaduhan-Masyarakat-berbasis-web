-- Migration script to add new columns for extended complaint flow

ALTER TABLE pengaduan
ADD COLUMN approved_at DATETIME NULL AFTER id_lurah,
ADD COLUMN approval_notes TEXT NULL AFTER approved_at,
ADD COLUMN can_unapprove TINYINT(1) DEFAULT 1 AFTER approval_notes,
ADD COLUMN is_rejected TINYINT(1) DEFAULT 0 AFTER can_unapprove,
ADD COLUMN rejected_by INT(11) NULL AFTER is_rejected,
ADD COLUMN rejection_reason TEXT NULL AFTER rejected_by,
ADD COLUMN rejected_at DATETIME NULL AFTER rejection_reason,
ADD COLUMN is_assigned TINYINT(1) DEFAULT 0 AFTER rejected_at,
ADD COLUMN assigned_by INT(11) NULL AFTER is_assigned,
ADD COLUMN assigned_petugas_id INT(11) NULL AFTER assigned_by,
ADD COLUMN assigned_at DATETIME NULL AFTER assigned_petugas_id,
ADD COLUMN disposition_notes TEXT NULL AFTER assigned_at,
ADD COLUMN progress_status ENUM('pending','on_progress','awaiting_completion') DEFAULT 'pending' AFTER disposition_notes,
ADD COLUMN progress_notes TEXT NULL AFTER progress_status,
ADD COLUMN last_progress_update DATETIME NULL AFTER progress_notes,
ADD COLUMN updated_progress_by INT(11) NULL AFTER last_progress_update,
ADD COLUMN validated_by_petugas TINYINT(1) DEFAULT 0 AFTER updated_progress_by,
ADD COLUMN petugas_validation_notes TEXT NULL AFTER validated_by_petugas,
ADD COLUMN petugas_validated_at DATETIME NULL AFTER petugas_validation_notes,
ADD COLUMN can_close TINYINT(1) DEFAULT 0 AFTER petugas_validated_at,
ADD COLUMN closed_by INT(11) NULL AFTER can_close,
ADD COLUMN closed_at DATETIME NULL AFTER closed_by,
ADD COLUMN completion_proof VARCHAR(255) NULL AFTER closed_at,
ADD COLUMN completion_notes TEXT NULL AFTER completion_proof,
ADD COLUMN is_workable TINYINT(1) DEFAULT 1 AFTER completion_notes,
ADD COLUMN non_workable_reason TEXT NULL AFTER is_workable;

-- Update existing data: if id_staff is set, set assigned_petugas_id and is_assigned
UPDATE pengaduan SET assigned_petugas_id = id_staff, is_assigned = 1 WHERE id_staff IS NOT NULL;

-- Update status enum to include new statuses
ALTER TABLE pengaduan MODIFY COLUMN status ENUM('pending','validated','rejected','needs_verification','opened','closed') NOT NULL;
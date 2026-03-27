-- Update enum for progress_status to include 'completed'
USE db_aduan;

ALTER TABLE pengaduan MODIFY COLUMN progress_status ENUM('pending','on_progress','awaiting_completion','completed') DEFAULT 'pending';
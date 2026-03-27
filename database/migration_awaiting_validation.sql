-- Migration to add awaiting_petugas_validation column
USE db_aduan;

ALTER TABLE pengaduan ADD COLUMN awaiting_petugas_validation TINYINT(1) DEFAULT 0;
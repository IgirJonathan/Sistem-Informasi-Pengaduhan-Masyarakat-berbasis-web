-- Migration to add 'petugas' level to petugas table
ALTER TABLE petugas MODIFY COLUMN level ENUM('admin','lurah','kepala_lingkungan','petugas') NOT NULL;

-- Insert sample petugas account
INSERT INTO petugas (nama_petugas, username, password, telp, level) VALUES
('Petugas 1', 'petugas', MD5('petugas'), '08123456789', 'petugas');
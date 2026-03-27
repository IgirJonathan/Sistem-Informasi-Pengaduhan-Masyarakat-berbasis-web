<?php
// migration.php - Run this to add missing columns to pengaduan table
include "config/koneksi.php";

$alters = [
    "ALTER TABLE pengaduan ADD COLUMN opened_at DATETIME DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN opened_by INT(11) DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN is_approved TINYINT(1) DEFAULT 0",
    "ALTER TABLE pengaduan ADD COLUMN approved_by INT(11) DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN approved_at DATETIME DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN approval_notes TEXT DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN can_unapprove TINYINT(1) DEFAULT 1",
    "ALTER TABLE pengaduan ADD COLUMN is_rejected TINYINT(1) DEFAULT 0",
    "ALTER TABLE pengaduan ADD COLUMN rejected_by INT(11) DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN rejection_reason TEXT DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN rejected_at DATETIME DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN is_assigned TINYINT(1) DEFAULT 0",
    "ALTER TABLE pengaduan ADD COLUMN assigned_by INT(11) DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN assigned_petugas_id INT(11) DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN assigned_at DATETIME DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN disposition_notes TEXT DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN progress_status ENUM('pending','on_progress','awaiting_completion') DEFAULT 'pending'",
    "ALTER TABLE pengaduan ADD COLUMN progress_notes TEXT DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN last_progress_update DATETIME DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN updated_progress_by INT(11) DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN validated_by_petugas TINYINT(1) DEFAULT 0",
    "ALTER TABLE pengaduan ADD COLUMN petugas_validation_notes TEXT DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN petugas_validated_at DATETIME DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN can_close TINYINT(1) DEFAULT 0",
    "ALTER TABLE pengaduan ADD COLUMN closed_by INT(11) DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN closed_at DATETIME DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN completion_proof VARCHAR(255) DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN completion_notes TEXT DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN is_workable TINYINT(1) DEFAULT 1",
    "ALTER TABLE pengaduan ADD COLUMN non_workable_reason TEXT DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN id_lurah INT(11) DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN id_staff INT(11) DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN wilayah VARCHAR(50) DEFAULT NULL",
    "ALTER TABLE pengaduan ADD COLUMN progress_log TEXT DEFAULT NULL"
];

foreach ($alters as $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "Executed: $sql<br>";
    } else {
        echo "Error: " . mysqli_error($conn) . " for: $sql<br>";
    }
}

// Migrate tgl_pengaduan to DATETIME and fill legacy 00:00 gaps
$alterTgl = "ALTER TABLE pengaduan MODIFY tgl_pengaduan DATETIME NOT NULL";
if (mysqli_query($conn, $alterTgl)) {
    echo "Altered tgl_pengaduan to DATETIME<br>";
} else {
    echo "Error altering tgl_pengaduan: " . mysqli_error($conn) . "<br>";
}

// Migrate tanggapan.tgl_tanggapan ke DATETIME (dari DATE) dan backfill legacy
$alterTgl2 = "ALTER TABLE tanggapan MODIFY tgl_tanggapan DATETIME NOT NULL";
if (mysqli_query($conn, $alterTgl2)) {
    echo "Altered tanggapan.tgl_tanggapan to DATETIME<br>";
} else {
    echo "Error altering tanggapan.tgl_tanggapan: " . mysqli_error($conn) . "<br>";
}

// Backfill existing tanggapan (date-only rows) agar jam tidak 00:00
$updateTanggapanOld = "UPDATE tanggapan SET tgl_tanggapan = CONCAT(DATE(tgl_tanggapan), ' 08:00:00') WHERE TIME(tgl_tanggapan) = '00:00:00'";
if (mysqli_query($conn, $updateTanggapanOld)) {
    echo "Backfilled historical tanggapan timestamps to 08:00:00 where missing<br>";
} else {
    echo "Error backfilling tanggapan timestamps: " . mysqli_error($conn) . "<br>";
}

// Set tgl_pengaduan to first tanggapan time if available (older rows may not have it)
$updateOld = "UPDATE pengaduan p
    LEFT JOIN (
        SELECT id_pengaduan, MIN(tgl_tanggapan) AS first_tgl
        FROM tanggapan
        GROUP BY id_pengaduan
    ) t ON p.id_pengaduan = t.id_pengaduan
    SET p.tgl_pengaduan = COALESCE(t.first_tgl, CONCAT(DATE(p.tgl_pengaduan), ' 08:00:00'))
    WHERE p.tgl_pengaduan IS NOT NULL";
if (mysqli_query($conn, $updateOld)) {
    echo "Updated historical tgl_pengaduan timestamps<br>";
} else {
    echo "Error updating historical tgl_pengaduan: " . mysqli_error($conn) . "<br>";
}

// Update wilayah
$sql = "UPDATE pengaduan p INNER JOIN masyarakat m ON p.nik = m.nik SET p.wilayah = m.wilayah WHERE p.wilayah IS NULL";
if (mysqli_query($conn, $sql)) {
    echo "Updated wilayah<br>";
} else {
    echo "Error updating wilayah: " . mysqli_error($conn) . "<br>";
}

// Create notifications table
$sql = "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nik CHAR(16) NOT NULL,
    type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
if (mysqli_query($conn, $sql)) {
    echo "Created notifications table<br>";
} else {
    echo "Error creating notifications: " . mysqli_error($conn) . "<br>";
}

echo "Migration completed.";
?>
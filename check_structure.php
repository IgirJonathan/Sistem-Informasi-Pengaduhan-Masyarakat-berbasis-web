<?php
include 'config/koneksi.php';

// Check pengaduan table structure
$query = mysqli_query($conn, "DESCRIBE pengaduan");
echo "Struktur tabel pengaduan:\n";
while ($row = mysqli_fetch_assoc($query)) {
    echo "- {$row['Field']}: {$row['Type']}\n";
}

// Check if any pengaduan exists
$query2 = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengaduan");
$row = mysqli_fetch_assoc($query2);
echo "\nTotal pengaduan: {$row['total']}\n";
?>
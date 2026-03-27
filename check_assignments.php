<?php
include 'config/koneksi.php';
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengaduan WHERE id_kepala_lingkungan IS NOT NULL AND status = 'pending'");
$row = mysqli_fetch_assoc($result);
echo 'Assigned pending: ' . $row['total'] . PHP_EOL;

$result2 = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengaduan WHERE id_kepala_lingkungan IS NULL AND status = 'pending'");
$row2 = mysqli_fetch_assoc($result2);
echo 'Unassigned pending: ' . $row2['total'] . PHP_EOL;
?>
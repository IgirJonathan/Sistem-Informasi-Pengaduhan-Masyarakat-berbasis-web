<?php
include 'config/koneksi.php';

$query = mysqli_query($conn, "INSERT INTO tanggapan (id_pengaduan, tgl_tanggapan, tanggapan, id_petugas) VALUES (134, NOW(), 'Petugas sedang menuju lokasi kejadian', 16)");
echo "Insert petugas response: " . (mysqli_affected_rows($conn) > 0 ? "Success" : "Failed - " . mysqli_error($conn)) . "\n";
?>
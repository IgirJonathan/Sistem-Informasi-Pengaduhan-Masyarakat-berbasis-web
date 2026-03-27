<?php
include 'config/koneksi.php';

$query = mysqli_query($conn, "INSERT INTO tanggapan (id_pengaduan, tgl_tanggapan, tanggapan, id_petugas) VALUES (136, NOW(), 'Rejected: anda bukan warga di sini', 15)");
echo "Insert rejected: " . (mysqli_affected_rows($conn) > 0 ? "Success" : "Failed - " . mysqli_error($conn)) . "\n";
?>
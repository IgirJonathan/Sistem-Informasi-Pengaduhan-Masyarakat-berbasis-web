<?php
include 'config/koneksi.php';

$query = mysqli_query($conn, "INSERT INTO tanggapan (id_pengaduan, tgl_tanggapan, tanggapan, id_petugas) VALUES (134, NOW(), 'Approved: Terima kasih atas laporan nya', 15)");
echo "Insert approved with message: " . (mysqli_affected_rows($conn) > 0 ? "Success" : "Failed - " . mysqli_error($conn)) . "\n";
?>
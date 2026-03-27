<?php
include 'config/koneksi.php';

// Insert disposisi dari lurah (ID 11) untuk pengaduan ID 132
$query1 = mysqli_query($conn, "INSERT INTO tanggapan (id_pengaduan, tgl_tanggapan, tanggapan, id_petugas) VALUES (132, NOW(), 'Disposisi to Petugas: Bantu Kepala Lingkungan untuk mencari gundam', 11)");
echo "Insert disposisi: " . (mysqli_affected_rows($conn) > 0 ? "Success" : "Failed - " . mysqli_error($conn)) . "\n";

// Insert approved dari KL (ID 15)
$query2 = mysqli_query($conn, "INSERT INTO tanggapan (id_pengaduan, tgl_tanggapan, tanggapan, id_petugas) VALUES (132, NOW(), 'Approved: Laporan akan saya tangani', 15)");
echo "Insert approved: " . (mysqli_affected_rows($conn) > 0 ? "Success" : "Failed - " . mysqli_error($conn)) . "\n";

// Insert progress dari petugas (ID 16)
$query3 = mysqli_query($conn, "INSERT INTO tanggapan (id_pengaduan, tgl_tanggapan, tanggapan, id_petugas) VALUES (132, NOW(), 'Petugas telah menuju lokasi untuk penanganan', 16)");
echo "Insert progress: " . (mysqli_affected_rows($conn) > 0 ? "Success" : "Failed - " . mysqli_error($conn)) . "\n";
?>
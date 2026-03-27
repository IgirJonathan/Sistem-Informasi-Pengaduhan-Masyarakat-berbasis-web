<?php
include 'config/koneksi.php';

$id = 134;

$query = mysqli_query($conn, "SELECT t.*, p.nama_petugas, p.level FROM tanggapan t JOIN petugas p ON t.id_petugas = p.id_petugas WHERE t.id_pengaduan = '$id' ORDER BY t.tgl_tanggapan DESC");

echo "All tanggapan for ID $id:\n";
while ($row = mysqli_fetch_assoc($query)) {
    echo "- {$row['nama_petugas']} ({$row['level']}): '{$row['tanggapan']}'\n";
}
?>
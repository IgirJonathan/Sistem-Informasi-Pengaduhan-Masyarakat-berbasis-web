<?php
include 'config/koneksi.php';

// Cek data tanggapan untuk id_pengaduan=136
$query = mysqli_query($conn, "SELECT t.*, p.nama_petugas, p.level FROM tanggapan t JOIN petugas p ON t.id_petugas = p.id_petugas WHERE t.id_pengaduan = 136 ORDER BY t.tgl_tanggapan DESC");
echo "Data tanggapan untuk id_pengaduan=136:\n";
$num = mysqli_num_rows($query);
echo "Total: $num\n";
while ($row = mysqli_fetch_assoc($query)) {
    echo "- {$row['nama_petugas']} ({$row['level']}): {$row['tanggapan']}\n";
}

// Cek level petugas
$query2 = mysqli_query($conn, "SELECT id_petugas, nama_petugas, level FROM petugas");
echo "\nData petugas:\n";
while ($row = mysqli_fetch_assoc($query2)) {
    echo "- ID: {$row['id_petugas']}, Nama: {$row['nama_petugas']}, Level: {$row['level']}\n";
}
?>
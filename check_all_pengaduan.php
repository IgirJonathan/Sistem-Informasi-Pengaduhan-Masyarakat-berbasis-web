<?php
include 'config/koneksi.php';

// Check all pengaduan
$query = mysqli_query($conn, "SELECT p.id_pengaduan, p.nik, m.nama, p.judul_pengaduan, p.status FROM pengaduan p JOIN masyarakat m ON p.nik = m.nik");
echo "Semua pengaduan:\n";
while ($row = mysqli_fetch_assoc($query)) {
    echo "- ID: {$row['id_pengaduan']}, NIK: {$row['nik']}, Nama: {$row['nama']}, Judul: {$row['judul_pengaduan']}, Status: {$row['status']}\n";
}
?>
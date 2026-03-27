<?php
include 'config/koneksi.php';

// Check pengaduan id=136
$query = mysqli_query($conn, "SELECT p.*, m.nama FROM pengaduan p JOIN masyarakat m ON p.nik = m.nik WHERE p.id_pengaduan = 136");
if ($row = mysqli_fetch_assoc($query)) {
    echo "Pengaduan ID 136:\n";
    echo "- NIK: {$row['nik']}, Nama: {$row['nama']}\n";
    echo "- Judul: {$row['judul_pengaduan']}\n";
    echo "- Isi: {$row['isi_laporan']}\n";
    echo "- Status: {$row['status']}\n";
    echo "- Validated by petugas: {$row['validated_by_petugas']}\n";
    echo "- Is workable: {$row['is_workable']}\n";
    echo "- Progress status: {$row['progress_status']}\n";
} else {
    echo "Pengaduan ID 134 tidak ditemukan\n";
}
?>
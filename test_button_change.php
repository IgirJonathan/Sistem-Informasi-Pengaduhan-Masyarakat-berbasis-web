<?php
include 'config/koneksi.php';

// Simulate KL login
$_SESSION['login'] = 'petugas';
$_SESSION['level'] = 'kepala_lingkungan';
$_SESSION['id_petugas'] = 15; // KL ID 15 has approved complaint

$id_kepala = $_SESSION['id_petugas'];
$wilayah_query = mysqli_query($conn, "SELECT wilayah FROM petugas WHERE id_petugas = '$id_kepala'");
$wilayah_data = mysqli_fetch_assoc($wilayah_query);
$wilayah = $wilayah_data['wilayah'] ?? 'Tidak Ditetapkan';

echo "Testing button changes for KL in wilayah: $wilayah\n\n";

// Query pengaduan di wilayah kepala_lingkungan
$ambil = mysqli_query($conn, "SELECT a.*, b.* FROM pengaduan a INNER JOIN masyarakat b ON a.nik = b.nik WHERE a.wilayah = '$wilayah' AND (a.status = 'pending' OR (a.status = 'opened' AND a.is_approved = 1 AND a.approved_by = '$id_kepala')) ORDER BY id_pengaduan ASC");

while ($data = mysqli_fetch_array($ambil)) {
    // Check if already approved by this KL
    $is_approved = false;
    $approved_check = mysqli_query($conn, "SELECT is_approved FROM pengaduan WHERE id_pengaduan = '{$data['id_pengaduan']}' AND is_approved = 1 AND approved_by = '$id_kepala'");
    if ($approved_check && mysqli_num_rows($approved_check) > 0) {
        $is_approved = true;
    }

    echo "ID: {$data['id_pengaduan']} - {$data['judul_pengaduan']}\n";
    echo "Status: " . ($is_approved ? 'Approved' : 'Pending') . "\n";
    echo "Button: " . ($is_approved ? 'Batalkan' : 'Validasi') . "\n\n";
}
?>
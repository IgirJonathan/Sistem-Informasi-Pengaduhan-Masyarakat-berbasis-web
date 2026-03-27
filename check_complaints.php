<?php
include 'config/koneksi.php';
$result = mysqli_query($conn, 'SELECT id_pengaduan, judul_pengaduan, wilayah, status, is_approved, approved_by FROM pengaduan LIMIT 5');
while ($row = mysqli_fetch_assoc($result)) {
    echo 'ID: ' . $row['id_pengaduan'] . ' - ' . $row['judul_pengaduan'] . ' - Lingkungan: ' . $row['wilayah'] . ' - Status: ' . $row['status'] . ' - Approved: ' . ($row['is_approved'] ? 'Yes' : 'No') . ' - By: ' . $row['approved_by'] . "\n";
}
?>
<?php
include 'config/koneksi.php';
$result = mysqli_query($conn, 'SELECT id_pengaduan FROM pengaduan ORDER BY id_pengaduan DESC LIMIT 1');
if ($row = mysqli_fetch_assoc($result)) {
    echo 'Max ID: ' . $row['id_pengaduan'];
}
?>
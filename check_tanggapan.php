<?php
include 'config/koneksi.php';
$result = mysqli_query($conn, 'SELECT DISTINCT t.id_pengaduan FROM tanggapan t ORDER BY t.id_pengaduan DESC LIMIT 5');
echo "Available pengaduan IDs with tanggapan:\n";
while ($row = mysqli_fetch_assoc($result)) {
    echo $row['id_pengaduan'] . "\n";
}
?>
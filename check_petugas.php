<?php
include 'config/koneksi.php';

// Check petugas passwords
$query = mysqli_query($conn, "SELECT id_petugas, nama_petugas, username, password, level FROM petugas");
echo "Petugas accounts:\n";
while ($row = mysqli_fetch_assoc($query)) {
    echo "- ID: {$row['id_petugas']}, Nama: {$row['nama_petugas']}, Username: {$row['username']}, Level: {$row['level']}, Password hash: {$row['password']}\n";
    if ($row['password'] == md5('123')) {
        echo "  -> Password is '123'\n";
    }
}
?>
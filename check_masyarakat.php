<?php
include 'config/koneksi.php';

// Check masyarakat accounts
$query = mysqli_query($conn, "SELECT nik, nama, username, password FROM masyarakat LIMIT 5");
echo "Masyarakat accounts:\n";
while ($row = mysqli_fetch_assoc($query)) {
    echo "- NIK: {$row['nik']}, Nama: {$row['nama']}, Username: {$row['username']}, Password: {$row['password']}\n";
}
?>
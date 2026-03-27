<?php
// Check if password '123' matches the hash
$password = '123';
$hash = md5($password);
echo "MD5 hash of '123': $hash\n";

// Check masyarakat with NIK 7577
include 'config/koneksi.php';
$query = mysqli_query($conn, "SELECT * FROM masyarakat WHERE nik = '7577'");
if ($row = mysqli_fetch_assoc($query)) {
    echo "Masyarakat NIK 7577:\n";
    echo "- Nama: {$row['nama']}\n";
    echo "- Username: {$row['username']}\n";
    echo "- Password hash: {$row['password']}\n";
    echo "- Matches '123': " . ($row['password'] == $hash ? 'Yes' : 'No') . "\n";
}
?>
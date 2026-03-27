<?php
/**
 * Setup file untuk menambahkan sample Kepala Lingkungan untuk setiap Lingkungan (1-5)
 * Jalankan file ini sekali untuk inisialisasi data
 * 
 * Username: kl1-kl5
 * Password: kl123 (MD5: 202cb962ac59075b964b07152d234b70)
 */

include "config/koneksi.php";

// Check if admin is logged in
if (!isset($_SESSION['login']) || $_SESSION['login'] != 'petugas' || $_SESSION['level'] != 'admin') {
    echo "Akses ditolak. Hanya admin yang dapat menjalankan setup ini.";
    exit;
}

$password_md5 = md5('kl123'); // Password default untuk KL

$kl_data = [
    ['nama' => 'Kepala Lingkungan Lingkungan 1', 'username' => 'kl1', 'wilayah' => '1'],
    ['nama' => 'Kepala Lingkungan Lingkungan 2', 'username' => 'kl2', 'wilayah' => '2'],
    ['nama' => 'Kepala Lingkungan Lingkungan 3', 'username' => 'kl3', 'wilayah' => '3'],
    ['nama' => 'Kepala Lingkungan Lingkungan 4', 'username' => 'kl4', 'wilayah' => '4'],
    ['nama' => 'Kepala Lingkungan Lingkungan 5', 'username' => 'kl5', 'wilayah' => '5'],
];

$success_count = 0;
$error_messages = [];

foreach ($kl_data as $kl) {
    // Cek apakah KL sudah ada
    $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_petugas FROM petugas WHERE username='{$kl['username']}'"));
    
    if ($check) {
        $error_messages[] = "KL {$kl['username']} sudah ada.";
        continue;
    }
    
    // Insert KL baru
    $query = "INSERT INTO petugas (nama_petugas, username, password, telp, level, wilayah) 
              VALUES ('{$kl['nama']}', '{$kl['username']}', '$password_md5', '0812345678', 'kepala_lingkungan', '{$kl['wilayah']}')";
    
    if (mysqli_query($conn, $query)) {
        $success_count++;
    } else {
        $error_messages[] = "Error menambah {$kl['username']}: " . mysqli_error($conn);
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Setup Kepala Lingkungan</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h3>Setup Sample Kepala Lingkungan</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <strong><?php echo $success_count; ?> Kepala Lingkungan berhasil ditambahkan</strong>
                </div>
                
                <?php if (!empty($error_messages)): ?>
                    <div class="alert alert-warning">
                        <strong>Pesan:</strong><br>
                        <?php foreach ($error_messages as $msg): ?>
                            &bull; <?php echo $msg; ?><br>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <h5>Data Kepala Lingkungan Ditambahkan:</h5>
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Wilayah</th>
                            <th>Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kl_data as $kl): ?>
                            <tr>
                                <td><?php echo $kl['nama']; ?></td>
                                <td><?php echo $kl['username']; ?></td>
                                <td>kl123</td>
                                <td><?php echo $kl['wilayah']; ?></td>
                                <td><span class="badge bg-info">kepala_lingkungan</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <p class="mt-4"><strong>Catatan:</strong></p>
                <ul>
                    <li>Login sebagai Kepala Lingkungan menggunakan username dan password di atas</li>
                    <li>Setiap KL akan melihat laporan yang masuk dari wilayah mereka</li>
                    <li>Password default dapat diganti melalui pengaturan user</li>
                    <li>File ini dapat dihapus setelah setup selesai</li>
                </ul>
                
                <div class="mt-3">
                    <a href="admin/index.php" class="btn btn-primary">Kembali ke Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
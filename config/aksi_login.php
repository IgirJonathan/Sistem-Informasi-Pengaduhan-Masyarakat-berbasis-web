<?php

session_start();
include 'koneksi.php';

//menangkap var yang dikirim lewat $_POST
$username = htmlspecialchars($_POST["username"]);
$password = htmlspecialchars(md5($_POST["password"]));
$level = htmlspecialchars($_POST["level"]);

//pengkondisian jika yang masuk levelnya masyarakat maka mengambil dari TABEL masyarakat dan selain masyarakat mengambil dari TABEL PETUGAS
if ($level === 'masyarakat') {
    $login = mysqli_query($conn, "SELECT * FROM masyarakat WHERE `username` = '$username' AND `password` = '$password'");
} elseif (in_array($level, ['admin', 'lurah', 'kepala_lingkungan'])) {
    $login = mysqli_query($conn, "SELECT * FROM petugas WHERE `username`= '$username' AND `password` = '$password' AND `level` = '$level'");
} else {
    echo "<script>alert('Level login tidak valid'); document.location.href='../index.php?page=login';</script>";
    exit;
}

//cek data apakah ada ?
$cek = mysqli_num_rows($login);

if ($cek > 0) {
    //looping data
    $data = mysqli_fetch_assoc($login);

    if ($data['level'] == 'masyarakat') {
        //membuat session untuk masyarakat
        $_SESSION['login'] = 'masyarakat';
        $_SESSION['nik'] = $data['nik'];
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['level'] = 'masyarakat';
        header("location:../masyarakat/");
        exit();
    } elseif (in_array($data['level'], ['admin', 'lurah', 'kepala_lingkungan'])) {
        //membuat session untuk petugas (admin, lurah, kepala_lingkungan)
        $_SESSION['login'] = $data['level']; // Set to actual level: admin, lurah, or kepala_lingkungan
        $_SESSION['id_petugas'] = $data['id_petugas'];
        $_SESSION['nama_petugas'] = $data['nama_petugas'];
        $_SESSION['level'] = $data['level']; // 'admin', 'lurah', atau 'kepala_lingkungan'
        header("location:../admin/");
        exit();
    }
} else {
    echo
    "
        <script>
            alert('username atau password tidak terdaftar');
            document.location.href='../index.php?page=login';
        </script>
    ";
}


?>
<!-- end -->
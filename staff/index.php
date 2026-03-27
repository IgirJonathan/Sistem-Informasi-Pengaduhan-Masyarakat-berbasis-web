<?php

session_start();
// ini agar user tidak bisa masuk lewat url ke dalam halaman STAFF
if (!isset($_SESSION['login']) || $_SESSION['login'] != 'petugas' || $_SESSION['level'] != 'petugas') {
    header("Location:../index.php?page=login");
    die();
}

?>

<?php include "../layouts/admin/head.php"; ?>

<body class="g-sidenav-show bg-primary ">
    <div class="min-height-300 position-absolute w-100">
        <!-- Side Bar -->
        <?php include "../layouts/staff/aside.php"; ?>
        <!-- End Side Bar -->
        <main class="main-content position-relative border-radius-lg ">
            <!-- Navbar -->
            <?php include "../layouts/admin/header.php"; ?>
            <!-- End Navbar -->
            <?php
            if (isset($_GET['page'])) {
                $page = $_GET['page'];

                switch ($page) {
                    case 'dashboard':
                        include 'dashboard_petugas.php';
                        break;
                    case 'detail':
                        include 'detail_laporan.php';
                        break;
                    default:
                        echo "HALAMAN TAK TERSEDIA";
                        break;
                }
            } else {
                include 'dashboard_petugas.php';
            }
            ?>
        </main>

        <?php include "../layouts/admin/footer.php"; ?>

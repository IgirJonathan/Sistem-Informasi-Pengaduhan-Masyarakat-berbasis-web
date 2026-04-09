    <?php
    ob_start();
    session_start();
    // ini agar user tidak bisa masuk lewat url ke dalam halaman ADMIN
    // Only admin/kepala_lingkungan/lurah roles are allowed
    if (!isset($_SESSION['level']) || !in_array($_SESSION['level'], ['admin', 'kepala_lingkungan', 'lurah'])) {
        header("Location:../index.php?page=login");
        die();
    }

    ?>

    <?php include "../layouts/admin/head.php"; ?>

    <body class="g-sidenav-show bg-primary ">
        <div class="min-height-300 position-absolute w-100">
            <!-- Side Bar -->
            <?php include "../layouts/admin/aside.php"; ?>
            <!-- End Side Bar -->
            <main class="main-content position-relative border-radius-lg ">
                <!-- Navbar -->
                <?php include "../layouts/admin/header.php"; ?>
                <!-- End Navbar -->
                <?php
                if (isset($_GET['page'])) {
                    $page = $_GET['page'];

                    switch ($page) {
                        case 'pengaduan':
                            include 'data_pengaduan.php';
                            break;
                        case 'pengaduan_kepala':
                            include 'data_pengaduan_kepala.php';
                            break;
                        case 'pengaduan_lurah':
                            include 'pengaduan_lurah.php';
                            break;
                        case 'dashboard_kl':
                            include 'dashboard_kl.php';
                            break;
                        case 'lurah_monitoring':
                            include 'lurah_monitoring.php';
                            break;
                        case 'pengaduan_staff':
                            // petugas role removed; redirect to kepala_lingkungan dashboard
                            header("Location:index.php?page=pengaduan_kepala");
                            exit;
                        case 'masyarakat':
                            include 'data_masyarakat.php';
                            break;
                        case 'petugas':
                            include 'data_petugas.php';
                            break;
                        case 'hamas':
                            include 'hamasyarakat.php';
                            break;
                        case 'arsip_tugas':
                            include 'arsip_tugas.php';
                            break;
                        case 'detail_arsip':
                            include 'detail_arsip.php';
                            break;

                        default:
                            echo "<div class='container mt-4'><div class='alert alert-danger'>Halaman tidak tersedia</div></div>";
                            break;
                    }
                } else {
                    include 'home.php';
                }
                ?>
            </main>

            <?php include "../layouts/admin/footer.php"; ?>
<?php ob_end_flush(); ?>
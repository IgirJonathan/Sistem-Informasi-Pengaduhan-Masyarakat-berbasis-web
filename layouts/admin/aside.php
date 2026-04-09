<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4 " id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="index.php">
            <?php
                $brandLabel = 'Dashboard';
                if (isset($_SESSION['level'])) {
                    if ($_SESSION['level'] == 'admin') {
                        $brandLabel = 'Admin';
                    } elseif ($_SESSION['level'] == 'kepala_lingkungan') {
                        $brandLabel = 'Kepala Lingkungan';
                    } elseif ($_SESSION['level'] == 'lurah') {
                        $brandLabel = 'Lurah';
                    }
                }
            ?>
            <img src="../assets/img/logo2.png" class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-1 font-weight-bold"><?= htmlspecialchars($brandLabel); ?></span>
        </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class=" navbar-collapse w-auto " id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <?php if ($_SESSION['level'] == 'admin') { ?>
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Dashboard</span>
                    </a>
                </li>
                <!-- Masyarakat -->
                <li class="nav-item">
                    <a class="nav-link " href="index.php?page=masyarakat">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Data Masyarakat</span>
                    </a>
                </li>
                <!-- Petugas -->
                <li class="nav-item">
                    <a class="nav-link " href="index.php?page=petugas">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Data Petugas</span>
                    </a>
                </li>
                <!-- Logout -->
                <li class="nav-item">
                    <a class="nav-link " href="../config/aksi_logout.php">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-user-run text-info text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Log Out</span>
                    </a>
                </li>
            <?php } elseif ($_SESSION['level'] == 'kepala_lingkungan' || $_SESSION['level'] == 'Kepala_Lingkungan') { ?>
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Dashboard</span>
                    </a>
                </li>
                <!-- Pengaduan Kepala Lingkungan -->
                <li class="nav-item">
                    <a class="nav-link " href="index.php?page=pengaduan_kepala">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-single-copy-04 text-warning text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Validasi Pengaduan</span>
                    </a>
                </li>
                <!-- Arsip Tugas -->
                <li class="nav-item">
                    <a class="nav-link " href="index.php?page=arsip_tugas">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-archive-2 text-info text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Arsip Tugas</span>
                    </a>
                </li>
                <!-- Logout -->
                <li class="nav-item">
                    <a class="nav-link " href="../config/aksi_logout.php">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-user-run text-info text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Log Out</span>
                    </a>
                </li>
            <?php } elseif ($_SESSION['level'] == 'lurah') { ?>
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Dashboard</span>
                    </a>
                </li>
                <!-- Pengaduan Lurah -->
                <li class="nav-item">
                    <a class="nav-link " href="index.php?page=pengaduan_lurah">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-single-copy-04 text-warning text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Data Pengaduan</span>
                    </a>
                </li>
                <!-- Dashboard Kepala Lingkungan -->
                <li class="nav-item">
                    <a class="nav-link " href="index.php?page=lurah_monitoring">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-chart-bar-32 text-success text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Dashboard KL</span>
                    </a>
                </li>
                <!-- Logout -->
                <li class="nav-item">
                    <a class="nav-link " href="../config/aksi_logout.php">
                        <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-user-run text-info text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Log Out</span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>

</aside>
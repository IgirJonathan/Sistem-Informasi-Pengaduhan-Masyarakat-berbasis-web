<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl " id="navbarBlur" data-scroll="false">
    <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
            <?php
            function admin_page_name($page)
            {
                $map = [
                    'pengaduan' => 'Pengaduan',
                    'pengaduan_kepala' => 'Validasi Pengaduan',
                    'pengaduan_lurah' => 'Data Pengaduan',
                    'dashboard_kl' => 'Dashboard KL',
                    'lurah_monitoring' => 'Dashboard KL',
                    'pengaduan_staff' => 'Pengaduan Staff',
                    'masyarakat' => 'Data Masyarakat',
                    'petugas' => 'Data Petugas',
                    'hamas' => 'Hamas',
                    'arsip_tugas' => 'Arsip Tugas',
                    'detail_arsip' => 'Detail Arsip',
                    'dashboard' => 'Dashboard',
                    'detail' => 'Detail Laporan',
                ];
                return $map[$page] ?? 'Halaman tidak tersedia';
            }

            $page_label = isset($_GET['page']) ? admin_page_name($_GET['page']) : 'Dashboard';
            ?>
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="javascript:;">Pages</a></li>
                <li class="breadcrumb-item text-sm text-white active" aria-current="page"><?php echo $page_label; ?></li>
            </ol>
            <h6 class="font-weight-bolder text-white mb-0"><?php echo $page_label; ?></h6>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
            <ul class="navbar-nav ms-md-auto pe-md-3 d-flex justify-content-end">
                <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-white p-0" id="iconNavbarSidenav">
                        <div class="sidenav-toggler-inner">
                            <i class="sidenav-toggler-line bg-white"></i>
                            <i class="sidenav-toggler-line bg-white"></i>
                            <i class="sidenav-toggler-line bg-white"></i>
                        </div>
                    </a>
                </li>
                <?php
                $user_greeting = '';
                if (!empty($_SESSION['level']) && in_array($_SESSION['level'], ['kepala_lingkungan', 'lurah']) && !empty($_SESSION['nama_petugas'])) {
                    $user_name = htmlspecialchars($_SESSION['nama_petugas']);
                    if ($_SESSION['level'] === 'lurah') {
                        $user_greeting = "Selamat datang, {$user_name} (Lurah)";
                    } else {
                        $wilayah_label = '';
                        if (!empty($_SESSION['id_petugas'])) {
                            include_once __DIR__ . '/../../config/koneksi.php';
                            $petugas_id = mysqli_real_escape_string($conn, $_SESSION['id_petugas']);
                            $wilayah_query = mysqli_query($conn, "SELECT wilayah FROM petugas WHERE id_petugas = '$petugas_id' LIMIT 1");
                            if ($wilayah_query && $wilayah_row = mysqli_fetch_assoc($wilayah_query)) {
                                $wilayah_label = trim($wilayah_row['wilayah']);
                            }
                        }

                        if (!empty($wilayah_label)) {
                            $formatted_region = preg_replace('/^\s*Wilayah\s*/i', '', $wilayah_label);
                            $formatted_region = trim($formatted_region);
                            $role_label = 'Kepala Lingkungan' . (!empty($formatted_region) ? ' ' . $formatted_region : '');
                        } else {
                            $role_label = 'Kepala Lingkungan';
                        }
                        $user_greeting = "Selamat datang, {$user_name} ({$role_label})";
                    }
                }
                ?>
                <?php if (!empty($user_greeting)) : ?>
                    <li class="nav-item d-flex align-items-center ms-3">
                        <span class="nav-link text-white pe-0"><?php echo $user_greeting; ?></span>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
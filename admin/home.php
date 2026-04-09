<?php

include "../config/koneksi.php";

// Query berdasarkan level
if ($_SESSION['level'] == 'admin') {
    $masyarakat = mysqli_query($conn, "SELECT * FROM masyarakat");
    $jml_masyarakat = mysqli_num_rows($masyarakat);
    $petugas = mysqli_query($conn, "SELECT * FROM petugas");
    $jml_petugas = mysqli_num_rows($petugas);
}

$pengaduan = mysqli_query($conn, "SELECT * FROM pengaduan");
$jml_pengaduan = mysqli_num_rows($pengaduan);

// Query spesifik berdasarkan level
if ($_SESSION['level'] == 'kepala_lingkungan') {
    $pengaduan_pending = mysqli_query($conn, "SELECT * FROM pengaduan WHERE status = 'pending'");
    $jml_pengaduan_pending = mysqli_num_rows($pengaduan_pending);
    $pengaduan_selesai = mysqli_query($conn, "SELECT * FROM pengaduan WHERE status = 'closed' AND id_kepala_lingkungan = '" . intval($_SESSION['id_petugas']) . "'");
    $jml_pengaduan_selesai = mysqli_num_rows($pengaduan_selesai);
} elseif ($_SESSION['level'] == 'lurah') {
    $pengaduan_validated = mysqli_query($conn, "SELECT * FROM pengaduan WHERE id_kepala_lingkungan IS NOT NULL AND status = 'pending'");
    $jml_pengaduan_validated = mysqli_num_rows($pengaduan_validated);
    $pengaduan_selesai = mysqli_query($conn, "SELECT * FROM pengaduan WHERE id_kepala_lingkungan IS NOT NULL AND status = 'closed'");
    $jml_pengaduan_selesai = mysqli_num_rows($pengaduan_selesai);
}

?>
<div class="container-fluid py-4">
    <div class="row">
        <?php if ($_SESSION['level'] == 'admin') { ?>
            <!-- pengaduan -->
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Jumlah Pengaduan</p>
                                    <h4 class="font-weight-bolder">
                                        <?= $jml_pengaduan; ?>
                                    </h4>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                                    <i class="ni ni-single-copy-04 text-light text-lg opacity-10"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- petugas -->
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Jumlah Petugas</p>
                                    <h4 class="font-weight-bolder">
                                        <?= $jml_petugas; ?>
                                    </h4>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                    <i class="ni ni-single-02 text-light text-lg opacity-10"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- masyarakat -->
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Jumlah Masyarakat</p>
                                    <h4 class="font-weight-bolder">
                                        <?= $jml_masyarakat; ?>
                                    </h4>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                    <i class="ni ni-single-02 text-light text-lg opacity-10"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } elseif ($_SESSION['level'] == 'kepala_lingkungan') { ?>
            <!-- pengaduan pending -->
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Pengaduan Pending</p>
                                    <h4 class="font-weight-bolder">
                                        <?= $jml_pengaduan_pending; ?>
                                    </h4>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                    <i class="ni ni-single-copy-04 text-light text-lg opacity-10"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- pengaduan selesai -->
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Pengaduan Selesai</p>
                                    <h4 class="font-weight-bolder">
                                        <?= $jml_pengaduan_selesai; ?>
                                    </h4>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                    <i class="ni ni-check-bold text-light text-lg opacity-10"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } elseif ($_SESSION['level'] == 'lurah') { ?>
            <!-- pengaduan yang perlu di approve -->
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Pengaduan yang perlu di approve</p>
                                    <h4 class="font-weight-bolder">
                                        <?= $jml_pengaduan_validated; ?>
                                    </h4>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle">
                                    <i class="ni ni-single-copy-04 text-light text-lg opacity-10"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- pengaduan selesai dari setiap kepala lingkungan -->
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Laporan selesai dari setiap Kepala Lingkungan</p>
                                    <h4 class="font-weight-bolder">
                                        <?= $jml_pengaduan_selesai; ?>
                                    </h4>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                    <i class="ni ni-check-bold text-light text-lg opacity-10"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
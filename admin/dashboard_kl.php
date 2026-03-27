<?php
include "../config/koneksi.php";
include "../config/functions.php";

// Check access for kepala_lingkungan
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'kepala_lingkungan') {
    header("Location:../index.php?page=login");
    die();
}

$id_kl = $_SESSION['id_petugas'];

// Stats
$total_laporan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM pengaduan WHERE id_kepala_lingkungan='$id_kl'"))['cnt'];
$laporan_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM pengaduan WHERE id_kepala_lingkungan='$id_kl' AND status='pending'"))['cnt'];
$laporan_opened = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM pengaduan WHERE id_kepala_lingkungan='$id_kl' AND status='opened'"))['cnt'];
$laporan_closed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM pengaduan WHERE id_kepala_lingkungan='$id_kl' AND status='closed'"))['cnt'];

// List laporan
$laporan_q = mysqli_query($conn, "SELECT a.*, b.nama FROM pengaduan a INNER JOIN masyarakat b ON a.nik=b.nik WHERE a.id_kepala_lingkungan='$id_kl' ORDER BY a.id_pengaduan DESC LIMIT 20");
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h4>Selamat datang, <?php echo $_SESSION['nama_petugas']; ?> (Kepala Lingkungan)</h4>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Laporan</p>
                                <h5 class="font-weight-bolder mb-0"><?php echo $total_laporan; ?></h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                <i class="ni ni-single-copy-04 text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Pending</p>
                                <h5 class="font-weight-bolder mb-0"><?php echo $laporan_pending; ?></h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                                <i class="ni ni-time-alarm text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Opened</p>
                                <h5 class="font-weight-bolder mb-0"><?php echo $laporan_opened; ?></h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                <i class="ni ni-check-bold text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Closed</p>
                                <h5 class="font-weight-bolder mb-0"><?php echo $laporan_closed; ?></h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                <i class="ni ni-folder-17 text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="card">
            <div class="card-header d-flex pb-0">
                <h6>Daftar Laporan Saya</h6>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">No</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7 ps-2">ID Laporan</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Judul</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Pelapor</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Lokasi</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Status</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Tanggal Masuk</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while ($laporan = mysqli_fetch_assoc($laporan_q)) {
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $no++; ?></td>
                                <td><?php echo $laporan['id_pengaduan']; ?></td>
                                <td><?php echo $laporan['judul_pengaduan']; ?></td>
                                <td><?php echo $laporan['nama']; ?></td>
                                <td><?php echo htmlspecialchars(normalize_wilayah($laporan['wilayah'])); ?></td>
                                <td><?php echo $laporan['status']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($laporan['tgl_pengaduan'])); ?></td>
                                <td class="text-center">
                                    <a href="index.php?page=pengaduan_kepala" class="btn btn-primary btn-sm">Kelola</a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
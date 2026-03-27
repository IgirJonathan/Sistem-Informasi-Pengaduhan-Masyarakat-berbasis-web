<?php
include "../config/koneksi.php";
include "../config/functions.php";

// Petugas role has been removed. Redirect to admin dashboard.
echo "<script>alert('Halaman petugas dinonaktifkan. Akses dialihkan ke dashboard admin/kepala lingkungan.'); window.location.href='../admin/index.php';</script>";
exit();

// Stats
$total_tugas = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM pengaduan WHERE assigned_petugas_id='$id_petugas'"))['cnt'];
$tugas_progress = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM pengaduan WHERE assigned_petugas_id='$id_petugas' AND status='opened' AND progress_status='on_progress'"))['cnt'];
$tugas_selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM pengaduan WHERE assigned_petugas_id='$id_petugas' AND validated_by_petugas=1"))['cnt'];

// List tugas
$tugas_q = mysqli_query($conn, "SELECT a.*, b.nama FROM pengaduan a INNER JOIN masyarakat b ON a.nik=b.nik WHERE a.assigned_petugas_id='$id_petugas' AND a.status='opened' ORDER BY a.id_pengaduan ASC");
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h4>Selamat datang, <?php echo $_SESSION['nama_petugas']; ?></h4>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Tugas Diterima</p>
                                <h5 class="font-weight-bolder mb-0"><?php echo $total_tugas; ?></h5>
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
        <div class="col-lg-4 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Tugas Dalam Progress</p>
                                <h5 class="font-weight-bolder mb-0"><?php echo $tugas_progress; ?></h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                <i class="ni ni-time-alarm text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Tugas Selesai (Divalidasi)</p>
                                <h5 class="font-weight-bolder mb-0"><?php echo $tugas_selesai; ?></h5>
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
    </div>
    <div class="row mb-4">
        <div class="card">
            <div class="card-header d-flex pb-0">
                <h6>Daftar Tugas Saya</h6>
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
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Status Progress</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Tanggal Ditugaskan</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while ($tugas = mysqli_fetch_assoc($tugas_q)) {
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $no++; ?></td>
                                <td><?php echo $tugas['id_pengaduan']; ?></td>
                                <td><?php echo $tugas['judul_pengaduan']; ?></td>
                                <td><?php echo $tugas['nama']; ?></td>
                                <td><?php echo $tugas['wilayah']; ?></td>
                                <td><?php echo $tugas['progress_status']; ?></td>
                                <td><?php echo $tugas['assigned_at']; ?></td>
                                <td class="text-center">
                                    <a href="index.php?page=detail&id=<?php echo $tugas['id_pengaduan']; ?>" class="btn btn-primary btn-sm">Detail & Update</a>
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
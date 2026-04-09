<?php
include "../config/koneksi.php";
include "../config/functions.php";

// Check if user is kepala_lingkungan
check_access(['kepala_lingkungan']);

$level = $_SESSION['level'] ?? '';
$id_petugas = $_SESSION['id_petugas'] ?? 0;

if ($level == 'kepala_lingkungan') {
    // Untuk KL: tugas yang sudah selesai atau sudah di-mark completed oleh KL
    $query = mysqli_query($conn, "SELECT p.id_pengaduan, p.judul_pengaduan, m.nama AS pengirim, p.status, p.progress_status, COALESCE(p.closed_at, p.last_progress_update) AS tanggal
                                  FROM pengaduan p
                                  LEFT JOIN masyarakat m ON p.nik = m.nik
                                  WHERE p.id_kepala_lingkungan = '$id_petugas'
                                    AND (p.status = 'closed' OR p.progress_status = 'completed')
                                  GROUP BY p.id_pengaduan
                                  ORDER BY COALESCE(p.closed_at, p.last_progress_update) DESC");
} else {
    echo "<div class='alert alert-danger'>Akses ditolak</div>";
    exit;
}
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Arsip Tugas <?php echo ($level == 'petugas') ? 'Selesai (Divalidasi)' : 'Closed'; ?></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Judul Pengaduan</th>
                                    <th>Pengirim</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($query) > 0) { ?>
                                    <?php while ($row = mysqli_fetch_array($query)) { ?>
                                        <tr>
                                            <td><?php echo format_datetime($row['tanggal']); ?></td>
                                            <td><?php echo htmlspecialchars($row['judul_pengaduan']); ?></td>
                                            <td><?php echo htmlspecialchars($row['pengirim']); ?></td>
                                            <td>
                                                <?php if ($row['status'] == 'closed') : ?>
                                                    <span class="badge bg-success">Selesai</span>
                                                <?php elseif ($row['progress_status'] == 'completed') : ?>
                                                    <span class="badge bg-success">Selesai (Menunggu Tutup)</span>
                                                <?php else : ?>
                                                    <span class="badge bg-success">Selesai</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="index.php?page=detail_arsip&id_pengaduan=<?php echo $row['id_pengaduan']; ?>" class="btn btn-primary btn-sm">Lihat Detail</a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Belum ada tugas yang selesai</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
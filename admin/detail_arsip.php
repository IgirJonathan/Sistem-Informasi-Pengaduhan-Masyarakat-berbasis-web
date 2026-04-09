<?php

include "../config/koneksi.php";
include "../config/functions.php";

// Check if user is kepala_lingkungan or admin
check_access(['kepala_lingkungan', 'admin', 'lurah']);

if (!empty($_GET['id_pengaduan'])) {
    $id = $_GET['id_pengaduan'];
    $level = $_SESSION['level'] ?? '';
    $id_petugas = $_SESSION['id_petugas'] ?? 0;

    // Cek akses: sesuai level user
    $access_check = false;
    if ($level == 'admin') {
        // Admin dapat melihat semua arsip
        $access_check = true;
    } elseif ($level == 'lurah') {
        // Lurah dapat melihat arsip yang sudah closed dan ditangani olehnya
        $check_query = mysqli_query($conn, "SELECT id_pengaduan FROM pengaduan WHERE id_pengaduan = '$id' AND status = 'closed' AND id_lurah = '$id_petugas'");
        $access_check = mysqli_num_rows($check_query) > 0;
    } elseif ($level == 'kepala_lingkungan') {
        // KL dapat melihat arsip yang sudah closed dan divalidasi olehnya
        $check_query = mysqli_query($conn, "SELECT id_pengaduan FROM pengaduan WHERE id_pengaduan = '$id' AND status = 'closed' AND id_kepala_lingkungan = '$id_petugas'");
        $access_check = mysqli_num_rows($check_query) > 0;
    }    

    if (!$access_check) {
        echo "<div class='container'><div class='alert alert-danger'>Akses ditolak atau pengaduan tidak ditemukan</div></div>";
        exit;
    }

    // Query untuk mendapatkan data pengaduan dengan informasi lengkap
    $query = mysqli_query($conn, "SELECT a.*, 
                              b.nama as nama_masyarakat,
                              c.nama_petugas as nama_kepala_lingkungan,
                              d.nama_petugas as nama_lurah,
                              e.nama_petugas as nama_staff,
                              f.nama_petugas as opened_by_name,
                              g.nama_petugas as approved_by_name,
                              h.nama_petugas as rejected_by_name,
                              i.nama_petugas as assigned_by_name,
                              j.nama_petugas as closed_by_name
                          FROM pengaduan a 
                          LEFT JOIN masyarakat b ON a.nik = b.nik
                          LEFT JOIN petugas c ON a.id_kepala_lingkungan = c.id_petugas
                          LEFT JOIN petugas d ON a.id_lurah = d.id_petugas
                          LEFT JOIN petugas e ON a.id_staff = e.id_petugas
                          LEFT JOIN petugas f ON a.opened_by = f.id_petugas
                          LEFT JOIN petugas g ON a.approved_by = g.id_petugas
                          LEFT JOIN petugas h ON a.rejected_by = h.id_petugas
                          LEFT JOIN petugas i ON a.assigned_by = i.id_petugas
                          LEFT JOIN petugas j ON a.closed_by = j.id_petugas
                          WHERE a.id_pengaduan = '$id'");
    
    if ($query && mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_array($query);
        
        // Query untuk mendapatkan semua tanggapan
        $tanggapan_query = mysqli_query($conn, "SELECT t.*, p.nama_petugas 
                                               FROM tanggapan t 
                                               LEFT JOIN petugas p ON t.id_petugas = p.id_petugas 
                                               WHERE t.id_pengaduan = '$id' 
                                               ORDER BY t.tgl_tanggapan ASC");
?>
    <div class="container">
        <div class="row">
            <div class="col-md-12 mt-2">
                <div class="card">
                    <div class="card-header">
                        <h5>DETAIL ARSIP: <?= $data['judul_pengaduan']; ?> (SELESAI)</h5>
                    </div>
                    <div class="card-body">
                        <!-- Informasi Laporan -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>Informasi Laporan</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td width="120">Judul:</td>
                                        <td><?= $data['judul_pengaduan']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Isi Laporan:</td>
                                        <td><?= nl2br($data['isi_laporan']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Tanggal:</td>
                                        <td><?= format_datetime($data['tgl_pengaduan']); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Foto:</td>
                                        <td>
                                            <?php if ($data['foto']) { ?>
                                                <img src="../database/img/<?= $data['foto']; ?>" style="width: 150px" alt="Foto Laporan">
                                            <?php } else { ?>
                                                Tidak ada foto
                                            <?php } ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Status & Penanganan</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td width="120">Status:</td>
                                        <td>
                                            <?php 
                                            if ($data['status'] == 'pending') {
                                                echo '<span class="badge bg-secondary">Menunggu Validasi</span>';
                                            } elseif ($data['status'] == 'rejected') {
                                                echo '<span class="badge bg-danger">Ditolak</span>';
                                            } elseif ($data['status'] == 'opened') {
                                                if ($data['is_workable'] == 0) {
                                                    echo '<span class="badge bg-info">Diterima - Tidak Dapat Dikerjakan</span>';
                                                } else {
                                                    echo '<span class="badge bg-warning">Sedang Ditangani</span>';
                                                }
                                            } elseif ($data['status'] == 'closed') {
                                                echo '<span class="badge bg-success">Selesai</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php if ($data['nama_kepala_lingkungan']) { ?>
                                    <tr>
                                        <td>Kepala Lingkungan:</td>
                                        <td><?= $data['nama_kepala_lingkungan']; ?></td>
                                    </tr>
                                    <?php } ?>
                                    <?php if ($data['nama_lurah']) { ?>
                                    <tr>
                                        <td>Lurah:</td>
                                        <td><?= $data['nama_lurah']; ?></td>
                                    </tr>
                                    <?php } ?>
                                    <?php if ($data['nama_staff']) { ?>
                                    <tr>
                                        <td>Petugas:</td>
                                        <td><?= $data['nama_staff']; ?></td>
                                    </tr>
                                    <?php } ?>
                                    <tr>
                                        <td>Submitted:</td>
                                        <td><?= format_datetime($data['tgl_pengaduan']); ?></td>
                                    </tr>
                                    <?php if (!empty($data['opened_at'])) { ?>
                                    <tr>
                                        <td>Dibuka:</td>
                                        <td><?= format_datetime($data['opened_at']); ?>
                                            <?php if (!empty($data['opened_by_name'])) { echo ' - oleh ' . $data['opened_by_name']; } ?></td>
                                    </tr>
                                    <?php } ?>
                                    <?php if (!empty($data['approved_at'])) { ?>
                                    <tr>
                                        <td>Disetujui:</td>
                                        <td><?= format_datetime($data['approved_at']); ?>
                                            <?php if (!empty($data['approved_by_name'])) { echo ' - oleh ' . $data['approved_by_name']; } ?></td>
                                    </tr>
                                    <?php } ?>
                                    <?php if (!empty($data['is_assigned']) && $data['is_assigned'] == 1) { ?>
                                    <tr>
                                        <td>Ditugaskan:</td>
                                        <td><?= !empty($data['assigned_at']) ? format_datetime($data['assigned_at']) : '-'; ?>
                                            <?php if (!empty($data['assigned_by_name'])) { echo ' - oleh ' . $data['assigned_by_name']; } ?></td>
                                    </tr>
                                    <?php } ?>
                                    <?php if (!empty($data['closed_at'])) { ?>
                                    <tr>
                                        <td>Selesai:</td>
                                        <td><?= format_datetime($data['closed_at']); ?>
                                            <?php if (!empty($data['closed_by_name'])) { echo ' - oleh ' . $data['closed_by_name']; } ?></td>
                                    </tr>
                                    <?php } ?>
                                </table>
                                
                                <?php if ($data['status'] == 'rejected') { ?>
                                    <div class="alert alert-danger">
                                        <strong>Laporan Ditolak</strong><br>
                                        <?php if (!empty($data['rejected_by_name'])) { ?>
                                            <strong>Ditolak oleh:</strong> <?= $data['rejected_by_name']; ?><br>
                                        <?php } else { ?>
                                            <strong>Ditolak oleh:</strong> Kepala Lingkungan<br>
                                        <?php } ?>
                                        <?php if (!empty($data['rejection_reason'])) { ?>
                                            <br><strong>Alasan:</strong> <?= nl2br($data['rejection_reason']); ?><br>
                                        <?php } ?>
                                        <?php if (!empty($data['rejected_at'])) { ?>
                                            <br><small>Ditolak pada: <?= format_datetime($data['rejected_at']); ?></small>
                                        <?php } ?>
                                    </div>
                                <?php } elseif ($data['status'] == 'opened' && $data['is_workable'] == 0) { ?>
                                    <div class="alert alert-info">
                                        <strong>Laporan Diterima - Tidak Dapat Dikerjakan</strong><br>
                                        Laporan telah diterima namun tidak dapat ditangani oleh kelurahan.
                                        <?php if ($data['non_workable_reason']) { ?>
                                            <br><br><strong>Alasan:</strong> <?= nl2br($data['non_workable_reason']); ?>
                                        <?php } ?>
                                    </div>
                                <?php } elseif ($data['status'] == 'closed') { ?>
                                    <div class="alert alert-success">
                                        <strong>Laporan Telah Selesai</strong><br>
                                        Laporan telah berhasil ditangani dan ditutup.
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        
                        <!-- Riwayat Tanggapan -->
                        <?php if (mysqli_num_rows($tanggapan_query) > 0) { ?>
                        <div class="row">
                            <div class="col-md-12">
                                <h6>Riwayat Tanggapan & Progress</h6>
                                <div class="timeline">
                                    <?php while ($tanggapan = mysqli_fetch_array($tanggapan_query)) { ?>
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-primary"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">
                                                <?= $tanggapan['nama_petugas'] ?? 'Sistem'; ?> 
                                                <small class="text-muted">- <?= format_datetime($tanggapan['tgl_tanggapan']); ?></small>
                                            </h6>
                                            <p class="timeline-text"><?= nl2br($tanggapan['tanggapan']); ?></p>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <?php } else { ?>
                        <p class="text-muted">Belum ada tanggapan.</p>
                        <?php } ?>
                    </div>
                    <div class="card-footer">
                        <a href="index.php?page=arsip_tugas" class="btn btn-success">Kembali ke Arsip Tugas</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php  
    } else {
        echo "<div class='container'><div class='alert alert-danger'>Pengaduan tidak ditemukan</div></div>";
    }
} else {
    echo "<div class='container'><div class='alert alert-danger'>Halaman tidak tersedia</div></div>";
} 
?>
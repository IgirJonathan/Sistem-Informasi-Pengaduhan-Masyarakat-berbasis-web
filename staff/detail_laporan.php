<?php
include "../config/koneksi.php";
include "../config/functions.php";

// Detail laporan for petugas has been disabled because petugas role removed.
echo "<script>alert('Halaman petugas dinonaktifkan. Akses dialihkan ke dashboard Kepala Lingkungan.'); window.location.href='../admin/index.php?page=pengaduan_kepala';</script>";
exit();

// Get pengaduan details
$pengaduan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT a.*, b.nama, b.telp FROM pengaduan a INNER JOIN masyarakat b ON a.nik=b.nik WHERE a.id_pengaduan='$id_pengaduan' AND a.assigned_petugas_id='$id_petugas'"));
if (!$pengaduan) {
    echo "<script>alert('Laporan tidak ditemukan atau tidak ditugaskan kepada Anda'); document.location.href='index.php?page=dashboard';</script>";
    die();
}

// Get tanggapan relevant for petugas: include Lurah and Kepala Lingkungan notes,
// and include petugas entries originating from KL targeting petugas (KL requests validation)
$tanggapan_q = mysqli_query($conn, "SELECT a.*, b.nama_petugas as nama, LOWER(b.level) as level FROM tanggapan a INNER JOIN petugas b ON a.id_petugas=b.id_petugas WHERE a.id_pengaduan='$id_pengaduan' AND a.id_petugas != '{$_SESSION['id_petugas']}' AND (LOWER(b.level) IN ('lurah','kepala_lingkungan') OR (LOWER(b.level)='petugas' AND a.tanggapan LIKE 'KL requests validation%')) ORDER BY a.tgl_tanggapan ASC");

// Normalize to array so we can render in multiple places without exhausting the result pointer
$tanggapan_rows = [];
if ($tanggapan_q) {
    while ($r = mysqli_fetch_assoc($tanggapan_q)) {
        $tanggapan_rows[] = $r;
    }
}

// Handle progress update
if (isset($_POST['update_progress'])) {
    $progress_status = $_POST['progress_status'];
    $progress_desc = $_POST['progress_desc'];
    // Server-side check
    if ($pengaduan['progress_status'] == 'completed' && $pengaduan['awaiting_petugas_validation'] == 1) {
        echo "<script>alert('Progress dikunci setelah Kepala Lingkungan set selesai.'); history.back();</script>";
        exit;
    }
    $result = update_progress_staff($conn, $id_pengaduan, $progress_desc, $progress_status, $id_petugas);
    if ($result['success']) {
        echo "<script>alert('Progress berhasil diupdate'); document.location.href='index.php?page=detail&id=$id_pengaduan';</script>";
    } else {
        echo "<script>alert('Gagal update progress: " . $result['message'] . "');</script>";
    }
}

    // Handle validation
if (isset($_POST['validate_completion'])) {
    // Server-side guard: role
    if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'petugas') {
        echo "<script>alert('Akses ditolak'); document.location.href='index.php?page=dashboard';</script>";
        exit;
    }
    
    $id_pengaduan = (int)$_POST['id_pengaduan'];
    $id_petugas = (int)$_SESSION['id_petugas'];
    $catatan = mysqli_real_escape_string($conn, $_POST['validation_notes'] ?? 'Penyelesaian divalidasi oleh petugas');
    
    // Server-side guard: ownership & state
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT progress_status, assigned_petugas_id FROM pengaduan WHERE id_pengaduan=$id_pengaduan"));
    if (!$row || (int)$row['assigned_petugas_id'] !== $id_petugas) {
        echo "<script>alert('Bukan tanggung jawab Anda'); document.location.href='index.php?page=dashboard';</script>";
        exit;
    }
    if ($row['progress_status'] !== 'completed') {
        echo "<script>alert('Validasi tidak diizinkan saat ini'); document.location.href='index.php?page=detail&id=$id_pengaduan';</script>";
        exit;
    }
    
    // Transaction
    mysqli_begin_transaction($conn);
    try {
        $result = staff_validate_completion($conn, $id_pengaduan, $id_petugas, $catatan);
        if (!$result['success']) throw new Exception($result['message']);
        
        // Set can_close=1, awaiting_petugas_validation=0, validated_by_petugas=1
        mysqli_query($conn, "UPDATE pengaduan SET can_close=1, awaiting_petugas_validation=0, validated_by_petugas=1, petugas_validation_notes='$catatan', petugas_validated_at=NOW() WHERE id_pengaduan=$id_pengaduan");
        
        // staff_validate_completion already inserts a standardized tanggapan; no extra insert here
        
        // Notification to KL
        mysqli_query($conn, "INSERT INTO notifications (nik, type, message, link, created_at) VALUES ('-', 'report_validated', 'Laporan #$id_pengaduan telah divalidasi petugas', 'admin/index.php?page=pengaduan_kepala', NOW())");
        
        mysqli_commit($conn);
        echo "<script>alert('Laporan berhasil divalidasi selesai'); document.location.href='index.php?page=dashboard';</script>";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Validation failed: " . $e->getMessage());
        echo "<script>alert('Gagal validasi: " . $e->getMessage() . "');</script>";
    }
}
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h4>Detail Laporan #<?php echo $pengaduan['id_pengaduan']; ?></h4>
            <a href="index.php?page=dashboard" class="btn btn-secondary">Kembali ke Dashboard</a>
        </div>
    </div>
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#detail">Detail Laporan</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#riwayat">Riwayat Catatan Tugas</a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="detail">
            <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6>Informasi Laporan</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Judul:</strong> <?php echo $pengaduan['judul_pengaduan']; ?></p>
                            <p><strong>Pelapor:</strong> <?php echo $pengaduan['nama']; ?> (<?php echo $pengaduan['telp']; ?>)</p>
                            <p><strong>Lokasi:</strong> <?php echo $pengaduan['wilayah']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tanggal Laporan:</strong> <?php echo $pengaduan['tgl_pengaduan']; ?></p>
                            <p><strong>Status:</strong> <span class="badge bg-info"><?php echo $pengaduan['status']; ?></span></p>
                            <p><strong>Progress:</strong> <span class="badge bg-warning"><?php echo $pengaduan['progress_status']; ?></span></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <p><strong>Isi Laporan:</strong></p>
                            <p><?php echo $pengaduan['isi_laporan']; ?></p>
                        </div>
                    </div>
                    <?php if ($pengaduan['foto']) { ?>
                    <div class="row">
                        <div class="col-12">
                            <p><strong>Foto Bukti:</strong></p>
                            <img src="../database/img/<?php echo $pengaduan['foto']; ?>" alt="Foto Bukti" class="img-fluid" style="max-width: 300px;">
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <?php if ($pengaduan['validated_by_petugas'] != 1) { ?>
            <div class="card">
                <div class="card-header">
                    <h6>Update Progress</h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="progress_status" class="form-label">Status Progress</label>
                            <?php if ($pengaduan['progress_status'] == 'completed' && $pengaduan['awaiting_petugas_validation'] == 1) { ?>
                                <select class="form-control" id="progress_status" name="progress_status" disabled>
                                    <option value="completed" selected>Selesai (Locked - Menunggu Validasi)</option>
                                </select>
                                <small class="text-muted">Progress dikunci sampai Kepala Lingkungan ubah status.</small>
                            <?php } else { ?>
                                <select class="form-control" id="progress_status" name="progress_status" required>
                                    <option value="pending" <?php if ($pengaduan['progress_status'] == 'pending') echo 'selected'; ?>>Pending</option>
                                    <option value="on_progress" <?php if ($pengaduan['progress_status'] == 'on_progress') echo 'selected'; ?>>On Progress</option>
                                </select>
                            <?php } ?>
                        </div>
                        <div class="mb-3">
                            <label for="progress_desc" class="form-label">Deskripsi Progress</label>
                            <textarea class="form-control" id="progress_desc" name="progress_desc" rows="3" placeholder="Jelaskan progress yang telah dilakukan..."></textarea>
                        </div>
                        <button type="submit" name="update_progress" class="btn btn-primary">Update Progress</button>
                    </form>
                </div>
            </div>
            <?php } ?>
            <?php if ($pengaduan['progress_status'] == 'completed' && !$pengaduan['validated_by_petugas']) { ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h6>Validasi Penyelesaian</h6>
                </div>
                <div class="card-body">
                    <p>Laporan telah diselesaikan oleh Kepala Lingkungan. Klik tombol di bawah untuk memvalidasi penyelesaian.</p>
                    <form method="POST">
                        <input type="hidden" name="id_pengaduan" value="<?php echo $id_pengaduan; ?>">
                        <div class="mb-3">
                            <label for="validation_notes" class="form-label">Catatan Validasi</label>
                            <textarea class="form-control" id="validation_notes" name="validation_notes" rows="3" placeholder="Jelaskan validasi penyelesaian..."></textarea>
                        </div>
                        <button type="submit" name="validate_completion" class="btn btn-success">✅ Validasi Selesai</button>
                    </form>
                </div>
            </div>
            <?php } elseif ($pengaduan['progress_status'] == 'completed' && $pengaduan['validated_by_petugas']) { ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h6>Validasi Penyelesaian</h6>
                </div>
                <div class="card-body">
                    <p class="text-success">✅ Laporan telah divalidasi selesai oleh petugas.</p>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6>Riwayat Tanggapan</h6>
                </div>
                <div class="card-body">
                    <?php if (count($tanggapan_rows) > 0) { ?>
                    <div class="timeline timeline-one-side">
                        <?php foreach ($tanggapan_rows as $tanggapan) { ?>
                        <div class="timeline-block mb-3">
                            <span class="timeline-step">
                                <i class="ni ni-bell-55 text-success text-gradient"></i>
                            </span>
                        <div class="timeline-content">
                            <h6 class="text-dark text-sm font-weight-bold mb-0"><?php echo htmlspecialchars($tanggapan['nama']); ?> (<?php echo isset($tanggapan['level']) ? ucfirst($tanggapan['level']) : 'Petugas'; ?>)</h6>
                                <p class="text-secondary font-weight-bold text-xs mt-1 mb-0"><?php echo $tanggapan['tgl_tanggapan']; ?></p>
                                <p class="text-sm mt-1 mb-0"><?php echo nl2br(htmlspecialchars($tanggapan['tanggapan'])); ?></p>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                    <?php } else { ?>
                    <p class="text-muted">Belum ada tanggapan.</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
        </div>
        <div class="tab-pane" id="riwayat">
            <div class="card">
                <div class="card-header">
                    <h6>Riwayat Catatan Tugas dari Lurah</h6>
                </div>
                <div class="card-body">
                    <?php 
                    // Show only Lurah-origin tanggapan for the 'Riwayat Catatan Tugas dari Lurah' tab
                    $lurah_notes = array_filter($tanggapan_rows, function($it){ return isset($it['level']) && strtolower($it['level']) === 'lurah'; });
                    if (count($lurah_notes) > 0) {
                    ?>
                        <div class="list-group">
                            <?php foreach ($lurah_notes as $tanggapan) { ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($tanggapan['nama'] ?? 'Lurah'); ?> (Lurah)</h6>
                                        <small><?php echo $tanggapan['tgl_tanggapan']; ?></small>
                                    </div>
                                    <p class="mb-1"><?php echo nl2br(htmlspecialchars($tanggapan['tanggapan'])); ?></p>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <p class="text-muted">Belum ada catatan tugas dari lurah.</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
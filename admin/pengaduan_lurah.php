<?php
include "../config/koneksi.php";
include "../config/functions.php";

// Check access for lurah
check_access(['lurah']);

// Handle approval/rejection by Lurah
if (isset($_POST['approval_action'])) {
    $id_pengaduan = $_POST['id_pengaduan'];
    $action = $_POST['action']; // 'approve' atau 'reject'
    $catatan_publik = mysqli_real_escape_string($conn, $_POST['catatan_publik'] ?? '');
    $catatan_private = mysqli_real_escape_string($conn, $_POST['catatan_private'] ?? '');
    $id_lurah = $_SESSION['id_petugas'];
    
    // Check if not already approved
    $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT is_approved FROM pengaduan WHERE id_pengaduan='$id_pengaduan'"));
    if ($check && $check['is_approved'] == 1) {
        echo "<script>alert('Laporan sudah di-approve oleh Lurah'); document.location.href='index.php?page=pengaduan_lurah';</script>";
        exit;
    }
    
    if ($action === 'approve') {
        // Lurah approve: set is_approved=1, status='opened', id_lurah, approval_notes
        $query = "UPDATE pengaduan SET is_approved=1, id_lurah='$id_lurah', status='opened', approval_notes='$catatan_private', approved_by='$id_lurah', approved_at=NOW() WHERE id_pengaduan='$id_pengaduan'";
        $res = mysqli_query($conn, $query);
        if ($res) {
            $msg = !empty($catatan_publik) ? 'Disetujui oleh Lurah: ' . $catatan_publik : 'Disetujui oleh Lurah';
            create_tanggapan($conn, ['id_pengaduan' => $id_pengaduan, 'tanggapan' => $msg, 'id_petugas' => $id_lurah]);
            echo "<script>alert('Laporan berhasil di-approve oleh Lurah'); document.location.href='index.php?page=pengaduan_lurah';</script>";
            exit;
        } else {
            echo "<script>alert('Gagal approve laporan'); document.location.href='index.php?page=pengaduan_lurah';</script>";
            exit;
        }
    } elseif ($action === 'reject') {
        // Lurah reject: set status='rejected', rejected_by, rejected_at, rejection_reason
        $query = "UPDATE pengaduan SET status='rejected', rejected_by='$id_lurah', rejected_at=NOW(), rejection_reason='$catatan_private', is_approved=0 WHERE id_pengaduan='$id_pengaduan'";
        $res = mysqli_query($conn, $query);
        if ($res) {
            $msg = !empty($catatan_publik) ? 'Ditolak oleh Lurah: ' . $catatan_publik : 'Ditolak oleh Lurah';
            create_tanggapan($conn, ['id_pengaduan' => $id_pengaduan, 'tanggapan' => $msg, 'id_petugas' => $id_lurah]);
            echo "<script>alert('Laporan berhasil ditolak oleh Lurah'); document.location.href='index.php?page=pengaduan_lurah';</script>";
            exit;
        } else {
            echo "<script>alert('Gagal reject laporan'); document.location.href='index.php?page=pengaduan_lurah';</script>";
            exit;
        }
    }
}

// Query pengaduan yang validated oleh KL (tampilkan semua yang sudah divalidasi, termasuk yang sudah di-approve, tapi hilang jika sudah closed)
$sql = "SELECT a.*, a.wilayah as pengaduan_wilayah, b.*, c.nama_petugas as nama_kepala, 
               CASE 
                   WHEN a.progress_status = 'pending' THEN 'Sedang Ditangani (Pending)'
                   WHEN a.progress_status = 'on_progress' THEN 'Sedang Dikerjakan'
                   WHEN a.status = 'closed' THEN 'Selesai'
                   ELSE 'Menunggu Penanganan'
               END as progress_display,
               CASE 
                   WHEN a.progress_status = 'pending' THEN 'bg-secondary'
                   WHEN a.progress_status = 'on_progress' THEN 'bg-warning'
                   WHEN a.status = 'closed' THEN 'bg-success'
                   ELSE 'bg-info'
               END as progress_badge_class
        FROM pengaduan a
        INNER JOIN masyarakat b ON a.nik = b.nik
        LEFT JOIN petugas c ON a.id_kepala_lingkungan = c.id_petugas
        WHERE a.id_kepala_lingkungan IS NOT NULL
        AND a.status IN ('pending', 'opened')
        AND NOT EXISTS (
            SELECT 1 FROM tanggapan t
            JOIN petugas p ON t.id_petugas = p.id_petugas
            WHERE t.id_pengaduan = a.id_pengaduan
              AND p.level = 'kepala_lingkungan'
              AND t.tanggapan LIKE 'Validasi dibatalkan%'
        )
        ORDER BY 
            CASE 
                WHEN a.is_approved != 1 THEN 1  -- Belum di-approve di atas
                WHEN a.status = 'closed' THEN 3 -- Selesai di bawah
                ELSE 2                          -- Sedang ditangani di tengah
            END,
            a.tgl_pengaduan DESC";
$ambil = mysqli_query($conn, $sql);
if (!$ambil) {
    die("Query error: " . mysqli_error($conn) . " -- Query: " . $sql);
}
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="card">
            <div class="card-header d-flex pb-0">
                <h6>APPROVAL LAPORAN OLEH LURAH</h6>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table table-striped table-hover table-sm align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">No</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7 ps-2">Judul</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Pelapor</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Tanggal Masuk</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Kepala Lingkungan</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Status Approval</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Progress KL</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Detail</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while ($data = mysqli_fetch_array($ambil)) {
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $no++; ?></td>
                                <td><?php echo $data['judul_pengaduan']; ?></td>
                                <td><?php echo $data['nama']; ?></td>
                                <td><?php echo format_datetime($data['tgl_pengaduan']); ?></td>
                                <td><?php echo $data['nama_kepala'] ?? '-'; ?></td>
                                <td class="text-center">
                                    <?php if (!$data['is_approved'] || $data['is_approved'] != 1) { ?>
                                        <span class="badge badge-sm bg-warning">Menunggu Approval</span>
                                    <?php } else { ?>
                                        <span class="badge badge-sm bg-success">Sudah Approved</span>
                                    <?php } ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($data['is_approved'] == 1 && $data['status'] != 'closed') { ?>
                                        <span class="badge badge-sm <?php echo $data['progress_badge_class']; ?>"><?php echo $data['progress_display']; ?></span>
                                    <?php } elseif ($data['status'] == 'closed') { ?>
                                        <span class="badge badge-sm bg-success">Selesai</span>
                                    <?php } else { ?>
                                        <span class="badge badge-sm bg-light text-dark">-</span>
                                    <?php } ?>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="d-flex align-items-center justify-content-center" style="height:100%;">
                                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailModal<?php echo $data['id_pengaduan']; ?>">Detail</button>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="d-flex align-items-center justify-content-center" style="height:100%;">
                                        <div class="btn-group" role="group" aria-label="Aksi">
                                        <?php if (empty($data['is_approved']) || $data['is_approved'] != 1) { ?>
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#approval<?php echo $data['id_pengaduan']; ?>">Approval</button>
                                        <?php } elseif ($data['status'] == 'closed') { ?>
                                            <button class="btn btn-secondary btn-sm" disabled>Laporan Selesai</button>
                                        <?php } else { ?>
                                            <button class="btn btn-success btn-sm" disabled>Monitoring</button>
                                        <?php } ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal Detail Laporan Lurah -->
                            <div class="modal fade" id="detailModal<?php echo $data['id_pengaduan']; ?>" tabindex="-1" aria-labelledby="detailModalLabel<?php echo $data['id_pengaduan']; ?>" aria-hidden="true">
                                <div class="modal-dialog modal-xl" style="max-width:1100px;">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="detailModalLabel<?php echo $data['id_pengaduan']; ?>">Detail Laporan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Judul:</strong> <?php echo htmlspecialchars($data['judul_pengaduan']); ?></p>
                                            <p><strong>Isi Laporan:</strong><br><?php echo nl2br(htmlspecialchars($data['isi_laporan'])); ?></p>
                                            <p><strong>Lingkungan:</strong> <?php echo htmlspecialchars(normalize_wilayah($data['pengaduan_wilayah'])); ?></p>
                                            <?php if (!empty($data['foto'])) { ?>
                                                <div class="mb-2">
                                                    <img src="../database/img/<?php echo htmlspecialchars($data['foto']); ?>" alt="Foto" style="max-width:100%; height:auto;" />
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Modal Approval -->
                            <div class="modal fade" id="approval<?php echo $data['id_pengaduan']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Approval Laporan - <?php echo htmlspecialchars($data['judul_pengaduan']); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" novalidate>
                                            <div class="modal-body">
                                                <input type="hidden" name="id_pengaduan" value="<?php echo $data['id_pengaduan']; ?>">
                                                <div class="mb-3">
                                                    <p><strong>Pelapor:</strong> <?php echo htmlspecialchars($data['nama']); ?></p>
                                                    <p><strong>Kepala Lingkungan yang Membimbing:</strong> <?php echo htmlspecialchars($data['nama_kepala'] ?? '-'); ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <label><strong>Keputusan Anda:</strong></label><br>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="action" value="approve" id="approve_<?php echo $data['id_pengaduan']; ?>" checked>
                                                        <label class="form-check-label" for="approve_<?php echo $data['id_pengaduan']; ?>">
                                                            Setujui (Approve) - Laporan akan dilanjutkan ke Kepala Lingkungan untuk penanganan
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="action" value="reject" id="reject_<?php echo $data['id_pengaduan']; ?>">
                                                        <label class="form-check-label" for="reject_<?php echo $data['id_pengaduan']; ?>">
                                                            Tolak (Reject) - Laporan akan ditandai sebagai ditolak
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="catatan_publik_<?php echo $data['id_pengaduan']; ?>">Catatan Publik (akan ditampilkan di timeline masyarakat):</label>
                                                    <textarea name="catatan_publik" class="form-control" rows="2" id="catatan_publik_<?php echo $data['id_pengaduan']; ?>" placeholder="Catatan yang akan dilihat oleh pelapor..."></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="catatan_private_<?php echo $data['id_pengaduan']; ?>">Catatan untuk Kepala Lingkungan (internal):</label>
                                                    <textarea name="catatan_private" class="form-control" rows="2" id="catatan_private_<?php echo $data['id_pengaduan']; ?>" placeholder="Catatan internal untuk KL..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" name="approval_action" class="btn btn-primary">Proses Approval</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
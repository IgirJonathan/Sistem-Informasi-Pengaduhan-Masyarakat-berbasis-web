<?php
include "../config/koneksi.php";
include "../config/functions.php";

// Check access for kepala_lingkungan
check_access(['kepala_lingkungan', 'Kepala_Lingkungan']);

// Get current kepala_lingkungan id and wilayah
$id_kepala = $_SESSION['id_petugas'];
$wilayah_query = mysqli_query($conn, "SELECT wilayah FROM petugas WHERE id_petugas = '$id_kepala'");
if ($wilayah_query && mysqli_num_rows($wilayah_query) > 0) {
    $wilayah_data = mysqli_fetch_assoc($wilayah_query);
    $wilayah = normalize_wilayah($wilayah_data['wilayah']);
} else {
    $wilayah = 'Tidak Ditetapkan'; // Default jika tidak ada
}

// Get filter parameter from GET
$filter_wilayah = isset($_GET['filter_wilayah']) ? $_GET['filter_wilayah'] : '';

function formatWilayah($rawWilayah) {
    // Gunakan helper umum untuk semua modul
    return normalize_wilayah($rawWilayah);
}
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="card">
            <div class="card-header d-flex pb-0 justify-content-between align-items-center">
                <h6>PENGADUAN <?= formatWilayah($wilayah); ?> - VALIDASI AWAL</h6>
                <div style="width: 250px;">
                    <form method="GET" class="d-flex gap-2">
                        <input type="hidden" name="page" value="pengaduan_kepala">
                        <select name="filter_wilayah" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">-- Semua Lingkungan --</option>
                            <option value="1" <?php echo ($filter_wilayah === '1') ? 'selected' : ''; ?>>Lingkungan 1</option>
                            <option value="2" <?php echo ($filter_wilayah === '2') ? 'selected' : ''; ?>>Lingkungan 2</option>
                            <option value="3" <?php echo ($filter_wilayah === '3') ? 'selected' : ''; ?>>Lingkungan 3</option>
                            <option value="4" <?php echo ($filter_wilayah === '4') ? 'selected' : ''; ?>>Lingkungan 4</option>
                            <option value="5" <?php echo ($filter_wilayah === '5') ? 'selected' : ''; ?>>Lingkungan 5</option>
                        </select>
                    </form>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table table-striped align-items-center mb-0">
                        <thead class="text-center">

                            <tr>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">No</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7 ps-2">Nama</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Judul</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Tanggal Masuk</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Lingkungan</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Progress Status</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <?php
                            $no = 1;
                            // Hitung total laporan terkait KL (exclude yang disembunyikan)
                            $total_assigned = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM pengaduan WHERE id_kepala_lingkungan='$id_kepala' AND status != 'closed' AND (hidden_from_kl IS NULL OR hidden_from_kl = 0)"))['cnt'];

                            // Query pengaduan untuk Kepala Lingkungan:
                            // - Semua laporan pending (bisa dari wilayah manapun) untuk transparency
                            // - Laporan yang ditugaskan ke KL ini (pending/opened/rejected) untuk monitoring
                            // - Validation permission tetap dibatasi per wilayah di action level
                            if ($wilayah != 'Tidak Ditetapkan') {
                                // Build base query
                                $base_query = "SELECT a.*, a.wilayah as pengaduan_wilayah, b.* FROM pengaduan a INNER JOIN masyarakat b ON a.nik = b.nik WHERE a.status != 'closed' AND (a.id_kepala_lingkungan = '$id_kepala' OR a.status = 'pending') AND (a.hidden_from_kl IS NULL OR a.hidden_from_kl = 0)";
                                
                                // Add wilayah filter if selected
                                if (!empty($filter_wilayah)) {
                                    // Convert number to lingkungan format (1 = Lingkungan 1, etc)
                                    $wilayah_filter = normalize_wilayah($filter_wilayah);
                                    $base_query .= " AND a.wilayah LIKE '%{$wilayah_filter}%'";
                                }
                                
                                $base_query .= " ORDER BY a.wilayah ASC, a.id_pengaduan ASC;";
                                $ambil = mysqli_query($conn, $base_query);
                            } else {
                                $ambil = false;
                                echo "<tr><td colspan='7' class='text-center text-warning' style='padding: 30px 20px;'><h5 class='mb-0'>Lingkungan belum ditetapkan. Silakan hubungi admin untuk mengatur lingkungan Anda.</h5></td></tr>";
                            }

                            if ($ambil && mysqli_num_rows($ambil) > 0) {
                                while ($data = mysqli_fetch_array($ambil)) {
                                ?>
                                <?php
                                    // Ambil catatan Lurah: approve -> approval_notes, reject -> rejection_reason
                                    $lurah_note = '';
                                    if (!empty($data['is_approved']) && $data['is_approved'] == 1 && !empty($data['approval_notes'])) {
                                        $lurah_note = $data['approval_notes'];
                                    } elseif ($data['status'] === 'rejected' && !empty($data['rejection_reason'])) {
                                        // hanya tampilkan catatan Lurah jika di-reject oleh Lurah (bukan KL)
                                        $k = mysqli_query($conn, "SELECT level FROM petugas WHERE id_petugas = '{$data['rejected_by']}' LIMIT 1");
                                        if ($k && ($rowk = mysqli_fetch_assoc($k)) && $rowk['level'] === 'lurah') {
                                            $lurah_note = $data['rejection_reason'];
                                        }
                                    }
                                ?>
                                <tr>
                                    <td class="align-middle text-center text-sm">
                                        <?= $no++; ?>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <p class="text-xs font-weight-bold mb-0"><?= $data['nama']; ?></p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <?= $data['judul_pengaduan']; ?>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <?= format_datetime($data['tgl_pengaduan']); ?>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <span class="badge bg-light text-dark"><?= htmlspecialchars(formatWilayah($data['pengaduan_wilayah'])); ?></span>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <?php 
                                        // determine KL validation state from tanggapan (local KL action)
                                        $kl_state = 'none';
                                        $kl_state_q = mysqli_query($conn, "SELECT tanggapan FROM tanggapan WHERE id_pengaduan='{$data['id_pengaduan']}' AND id_petugas='$id_kepala' AND (tanggapan LIKE 'Validated by KL%' OR tanggapan LIKE 'Rejected by KL%') ORDER BY id_tanggapan DESC LIMIT 1");
                                        if ($kl_state_q && mysqli_num_rows($kl_state_q) > 0) {
                                            $kl_state_row = mysqli_fetch_assoc($kl_state_q);
                                            if (strpos($kl_state_row['tanggapan'], 'Validated by KL') !== false) {
                                                $kl_state = 'validated';
                                            } elseif (strpos($kl_state_row['tanggapan'], 'Rejected by KL') !== false) {
                                                $kl_state = 'rejected';
                                            }
                                        }

                                        if ($data['status'] == 'closed') {
                                            echo '<span class="badge bg-success">Selesai</span>';
                                        } elseif ($data['status'] == 'rejected') {
                                            echo '<span class="badge bg-danger">Ditolak</span>';
                                        } elseif (!empty($data['is_approved']) && $data['is_approved'] == 1 && $data['status'] == 'opened') {
                                            if ($data['progress_status'] == 'on_progress') {
                                                echo '<span class="badge bg-warning">On Progress</span>';
                                            } elseif ($data['progress_status'] == 'pending') {
                                                echo '<span class="badge bg-secondary">Pending</span>';
                                            } else {
                                                echo '<span class="badge bg-primary">Disetujui Lurah</span>';
                                            }
                                        } elseif ($data['status'] === 'pending' && $data['id_kepala_lingkungan'] == $id_kepala && $kl_state == 'validated') {
                                            echo '<span class="badge bg-info">Divalidasi Kepala Lingkungan</span>';
                                        } elseif ($data['status'] === 'pending' && $data['id_kepala_lingkungan'] == $id_kepala) {
                                            echo '<span class="badge bg-secondary">Menunggu</span>';
                                        } else {
                                            echo '<span class="badge bg-secondary">Menunggu</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detailModal<?= $data['id_pengaduan']; ?>">Detail</button>
                                        <?php 
                                        $has_kl_tanggapan = false;
                                        $kl_tang_q = mysqli_query($conn, "SELECT id_tanggapan FROM tanggapan WHERE id_pengaduan='{$data['id_pengaduan']}' AND id_petugas='$id_kepala' AND (tanggapan LIKE 'Validated by KL%' OR tanggapan LIKE 'Rejected by KL%')");
                                        if ($kl_tang_q && mysqli_num_rows($kl_tang_q) > 0) {
                                            $has_kl_tanggapan = true;
                                        }
                                        ?>
                                        <?php 
                                            $is_assigned_to_me = ($data['id_kepala_lingkungan'] == $id_kepala);
                                            $is_pending_in_my_wilayah = ($data['status'] == 'pending' && (empty($data['id_kepala_lingkungan']) || $data['id_kepala_lingkungan'] === 'NULL') && normalize_wilayah($data['pengaduan_wilayah']) == normalize_wilayah($wilayah));
                                        ?>
                                        <?php if ($data['status'] == 'pending' && ($is_assigned_to_me || $is_pending_in_my_wilayah) && !$has_kl_tanggapan) { ?>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#validasi<?= $data['id_pengaduan']; ?>">Validasi</button>
                                        <?php } elseif ($data['status'] == 'rejected' && ($data['rejected_by'] == $id_kepala || $rejected_by_lurah)) { ?>
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#dismissReport<?= $data['id_pengaduan']; ?>">Hapus</button>
                                        <?php } elseif ($data['status'] == 'pending' && $data['id_kepala_lingkungan'] == $id_kepala && $has_kl_tanggapan && empty($data['is_approved'])) { ?>
                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#cancelValidation<?= $data['id_pengaduan']; ?>">Batalkan</button>
                                        <?php } elseif (!empty($data['is_approved']) && $data['status'] == 'opened') { ?>
                                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#progress<?= $data['id_pengaduan']; ?>">Progress</button>
                                            <?php if ($data['progress_status'] == 'on_progress') { ?>
                                                <button class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#closeKL<?= $data['id_pengaduan']; ?>">Close</button>
                                            <?php } ?>
                                        <?php } elseif ($data['status'] == 'rejected') { ?>
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#dismissReport<?= $data['id_pengaduan']; ?>">Hapus</button>
                                        <?php } ?>
                                    </td>
                                </tr>

                                <!-- Modal Detail Laporan -->
                                <div class="modal fade" id="detailModal<?= $data['id_pengaduan']; ?>" tabindex="-1" aria-labelledby="detailModalLabel<?= $data['id_pengaduan']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-xl" style="max-width:1100px;">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="detailModalLabel<?= $data['id_pengaduan']; ?>">Detail Laporan</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Judul:</strong> <?= htmlspecialchars($data['judul_pengaduan']); ?></p>
                                                <p><strong>Isi Laporan:</strong><br><?= nl2br(htmlspecialchars($data['isi_laporan'])); ?></p>
                                                <p><strong>Lingkungan:</strong> <?= htmlspecialchars(formatWilayah($data['pengaduan_wilayah'])); ?></p>
                                                <?php if (!empty($data['foto'])) { ?>
                                                    <div class="mb-2">
                                                        <img src="../database/img/<?= htmlspecialchars($data['foto']); ?>" alt="Foto" style="max-width:100%; height:auto;" />
                                                    </div>
                                                <?php } ?>
                                                <p><strong>Catatan Lurah:</strong> <?= !empty($lurah_note) ? htmlspecialchars($lurah_note) : '-'; ?></p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Validasi -->
                                <div class="modal fade" id="validasi<?= $data['id_pengaduan'] ?>" tabindex="-1" aria-labelledby="validasiLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="validasiLabel">Validasi Laporan</h1>
                                                <button type="button" class="btn-close bg-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="edit_data.php" method="POST">
                                                    <input type="hidden" name="id_pengaduan" value="<?= $data['id_pengaduan']; ?>">
                                                    <div class="mb-3">
                                                        <label>Keputusan</label><br>
                                                        <input type="radio" name="action" value="terima" id="terima<?= $data['id_pengaduan'] ?>" required> <label for="terima<?= $data['id_pengaduan'] ?>">Terima (Validasi)</label><br>
                                                        <input type="radio" name="action" value="tolak" id="tolak<?= $data['id_pengaduan'] ?>"> <label for="tolak<?= $data['id_pengaduan'] ?>">Tolak (Reject)</label><br>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label>Catatan Validasi</label>
                                                        <textarea name="catatan_validasi" class="form-control" rows="3" placeholder="Catatan opsional..."></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <p>Apakah Anda yakin ingin melakukan aksi ini?</p>
                                                    </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" name="validasi_pengaduan" class="btn btn-primary">Proses</button>
                                            </div>
                                        </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- Modal Batalkan Validasi -->
                                <div class="modal fade" id="cancelValidation<?= $data['id_pengaduan'] ?>" tabindex="-1" aria-labelledby="cancelValidationLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="cancelValidationLabel">Batalkan Validasi</h1>
                                                <button type="button" class="btn-close bg-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="edit_data.php" method="POST">
                                                    <input type="hidden" name="id_pengaduan" value="<?= $data['id_pengaduan']; ?>">
                                                    <input type="hidden" name="action" value="batalkan_validasi">
                                                    <p>Apakah Anda yakin ingin membatalkan validasi laporan ini?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button>
                                                <button type="submit" name="validasi_pengaduan" class="btn btn-danger">Ya, Batalkan</button>
                                            </div>
                                                </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- Modal Hapus Laporan (Reject) -->
                                <div class="modal fade" id="dismissReport<?= $data['id_pengaduan'] ?>" tabindex="-1" aria-labelledby="dismissReportLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="dismissReportLabel">Hapus Laporan Ditolak</h1>
                                                <button type="button" class="btn-close bg-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="edit_data.php" method="POST">
                                                    <input type="hidden" name="id_pengaduan" value="<?= $data['id_pengaduan']; ?>">
                                                    <input type="hidden" name="action" value="dismiss_rejected">
                                                    <p>Laporan ini akan dihapus dari dashboard Anda. Namun, laporan tetap akan tersimpan dan dapat dilihat oleh masyarakat dengan status "Ditolak".</p>
                                                    <div class="mb-3">
                                                        <label for="dismiss_reason<?= $data['id_pengaduan'] ?>" class="form-label">Alasan Penghapusan (akan dikirim ke masyarakat):</label>
                                                        <textarea name="dismiss_reason" id="dismiss_reason<?= $data['id_pengaduan'] ?>" class="form-control" rows="3" placeholder="Berikan alasan kenapa laporan ini ditolak..."></textarea>
                                                        <small class="text-muted">Opsional. Jika kosong, maka hanya menampilkan status penolakan tanpa catatan tambahan.</small>
                                                    </div>
                                                    <p><strong>Apakah Anda lanjutkan?</strong></p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" name="dismiss_report" class="btn btn-danger">Ya, Hapus dari Dashboard</button>
                                            </div>
                                                </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- Modal Close by Kepala Lingkungan -->
                                <div class="modal fade" id="closeKL<?= $data['id_pengaduan'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Konfirmasi Penutupan (Kepala Lingkungan)</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" enctype="multipart/form-data" action="edit_data.php">
                                                <div class="modal-body">
                                                    <input type="hidden" name="id_pengaduan" value="<?php echo $data['id_pengaduan']; ?>">
                                                    <div class="mb-3">
                                                        <label>Catatan Penutupan:</label>
                                                        <textarea name="catatan_close" class="form-control" rows="3" required></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label>Upload Bukti Penyelesaian (gambar):</label>
                                                        <input type="file" name="completion_proof" accept="image/*" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" name="kl_close" class="btn btn-success">Tutup Laporan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Tanggapan -->
                                <div class="modal fade" id="tanggapan<?= $data['id_pengaduan'] ?>" tabindex="-1" aria-labelledby="tanggapanLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="tanggapanLabel">Tanggapan Awal & Arahan</h1>
                                                <button type="button" class="btn-close bg-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="edit_data.php" method="POST">
                                                    <input type="hidden" name="id_pengaduan" value="<?= $data['id_pengaduan']; ?>">
                                                    <div class="mb-3">
                                                        <label>Tanggapan Awal</label>
                                                        <textarea name="tanggapan_awal" class="form-control" rows="3" required></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label>Arahan Tindak Lanjut Teknis</label>
                                                        <textarea name="arahan_teknis" class="form-control" rows="3" required></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <p>Apakah Anda yakin ingin menambah tanggapan ini?</p>
                                                    </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" name="tanggapan_kepala" class="btn btn-primary">Simpan</button>
                                            </div>
                                        </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Progress -->
                                <div class="modal fade" id="progress<?= $data['id_pengaduan'] ?>" tabindex="-1" aria-labelledby="progressLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="progressLabel">Update Perkembangan Penanganan</h1>
                                                <button type="button" class="btn-close bg-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <h5>Update Progress</h5>
                                                </div>
                                                <div class="tab-content">
                                                    <div class="tab-pane active" id="update_progress_<?= $data['id_pengaduan'] ?>">
                                                    <div class="modal fade" id="imgModal" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Preview Foto</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body text-center">
                                                                    <img src="" alt="Preview" id="imgModalSrc" style="max-width:100%; height:auto; border-radius:6px;" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <script>
                                                    document.addEventListener('DOMContentLoaded', function(){
                                                        var imgModal = document.getElementById('imgModal');
                                                        if (!imgModal) return;
                                                        imgModal.addEventListener('show.bs.modal', function (event) {
                                                            var trigger = event.relatedTarget;
                                                            var src = trigger ? trigger.getAttribute('data-src') : '';
                                                            var img = document.getElementById('imgModalSrc');
                                                            if (img && src) img.src = src;
                                                        });
                                                    });
                                                    </script>
                                                        <form action="edit_data.php" method="POST">
                                                            <input type="hidden" name="id_pengaduan" value="<?= $data['id_pengaduan']; ?>">
                                                            <div class="mb-3">
                                                                <label>Update Progress</label>
                                                                <textarea name="progress_update" class="form-control" rows="3" required></textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Status Progress</label>
                                                                <select name="progress_status" class="form-control" required>
                                                                    <option value="pending" <?php if($data['progress_status'] == 'pending') echo 'selected'; ?>>Pending</option>
                                                                    <option value="on_progress" <?php if($data['progress_status'] == 'on_progress') echo 'selected'; ?>>Sedang Dikerjakan</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <p>Apakah Anda yakin ingin update progress ini?</p>
                                                            </div>
                                                            <button type="submit" name="update_progress" class="btn btn-primary">Simpan</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <?php } } else {
                                if (!empty($total_assigned) && $total_assigned > 0) {
                                    echo "<tr><td colspan='7' class='text-center text-info' style='padding: 30px 20px;'><h5 class='mb-0'>Anda memiliki $total_assigned laporan terhubung, tapi tidak ada yang dapat ditampilkan (mungkin sudah selesai/ditutup).</h5></td></tr>";
                                } else {
                                    echo "<tr><td colspan='7' class='text-center text-muted' style='padding: 30px 20px;'><h5 class='mb-0'>Tidak ada pengaduan untuk divalidasi saat ini.</h5></td></tr>";
                                }
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
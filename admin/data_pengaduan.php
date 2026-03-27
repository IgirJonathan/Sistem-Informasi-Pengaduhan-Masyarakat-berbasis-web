<?php
include "../config/koneksi.php";
include "../config/functions.php";

// Check access for Kepala_Lingkungan
check_access(['kepala_lingkungan']);

// Get current kepala_lingkungan wilayah
$id_kepala = $_SESSION['id_petugas'];
$wilayah_query = mysqli_query($conn, "SELECT wilayah FROM petugas WHERE id_petugas = '$id_kepala'");
$wilayah_data = mysqli_fetch_assoc($wilayah_query);
$wilayah = normalize_wilayah($wilayah_data['wilayah'] ?? 'Tidak Ditetapkan');

// If KL clicked to open a report, mark it opened and prepare to show modal
$open_modal_id = null;
if (!empty($_GET['open'])) {
    $open_id = (int) $_GET['open'];
    mark_opened($conn, $open_id, $id_kepala);
    // instruct page to auto-open the modal for this report
    $open_modal_id = $open_id;
}

// Handle progress update
if (isset($_POST['update_progress'])) {
    $id_pengaduan = $_POST['id_pengaduan'];
    $progress_status = $_POST['progress_status'];
    mysqli_query($conn, "UPDATE pengaduan SET progress_status='$progress_status' WHERE id_pengaduan='$id_pengaduan'");
    echo "<script>alert('Progress berhasil diupdate'); document.location.href='index.php?page=pengaduan';</script>";
    exit;
}

// Handle close report
if (isset($_POST['close_report'])) {
    $id_pengaduan = $_POST['id_pengaduan'];
    mysqli_query($conn, "UPDATE pengaduan SET status='closed' WHERE id_pengaduan='$id_pengaduan'");
    echo "<script>alert('Laporan berhasil ditutup'); document.location.href='index.php?page=pengaduan';</script>";
    exit;
}

// Handle remove report from KL dashboard after Lurah reject (non-destructive for masyarakat)
if (isset($_POST['remove_from_kl'])) {
    $id_pengaduan = $_POST['id_pengaduan'];
    mysqli_query($conn, "UPDATE pengaduan SET id_kepala_lingkungan=NULL WHERE id_pengaduan='$id_pengaduan'");
    echo "<script>alert('Laporan dihapus dari dashboard Kepala Lingkungan'); document.location.href='index.php?page=pengaduan';</script>";
    exit;
}

// Query pengaduan untuk Kepala Lingkungan (tampilkan yang sudah ditugaskan atau pending di wilayahnya)
$ambil = mysqli_query($conn, "SELECT a.*, a.wilayah as pengaduan_wilayah, b.* FROM pengaduan a INNER JOIN masyarakat b ON a.nik = b.nik WHERE (a.id_kepala_lingkungan = '$id_kepala' OR (a.status = 'pending' AND a.wilayah = '$wilayah')) AND a.status != 'closed' ORDER BY a.id_pengaduan ASC");

// Debug info
$total_found = mysqli_num_rows($ambil);
if ($total_found == 0) {
    echo "<div class='alert alert-info'>Tidak ada laporan yang perlu divalidasi di wilayah <strong>$wilayah</strong>. Total laporan pending di dashboard: " . 
         mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM pengaduan WHERE id_kepala_lingkungan='$id_kepala' AND status='pending'"))['cnt'] . 
         "</div>";
}

// Debug info
$total_found = mysqli_num_rows($ambil);
if ($total_found == 0) {
    echo "<div class='alert alert-info'>Tidak ada laporan yang perlu divalidasi di wilayah <strong>$wilayah</strong>. Total laporan pending di dashboard: " . 
         mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM pengaduan WHERE id_kepala_lingkungan='$id_kepala' AND status='pending'"))['cnt'] . 
         "</div>";
}
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="card">
            <div class="card-header d-flex pb-0">
                <h6>PENGADUAN <?= htmlspecialchars(normalize_wilayah($wilayah)); ?> - VALIDASI AWAL</h6>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">No</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7 ps-2">Judul</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Pelapor</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Tanggal Masuk</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Status</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Progress</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while ($data = mysqli_fetch_array($ambil)) {
                                // Semua laporan di sini belum di-approve lurah
                                $is_approved = false;
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $no++; ?></td>
                                <td><?php echo $data['judul_pengaduan']; ?></td>
                                <td><?php echo $data['nama']; ?></td>
                                <td><?php echo format_datetime($data['tgl_pengaduan']); ?></td>
                                <td class="text-center">
                                    <?php if ($data['status'] == 'rejected' && $rejected_by_lurah) { ?>
                                        <span class="badge badge-sm bg-danger">Ditolak oleh Lurah</span>
                                    <?php } elseif ($data['status'] == 'rejected') { ?>
                                        <span class="badge badge-sm bg-danger">Ditolak oleh Kepala Lingkungan</span>
                                    <?php } elseif ($data['status'] == 'pending') { ?>
                                        <span class="badge badge-sm bg-warning">Pending</span>
                                    <?php } else { ?>
                                        <span class="badge badge-sm bg-secondary"><?php echo ucfirst($data['status']); ?></span>
                                    <?php } ?>
                                </td>
                                <td class="text-center">
                                    <?php echo ucfirst(str_replace('_', ' ', $data['progress_status'] ?? 'pending')); ?>
                                </td>
                                    <td class="text-center">
                                        <?php
                                            $rejected_by_lurah = false;
                                            if ($data['status'] == 'rejected' && !empty($data['rejected_by'])) {
                                                $rej_p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT level FROM petugas WHERE id_petugas='{$data['rejected_by']}' LIMIT 1"));
                                                if ($rej_p && $rej_p['level'] == 'lurah') {
                                                    $rejected_by_lurah = true;
                                                }
                                            }
                                            $has_kl_tanggapan = false;
                                            $kl_tang_q = mysqli_query($conn, "SELECT id_tanggapan FROM tanggapan WHERE id_pengaduan='{$data['id_pengaduan']}' AND id_petugas='$id_kepala' AND (tanggapan LIKE 'Validated by KL%' OR tanggapan LIKE 'Rejected by KL%')");
                                            if ($kl_tang_q && mysqli_num_rows($kl_tang_q) > 0) {
                                                $has_kl_tanggapan = true;
                                            }
                                        ?>
                                        <?php 
                                        $is_assigned_to_me = $data['id_kepala_lingkungan'] == $id_kepala;
                                        $is_unassigned_in_my_wilayah = ($data['id_kepala_lingkungan'] == null || $data['id_kepala_lingkungan'] === 'NULL' || empty($data['id_kepala_lingkungan'])) && $data['pengaduan_wilayah'] == $wilayah;
                                        if ($data['status'] == 'pending' && ($is_assigned_to_me || $is_unassigned_in_my_wilayah) && !$has_kl_tanggapan) { ?>
                                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#validate<?php echo $data['id_pengaduan']; ?>">Validasi</button>
                                        <?php } elseif ($data['id_kepala_lingkungan'] == $id_kepala && $has_kl_tanggapan && $data['is_approved'] == 0) { ?>
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelValidation<?php echo $data['id_pengaduan']; ?>">Batalkan Validasi</button>
                                        <?php } elseif ($rejected_by_lurah) { ?>
                                            <form method="POST" style="display:inline-block;">
                                                <input type="hidden" name="id_pengaduan" value="<?php echo $data['id_pengaduan']; ?>">
                                                <button type="submit" name="remove_from_kl" class="btn btn-danger btn-sm">Hapus dari KL</button>
                                            </form>
                                        <?php } elseif ($data['is_approved'] == 1) { ?>
                                            <!-- Progress update form -->
                                            <form method="POST" style="display:inline-block; margin-right:5px;">
                                                <input type="hidden" name="id_pengaduan" value="<?php echo $data['id_pengaduan']; ?>">
                                                <select name="progress_status" class="form-select form-select-sm" style="width:auto; display:inline-block;" onchange="this.form.submit()">
                                                    <option value="pending" <?php echo ($data['progress_status'] == 'pending' ? 'selected' : ''); ?>>Pending</option>
                                                    <option value="on_progress" <?php echo ($data['progress_status'] == 'on_progress' ? 'selected' : ''); ?>>On Progress</option>
                                                </select>
                                                <input type="hidden" name="update_progress" value="1">
                                            </form>
                                            <?php if ($data['progress_status'] == 'on_progress') { ?>
                                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#closeModal<?php echo $data['id_pengaduan']; ?>">Tutup</button>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <span class="badge badge-sm bg-info">Menunggu</span>
                                        <?php } ?>
                                    </td>
                            </tr>
                            <!-- Modal Validasi -->
                            <div class="modal fade" id="validate<?php echo $data['id_pengaduan']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Validasi Laporan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="id_pengaduan" value="<?php echo $data['id_pengaduan']; ?>">
                                                <div class="mb-3">
                                                    <label>Keputusan:</label><br>
                                                    <input type="radio" name="action" value="terima" required> Terima & Forward ke Lurah<br>
                                                    <input type="radio" name="action" value="tolak"> Tolak
                                                </div>
                                                <div class="mb-3">
                                                    <label>Catatan Validasi:</label>
                                                    <textarea name="catatan" class="form-control" rows="3"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" name="validate" class="btn btn-primary">Proses</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!-- Modal Batalkan Validasi -->
                            <div class="modal fade" id="cancelValidation<?php echo $data['id_pengaduan']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Batalkan Validasi</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="id_pengaduan" value="<?php echo $data['id_pengaduan']; ?>">
                                                <input type="hidden" name="action" value="batalkan_validasi">
                                                <p>Apakah Anda yakin ingin membatalkan validasi laporan ini?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button>
                                                <button type="submit" name="validate" class="btn btn-danger">Ya</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!-- Modal Tutup Laporan -->
                            <div class="modal fade" id="closeModal<?php echo $data['id_pengaduan']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Tutup Laporan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="id_pengaduan" value="<?php echo $data['id_pengaduan']; ?>">
                                                <input type="hidden" name="close_report" value="1">
                                                <p>Apakah Anda yakin ingin menutup laporan ini? Laporan akan dipindahkan ke arsip.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button>
                                                <button type="submit" class="btn btn-danger">Ya, Tutup</button>
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
<?php if (!empty($open_modal_id)) { ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modalId = 'validate' + <?php echo (int)$open_modal_id; ?>;
            var modalEl = document.getElementById(modalId);
            if (modalEl) {
                var modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        });
    </script>
<?php } ?>
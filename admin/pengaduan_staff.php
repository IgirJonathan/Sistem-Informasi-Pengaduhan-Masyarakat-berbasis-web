<?php
include "../config/koneksi.php";
include "../config/functions.php";

// Check access for petugas
check_access(['petugas']);

// Handle progress update
if (isset($_POST['update_progress'])) {
    $id_pengaduan = $_POST['id_pengaduan'];
    $progress = $_POST['progress'];

    $res = update_progress_staff($conn, $id_pengaduan, $progress, $_SESSION['id_petugas']);
    if (is_array($res) && !empty($res['success'])) {
        echo "<script>alert('Progress berhasil diupdate'); document.location.href='index.php?page=pengaduan_staff';</script>";
        exit;
    } else {
        $err = is_array($res) && !empty($res['error']) ? $res['error'] : 'Progress gagal diupdate';
        echo "<script>alert('$err'); document.location.href='index.php?page=pengaduan_staff';</script>";
        exit;
    }
}

// Handle validation by petugas (unlock close)
if (isset($_POST['validate_complete'])) {
    $id_pengaduan = $_POST['id_pengaduan'];
    $catatan = $_POST['catatan'] ?? '';
    // Validate and store uploaded proof image (server-side)
    $upload = validate_and_store_image($_FILES['proof'] ?? null);
    if (!$upload['success']) {
        echo "<script>alert('Upload error: {$upload['error']}'); document.location.href='index.php?page=pengaduan_staff';</script>";
        exit;
    }
    $proof_path = $upload['filename'] ?? null;

    // Append proof info to notes if exists
    if ($proof_path) {
        $catatan = ($catatan ? $catatan . ' | ' : '') . 'Bukti: ' . $proof_path;
    }

    $res = staff_validate_completion($conn, $id_pengaduan, $_SESSION['id_petugas'], $catatan);
    if (is_array($res) && !empty($res['success'])) {
        echo "<script>alert('Validasi selesai dikirim, Kepala Lingkungan dapat menutup laporan setelah review.'); document.location.href='index.php?page=pengaduan_staff';</script>";
        exit;
    } else {
        $err = is_array($res) && !empty($res['error']) ? $res['error'] : 'Gagal mengirim validasi';
        echo "<script>alert('$err'); document.location.href='index.php?page=pengaduan_staff';</script>";
        exit;
    }
}

// Query tugas yang didisposisikan ke staff ini
$ambil = mysqli_query($conn, "SELECT a.*, b.*, c.nama_petugas as nama_lurah FROM pengaduan a
                              INNER JOIN masyarakat b ON a.nik = b.nik
                              LEFT JOIN petugas c ON a.id_lurah = c.id_petugas
                              WHERE a.status='opened' AND a.id_staff='{$_SESSION['id_petugas']}' ORDER BY id_pengaduan ASC");
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="card">
            <div class="card-header d-flex pb-0">
                <h6>TUGAS DISPOSISI STAFF</h6>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">No</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7 ps-2">Judul</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Pelapor</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Disposisi dari</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Progress Log</th>
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
                                <td><?php echo $data['nama_lurah'] ?? 'Belum Ditugaskan'; ?></td>
                                <td><?php echo nl2br($data['progress_log'] ?? 'Belum ada progress'); ?></td>
                                <td class="text-center">
                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#progress<?php echo $data['id_pengaduan']; ?>">Update Progress</button>
                                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#validate<?php echo $data['id_pengaduan']; ?>">Validasi Selesai</button>
                                </td>
                            </tr>
                            <!-- Modal Progress -->
                            <div class="modal fade" id="progress<?php echo $data['id_pengaduan']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Update Progress</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="id_pengaduan" value="<?php echo $data['id_pengaduan']; ?>">
                                            <div class="mb-3">
                                                <label>Update Progress:</label>
                                                <textarea name="progress" class="form-control" rows="3" required placeholder="Deskripsikan progress pekerjaan..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="update_progress" class="btn btn-info">Update</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- Modal Validasi Petugas -->
                            <div class="modal fade" id="validate<?php echo $data['id_pengaduan']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Validasi Pekerjaan Selesai</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" enctype="multipart/form-data">
                                            <div class="modal-body">
                                                <input type="hidden" name="id_pengaduan" value="<?php echo $data['id_pengaduan']; ?>">
                                                <div class="mb-3">
                                                    <label>Catatan Validasi:</label>
                                                    <textarea name="catatan" class="form-control" rows="3" placeholder="Catatan validasi..."></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Upload Bukti Pekerjaan (opsional):</label>
                                                    <input type="file" name="proof" accept="image/*" class="form-control">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" name="validate_complete" class="btn btn-success">Kirim Validasi</button>
                                            </div>
                                        </form>
                                    </div>
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
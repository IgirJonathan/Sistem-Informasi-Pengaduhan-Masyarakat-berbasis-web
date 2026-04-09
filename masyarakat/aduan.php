<div class="container">
    <div class="row">
        <div class="col-md-12 mt-3">

            <div; class="card">
                <div class="card-header d-flex pb-0">
                    <h6>PENGADUAN</h6>                    <div class="ms-auto">
                        <form method="GET" class="d-inline">
                            <input type="hidden" name="page" value="aduan">
                            <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Urutkan Berdasarkan</option>
                                <option value="terbaru" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'terbaru') ? 'selected' : ''; ?>>Terbaru</option>
                                <option value="terlama" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'terlama') ? 'selected' : ''; ?>>Terlama</option>
                                <option value="selesai" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                                <option value="belum_selesai" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'belum_selesai') ? 'selected' : ''; ?>>Belum Selesai</option>
                            </select>
                        </form>
                    </div>                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table table-striped align-items-center mb-0">
                            <thead class="text-center">
                                <tr>
                                    <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">No</th>
                                    <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Judul</th>
                                    <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Isi</th>
                                    <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Foto</th>
                                    <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                <?php $no = 1; ?>
                                <?php
                                include "../config/koneksi.php";
                                include "../config/functions.php";
                                //menampung data nik dari session yang dibuat setelah login
                                $nik = $_SESSION['nik'];
                                $sort = isset($_GET['sort']) ? $_GET['sort'] : '';
                                $order_by = "ORDER BY id_pengaduan DESC"; // default terbaru
                                if ($sort == 'terlama') {
                                    $order_by = "ORDER BY id_pengaduan ASC";
                                } elseif ($sort == 'selesai') {
                                    $order_by = "ORDER BY FIELD(status, 'closed', 'rejected', 'opened', 'pending'), id_pengaduan DESC";
                                } elseif ($sort == 'belum_selesai') {
                                    $order_by = "ORDER BY FIELD(status, 'pending', 'opened', 'rejected', 'closed'), id_pengaduan DESC";
                                }
                                $query = mysqli_query($conn, "SELECT * FROM pengaduan WHERE nik = '$nik' AND is_deleted_by_masyarakat = 0 $order_by"); //menampilkan data pengaduan yang belum dihapus masyarakat
                                while ($data = mysqli_fetch_array($query)) { ?>
                                    <tr>
                                        <td class="align-middle text-center text-sm">
                                            <?= $no++; ?>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <?= $data['judul_pengaduan']; ?>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <?= $data['isi_laporan']; ?>
                                        </td>
                                        <td class="align-middle text-center">
                                            <img src="../database/img/<?= $data['foto']; ?>" alt="Ini Foto" width="70px">
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-secondary text-xs font-weight-bold">
                                                <?php 
                                                // Determine validation status based on tanggapan history
                                                $validated_by_kl = false;
                                                $rejected_by_kl = false;
                                                $validated_time = null;
                                                $rejected_time = null;

                                                $kl_check = mysqli_query($conn, "SELECT t.tgl_tanggapan FROM tanggapan t JOIN petugas p ON t.id_petugas = p.id_petugas WHERE t.id_pengaduan = '{$data['id_pengaduan']}' AND p.level = 'kepala_lingkungan' AND t.tanggapan LIKE 'Validated by KL%' ORDER BY t.tgl_tanggapan DESC LIMIT 1");
                                                if ($kl_check && $kl_row = mysqli_fetch_assoc($kl_check)) {
                                                    $validated_time = $kl_row['tgl_tanggapan'];
                                                }

                                                $reject_check = mysqli_query($conn, "SELECT t.tgl_tanggapan FROM tanggapan t JOIN petugas p ON t.id_petugas = p.id_petugas WHERE t.id_pengaduan = '{$data['id_pengaduan']}' AND p.level = 'kepala_lingkungan' AND t.tanggapan LIKE 'Rejected by KL%' ORDER BY t.tgl_tanggapan DESC LIMIT 1");
                                                if ($reject_check && $reject_row = mysqli_fetch_assoc($reject_check)) {
                                                    $rejected_time = $reject_row['tgl_tanggapan'];
                                                }

                                                $cancel_check = mysqli_query($conn, "SELECT t.tgl_tanggapan FROM tanggapan t JOIN petugas p ON t.id_petugas = p.id_petugas WHERE t.id_pengaduan = '{$data['id_pengaduan']}' AND p.level = 'kepala_lingkungan' AND (t.tanggapan LIKE 'Validasi dibatalkan%' OR t.tanggapan LIKE 'Validasi dibatalkan oleh Kepala Lingkungan%') ORDER BY t.tgl_tanggapan DESC LIMIT 1");
                                                $cancelled_by_kl = false;
                                                if ($cancel_check && $cancel_row = mysqli_fetch_assoc($cancel_check)) {
                                                    $cancelled_time = $cancel_row['tgl_tanggapan'];
                                                } else {
                                                    $cancelled_time = null;
                                                }

                                                if ($validated_time && (!$rejected_time || datetime_to_timestamp($validated_time) > datetime_to_timestamp($rejected_time))) {
                                                    $validated_by_kl = true;
                                                }
                                                if ($rejected_time && (!$validated_time || datetime_to_timestamp($rejected_time) > datetime_to_timestamp($validated_time))) {
                                                    $rejected_by_kl = true;
                                                }
                                                if ($cancelled_time && (!$validated_time || datetime_to_timestamp($cancelled_time) > datetime_to_timestamp($validated_time))) {
                                                    $cancelled_by_kl = true;
                                                    $validated_by_kl = false;
                                                    $rejected_by_kl = false;
                                                }

                                                $rejected_by_lurah = false;
                                                if ($data['status'] == 'rejected' || ($data['status']=='pending' && !$validated_by_kl && $rejected_by_kl)) {
                                                    $rejectsource = mysqli_query($conn, "SELECT level FROM petugas WHERE id_petugas='{$data['rejected_by']}' LIMIT 1");
                                                    if ($rejectsource && $rowx = mysqli_fetch_assoc($rejectsource)) {
                                                        if ($rowx['level'] === 'lurah') {
                                                            $rejected_by_lurah = true;
                                                        }
                                                    }
                                                }

                                                if ($data['status'] == "closed") {
                                                    echo "<span class ='badge bg-success text-light'>Selesai</span>";
                                                    echo "<br><a href='index.php?page=tanggapan&id_pengaduan=$data[id_pengaduan]'>lihat detail</a>";
                                                } elseif ($data['status'] == "opened") {
                                                    if ($data['is_approved'] == 1) {
                                                        if ($data['progress_status'] == 'on_progress') {
                                                            echo "<span style='color:#856404;font-weight:600;'>Sedang Ditangani</span>";
                                                        } elseif ($data['progress_status'] == 'pending') {
                                                            echo "<span style='color:#6c757d;font-weight:600;'>Sedang Ditangani</span>";
                                                        } else {
                                                            echo "<span style='color:#0d6efd;font-weight:600;'>Disetujui Lurah</span>";
                                                        }
                                                    } elseif ($validated_by_kl) {
                                                        echo "<span style='color:#0dcaf0;font-weight:600;'>Divalidasi Kepala Lingkungan</span>";
                                                    } else {
                                                        echo "<span class ='badge bg-secondary text-light'>Menunggu Validasi</span>";
                                                    }
                                                    echo "<br><a href='index.php?page=tanggapan&id_pengaduan=$data[id_pengaduan]'>lihat detail</a>";
                                                } elseif ($data['status'] == "rejected") {
                                                    if ($rejected_by_lurah) {
                                                        echo "<span class ='badge bg-danger text-light'>Ditolak oleh Lurah</span>";
                                                        echo "<br><small class='text-muted'>Laporan ditolak oleh Lurah</small>";
                                                    } else {
                                                        echo "<span class ='badge bg-danger text-light'>Ditolak oleh Kepala Lingkungan</span>";
                                                        echo "<br><small class='text-muted'>Laporan ditolak validasi oleh Kepala Lingkungan</small>";
                                                    }
                                                    echo "<br><a href='index.php?page=tanggapan&id_pengaduan=$data[id_pengaduan]'>lihat detail</a>";
                                                } elseif ($data['status'] == "pending") {
                                                    if ($validated_by_kl) {
                                                        echo "<span class ='badge bg-info text-dark'>Divalidasi Kepala Lingkungan</span>";
                                                    } elseif ($rejected_by_lurah) {
                                                        echo "<span class ='badge bg-danger text-light'>Ditolak oleh Lurah</span>";
                                                    } elseif ($rejected_by_kl) {
                                                        echo "<span class ='badge bg-danger text-light'>Ditolak oleh Kepala Lingkungan</span>";
                                                    } else {
                                                        echo "<span class ='badge bg-secondary text-light'>Menunggu Validasi</span>";
                                                    }
                                                    echo "<br><a href='index.php?page=tanggapan&id_pengaduan=$data[id_pengaduan]'>lihat detail</a>";
                                                } else {
                                                    echo "<span class ='badge bg-danger text-light'>Status Tidak Dikenal</span>";
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <!-- HAPUS -->
                                            <?php
                                            // Kondisi tombol hapus aktif:
                                            // - Bisa dihapus jika belum divalidasi oleh Kepala Lingkungan
                                            // - atau jika sudah ditolak oleh Lurah
                                            $can_delete = !$validated_by_kl || $rejected_by_lurah;
                                            ?>
                                            <?php if ($can_delete) : ?>
                                                <a href="#" data-bs-toggle="modal" class="btn btn-danger" data-bs-target="#hapus<?= $data['id_pengaduan'] ?>" style="text-decoration:none; color:white;">HAPUS</a>

                                                <!-- modal HAPUS -->
                                                <div class="modal fade" id="hapus<?= $data['id_pengaduan'] ?>" tabindex="-1" aria-labelledby="hapusLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h1 class="modal-title fs-5" id="hapusLabel">Hapus Data</h1>
                                                                <button type="button" class="btn-close bg-dark " data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form action="edit_data.php" method="POST">
                                                                    <input type="hidden" name="id_pengaduan" class="form-control" value="<?= $data['id_pengaduan']; ?>">
                                                                    <p>Yakin mau dihapus data <br> <?= $data['judul_pengaduan']; ?>?</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="submit" name="hapus_pengaduan" value="hapus_pengaduan" class="btn btn-danger">Hapus</button>
                                                            </div>
                                                            </form>

                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- /modal-HAPUS -->
                                            <?php endif; ?>
                                            <!-- /HAPUS -->
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
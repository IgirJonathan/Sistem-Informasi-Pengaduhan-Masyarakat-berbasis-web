<?php

include "../config/koneksi.php";
include "../config/functions.php";

if (!empty($_GET['id_pengaduan'])) {
    $id = $_GET['id_pengaduan'];
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
                        <h5>DETAIL LAPORAN: <?= $data['judul_pengaduan']; ?></h5>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs mb-4">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#detail">Detail Laporan</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tracking">Tracking Progress</a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="detail">
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
                                            // Determine validation status based on tanggapan history (same logic as aduan.php)
                                            $validated_by_kl_status = false;
                                            $rejected_by_kl_status = false;
                                            $cancelled_by_kl_status = false;
                                            $validated_time_status = null;
                                            $rejected_time_status = null;
                                            $cancelled_time_status = null;

                                            $kl_check_status = mysqli_query($conn, "SELECT t.tgl_tanggapan FROM tanggapan t JOIN petugas p ON t.id_petugas = p.id_petugas WHERE t.id_pengaduan = '$id' AND p.level = 'kepala_lingkungan' AND t.tanggapan LIKE 'Validated by KL%' ORDER BY t.tgl_tanggapan DESC LIMIT 1");
                                            if ($kl_check_status && $kl_row_status = mysqli_fetch_assoc($kl_check_status)) {
                                                $validated_time_status = $kl_row_status['tgl_tanggapan'];
                                            }

                                            $reject_check_status = mysqli_query($conn, "SELECT t.tgl_tanggapan FROM tanggapan t JOIN petugas p ON t.id_petugas = p.id_petugas WHERE t.id_pengaduan = '$id' AND p.level = 'kepala_lingkungan' AND t.tanggapan LIKE 'Rejected by KL%' ORDER BY t.tgl_tanggapan DESC LIMIT 1");
                                            if ($reject_check_status && $reject_row_status = mysqli_fetch_assoc($reject_check_status)) {
                                                $rejected_time_status = $reject_row_status['tgl_tanggapan'];
                                            }

                                            $cancel_check_status = mysqli_query($conn, "SELECT t.tgl_tanggapan FROM tanggapan t JOIN petugas p ON t.id_petugas = p.id_petugas WHERE t.id_pengaduan = '$id' AND p.level = 'kepala_lingkungan' AND t.tanggapan = 'Validasi dibatalkan oleh Kepala Lingkungan' ORDER BY t.tgl_tanggapan DESC LIMIT 1");
                                            if ($cancel_check_status && $cancel_row_status = mysqli_fetch_assoc($cancel_check_status)) {
                                                $cancelled_time_status = $cancel_row_status['tgl_tanggapan'];
                                            }

                                            // Determine the most recent action
                                            $latest_action_time = null;
                                            $latest_action = 'none';

                                            if ($validated_time_status) {
                                                $latest_action_time = $validated_time_status;
                                                $latest_action = 'validated';
                                            }
                                            if ($rejected_time_status && (!$latest_action_time || datetime_to_timestamp($rejected_time_status) > datetime_to_timestamp($latest_action_time))) {
                                                $latest_action_time = $rejected_time_status;
                                                $latest_action = 'rejected';
                                            }
                                            if ($cancelled_time_status && (!$latest_action_time || datetime_to_timestamp($cancelled_time_status) > datetime_to_timestamp($latest_action_time))) {
                                                $latest_action_time = $cancelled_time_status;
                                                $latest_action = 'cancelled';
                                            }

                                            if ($latest_action == 'validated') {
                                                $validated_by_kl_status = true;
                                            } elseif ($latest_action == 'rejected') {
                                                $rejected_by_kl_status = true;
                                            } elseif ($latest_action == 'cancelled') {
                                                $cancelled_by_kl_status = true;
                                            }

                                            $rejected_by_lurah_status = false;
                                            if (!empty($data['rejected_by'])) {
                                                $rej_check = mysqli_query($conn, "SELECT level FROM petugas WHERE id_petugas='{$data['rejected_by']}' LIMIT 1");
                                                if ($rej_check) {
                                                    $rej_row = mysqli_fetch_assoc($rej_check);
                                                    if ($rej_row && $rej_row['level'] == 'lurah') {
                                                        $rejected_by_lurah_status = true;
                                                    }
                                                }
                                            }

                                            if ($data['status'] == 'pending') {
                                                if (!empty($data['is_approved']) && $data['is_approved'] == 1) {
                                                    echo '<span class="badge bg-success">Disetujui Lurah</span>';
                                                } elseif ($rejected_by_lurah_status) {
                                                    echo '<span class="badge bg-danger">Ditolak oleh Lurah</span>';
                                                } elseif ($cancelled_by_kl_status) {
                                                    echo '<span class="badge bg-secondary">Menunggu Validasi</span>';
                                                } elseif ($validated_by_kl_status) {
                                                    echo '<span class="badge bg-info">Divalidasi Kepala Lingkungan</span>';
                                                } elseif ($rejected_by_kl_status) {
                                                    echo '<span class="badge bg-danger">Ditolak oleh Kepala Lingkungan</span>';
                                                } else {
                                                    echo '<span class="badge bg-secondary">Menunggu Validasi</span>';
                                                }
                                            } elseif ($data['status'] == 'rejected') {
                                                if ($rejected_by_lurah_status) {
                                                    echo '<span class="badge bg-danger">Ditolak oleh Lurah</span>';
                                                } else {
                                                    echo '<span class="badge bg-danger">Ditolak oleh Kepala Lingkungan</span>';
                                                }
                                            } elseif ($data['status'] == 'opened') {
                                                if (!empty($data['is_approved']) && $data['is_approved'] == 1) {
                                                    if (isset($data['progress_status']) && $data['progress_status'] == 'on_progress') {
                                                        echo '<span class="badge bg-warning">Sedang Ditangani</span>';
                                                    } elseif (isset($data['progress_status']) && $data['progress_status'] == 'pending') {
                                                        echo '<span class="badge bg-secondary">Sedang Ditangani</span>';
                                                    } else {
                                                        echo '<span class="badge bg-primary">Disetujui Lurah</span>';
                                                    }
                                                } elseif ($cancelled_by_kl_status) {
                                                    echo '<span class="badge bg-secondary">Menunggu Validasi</span>';
                                                } elseif ($validated_by_kl_status) {
                                                    echo '<span class="badge bg-info">Divalidasi Kepala Lingkungan</span>';
                                                } else {
                                                    echo '<span class="badge bg-secondary">Menunggu Validasi</span>';
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
                                
                                <?php if ($data['status'] == 'rejected') { 
                                    // Get public rejection reason from tanggapan
                                    $public_rejection_reason = '';
                                    $reject_tanggapan_check = mysqli_query($conn, "SELECT t.tanggapan FROM tanggapan t JOIN petugas p ON t.id_petugas = p.id_petugas WHERE t.id_pengaduan = '$id' AND (p.level = 'kepala_lingkungan' OR p.level = 'lurah') AND (t.tanggapan LIKE 'Rejected by KL%' OR t.tanggapan LIKE 'Ditolak oleh Lurah%' OR t.tanggapan LIKE 'Ditolak oleh Kepala Lingkungan%') ORDER BY t.tgl_tanggapan DESC LIMIT 1");
                                    if ($reject_tanggapan_check && $reject_row = mysqli_fetch_assoc($reject_tanggapan_check)) {
                                        if (strpos($reject_row['tanggapan'], ':') !== false) {
                                            $public_rejection_reason = trim(substr($reject_row['tanggapan'], strpos($reject_row['tanggapan'], ':') + 1));
                                        }
                                    }
                                    ?>
                                    <div class="alert alert-danger">
                                        <strong>Laporan Ditolak</strong><br>
                                        <?php if (!empty($data['rejected_by_name'])) { ?>
                                            <strong>Ditolak oleh:</strong> <?= $data['rejected_by_name']; ?><br>
                                        <?php } else { ?>
                                            <strong>Ditolak oleh:</strong> Kepala Lingkungan<br>
                                        <?php } ?>
                                        <?php if (!empty($public_rejection_reason)) { ?>
                                            <br><strong>Alasan:</strong> <?= nl2br($public_rejection_reason); ?><br>
                                        <?php } ?>
                                        <?php if (!empty($data['rejected_at'])) { ?>
                                            <br><small>Ditolak pada: <?= format_datetime($data['rejected_at']); ?></small>
                                        <?php } ?>
                                    </div>
                                <?php } elseif ($data['status'] == 'pending') {
                                    if ($cancelled_by_kl_status) { ?>
                                        <div class="alert alert-secondary">
                                            <strong>Menunggu Validasi</strong><br>
                                            Validasi sebelumnya dibatalkan oleh Kepala Lingkungan. Laporan Anda menunggu validasi ulang.
                                        </div>
                                    <?php } elseif ($validated_by_kl_status) { ?>
                                        <div class="alert alert-info">
                                            <strong>Laporan Divalidasi Kepala Lingkungan</strong><br>
                                            Laporan Anda sudah divalidasi oleh Kepala Lingkungan dan menunggu approval Lurah.
                                        </div>
                                    <?php } elseif ($rejected_by_kl_status) { ?>
                                        <div class="alert alert-danger">
                                            <strong>Laporan Ditolak Kepala Lingkungan</strong><br>
                                            Laporan Anda ditolak oleh Kepala Lingkungan.
                                        </div>
                                    <?php } else { ?>
                                        <div class="alert alert-secondary">
                                            <strong>Menunggu Validasi</strong><br>
                                            Laporan Anda belum divalidasi oleh Kepala Lingkungan.
                                        </div>
                                    <?php }
                                } elseif ($data['status'] == 'opened') {
                                    if (!empty($data['is_approved']) && $data['is_approved'] == 1) {
                                        if (isset($data['progress_status']) && $data['progress_status'] == 'on_progress') { ?>
                                            <div class="alert alert-warning">
                                                <strong>Laporan Sedang Dikerjakan</strong><br>
                                                Laporan Anda sedang dalam proses penanganan berdasarkan arahan Kepala Lingkungan.
                                            </div>
                                        <?php } elseif (isset($data['progress_status']) && $data['progress_status'] == 'pending') { ?>
                                            <div class="alert alert-secondary">
                                                <strong>Kepala Lingkungan menunda pekerjaan sementara</strong><br>
                                                Laporan Anda tetap berada di progress sampai Kepala Lingkungan update status berikutnya.
                                            </div>
                                        <?php } else { ?>
                                            <div class="alert alert-primary">
                                                <strong>Laporan Disetujui Lurah</strong><br>
                                                Laporan Anda sudah disetujui, menunggu update pekerjaan.
                                            </div>
                                        <?php }
                                    } elseif (!empty($data['is_workable']) && $data['is_workable'] == 0) { ?>
                                        <div class="alert alert-info">
                                            <strong>Laporan Diterima - Tidak Dapat Dikerjakan</strong><br>
                                            Laporan Anda telah diterima namun tidak bisa ditangani kelurahan.
                                            <?php if ($data['non_workable_reason']) { ?>
                                                <br><br><strong>Alasan:</strong> <?= nl2br($data['non_workable_reason']); ?>
                                            <?php } ?>
                                        </div>
                                    <?php } else { ?>
                                        <div class="alert alert-secondary">
                                            <strong>Laporan Menunggu Penanganan</strong><br>
                                            Laporan Anda sedang menunggu tindakan lebih lanjut.
                                        </div>
                                    <?php }
                                } elseif ($data['status'] == 'closed') { ?>
                                    <div class="alert alert-success">
                                        <strong>Laporan Telah Selesai</strong><br>
                                        Laporan Anda telah berhasil ditangani.
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                            </div>
                            <div class="tab-pane" id="tracking">
                                <!-- Tracking Progress Real-Time -->
                                <div class="card">
                                    <div class="card-header">
                                        <h6>Tracking Progress Pengaduan</h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="progress-container">
                                            <?php
                                            // Check if validated by KL (based on latest tanggapan)
                                            // Validasi is ACTIVE unless cancelled
                                            $validated_by_kl = false;
                                            $validated_time = null;
                                            $cancelled_by_kl = false;
                                            $cancelled_time = null;

                                            $kl_check = mysqli_query($conn, "SELECT t.tgl_tanggapan FROM tanggapan t JOIN petugas p ON t.id_petugas = p.id_petugas WHERE t.id_pengaduan = '$id' AND p.level = 'kepala_lingkungan' AND t.tanggapan LIKE 'Validated by KL%' ORDER BY t.tgl_tanggapan DESC LIMIT 1");
                                            if ($kl_check && $kl_row = mysqli_fetch_assoc($kl_check)) {
                                                $validated_time = $kl_row['tgl_tanggapan'];
                                            }

                                            $cancel_check = mysqli_query($conn, "SELECT t.tgl_tanggapan FROM tanggapan t JOIN petugas p ON t.id_petugas = p.id_petugas WHERE t.id_pengaduan = '$id' AND p.level = 'kepala_lingkungan' AND (t.tanggapan LIKE 'Validasi dibatalkan%' OR t.tanggapan LIKE 'Validasi dibatalkan oleh Kepala Lingkungan%') ORDER BY t.tgl_tanggapan DESC LIMIT 1");
                                            if ($cancel_check && $cancel_row = mysqli_fetch_assoc($cancel_check)) {
                                                $cancelled_time = $cancel_row['tgl_tanggapan'];
                                            }

                                            // Validation is active only if not cancelled or if validation is more recent than cancellation
                                            if ($validated_time && (!$cancelled_time || datetime_to_timestamp($validated_time) > datetime_to_timestamp($cancelled_time))) {
                                                $validated_by_kl = true;
                                            }
                                            if ($cancelled_time && (!$validated_time || datetime_to_timestamp($cancelled_time) > datetime_to_timestamp($validated_time))) {
                                                $cancelled_by_kl = true;
                                            }
                                            
                                            // Check if approved by Lurah
                                            $approved_by_lurah = (!empty($data['is_approved']) && $data['is_approved'] == 1);
                                            
                                            // Check if progress updates exist (KL progress updates) and build timeline points
                                            $has_progress_pending = false;
                                            $has_progress_on_progress = false;
                                            $ever_progress_started = false;
                                            $latest_progress_status = null;

                                            $progress_rows = mysqli_query($conn, "SELECT t.tgl_tanggapan, t.tanggapan FROM tanggapan t JOIN petugas p ON t.id_petugas = p.id_petugas WHERE t.id_pengaduan = '$id' AND p.level = 'kepala_lingkungan' AND t.tanggapan LIKE 'KL update progress%' ORDER BY t.tgl_tanggapan ASC");
                                            if ($progress_rows) {
                                                while ($pr = mysqli_fetch_assoc($progress_rows)) {
                                                    $ever_progress_started = true;
                                                    if (strpos($pr['tanggapan'], 'to pending') !== false) {
                                                        $has_progress_pending = true;
                                                        $latest_progress_status = 'pending';
                                                    } elseif (strpos($pr['tanggapan'], 'to on_progress') !== false) {
                                                        $has_progress_on_progress = true;
                                                        $latest_progress_status = 'on_progress';
                                                    } elseif (strpos($pr['tanggapan'], 'to completed') !== false) {
                                                        $latest_progress_status = 'completed';
                                                    }
                                                }
                                            }

                                            // Tentukan status penolakan/batal validasi KL/Lurah lebih awal
                                            $cancelled_by_kl = false;
                                            $rejected_by_kl = false;
                                            $rejected_by_lurah = false;

                                            $cancel_check = mysqli_query($conn, "SELECT t.tgl_tanggapan FROM tanggapan t JOIN petugas p ON t.id_petugas = p.id_petugas WHERE t.id_pengaduan = '$id' AND p.level = 'kepala_lingkungan' AND t.tanggapan = 'Validasi dibatalkan oleh Kepala Lingkungan'");
                                            if ($cancel_check && mysqli_num_rows($cancel_check) > 0) {
                                                $cancelled_by_kl = true;
                                            }

                                            if (!empty($data['status']) && $data['status'] == 'rejected') {
                                                if ($rejected_by_lurah_status) {
                                                    $rejected_by_lurah = true;
                                                } else {
                                                    $rejected_by_kl = true;
                                                }
                                            }

                                            // Determine current step index
                                            $steps = [
                                                ['label' => 'Diajukan', 'status' => 'submitted', 'active' => true, 'class' => 'bg-secondary'],
                                                ['label' => 'Divalidasi Kepala Lingkungan', 'status' => 'validated', 'active' => $validated_by_kl || $rejected_by_kl || $cancelled_by_kl, 'class' => $rejected_by_kl ? 'bg-danger' : ($cancelled_by_kl ? 'bg-secondary' : 'bg-info')],
                                                ['label' => 'Disetujui Lurah', 'status' => 'approved', 'active' => ($approved_by_lurah && $data['status'] != 'rejected') || $rejected_by_lurah, 'class' => $rejected_by_lurah ? 'bg-danger' : 'bg-primary'],
                                                ['label' => 'Sedang Dikerjakan', 'status' => 'in_progress', 'active' => ($approved_by_lurah && ($ever_progress_started || $has_progress_pending || $has_progress_on_progress)) && $data['status'] != 'rejected', 'class' => 'bg-warning'],
                                                ['label' => 'Selesai', 'status' => 'closed', 'active' => $data['status'] == 'closed', 'class' => 'bg-success']
                                            ];
                                            $current_step = 0;
                                            foreach ($steps as $index => $step) {
                                                if ($step['active']) $current_step = $index + 1;
                                            }
                                            // Handling special cases: rejected or cancelled
                                            if ($rejected_by_kl) {
                                                $current_step = 2;
                                            } elseif ($rejected_by_lurah) {
                                                $current_step = 3;
                                            } elseif ($cancelled_by_kl) {
                                                $current_step = 2;
                                            }
                                            $progress_width = ($current_step / count($steps)) * 100;

                                            // Determine progress bar color based on most recent operational status
                                            if ($data['status'] == 'closed') {
                                                $progress_color = 'bg-success';
                                            } elseif ($data['status'] == 'rejected') {
                                                $progress_color = 'bg-danger';
                                            } elseif ($rejected_by_kl || $rejected_by_lurah) {
                                                $progress_color = 'bg-danger';
                                            } elseif ($cancelled_by_kl) {
                                                $progress_color = 'bg-secondary';
                                            } elseif ($approved_by_lurah) {
                                                if ($latest_progress_status === 'on_progress') {
                                                    $progress_color = 'bg-warning'; // oranye
                                                } elseif ($latest_progress_status === 'pending') {
                                                    $progress_color = 'bg-secondary'; // abu-abu
                                                } elseif ($latest_progress_status === 'completed') {
                                                    $progress_color = 'bg-success';
                                                } else {
                                                    $progress_color = 'bg-primary'; // biru tua
                                                }
                                            } elseif ($validated_by_kl) {
                                                $progress_color = 'bg-info'; // biru muda
                                            } else {
                                                $progress_color = 'bg-secondary'; // abu-abu
                                            }
                                            ?>
                                            <div class="progress mb-4" style="height: 34px;">
                                                <div class="progress-bar <?php echo $progress_color; ?>" role="progressbar" style="width: <?php echo $progress_width; ?>%; font-size: 1rem;" aria-valuenow="<?php echo $progress_width; ?>" aria-valuemin="0" aria-valuemax="100">
                                                    Step <?php echo $current_step; ?> dari <?php echo count($steps); ?>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <?php foreach ($steps as $index => $step) { ?>
                                                    <div class="col text-center">
                                                        <?php
                                                        $circleClass = 'step-circle';
                                                        // Hanya highlight langkah saat ini saja
                                                        if ($index + 1 == $current_step) {
                                                            $circleClass .= ' active';
                                                        } else {
                                                            $circleClass .= ' inactive';
                                                        }
                                                        ?>
                                                        <div class="<?php echo $circleClass; ?>">
                                                            <?php echo $index + 1; ?>
                                                        </div>
                                                        <small><?php echo $step['label']; ?></small>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <h6>Status Terakhir:</h6>
                                            <p id="current-status">
                                                <?php
                                                // Query untuk pesan dari KL (public)
                                                $kl_message = '';
                                                // Check if KL posted a progress message but ensure we don't expose internal KL notes
                                                // when a petugas-targeted validation request exists (created by KL -> petugas forwarding).
                                                $petugas_req_check = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM tanggapan t JOIN petugas p ON t.id_petugas = p.id_petugas WHERE t.id_pengaduan = '$id' AND p.level = 'kepala_lingkungan' AND t.tanggapan LIKE 'KL requests validation%'");
                                                $has_petugas_req = false;
                                                if ($petugas_req_check) {
                                                    $has_petugas_req = (mysqli_fetch_assoc($petugas_req_check)['cnt'] > 0);
                                                }

                                                if (!$has_petugas_req) {
                                                    $kl_tanggapan = mysqli_query($conn, "
                                                        SELECT 
                                                            CASE 
                                                                WHEN t.tanggapan LIKE 'Approved%' THEN t.tanggapan
                                                                WHEN t.tanggapan LIKE 'KL update progress%' THEN 
                                                                    CASE 
                                                                        WHEN LOCATE(':', t.tanggapan) > 0 THEN SUBSTRING_INDEX(t.tanggapan, ':', 1)
                                                                        ELSE t.tanggapan
                                                                    END
                                                                ELSE NULL
                                                            END as public_message
                                                        FROM tanggapan t 
                                                        JOIN petugas p ON t.id_petugas = p.id_petugas 
                                                        WHERE t.id_pengaduan = '$id' 
                                                        AND p.level = 'kepala_lingkungan' 
                                                        AND (
                                                            t.tanggapan LIKE 'Approved%' OR 
                                                            t.tanggapan LIKE 'KL update progress%'
                                                        )
                                                        ORDER BY t.tgl_tanggapan DESC LIMIT 1
                                                    ");
                                                    if ($kl_tanggapan && mysqli_num_rows($kl_tanggapan) > 0) {
                                                        $msg = mysqli_fetch_assoc($kl_tanggapan);
                                                        if ($msg['public_message']) {
                                                            $kl_message = $msg['public_message'];
                                                        }
                                                    }
                                                } else {
                                                    // If there is a petugas-targeted request, treat KL internal note as internal (do not expose)
                                                    $kl_message = '';
                                                }

                                                if ($data['status'] == 'pending') {
                                                    if ($approved_by_lurah) {
                                                        echo 'Status: Disetujui Lurah';
                                                    } elseif ($cancelled_by_kl) {
                                                        echo 'Status: Menunggu Validasi';
                                                    } elseif ($validated_by_kl) {
                                                        echo 'Status: Divalidasi Kepala Lingkungan';
                                                    } else {
                                                        echo 'Status: Menunggu Validasi';
                                                    }
                                                } elseif ($data['status'] == 'opened') {
                                                    if ($has_progress_on_progress) {
                                                        echo 'Status: Sedang Dikerjakan';
                                                    } elseif ($has_progress_pending) {
                                                        echo 'Status: Kepala Lingkungan menunda pekerjaan sementara';
                                                    } elseif ($approved_by_lurah) {
                                                        echo 'Status: Disetujui Lurah';
                                                    } elseif ($validated_by_kl) {
                                                        echo 'Status: Divalidasi Kepala Lingkungan';
                                                    } else {
                                                        echo 'Status: Menunggu Validasi';
                                                    }
                                                } elseif ($data['status'] == 'closed') {
                                                    echo 'Status: Selesai Ditangani';
                                                } elseif ($data['status'] == 'rejected') {
                                                    $reject_role = 'Kepala Lingkungan';
                                                    if (!empty($data['rejected_by'])) {
                                                        $reject_by_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT level FROM petugas WHERE id_petugas='{$data['rejected_by']}' LIMIT 1"));
                                                        if ($reject_by_info && $reject_by_info['level'] == 'lurah') {
                                                            $reject_role = 'Lurah';
                                                        }
                                                    }
                                                    echo 'Status: Ditolak oleh ' . $reject_role;
                                                }
                                                ?>
                                            </p>
                                        </div>
                                        <div class="mt-4">
                                            <h6>Timeline Progress</h6>
                                            <div id="progress-timeline">
                                                <?php
                                                // Build timeline by combining public tanggapan with visible alert-box messages
                                                $updates = [];
                                                $res = mysqli_query($conn, "SELECT t.tgl_tanggapan, t.tanggapan, IFNULL(p.nama_petugas, 'System') as nama_petugas, IFNULL(p.level, 'system') as level FROM tanggapan t LEFT JOIN petugas p ON t.id_petugas = p.id_petugas WHERE t.id_pengaduan = '$id'");
                                                if ($res) {
                                                    while ($r = mysqli_fetch_assoc($res)) {
                                                        $updates[] = $r;
                                                    }
                                                }
                                                // If KL forwarded a petugas-targeted validation request, hide KL internal tanggapan from public timeline
                                                if (!empty($has_petugas_req) && $has_petugas_req) {
                                                    $updates = array_values(array_filter($updates, function($u) {
                                                        if (empty($u['tanggapan'])) return true;
                                                        return !(strpos($u['tanggapan'], 'KL_INTERNAL:') === 0) && !(strpos($u['tanggapan'], 'KL requests validation') === 0);
                                                    }));
                                                }

                                                // System/alert messages to show in timeline (with timestamps)
                                                $system_events = [];
                                                // Submitted (tetap pakai tgl_pengaduan bila ada, tidak di-overwrite oleh validasi/reject)
                                                $submitted_time = null;
                                                if (!empty($data['tgl_pengaduan'])) {
                                                    $submitted_time = $data['tgl_pengaduan'];
                                                } else {
                                                    $first_tanggapan_time = mysqli_query($conn, "SELECT tgl_tanggapan FROM tanggapan WHERE id_pengaduan='$id' ORDER BY tgl_tanggapan ASC LIMIT 1");
                                                    if ($first_tanggapan_time && mysqli_num_rows($first_tanggapan_time) > 0) {
                                                        $submitted_time = mysqli_fetch_assoc($first_tanggapan_time)['tgl_tanggapan'];
                                                    }
                                                }
                                                if (!empty($submitted_time)) {
                                                    // Pastikan dalam format datetime (jika date-only, tambahkan 00:00)
                                                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $submitted_time)) {
                                                        $submitted_time .= ' 00:00:00';
                                                    }
                                                    $system_events[] = ['tgl_tanggapan' => $submitted_time, 'tanggapan' => 'Pengaduan diajukan', 'nama_petugas' => $data['nama_masyarakat'] ?? 'Pengirim', 'level' => 'masyarakat'];
                                                }
                                                // Validated by KL -> show if exists and not cancelled
                                                if ($validated_by_kl && !$cancelled_by_kl) {
                                                    $kl_tanggapan_check = mysqli_query($conn, "SELECT t.tgl_tanggapan, t.tanggapan FROM tanggapan t JOIN petugas p ON t.id_petugas = p.id_petugas WHERE t.id_pengaduan = '$id' AND p.level = 'kepala_lingkungan' AND t.tanggapan LIKE 'Validated by KL%' ORDER BY t.tgl_tanggapan DESC LIMIT 1");
                                                    if ($kl_tanggapan_check && $kl_row = mysqli_fetch_assoc($kl_tanggapan_check)) {
                                                        $msg = 'Laporan Divalidasi oleh Kepala Lingkungan';
                                                        if (strpos($kl_row['tanggapan'], ':') !== false) {
                                                            $notes = trim(substr($kl_row['tanggapan'], strpos($kl_row['tanggapan'], ':') + 1));
                                                            if (!empty($notes)) $msg .= ': ' . $notes;
                                                        }
                                                        $tgl_validated = $kl_row['tgl_tanggapan'] ?: ($data['last_progress_update'] ?? $data['tgl_pengaduan']);
                                                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl_validated)) {
                                                            $tgl_validated .= ' 00:00:00';
                                                        }
                                                        $system_events[] = ['tgl_tanggapan' => $tgl_validated, 'tanggapan' => $msg, 'nama_petugas' => $data['nama_kepala_lingkungan'] ?? 'Kepala Lingkungan', 'level' => 'kepala_lingkungan'];
                                                    }
                                                }
                                                // Validation cancelled by KL -> show if exists
                                                if ($cancelled_by_kl) {
                                                    $cancel_tanggapan_check = mysqli_query($conn, "SELECT t.tgl_tanggapan, t.tanggapan FROM tanggapan t JOIN petugas p ON t.id_petugas = p.id_petugas WHERE t.id_pengaduan = '$id' AND p.level = 'kepala_lingkungan' AND t.tanggapan = 'Validasi dibatalkan oleh Kepala Lingkungan' ORDER BY t.tgl_tanggapan DESC LIMIT 1");
                                                    if ($cancel_tanggapan_check && $cancel_row = mysqli_fetch_assoc($cancel_tanggapan_check)) {
                                                        $msg = 'Validasi Dibatalkan oleh Kepala Lingkungan';
                                                        if (strpos($cancel_row['tanggapan'], ':') !== false) {
                                                            $notes = trim(substr($cancel_row['tanggapan'], strpos($cancel_row['tanggapan'], ':') + 1));
                                                            if (!empty($notes)) $msg .= ': ' . $notes;
                                                        }
                                                        $tgl_cancelled = $cancel_row['tgl_tanggapan'] ?: ($data['last_progress_update'] ?? $data['tgl_pengaduan']);
                                                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl_cancelled)) {
                                                            $tgl_cancelled .= ' 00:00:00';
                                                        }
                                                        $system_events[] = ['tgl_tanggapan' => $tgl_cancelled, 'tanggapan' => $msg, 'nama_petugas' => $data['nama_kepala_lingkungan'] ?? 'Kepala Lingkungan', 'level' => 'kepala_lingkungan'];
                                                    }
                                                }
                                                // Approved by Lurah -> show if exists and not rejected
                                                if ($approved_by_lurah && $data['status'] != 'rejected') {
                                                    $lurah_tanggapan_check = mysqli_query($conn, "SELECT t.tgl_tanggapan, t.tanggapan FROM tanggapan t JOIN petugas p ON t.id_petugas = p.id_petugas WHERE t.id_pengaduan = '$id' AND p.level = 'lurah' AND t.tanggapan LIKE 'Disetujui oleh Lurah%' ORDER BY t.tgl_tanggapan DESC LIMIT 1");
                                                    if ($lurah_tanggapan_check && $lurah_row = mysqli_fetch_assoc($lurah_tanggapan_check)) {
                                                        $msg = 'Laporan Disetujui oleh Lurah';
                                                        if (strpos($lurah_row['tanggapan'], ':') !== false) {
                                                            $notes = trim(substr($lurah_row['tanggapan'], strpos($lurah_row['tanggapan'], ':') + 1));
                                                            if (!empty($notes)) $msg .= ': ' . $notes;
                                                        }
                                                        $tgl_approved = $lurah_row['tgl_tanggapan'] ?: ($data['approved_at'] ?? $data['last_progress_update'] ?? $data['tgl_pengaduan']);
                                                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl_approved)) {
                                                            $tgl_approved .= ' 00:00:00';
                                                        }
                                                        $system_events[] = ['tgl_tanggapan' => $tgl_approved, 'tanggapan' => $msg, 'nama_petugas' => $data['nama_lurah'] ?? 'Lurah', 'level' => 'lurah'];
                                                    }
                                                }
                                                // Rejected by Lurah or Kepala Lingkungan -> show if exists (this replaces validation)
                                                if ($data['status'] == 'rejected') {
                                                    $rejected_by_level = 'kepala_lingkungan';
                                                    $rejected_by_name = $data['nama_kepala_lingkungan'] ?? 'Kepala Lingkungan';
                                                    if (!empty($data['rejected_by'])) {
                                                        $rb = mysqli_query($conn, "SELECT nama_petugas, level FROM petugas WHERE id_petugas='{$data['rejected_by']}' LIMIT 1");
                                                        if ($rb && $rb_row = mysqli_fetch_assoc($rb)) {
                                                            $rejected_by_level = $rb_row['level'];
                                                            $rejected_by_name = $rb_row['nama_petugas'];
                                                        }
                                                    }

                                                    // priority: use existing tanggapan from reject event (Lurah or KL) if available
                                                    $reject_tanggapan_check = mysqli_query($conn, "SELECT t.tgl_tanggapan, t.tanggapan FROM tanggapan t JOIN petugas p ON t.id_petugas = p.id_petugas WHERE t.id_pengaduan = '$id' AND (p.level = 'kepala_lingkungan' OR p.level = 'lurah') AND (t.tanggapan LIKE 'Rejected by KL%' OR t.tanggapan LIKE 'Laporan Ditolak oleh Kepala Lingkungan%' OR t.tanggapan LIKE 'Ditolak oleh Lurah%' OR t.tanggapan LIKE 'Ditolak oleh Kepala Lingkungan%') ORDER BY t.tgl_tanggapan DESC LIMIT 1");
                                                    if ($reject_tanggapan_check && $reject_row = mysqli_fetch_assoc($reject_tanggapan_check)) {
                                                        $msg = ($rejected_by_level == 'lurah') ? 'Laporan Ditolak oleh Lurah' : 'Laporan Ditolak oleh Kepala Lingkungan';
                                                        if (strpos($reject_row['tanggapan'], ':') !== false) {
                                                            $notes = trim(substr($reject_row['tanggapan'], strpos($reject_row['tanggapan'], ':') + 1));
                                                            if (!empty($notes)) $msg .= ': ' . $notes;
                                                        }
                                                        $tgl_rejected = $reject_row['tgl_tanggapan'] ?: ($data['rejected_at'] ?? $data['tgl_pengaduan']);
                                                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl_rejected)) {
                                                            $tgl_rejected .= ' 00:00:00';
                                                        }
                                                        $system_events[] = ['tgl_tanggapan' => $tgl_rejected, 'tanggapan' => $msg, 'nama_petugas' => $rejected_by_name, 'level' => ($rejected_by_level == 'lurah' ? 'lurah' : 'kepala_lingkungan')];
                                                    }
                                                }
                                                // Progress updates by KL (show semua progress update agar timeline lengkap)
                                                $progress_entries = mysqli_query($conn, "SELECT t.tgl_tanggapan, t.tanggapan FROM tanggapan t JOIN petugas p ON t.id_petugas = p.id_petugas WHERE t.id_pengaduan = '$id' AND p.level = 'kepala_lingkungan' AND (t.tanggapan LIKE 'KL update progress%' OR t.tanggapan LIKE 'Progress Update%') ORDER BY t.tgl_tanggapan ASC");
                                                if ($progress_entries) {
                                                    while ($prog_row = mysqli_fetch_assoc($progress_entries)) {
                                                        $tanggapan_text = $prog_row['tanggapan'];
                                                        // Handle both formats: "KL update progress to pending: description" and "Progress Update: description (Status: pending)"
                                                        if (preg_match('/KL update progress to (pending|on_progress|completed):\s*(.*)/i', $tanggapan_text, $matches)) {
                                                            $status = strtolower($matches[1]);
                                                            $description = trim($matches[2]);
                                                            $msg = "KL update progress to $status";
                                                            if (!empty($description)) {
                                                                $msg .= ": $description";
                                                            }
                                                            $system_events[] = ['tgl_tanggapan' => $prog_row['tgl_tanggapan'], 'tanggapan' => $msg, 'nama_petugas' => $data['nama_kepala_lingkungan'] ?? 'Kepala Lingkungan', 'level' => 'kepala_lingkungan'];
                                                        } elseif (preg_match('/Progress Update:\s*(.*?)\s*\(Status:\s*(pending|on_progress|completed)\)/i', $tanggapan_text, $matches)) {
                                                            $description = trim($matches[1]);
                                                            $status = strtolower($matches[2]);
                                                            $msg = "KL update progress to $status";
                                                            if (!empty($description)) {
                                                                $msg .= ": $description";
                                                            }
                                                            $system_events[] = ['tgl_tanggapan' => $prog_row['tgl_tanggapan'], 'tanggapan' => $msg, 'nama_petugas' => $data['nama_kepala_lingkungan'] ?? 'Kepala Lingkungan', 'level' => 'kepala_lingkungan'];
                                                        }
                                                    }
                                                }
                                                // Not workable message
                                                if (!empty($data['is_workable']) && $data['is_workable'] == 0) {
                                                    $time = $data['last_progress_update'] ?? $data['approved_at'] ?? $data['tgl_pengaduan'];
                                                    $msg = 'Laporan Diterima - Tidak Dapat Dikerjakan';
                                                    if (!empty($data['non_workable_reason'])) $msg .= ': ' . $data['non_workable_reason'];
                                                    $system_events[] = ['tgl_tanggapan' => $time, 'tanggapan' => $msg, 'nama_petugas' => $data['nama_lurah'] ?? 'Lurah', 'level' => 'lurah'];
                                                }
                                                // Do not expose raw progress_log or Progress Terakhir lines to masyarakat timeline.
                                                // The assigned/handled summary ('Laporan Sedang Ditangani') is already added above when assigned.
                                                // Closed - include completion notes and proof (if any) into masyarakat timeline
                                                if (!empty($data['closed_at'])) {
                                                    $msg = 'Laporan Telah Selesai';
                                                    $details = 'Laporan Anda telah berhasil ditangani.';
                                                    if (!empty($data['completion_notes'])) $details .= "\nCatatan: " . $data['completion_notes'];
                                                    $system_events[] = ['tgl_tanggapan' => $data['closed_at'], 'tanggapan' => $msg . "\n" . $details, 'nama_petugas' => $data['closed_by_name'] ?? 'Kepala Lingkungan', 'level' => 'kepala_lingkungan', 'proof' => $data['completion_proof'] ?? null];
                                                }

                                                // Rejected fallback - insert only if no explicit reject event exists in tanggapan
                                                if (!empty($data['rejected_at'])) {
                                                    $has_reject_row = false;
                                                    $reject_tanggapan_check = mysqli_query($conn, "SELECT 1 FROM tanggapan t JOIN petugas p ON t.id_petugas=p.id_petugas WHERE t.id_pengaduan='$id' AND ((p.level='lurah' AND t.tanggapan LIKE 'Ditolak oleh Lurah%') OR (p.level='kepala_lingkungan' AND (t.tanggapan LIKE 'Rejected by KL%' OR t.tanggapan LIKE 'Laporan Ditolak oleh Kepala Lingkungan%' OR t.tanggapan LIKE 'Ditolak oleh Kepala Lingkungan%'))) LIMIT 1");
                                                    if ($reject_tanggapan_check && mysqli_num_rows($reject_tanggapan_check) > 0) {
                                                        $has_reject_row = true;
                                                    }

                                                    if (!$has_reject_row) {
                                                        // Keep fallback for legacy/rejected-by-KL that has no tanggapan row
                                                        $reject_role = 'Kepala Lingkungan';
                                                        if (!empty($data['rejected_by'])) {
                                                            $rb_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT level FROM petugas WHERE id_petugas='{$data['rejected_by']}' LIMIT 1"));
                                                            if ($rb_info && $rb_info['level'] === 'lurah') {
                                                                $reject_role = 'Lurah';
                                                            }
                                                        }

                                                        // Don't expose Lurah private notes in fallback
                                                        $msg = ($reject_role === 'lurah') ? 'Laporan Ditolak oleh Lurah' : 'Laporan Ditolak oleh Kepala Lingkungan';
                                                        if ($reject_role !== 'lurah' && !empty($data['rejection_reason'])) {
                                                            $msg .= ': ' . $data['rejection_reason'];
                                                        }
                                                        $timeline_rejected_time = pick_best_datetime($data['rejected_at'], [$data['approved_at'] ?? null, $data['opened_at'] ?? null, $data['tgl_pengaduan'] ?? null]);
                                                        $system_events[] = ['tgl_tanggapan' => $timeline_rejected_time, 'tanggapan' => $msg, 'nama_petugas' => ($reject_role === 'lurah' ? ($data['rejected_by_name'] ?? 'Lurah') : ($data['nama_kepala_lingkungan'] ?? 'Kepala Lingkungan')), 'level' => ($reject_role === 'lurah' ? 'lurah' : 'kepala_lingkungan')];
                                                    }
                                                }

                                                // For masyarakat view: show only curated public system events (do not display raw tanggapan from lurah/kl/petugas)
                                                $all = $system_events;

                                                // Sort by timestamp using reliable datetime parsing
                                                usort($all, function($a, $b){
                                                    $ta = datetime_to_timestamp($a['tgl_tanggapan']);
                                                    $tb = datetime_to_timestamp($b['tgl_tanggapan']);
                                                    if ($ta == $tb) return 0;
                                                    return ($ta < $tb) ? -1 : 1;
                                                });

                                                if (count($all) > 0) {
                                                    echo '<div class="timeline">';
                                                    foreach ($all as $update) {
                                                        if (empty($update['tanggapan'])) continue;
                                                        $role = ($update['level'] == 'kepala_lingkungan') ? 'Kepala Lingkungan' : (($update['level'] == 'lurah') ? 'Lurah' : (($update['level'] == 'petugas') ? 'Petugas' : ($update['level']=='masyarakat' ? 'Masyarakat' : 'Sistem')));
                                                        // For petugas roles, display role name instead of potentially incorrect nama_petugas
                                                        if (in_array($update['level'], ['kepala_lingkungan', 'lurah', 'petugas'])) {
                                                            $display_name = $role;
                                                        } else {
                                                            $display_name = $update['nama_petugas'];
                                                        }
                                                        echo '<div class="timeline-item">';
                                                        echo '<div class="timeline-marker bg-primary"></div>';
                                                        echo '<div class="timeline-content">';
                                                        $formatted_time = format_datetime($update['tgl_tanggapan']);
                                                        echo '<h6 class="timeline-title">' . htmlspecialchars($display_name) . ' <small class="text-muted">- ' . $formatted_time . '</small></h6>';
                                                        
                                                        // Check if tanggapan is long and from lurah
                                                        $tanggapan_text = htmlspecialchars($update['tanggapan']);
                                                        $is_long_text = strlen($update['tanggapan']) > 200;
                                                        $is_from_lurah = $update['level'] == 'lurah';
                                                        
                                                        if ($is_long_text && $is_from_lurah) {
                                                            $short_text = substr($tanggapan_text, 0, 200) . '...';
                                                            echo '<p class="timeline-text">' . nl2br($short_text) . '</p>';
                                                            echo '<button class="btn btn-sm btn-outline-primary" onclick="showFullText(this, \'' . addslashes($tanggapan_text) . '\')">Lihat Detail</button>';
                                                        } else {
                                                            echo '<p class="timeline-text">' . nl2br($tanggapan_text) . '</p>';
                                                        }
                                                        if (!empty($update['proof'])) {
                                                            $proofPath = htmlspecialchars($update['proof']);
                                                            echo '<p class="mt-2"><img src="../database/img/' . $proofPath . '" style="max-width:240px;" alt="Bukti Penyelesaian"></p>';
                                                        }
                                                        echo '</div></div>';
                                                    }
                                                    echo '</div>';
                                                } else {
                                                    echo '<p class="text-muted">Belum ada update progress.</p>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <script>
                                    function showFullText(button, fullText) {
                                        // Replace the truncated text with full text
                                        const textElement = button.previousElementSibling;
                                        textElement.innerHTML = fullText.split('\n').join('<br>');
                                        button.style.display = 'none';
                                    }
                                    
                                    function updateProgress() {
                                        fetch('?page=tanggapan&id_pengaduan=<?php echo $id; ?>', {
                                            method: 'GET',
                                            headers: {
                                                'X-Requested-With': 'XMLHttpRequest'
                                            }
                                        })
                                        .then(response => response.text())
                                        .then(data => {
                                            // Simple refresh for now - in production, parse JSON for partial updates
                                            location.reload();
                                        })
                                        .catch(error => console.error('Error updating progress:', error));
                                    }
                                    setInterval(updateProgress, 30000); // Update every 30 seconds
                                </script>
                            </div>
                        </div>
                        
                    </div>
                    <div class="card-footer">
                        <a href="index.php?page=aduan" class="btn btn-success">Kembali ke Daftar Laporan</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php  
    } else {
        echo "<div class='container'><div class='alert alert-danger'>Laporan tidak ditemukan</div></div>";
    }
} else {
    echo "<div class='container'><div class='alert alert-danger'>Halaman tidak tersedia</div></div>";
} 
?>
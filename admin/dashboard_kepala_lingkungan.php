<?php
include "../config/koneksi.php";
include "../config/functions.php";

// Access: lurah only
check_access(['lurah']);

// Fetch all kepala_lingkungan
$kl_q = mysqli_query($conn, "SELECT id_petugas, nama_petugas, telp, wilayah FROM petugas WHERE level='kepala_lingkungan' ORDER BY nama_petugas ASC");
$heads = [];
while ($kl = mysqli_fetch_assoc($kl_q)) {
    $id = $kl['id_petugas'];

    // Total Opened (opened but no action)
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS cnt, SUM(CASE WHEN (opened_at IS NOT NULL AND DATEDIFF(NOW(), opened_at) > 3) THEN 1 ELSE 0 END) AS overdue FROM pengaduan WHERE id_kepala_lingkungan='$id' AND status='opened' AND is_approved=0"));
    $total_opened = intval($r['cnt']);
    $overdue = intval($r['overdue']);

    // Total Approved
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM pengaduan WHERE id_kepala_lingkungan='$id' AND is_approved=1"));
    $total_approved = intval($r['cnt']);

    // Total Rejected
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM pengaduan WHERE id_kepala_lingkungan='$id' AND is_rejected=1"));
    $total_rejected = intval($r['cnt']);

    // Total Closed
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM pengaduan WHERE id_kepala_lingkungan='$id' AND status='closed'"));
    $total_closed = intval($r['cnt']);

    // Completion rate
    $denom = ($total_approved + $total_closed);
    $completion_rate = $denom > 0 ? ($total_closed / $denom) * 100 : 0.0;

    // Avg response time (seconds) between opened_at and (approved_at or rejected_at)
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(TIMESTAMPDIFF(SECOND, opened_at, COALESCE(approved_at, rejected_at))) AS avg_sec FROM pengaduan WHERE id_kepala_lingkungan='$id' AND opened_at IS NOT NULL AND (approved_at IS NOT NULL OR rejected_at IS NOT NULL)"));
    $avg_sec = floatval($r['avg_sec']);
    $avg_hours = $avg_sec > 0 ? round($avg_sec / 3600, 2) : null;

    $heads[] = [
        'id' => $id,
        'nama' => $kl['nama_petugas'],
        'telp' => $kl['telp'],
        'wilayah' => $kl['wilayah'],
        'opened' => $total_opened,
        'overdue' => $overdue,
        'approved' => $total_approved,
        'rejected' => $total_rejected,
        'closed' => $total_closed,
        'completion_rate' => $completion_rate,
        'avg_hours' => $avg_hours
    ];
}

// Sort by completion_rate desc
usort($heads, function($a, $b) {
    if ($a['completion_rate'] == $b['completion_rate']) return 0;
    return ($a['completion_rate'] > $b['completion_rate']) ? -1 : 1;
});
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="card">
            <div class="card-header d-flex pb-0">
                <h6>Dashboard Monitoring Kepala Lingkungan</h6>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Wilayah</th>
                                <th>Contact</th>
                                <th>Opened (no action)</th>
                                <th>Approved</th>
                                <th>Rejected</th>
                                <th>Closed</th>
                                <th>Completion Rate</th>
                                <th>Avg Response (hrs)</th>
                                <th>Alert</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no=1; foreach($heads as $h) { ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($h['nama']); ?></td>
                                <td><?= htmlspecialchars($h['wilayah']); ?></td>
                                <td><?= htmlspecialchars($h['telp']); ?></td>
                                <td><?= $h['opened']; ?></td>
                                <td><?= $h['approved']; ?></td>
                                <td><?= $h['rejected']; ?></td>
                                <td><?= $h['closed']; ?></td>
                                <td><?= number_format($h['completion_rate'],2); ?>%</td>
                                <td><?= $h['avg_hours'] !== null ? $h['avg_hours'] : '-'; ?></td>
                                <td>
                                    <?php if ($h['overdue'] > 0) { ?>
                                        <span class="badge bg-danger"><?= $h['overdue']; ?> opened >3 days</span>
                                    <?php } else { ?>
                                        <span class="badge bg-success">OK</span>
                                    <?php } ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="11">
                                    <details>
                                        <summary>Detail laporan untuk <?= htmlspecialchars($h['nama']); ?></summary>
                                        <?php
                                            // Show last 10 complaints summary for this KL
                                            $q = mysqli_query($conn, "SELECT id_pengaduan, tgl_pengaduan, judul_pengaduan, status, is_approved, is_rejected, opened_at, approved_at, rejected_at, closed_at FROM pengaduan WHERE id_kepala_lingkungan='".intval($h['id'])."' ORDER BY id_pengaduan DESC LIMIT 10");
                                            if (!$q) echo "<div>Query error: " . mysqli_error($conn) . "</div>";
                                        ?>
                                        <table class="table">
                                            <thead>
                                                <tr><th>ID</th><th>Tgl</th><th>Judul</th><th>Status</th><th>Opened</th><th>Approved/Rejected</th><th>Closed</th></tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($row = mysqli_fetch_assoc($q)) { ?>
                                                    <tr>
                                                        <td><?= $row['id_pengaduan']; ?></td>
                                                        <td><?= $row['tgl_pengaduan']; ?></td>
                                                        <td><?= htmlspecialchars($row['judul_pengaduan']); ?></td>
                                                        <td><?= $row['status']; ?></td>
                                                        <td><?= $row['opened_at']; ?></td>
                                                        <td><?= $row['approved_at'] ?? $row['rejected_at'] ?? '-'; ?></td>
                                                        <td><?= $row['closed_at'] ?? '-'; ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </details>
                                </td>
                            </tr>
                            <?php } if (empty($heads)) { echo '<tr><td colspan="11">Tidak ada data Kepala Lingkungan.</td></tr>'; } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

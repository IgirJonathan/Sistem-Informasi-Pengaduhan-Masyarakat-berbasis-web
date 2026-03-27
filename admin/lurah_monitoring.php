<?php
include "../config/koneksi.php";
include "../config/functions.php";

// Access: lurah
check_access(['lurah']);

// Fetch all kepala_lingkungan
$kl_q = mysqli_query($conn, "SELECT id_petugas, nama_petugas, wilayah FROM petugas WHERE level='kepala_lingkungan' ORDER BY nama_petugas ASC");
$heads = [];
while ($kl = mysqli_fetch_assoc($kl_q)) {
    $id = $kl['id_petugas'];

    // Total Opened (opened and approved, ready for disposisi)
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM pengaduan WHERE id_kepala_lingkungan='$id' AND status='opened' AND is_approved=1"));
    $total_opened = intval($r['cnt']);

    // Total Closed
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM pengaduan WHERE id_kepala_lingkungan='$id' AND status='closed'"));
    $total_closed = intval($r['cnt']);

    $heads[] = [
        'id' => $id,
        'nama' => $kl['nama_petugas'],
        'wilayah' => $kl['wilayah'],
        'opened' => $total_opened,
        'closed' => $total_closed
    ];
}
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="card">
            <div class="card-header d-flex pb-0">
                <h6>Monitoring Laporan per Kepala Lingkungan</h6>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Kepala Lingkungan</th>
                                <th>Wilayah</th>
                                <th>Total Opened (Siap Disposisi)</th>
                                <th>Total Closed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no=1; foreach($heads as $h) { ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($h['nama']); ?></td>
                                <td><?= htmlspecialchars($h['wilayah']); ?></td>
                                <td><?= $h['opened']; ?></td>
                                <td><?= $h['closed']; ?></td>
                            </tr>
                            <?php } if (empty($heads)) { echo '<tr><td colspan="5">Tidak ada data Kepala Lingkungan.</td></tr>'; } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
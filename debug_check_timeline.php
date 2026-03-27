<?php
include "config/koneksi.php";
$id = $_GET['id'] ?? null;
if (!$id) {
    echo "Usage: debug_check_timeline.php?id=<id_pengaduan>\n";
    exit;
}
$id = mysqli_real_escape_string($conn, $id);
$peng = mysqli_query($conn, "SELECT id_pengaduan, nik, judul_pengaduan, tgl_pengaduan, status, is_assigned, is_workable, progress_log, last_progress_update, completion_notes, completion_proof FROM pengaduan WHERE id_pengaduan='$id' LIMIT 1");
if (!$peng || mysqli_num_rows($peng) == 0) { echo "Pengaduan $id not found\n"; exit; }
$data = mysqli_fetch_assoc($peng);
echo "-- PENGADUAN $id --\n";
foreach ($data as $k=>$v) { echo "$k: $v\n"; }

echo "\n-- TANGGAPAN --\n";
$q = mysqli_query($conn, "SELECT t.tgl_tanggapan, t.tanggapan, t.id_petugas, p.nama_petugas, p.level FROM tanggapan t LEFT JOIN petugas p ON t.id_petugas = p.id_petugas WHERE t.id_pengaduan='$id' ORDER BY t.tgl_tanggapan ASC");
while ($r = mysqli_fetch_assoc($q)) {
    echo "[{$r['tgl_tanggapan']}] ({$r['level']} - {$r['id_petugas']}) {$r['nama_petugas']}: {$r['tanggapan']}\n";
}

?>
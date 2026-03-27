<?php
include 'config/koneksi.php';

$id = 136; // Test ID

// Simulate rejected status
$data = ['status' => 'rejected', 'validated_by_petugas' => 0];

// Check if there are petugas responses
$has_petugas_response = false;
$petugas_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM tanggapan t JOIN petugas p ON t.id_petugas = p.id_petugas WHERE t.id_pengaduan = '$id' AND p.level = 'petugas'");
if ($petugas_check && mysqli_fetch_assoc($petugas_check)['count'] > 0) {
    $has_petugas_response = true;
}

// Check if validated by KL (approved or rejected)
$validated_by_kl = false;
$kl_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM tanggapan t JOIN petugas p ON t.id_petugas = p.id_petugas WHERE t.id_pengaduan = '$id' AND p.level = 'kepala_lingkungan' AND (t.tanggapan LIKE 'Approved%' OR t.tanggapan LIKE 'Rejected%')");
if ($kl_check && mysqli_fetch_assoc($kl_check)['count'] > 0) {
    $validated_by_kl = true;
}

$steps = [
    ['label' => 'Diajukan', 'status' => 'pending', 'active' => true],
    ['label' => 'Divalidasi', 'status' => 'validated', 'active' => $data['validated_by_petugas'] == 1 || $validated_by_kl],
    ['label' => 'Ditangani', 'status' => 'completed', 'active' => $has_petugas_response],
    ['label' => 'Selesai', 'status' => 'closed', 'active' => $data['status'] == 'closed']
];

$current_step = 0;
foreach ($steps as $index => $step) {
    if ($step['active']) $current_step = $index + 1;
}
$progress_width = ($current_step / count($steps)) * 100;
$progress_color = ($data['status'] == 'rejected') ? 'bg-danger' : 'bg-success';

echo "For rejected status:\n";
echo "Current step: $current_step\n";
echo "Progress width: $progress_width%\n";
echo "Progress color: $progress_color\n";
echo "Steps:\n";
foreach ($steps as $index => $step) {
    echo "- Step " . ($index + 1) . ": " . $step['label'] . " - " . ($step['active'] ? 'ACTIVE' : 'inactive') . "\n";
}
?>
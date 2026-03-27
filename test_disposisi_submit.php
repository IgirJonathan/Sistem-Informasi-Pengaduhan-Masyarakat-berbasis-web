<?php
// Simulate disposisi form submit
$_POST['disposisi'] = '1';
$_POST['id_pengaduan'] = '139';
$_POST['jenis_disposisi'] = 'dikerjakan';
$_POST['id_staff'] = '3';
$_POST['catatan'] = 'Silakan tangani laporan ini';
$_POST['alasan_tidak_dikerjakan'] = ''; // Empty

// Simulate session
$_SESSION['id_petugas'] = 2;

include 'config/koneksi.php';
include 'config/functions.php';

// Check if handler exists
if (isset($_POST['disposisi'])) {
    $id_pengaduan = $_POST['id_pengaduan'];
    // Cek if already assigned
    $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT is_assigned FROM pengaduan WHERE id_pengaduan='$id_pengaduan'"));
    if ($check && $check['is_assigned'] == 1) {
        echo "Laporan sudah didisposisikan\n";
        exit;
    }
    
    $jenis = $_POST['jenis_disposisi'] ?? 'dikerjakan';
    $catatan = $_POST['catatan'] ?? '';
    
    echo "Jenis: $jenis\n";
    echo "Catatan: '$catatan'\n";
    
    if ($jenis === 'dikerjakan') {
        $id_staff = $_POST['id_staff'] ?? null;
        echo "ID Staff: $id_staff\n";
        
        if (empty($id_staff)) {
            echo "ERROR: Silakan pilih staff terlebih dahulu\n";
            exit;
        }
        if (empty($catatan)) {
            echo "ERROR: Catatan disposisi harus diisi\n";
            exit;
        }
        echo "Processing disposisi to staff...\n";
        $res = disposisi_to_staff($conn, $id_pengaduan, $id_staff, $_SESSION['id_petugas'], $catatan);
        echo "Result: " . ($res['success'] ? 'SUCCESS' : 'FAILED - ' . $res['error']) . "\n";
    } elseif ($jenis === 'tidak_dikerjakan') {
        $alasan = $_POST['alasan_tidak_dikerjakan'] ?? '';
        echo "Alasan: '$alasan'\n";
        
        if (empty($alasan)) {
            echo "ERROR: Alasan tidak dapat dikerjakan harus diisi\n";
            exit;
        }
        echo "Processing tidak dapat dikerjakan...\n";
        // ... process
        echo "SUCCESS\n";
    } else {
        echo "ERROR: Jenis disposisi tidak valid\n";
    }
} else {
    echo "Handler not triggered\n";
}
?>
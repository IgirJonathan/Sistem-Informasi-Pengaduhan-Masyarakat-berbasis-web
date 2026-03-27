<?php
// Simulate cancel disposisi POST
$_POST['cancel_disposisi'] = '1';
$_POST['id_pengaduan'] = '139';

// Simulate session
$_SESSION['id_petugas'] = 2;

include 'config/koneksi.php';
include 'config/functions.php';

// Check if handler exists
if (isset($_POST['cancel_disposisi'])) {
    $id_pengaduan = $_POST['id_pengaduan'];
    echo "Processing cancel disposisi for ID: $id_pengaduan\n";
    
    $query = "UPDATE pengaduan SET is_assigned=0, assigned_by=NULL, assigned_petugas_id=NULL, assigned_at=NULL, id_staff=NULL, can_unapprove=1 WHERE id_pengaduan='$id_pengaduan'";
    $res = mysqli_query($conn, $query);
    if ($res) {
        echo "Cancel disposisi: SUCCESS\n";
        create_tanggapan($conn, ['id_pengaduan' => $id_pengaduan, 'tanggapan' => 'Disposisi dibatalkan oleh Lurah', 'id_petugas' => $_SESSION['id_petugas']]);
        echo "Tanggapan created\n";
    } else {
        echo "Cancel disposisi: FAILED\n";
    }
} else {
    echo "Handler not triggered\n";
}
?>
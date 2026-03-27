<?php
include 'config/koneksi.php';
include 'config/functions.php';

// Simulate KL login
$_SESSION['login'] = 'petugas';
$_SESSION['level'] = 'kepala_lingkungan';
$_SESSION['id_petugas'] = 15;

$id_kepala = 15;
$id_pengaduan = 137; // The approved complaint

echo "Testing unapprove function for complaint ID $id_pengaduan\n";

// Check current status
$result = mysqli_query($conn, "SELECT status, is_approved, approved_by FROM pengaduan WHERE id_pengaduan = '$id_pengaduan'");
$row = mysqli_fetch_assoc($result);
echo "Before unapprove: Status={$row['status']}, Approved={$row['is_approved']}, By={$row['approved_by']}\n";

// Perform unapprove
$res = validate_complaint($conn, $id_pengaduan, 'unapprove', '', $id_kepala);
echo "Unapprove result: " . ($res['success'] ? 'Success' : 'Failed - ' . $res['error']) . "\n";

// Check status after
$result = mysqli_query($conn, "SELECT status, is_approved, approved_by FROM pengaduan WHERE id_pengaduan = '$id_pengaduan'");
$row = mysqli_fetch_assoc($result);
echo "After unapprove: Status={$row['status']}, Approved={$row['is_approved']}, By={$row['approved_by']}\n";
?>
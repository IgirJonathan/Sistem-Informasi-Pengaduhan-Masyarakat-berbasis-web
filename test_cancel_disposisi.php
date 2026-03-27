<?php
include 'config/koneksi.php';
include 'config/functions.php';

// Test cancel disposisi
$id_pengaduan = 139; // Complaint that was assigned

// Check current status
$result = mysqli_query($conn, "SELECT is_assigned, assigned_petugas_id, can_unapprove FROM pengaduan WHERE id_pengaduan = '$id_pengaduan'");
if ($result && $row = mysqli_fetch_assoc($result)) {
    echo "Before cancel:\n";
    echo "is_assigned: {$row['is_assigned']}\n";
    echo "assigned_petugas_id: {$row['assigned_petugas_id']}\n";
    echo "can_unapprove: {$row['can_unapprove']}\n\n";
} else {
    echo "Complaint not found\n";
    exit;
}

// Simulate cancel disposisi
$query = "UPDATE pengaduan SET is_assigned=0, assigned_by=NULL, assigned_petugas_id=NULL, assigned_at=NULL, id_staff=NULL, can_unapprove=1 WHERE id_pengaduan='$id_pengaduan'";
$res = mysqli_query($conn, $query);
if ($res) {
    echo "Cancel disposisi: Success\n\n";
} else {
    echo "Cancel disposisi: Failed\n\n";
}

// Check status after
$result = mysqli_query($conn, "SELECT is_assigned, assigned_petugas_id, can_unapprove FROM pengaduan WHERE id_pengaduan = '$id_pengaduan'");
if ($result && $row = mysqli_fetch_assoc($result)) {
    echo "After cancel:\n";
    echo "is_assigned: {$row['is_assigned']}\n";
    echo "assigned_petugas_id: {$row['assigned_petugas_id']}\n";
    echo "can_unapprove: {$row['can_unapprove']}\n";
}
?>
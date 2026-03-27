<?php
include 'config/koneksi.php';
include 'config/functions.php';

// Test disposisi from "tidak dapat dikerjakan" to "disposisi ke staff"

// First, simulate a complaint that was marked as "tidak dapat dikerjakan"
$id_pengaduan = 139; // Use existing ID
$id_lurah = 2; // Assume Lurah ID
$id_staff = 3; // Assume Staff ID
$catatan = 'Silakan tangani laporan ini segera';

// Check current status
$result = mysqli_query($conn, "SELECT is_workable, is_assigned, non_workable_reason FROM pengaduan WHERE id_pengaduan = '$id_pengaduan'");
if ($result && $row = mysqli_fetch_assoc($result)) {
    echo "Before disposisi:\n";
    echo "is_workable: {$row['is_workable']}\n";
    echo "is_assigned: {$row['is_assigned']}\n";
    echo "non_workable_reason: {$row['non_workable_reason']}\n\n";
} else {
    echo "Complaint ID $id_pengaduan not found\n";
    exit;
}

// Perform disposisi
echo "Performing disposisi to staff...\n";
$res = disposisi_to_staff($conn, $id_pengaduan, $id_staff, $id_lurah, $catatan);
echo "Result: " . ($res['success'] ? 'Success' : 'Failed - ' . $res['error']) . "\n\n";

// Check status after
$result = mysqli_query($conn, "SELECT is_workable, is_assigned, non_workable_reason, assigned_petugas_id FROM pengaduan WHERE id_pengaduan = '$id_pengaduan'");
if ($result && $row = mysqli_fetch_assoc($result)) {
    echo "After disposisi:\n";
    echo "is_workable: {$row['is_workable']}\n";
    echo "is_assigned: {$row['is_assigned']}\n";
    echo "non_workable_reason: {$row['non_workable_reason']}\n";
    echo "assigned_petugas_id: {$row['assigned_petugas_id']}\n";
} else {
    echo "Failed to check after status\n";
}
?>
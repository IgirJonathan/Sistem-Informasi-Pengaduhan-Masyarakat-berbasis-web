<?php
include 'config/koneksi.php';

// Test status logic for masyarakat
// Simulate different scenarios

$test_cases = [
    ['status' => 'opened', 'is_workable' => 1, 'is_assigned' => 0, 'desc' => 'Normal opened'],
    ['status' => 'opened', 'is_workable' => 0, 'is_assigned' => 0, 'desc' => 'Tidak dapat dikerjakan'],
    ['status' => 'opened', 'is_workable' => 0, 'is_assigned' => 1, 'desc' => 'Dulu tidak dapat dikerjakan, sekarang assigned'],
    ['status' => 'opened', 'is_workable' => 1, 'is_assigned' => 1, 'desc' => 'Normal assigned'],
];

echo "Testing status badge logic:\n\n";

foreach ($test_cases as $test) {
    echo "Case: {$test['desc']}\n";
    echo "Status: {$test['status']}, is_workable: {$test['is_workable']}, is_assigned: {$test['is_assigned']}\n";
    
    if ($test['status'] == 'opened') {
        if ($test['is_assigned'] == 1) {
            echo "Badge: Sedang Ditangani (bg-warning)\n";
        } elseif ($test['is_workable'] == 0) {
            echo "Badge: Diterima - Tidak Dapat Dikerjakan (bg-info)\n";
        } else {
            echo "Badge: Sedang Ditangani (bg-warning)\n";
        }
    }
    echo "\n";
}
?>
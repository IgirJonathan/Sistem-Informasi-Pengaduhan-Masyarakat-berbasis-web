<?php
include 'config/koneksi.php';
$result = mysqli_query($conn, 'SELECT id_pengaduan, is_workable, is_assigned FROM pengaduan WHERE is_workable = 0 AND is_assigned = 1 LIMIT 5');
while ($row = mysqli_fetch_assoc($result)) {
    echo 'ID: ' . $row['id_pengaduan'] . ' - workable: ' . $row['is_workable'] . ' - assigned: ' . $row['is_assigned'] . "\n";
}
?>
<?php
// test_columns.php - Check if columns exist in pengaduan table
include "config/koneksi.php";

$query = "DESCRIBE pengaduan";
$result = mysqli_query($conn, $query);

if ($result) {
    echo "<h2>Columns in pengaduan table:</h2><ul>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<li>" . $row['Field'] . " - " . $row['Type'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "Error: " . mysqli_error($conn);
}

// Test the problematic query
echo "<h2>Test query:</h2>";
$sql = "SELECT a.id_pengaduan, a.status, a.is_approved FROM pengaduan a LIMIT 1";
$result = mysqli_query($conn, $sql);
if ($result) {
    echo "Query successful. Sample row: ";
    $row = mysqli_fetch_assoc($result);
    print_r($row);
} else {
    echo "Query failed: " . mysqli_error($conn);
}
?>
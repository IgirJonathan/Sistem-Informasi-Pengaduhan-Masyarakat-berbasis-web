<?php
include 'config/koneksi.php';

$id = 132; // Test ID

// Query untuk pesan dari KL (public untuk masyarakat)
$kl_tanggapan = mysqli_query($conn, "
    SELECT 
        CASE 
            WHEN t.tanggapan LIKE 'Approved%' THEN t.tanggapan
            WHEN t.tanggapan LIKE 'KL update progress%' THEN 
                CASE 
                    WHEN LOCATE(':', t.tanggapan) > 0 THEN SUBSTRING_INDEX(t.tanggapan, ':', 1)
                    ELSE t.tanggapan
                END
            ELSE NULL
        END as public_message
    FROM tanggapan t 
    JOIN petugas p ON t.id_petugas = p.id_petugas 
    WHERE t.id_pengaduan = '$id' 
    AND p.level = 'kepala_lingkungan' 
    AND (
        t.tanggapan LIKE 'Approved%' OR 
        t.tanggapan LIKE 'KL update progress%'
    )
    ORDER BY t.tgl_tanggapan DESC LIMIT 1
");

echo "KL public message for masyarakat ID $id:\n";
if ($kl_tanggapan && mysqli_num_rows($kl_tanggapan) > 0) {
    $msg = mysqli_fetch_assoc($kl_tanggapan);
    echo "Message: '{$msg['public_message']}'\n";
} else {
    echo "No public message from KL\n";
}
?>
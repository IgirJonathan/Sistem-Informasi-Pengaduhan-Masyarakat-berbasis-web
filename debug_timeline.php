<?php
include 'config/koneksi.php';

$id = 134;

$timeline_q = mysqli_query($conn, "
    SELECT
        t.tgl_tanggapan,
        CASE
            WHEN p.level = 'lurah' AND t.tanggapan LIKE 'Disposisi%' THEN 'Disposisi to Petugas'
            WHEN p.level = 'kepala_lingkungan' AND t.tanggapan LIKE 'KL update progress%' THEN
                CASE
                    WHEN LOCATE(':', t.tanggapan) > 0 THEN SUBSTRING_INDEX(t.tanggapan, ':', 1)
                    ELSE t.tanggapan
                END
            WHEN p.level = 'kepala_lingkungan' AND t.tanggapan LIKE 'Approved%' THEN
                CASE
                    WHEN LOCATE(':', t.tanggapan) > 0 AND LENGTH(TRIM(SUBSTRING(t.tanggapan, LOCATE(':', t.tanggapan) + 1))) > 0 THEN t.tanggapan
                    ELSE 'Approved'
                END
            WHEN p.level = 'petugas' AND t.tanggapan NOT LIKE 'Progress Update%' THEN t.tanggapan
            ELSE NULL
        END as tanggapan,
        p.nama_petugas,
        p.level
    FROM tanggapan t
    JOIN petugas p ON t.id_petugas = p.id_petugas
    WHERE t.id_pengaduan = '$id'
    AND (
        (p.level = 'lurah' AND t.tanggapan LIKE 'Disposisi%') OR
        (p.level = 'kepala_lingkungan' AND (t.tanggapan LIKE 'Approved%' OR t.tanggapan LIKE 'KL update progress%')) OR
        (p.level = 'petugas' AND t.tanggapan NOT LIKE 'Progress Update%')
    )
    ORDER BY t.tgl_tanggapan DESC
");

if (!$timeline_q) {
    echo "Query error: " . mysqli_error($conn) . "\n";
} else {
    $num_updates = mysqli_num_rows($timeline_q);
    echo "Number of rows: $num_updates\n";

    while ($update = mysqli_fetch_assoc($timeline_q)) {
        if ($update['tanggapan'] !== NULL) {
            echo "- {$update['nama_petugas']} ({$update['level']}): {$update['tanggapan']} [{$update['tgl_tanggapan']}]\n";
        }
    }
}
?>
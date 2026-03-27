<?php
include 'config/koneksi.php';

$id = 139;
$timeline_q = mysqli_query($conn, "
    SELECT * FROM (
        -- Kepala Lingkungan Approvals
        SELECT
            MAX(t.tgl_tanggapan) as tgl_tanggapan,
            CASE
                WHEN LOCATE(':', MAX(t.tanggapan)) > 0 AND LENGTH(TRIM(SUBSTRING(MAX(t.tanggapan), LOCATE(':', MAX(t.tanggapan)) + 1))) > 0 THEN MAX(t.tanggapan)
                ELSE 'Approved'
            END as tanggapan,
            p.nama_petugas,
            p.level
        FROM tanggapan t
        JOIN petugas p ON t.id_petugas = p.id_petugas
        WHERE t.id_pengaduan = '$id' AND p.level = 'kepala_lingkungan' AND t.tanggapan LIKE 'Approved%'
        GROUP BY p.level, p.nama_petugas

        UNION ALL

        -- Kepala Lingkungan Rejections
        SELECT
            MAX(t.tgl_tanggapan) as tgl_tanggapan,
            CASE
                WHEN LOCATE(':', MAX(t.tanggapan)) > 0 AND LENGTH(TRIM(SUBSTRING(MAX(t.tanggapan), LOCATE(':', MAX(t.tanggapan)) + 1))) > 0 THEN MAX(t.tanggapan)
                ELSE 'Rejected'
            END as tanggapan,
            p.nama_petugas,
            p.level
        FROM tanggapan t
        JOIN petugas p ON t.id_petugas = p.id_petugas
        WHERE t.id_pengaduan = '$id' AND p.level = 'kepala_lingkungan' AND t.tanggapan LIKE 'Rejected%'
        GROUP BY p.level, p.nama_petugas

        UNION ALL

        -- Lurah All Actions (chronological)
        SELECT
            t.tgl_tanggapan,
            CASE
                WHEN t.tanggapan LIKE 'Tidak dapat dikerjakan%' THEN t.tanggapan
                WHEN t.tanggapan LIKE 'Disposisi to Petugas%' THEN 'Laporan telah didisposisikan ke petugas untuk penanganan'
                ELSE NULL
            END as tanggapan,
            p.nama_petugas,
            p.level
        FROM tanggapan t
        JOIN petugas p ON t.id_petugas = p.id_petugas
        WHERE t.id_pengaduan = '$id' AND p.level = 'lurah'
        AND (
            t.tanggapan LIKE 'Tidak dapat dikerjakan%' OR
            t.tanggapan LIKE 'Disposisi to Petugas%'
        )

        UNION ALL

        -- Petugas Laporan Selesai
        SELECT
            MAX(t.tgl_tanggapan) as tgl_tanggapan,
            MAX(t.tanggapan) as tanggapan,
            p.nama_petugas,
            p.level
        FROM tanggapan t
        JOIN petugas p ON t.id_petugas = p.id_petugas
        WHERE t.id_pengaduan = '$id' AND p.level = 'petugas' AND t.tanggapan LIKE 'Laporan selesai%'
        GROUP BY p.level, p.nama_petugas

        UNION ALL

        -- Petugas Laporan Ditutup
        SELECT
            MAX(t.tgl_tanggapan) as tgl_tanggapan,
            MAX(t.tanggapan) as tanggapan,
            p.nama_petugas,
            p.level
        FROM tanggapan t
        JOIN petugas p ON t.id_petugas = p.id_petugas
        WHERE t.id_pengaduan = '$id' AND p.level = 'petugas' AND t.tanggapan LIKE 'Laporan ditutup%'
        GROUP BY p.level, p.nama_petugas
    ) combined_timeline
    ORDER BY tgl_tanggapan ASC
");

echo "Timeline untuk complaint $id:\n";
$num_updates = mysqli_num_rows($timeline_q);
echo "Total entries: $num_updates\n\n";

while ($update = mysqli_fetch_assoc($timeline_q)) {
    if ($update['tanggapan']) {
        echo "- {$update['level']}: {$update['tanggapan']}\n";
    }
}
?>
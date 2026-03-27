<?php
include 'config/koneksi.php';

$id = 136;

$query = mysqli_query($conn, "SELECT a.*, 
                              b.nama as nama_masyarakat,
                              c.nama_petugas as nama_kepala_lingkungan,
                              d.nama_petugas as nama_lurah,
                              e.nama_petugas as nama_staff,
                              f.nama_petugas as opened_by_name,
                              g.nama_petugas as approved_by_name,
                              h.nama_petugas as rejected_by_name,
                              i.nama_petugas as assigned_by_name,
                              j.nama_petugas as closed_by_name
                          FROM pengaduan a 
                          LEFT JOIN masyarakat b ON a.nik = b.nik
                          LEFT JOIN petugas c ON a.id_kepala_lingkungan = c.id_petugas
                          LEFT JOIN petugas d ON a.id_lurah = d.id_petugas
                          LEFT JOIN petugas e ON a.id_staff = e.id_petugas
                          LEFT JOIN petugas f ON a.opened_by = f.id_petugas
                          LEFT JOIN petugas g ON a.approved_by = g.id_petugas
                          LEFT JOIN petugas h ON a.rejected_by = h.id_petugas
                          LEFT JOIN petugas i ON a.assigned_by = i.id_petugas
                          LEFT JOIN petugas j ON a.closed_by = j.id_petugas
                          WHERE a.id_pengaduan = '$id'");

if (!$query) {
    echo "Query error: " . mysqli_error($conn) . "\n";
} else {
    if (mysqli_num_rows($query) > 0) {
        echo "Data found\n";
        $data = mysqli_fetch_array($query);
        echo "Judul: " . $data['judul_pengaduan'] . "\n";
    } else {
        echo "No data found for id $id\n";
    }
}
?>
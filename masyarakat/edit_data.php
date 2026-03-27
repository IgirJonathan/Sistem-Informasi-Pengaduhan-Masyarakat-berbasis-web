<?php

include "../config/koneksi.php";

//program yg berfungsi untuk menghapus data dari pengaduan,
//namun saat status data pengaduan tersebut selesai maka data yg id-nya sama di tabel tanggapan juga ikut terhapus

if (isset($_POST["hapus_pengaduan"])) {
    //menampung id_pengaduan yg dikirim hapus_pengaduan
    $id_pengaduan = $_POST["id_pengaduan"];

    $h_pengaduan = mysqli_query($conn, "SELECT * FROM pengaduan WHERE id_pengaduan = '$id_pengaduan'");//query data tabel pengaduan

    $cek = mysqli_num_rows($h_pengaduan);

    if ($cek > 0) {
        $data = mysqli_fetch_array($h_pengaduan);//looping data dari tabel pengaduan
        
        // Jika status sudah closed (selesai), jangan hapus data tapi set flag deleted
        if ($data['status'] == 'closed') {
            mysqli_query($conn, "UPDATE pengaduan SET is_deleted_by_masyarakat = 1 WHERE id_pengaduan = '$id_pengaduan'");
            echo "<script>
                     alert('Laporan berhasil dihapus dari daftar Anda. Data tetap tersimpan untuk keperluan administrasi.');
                    document.location.href='index.php';
                </script>";
        } else {
            // Untuk laporan yang belum selesai, hapus sepenuhnya
            if (is_file("../database/img/" . $data['foto'])) {
                unlink("../database/img/" . $data['foto']);
            }

            mysqli_query($conn, "DELETE FROM pengaduan WHERE id_pengaduan = '$id_pengaduan' "); //mengapus data dari pengaduan 
            mysqli_query($conn, "DELETE FROM tanggapan WHERE id_pengaduan = '$id_pengaduan' ");//mengapus data dari tanggapan
            echo "<script>
                     alert('Data Berhasil di Hapus');
                    document.location.href='index.php';
                </script>";
        }
    } else {
        echo "<script>
                 alert('Data Gagal di Hapus');
                document.location.href='index.php';
            </script>";
    }
}

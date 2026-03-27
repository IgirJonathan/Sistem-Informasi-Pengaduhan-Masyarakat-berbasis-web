<?php
require '../config/koneksi.php';
require '../config/functions.php';

?>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h4>Selamat Datang <?= $_SESSION["nama"]; ?></h4>
            <div class="card">
                <div class="card-body">
                    FORM PENGADUAN <br>
                    <hr class="bg-dark">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="judul_laporan" class="form-label"> Judul Laporan </label>
                            <input type="text" class="form-control" name="judul_laporan" id="judul_laporan" placeholder="Masukan Judul Laporan" required autocomplete="off" autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="isi_laporan" class="form-label"> Isi Laporan </label>
                            <textarea class="form-control" name="isi_laporan" placeholder="Masukan Isi Laporan" required autocomplete="off"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="foto" class="form-label"> Foto </label>
                            <input type="file" class="form-control" name="foto" id="foto">
                        </div>
                        <div class="mb-3">
                            <label for="wilayah" class="form-label"> Lingkungan (RW) </label>
                            <select class="form-control" name="wilayah" id="wilayah" required>
                                <option value="">-- Pilih Lingkungan --</option>
                                <option value="1">Lingkungan 1</option>
                                <option value="2">Lingkungan 2</option>
                                <option value="3">Lingkungan 3</option>
                                <option value="4">Lingkungan 4</option>
                                <option value="5">Lingkungan 5</option>
                            </select>
                        </div>
                </div>
                <div class="card-footer">
                    <button type="submit" name="kirim" class="btn btn-success">Kirim</button>
                </div>
                </form>
                <?php
                if (isset($_POST['kirim'])) {
                    //menampung data yang dikirim $POST
                    $nik = $_SESSION["nik"];
                    $judul = $_POST["judul_laporan"];
                    $isi = $_POST["isi_laporan"];
                    $wilayah = $_POST["wilayah"];
                    // Validate and store uploaded image (server-side)
                    $uploadRes = validate_and_store_image($_FILES['foto']);
                    if (empty($uploadRes['success'])) {
                        echo "<script>alert('Upload error: {$uploadRes['error']}'); document.location.href='index.php';</script>";
                        exit;
                    }
                    $nama_foto = $uploadRes['filename'] ?? '';
                    
                    // Gunakan fungsi create_pengaduan
                    $query = create_pengaduan($conn, [
                        'nik' => $nik,
                        'judul_pengaduan' => $judul,
                        'isi_laporan' => $isi,
                        'foto' => $nama_foto,
                        'wilayah' => $wilayah
                    ]);

                    //jika query/insert data berhasil/gagal maka tampilkan alert
                    if ($query) {

                        echo "
                            <script>
                             alert('Data Berhasil Dikirim');
                             document.location.href='index.php';
                         </script>
                        ";
                    } else {
                        echo "
                            <script>
                                alert('Data Gagal Dikirim');
                                document.location.href='index.php';
                         </script>
                        ";
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>
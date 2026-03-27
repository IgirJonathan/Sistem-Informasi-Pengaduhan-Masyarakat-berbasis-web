<?php
include "../config/koneksi.php";
include "../config/functions.php";

// Check access for admin
check_access(['admin']);

// Handle filter and sort parameters
$filter = isset($_GET['filter']) ? mysqli_real_escape_string($conn, $_GET['filter']) : 'all';
$sort = isset($_GET['sort']) ? mysqli_real_escape_string($conn, $_GET['sort']) : 'level';
$order = isset($_GET['order']) && $_GET['order'] == 'desc' ? 'desc' : 'asc';

// Build query
$query_str = "SELECT * FROM petugas";
$where = "";
if ($filter != 'all') {
    $where = " WHERE level = '$filter'";
}
$query_str .= $where . " ORDER BY $sort $order";

$query = mysqli_query($conn, $query_str);
?>
<div class="container-fluid">
    <div class="row">
        <div class="card">
            <div class="card-header d-flex pb-0">
                <h6>DATA PETUGAS</h6>
                <a href="" class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#tambah">Tambah Data</a>
                <div class="ms-3">
                    <label for="filter" class="me-2">Filter Level:</label>
                    <select name="filter" id="filter" class="form-select form-select-sm" onchange="window.location.href='?page=petugas&filter=' + this.value + '&sort=<?= $sort ?>&order=<?= $order ?>'">
                        <option value="all" <?= $filter == 'all' ? 'selected' : '' ?>>Semua</option>
                        <option value="admin" <?= $filter == 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="lurah" <?= $filter == 'lurah' ? 'selected' : '' ?>>Lurah</option>
                        <option value="kepala_lingkungan" <?= $filter == 'kepala_lingkungan' ? 'selected' : '' ?>>Kepala Lingkungan</option>
                    </select>
                </div>
            </div>
            <div class="card-body  px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table table-striped align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">No</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7 ps-2">Nama</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Username</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Telepon</th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">
                                    <a href="?filter=<?= $filter ?>&sort=level&order=<?= $order == 'asc' ? 'desc' : 'asc' ?>" style="text-decoration: none; color: inherit;">Level</a>
                                    <?php if ($sort == 'level') { echo $order == 'asc' ? ' ↑' : ' ↓'; } ?>
                                </th>
                                <th class="text-center text-uppercase text-dark text-xs font-weight-bolder opacity-7">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while ($data = mysqli_fetch_array($query)) {
                            ?>
                                <tr>
                                    <td class="align-middle text-center text-sm"><?= $no++; ?></td>
                                    <td class="align-middle text-center text-sm"><?= $data['nama_petugas']; ?></td>
                                    <td class="align-middle text-center text-sm"><?= $data['username']; ?></td>
                                    <td class="align-middle text-center text-sm"><?= $data['telp']; ?></td>
                                    <td class="align-middle text-center text-sm"><?= $data['level']; ?></td>
                                    <td class="align-middle text-center">
                                        <!-- EDIT -->
                                        <span class="badge badge-sm bg-warning">
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#edit<?= $data['id_petugas'] ?>" style="text-decoration: none; color:white;">EDIT</a>
                                        </span>
                                        <!-- RESET PASSWORD -->
                                        <span class="badge badge-sm bg-info">
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#reset<?= $data['id_petugas'] ?>" style="text-decoration: none; color:white;">RESET</a>
                                        </span>
                                        <?php if ($data['level'] != 'admin') { ?>
                                        <!-- HAPUS (hanya untuk non-admin) - Pencegahan penghapusan akun admin untuk keamanan sistem -->
                                        <span class="badge badge-sm bg-danger">
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#hapus<?= $data['id_petugas'] ?>" style="text-decoration: none; color: white;">HAPUS</a>
                                        </span>
                                        <?php } ?>
                                        <!-- modal EDIT -->
                                        <div class="modal fade" id="edit<?= $data['id_petugas'] ?>" tabindex="-1" aria-labelledby="editLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="editLabel">Edit Data Petugas</h1>
                                                        <button type="button" class="btn-close bg-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="edit_data.php" method="POST">
                                                            <input type="hidden" name="id_petugas" value="<?= $data['id_petugas']; ?>">
                                                            <div class="mb-3">
                                                                <label>Nama Petugas</label>
                                                                <input type="text" name="nama_petugas" class="form-control" value="<?= $data['nama_petugas']; ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Username</label>
                                                                <input type="text" name="username" class="form-control" value="<?= $data['username']; ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Telepon</label>
                                                                <input type="text" name="telp" class="form-control" value="<?= $data['telp']; ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label>Level</label>
                                                                <select name="level" class="form-control" required>
                                                                    <option value="admin" <?= $data['level'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                                    <option value="lurah" <?= $data['level'] == 'lurah' ? 'selected' : ''; ?>>Lurah</option>
                                                                    <option value="kepala_lingkungan" <?= $data['level'] == 'kepala_lingkungan' ? 'selected' : ''; ?>>Kepala Lingkungan</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3" id="wilayah_edit_<?= $data['id_petugas'] ?>" style="display: <?= $data['level'] == 'kepala_lingkungan' ? 'block' : 'none'; ?>;">
                                                                <label>Lingkungan</label>
                                                                <select name="wilayah" class="form-control" <?= $data['level'] == 'kepala_lingkungan' ? 'required' : ''; ?>>
                                                                    <option value="">-- Pilih Lingkungan --</option>
                                                                    <?php
                                                                    $wilayahs = ['Lingkungan 1','Lingkungan 2','Lingkungan 3','Lingkungan 4','Lingkungan 5'];
                                                                    foreach ($wilayahs as $w) {
                                                                        $sel = ($data['wilayah'] == $w) ? 'selected' : '';
                                                                        echo "<option value=\"$w\" $sel>$w</option>";
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                            <script>
                                                                document.querySelector('#edit<?= $data['id_petugas'] ?> select[name="level"]').addEventListener('change', function() {
                                                                    var wilayahField = document.getElementById('wilayah_edit_<?= $data['id_petugas'] ?>');
                                                                    if (this.value === 'kepala_lingkungan') {
                                                                        wilayahField.style.display = 'block';
                                                                        wilayahField.querySelector('input').setAttribute('required', 'required');
                                                                    } else {
                                                                        wilayahField.style.display = 'none';
                                                                        wilayahField.querySelector('input').removeAttribute('required');
                                                                    }
                                                                });
                                                            </script>
                                                            <?php if ($data['level'] == 'admin') { ?>
                                                            <!-- Konfirmasi password untuk edit admin - Role-Based Access Control: Verifikasi identitas admin sebelum perubahan sensitif -->
                                                            <div class="mb-3">
                                                                <label>Konfirmasi Password Admin Saat Ini</label>
                                                                <input type="password" name="admin_password" class="form-control" required>
                                                            </div>
                                                            <?php } ?>
                                                            <div class="mb-3">
                                                                <p>Apakah Anda yakin ingin mengubah data ini?</p>
                                                            </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" name="edit_petugas" class="btn btn-primary">Simpan</button>
                                                    </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- modal RESET -->
                                        <div class="modal fade" id="reset<?= $data['id_petugas'] ?>" tabindex="-1" aria-labelledby="resetLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="resetLabel">Reset Password</h1>
                                                        <button type="button" class="btn-close bg-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="edit_data.php" method="POST">
                                                            <input type="hidden" name="id_petugas" value="<?= $data['id_petugas']; ?>">
                                                            <p>Yakin reset password <?= $data['nama_petugas']; ?> ke default (123456)?</p>
                                                            <?php if ($data['level'] == 'admin') { ?>
                                                            <!-- Konfirmasi password untuk reset admin -->
                                                            <div class="mb-3">
                                                                <label>Konfirmasi Password Admin Saat Ini</label>
                                                                <input type="password" name="admin_password" class="form-control" required>
                                                            </div>
                                                            <?php } ?>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" name="reset_petugas" class="btn btn-warning">Reset</button>
                                                    </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if ($data['level'] != 'admin') { ?>
                                        <!-- modal HAPUS (hanya untuk non-admin) -->
                                        <div class="modal fade" id="hapus<?= $data['id_petugas'] ?>" tabindex="-1" aria-labelledby="hapusLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="hapusLabel">Hapus Data</h1>
                                                        <button type="button" class="btn-close bg-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="edit_data.php" method="POST">
                                                            <input type="hidden" name="id_petugas" class="form-control" value="<?= $data['id_petugas']; ?>">
                                                            <p>Yakin mau dihapus data <br> <?= $data['nama_petugas']; ?>?</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" name="hapus_petugas" value="hapus_petugas" class="btn btn-danger">Hapus</button>
                                                    </div>
                                                    </form>

                                                </div>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        <!-- /modal-HAPUS -->
                                        <!-- /HAPUS -->
                                    </td>
                                </tr>
                            <?php  } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal TAMBAH PETUGAS-->
<div class="modal fade" id="tambah" tabindex="-1" aria-labelledby="verifikasiLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="verifikasiLabel">Tambah Data Petugas</h1>
                <button type="button" class="btn-close bg-dark" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <div class="row mb-3">
                        <label class="label col-md-4">Nama Lengkap</label>
                        <div class="col-md-8">
                            <input type="text" name="nama" class="form-control" placeholder="Masukan Nama" required autocomplete="off">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="label col-md-4">Username</label>
                        <div class="col-md-8">
                            <input type="text" name="username" class="form-control" placeholder="Masukan Username" required autocomplete="off">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="label col-md-4">Password</label>
                        <div class="col-md-8">
                            <input type="password" name="password" class="form-control" placeholder="Masukan Password" required autocomplete="off">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="label col-md-4">No.Telpon</label>
                        <div class="col-md-8">
                            <input type="number" name="telp" class="form-control" placeholder="Masukan Telpon" required autocomplete="off">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="label col-md-4">Level</label>
                        <div class="col-md-8">
                            <select name="level" id="level_select" class="form-control" required>
                                <option value="admin">Admin</option>
                                <option value="lurah">Lurah</option>
                                <option value="kepala_lingkungan">Kepala Lingkungan</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3" id="wilayah_field" style="display: none;">
                        <label class="label col-md-4">Wilayah</label>
                        <div class="col-md-8">
                            <select name="wilayah" class="form-control">
                                <option value="">-- Pilih Lingkungan --</option>
                                <option value="Lingkungan 1">Lingkungan 1</option>
                                <option value="Lingkungan 2">Lingkungan 2</option>
                                <option value="Lingkungan 3">Lingkungan 3</option>
                                <option value="Lingkungan 4">Lingkungan 4</option>
                                <option value="Lingkungan 5">Lingkungan 5</option>
                            </select>
                        </div>
                    </div>
                    <script>
                        document.getElementById('level_select').addEventListener('change', function() {
                            var wilayahField = document.getElementById('wilayah_field');
                            var wilayahSelect = wilayahField.querySelector('select');
                            if (this.value === 'kepala_lingkungan') {
                                wilayahField.style.display = 'block';
                                wilayahSelect.setAttribute('required', 'required');
                            } else {
                                wilayahField.style.display = 'none';
                                wilayahSelect.removeAttribute('required');
                            }
                        });
                    </script>
            </div>
            <div class="modal-footer">
                <button type="submit" name="kirim" class="btn btn-success">Kirim</button>
            </div>
            </form>
        </div>
    </div>
</div>
<!-- /modal- TAMBAH PETUGAS -->
<?php

include '../config/koneksi.php';
if (isset($_POST["kirim"])) {

    //TANGKAP DATA DARI VAR POST DI DALAM FORM
    $nama = mysqli_real_escape_string($conn, $_POST["nama"]);
    $username = mysqli_real_escape_string($conn, $_POST["username"]);
    $password = md5($_POST["password"]);
    $telp = mysqli_real_escape_string($conn, $_POST["telp"]);
    $level = mysqli_real_escape_string($conn, $_POST["level"]);
    $raw_wilayah = isset($_POST["wilayah"]) ? trim($_POST["wilayah"]) : '';

    // Normalize wilayah agar sama dengan form pengaduan
    if (!empty($raw_wilayah) && ctype_digit($raw_wilayah)) {
        $wilayah = 'Wilayah ' . $raw_wilayah;
    } elseif (!empty($raw_wilayah) && stripos($raw_wilayah, 'wilayah') !== 0) {
        $wilayah = 'Wilayah ' . str_replace('Wilayah', '', $raw_wilayah);
    } else {
        $wilayah = $raw_wilayah;
    }

    if ($level != 'kepala_lingkungan') {
        $wilayah = '';
    }

    //INSERT DATA KE TABEL PETUGAS
    $query = mysqli_query($conn, "INSERT INTO petugas (nama_petugas, username, password, telp, level, wilayah) 
                        VALUES ('$nama','$username','$password','$telp','$level', '$wilayah') ");


    //Pengkondisian SETELAH INSERT AKAN DI BAWA KEMANA
    if ($query) {

        echo "
                        <script>
                         alert('Data Petugas Berhasil Ditambahkan');
                         document.location.href='index.php?page=petugas';
                     </script>
                 ";
    } else {
        echo "
                     <script>
                         alert('Data Petugas Gagal Ditambahkan');
                         document.location.href='index.php?page=petugas';
                     </script>
                 ";
    }
}

?>
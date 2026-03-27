<?php
session_start();
include "../config/koneksi.php";
include "../config/functions.php";

//untuk hapus pengaduan
if (isset($_POST['hapus_pengaduan'])) {
    check_access(['admin']);
    //menampung data dari id_pengaduan
    $id_pengaduan = mysqli_real_escape_string($conn, $_POST["id_pengaduan"]);

    //sintaks sql untuk mengambil data dari pengaduan
    $query = mysqli_query($conn, "SELECT * FROM pengaduan WHERE id_pengaduan = '$id_pengaduan'");

    // Cek Jika Data tidak kosong
    if ($query && mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_array($query);

        // Hapus Gambar
        if (is_file("../database/img/" . $data['foto'])) {
            unlink("../database/img/" . $data['foto']);
        }

        // Hapus data yg id_pengaduan = $id_pengaduan
        mysqli_query($conn, "DELETE FROM pengaduan WHERE id_pengaduan = '$id_pengaduan'");
        mysqli_query($conn, "DELETE FROM tanggapan WHERE id_pengaduan = '$id_pengaduan'");
        echo "<script>
                 alert('Data Berhasil di Hapus');
                document.location.href='index.php?page=pengaduan';
            </script>";
    } else {
        echo "<script>
                 alert('Data Gagal di HAPUS');
                document.location.href='index.php?page=pengaduan';
            </script>";
    }
}

//untuk hapus Tanggapan
if (isset($_POST['hapus_tanggapan'])) {
    check_access(['admin']);
    //menampung data dari id_tangagpan dan id_pengaduan
    $id_tanggapan = mysqli_real_escape_string($conn, $_POST["id_tanggapan"]);
    $id_pengaduan = mysqli_real_escape_string($conn, $_POST["id_pengaduan"]);

    //sintaks sql untuk menghapus pengaduan dan tanggapan
    $h_tanggapan = mysqli_query($conn, "DELETE FROM tanggapan WHERE id_tanggapan = '$id_tanggapan'");
    $h_pengaduan = mysqli_query($conn, "DELETE FROM pengaduan WHERE id_pengaduan = '$id_pengaduan'");
    if ($h_tanggapan && $h_pengaduan) {
        echo "<script>
                 alert('Data Berhasil di Hapus');
                document.location.href='index.php?page=tanggapan';
            </script>";
    } else {
        echo "<script>
                 alert('Data Gagal di HAPUS');
                document.location.href='index.php?page=tanggapan';
            </script>";
    }
}

//untuk hapus Petugas
if (isset($_POST['hapus_petugas'])) {
    check_access(['admin']);
    //menampung data id_petugas
    $id_petugas = mysqli_real_escape_string($conn, $_POST["id_petugas"]);

    //sintaks sql untuk menghapus data petugas
    $delete = mysqli_query($conn, "DELETE FROM petugas WHERE id_petugas = '$id_petugas'");
    if ($delete) {
        echo "<script>
                 alert('Data Berhasil di Hapus');
                document.location.href='index.php?page=petugas';
            </script>";
    } else {
        echo "<script>
                 alert('Data Gagal di HAPUS');
                document.location.href='index.php?page=petugas';
            </script>";
    }
}

//untuk hapus masyarakat
if (isset($_POST['hapus_masyarakat'])) {
    check_access(['admin']);
    //menampung data dari nik
    $nik = mysqli_real_escape_string($conn, $_POST["nik"]);

    //sintaks sql untuk menghapus data masyarakat
    $delete = mysqli_query($conn, "DELETE FROM masyarakat WHERE nik = '$nik'");
    if ($delete) {
        echo "<script>
                 alert('Data Berhasil di Hapus');
                document.location.href='index.php?page=masyarakat';
            </script>";
    } else {
        echo "<script>
                 alert('Data Gagal di HAPUS');
                document.location.href='index.php?page=masyarakat';
            </script>";
    }
}

//untuk edit masyarakat
//untuk edit masyarakat
if (isset($_POST['edit_masyarakat'])) {
    check_access(['admin']);
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $telp = mysqli_real_escape_string($conn, $_POST['telp']);

    $query = mysqli_query($conn, "UPDATE masyarakat SET nama='$nama', username='$username', telp='$telp' WHERE nik='$nik'");
    if ($query) {
        echo "<script>alert('Data berhasil diupdate'); document.location.href='index.php?page=masyarakat';</script>";
    } else {
        echo "<script>alert('Gagal update data'); document.location.href='index.php?page=masyarakat';</script>";
    }
}

//untuk reset password masyarakat
if (isset($_POST['reset_masyarakat'])) {
    check_access(['admin']);
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);
    $password = md5('123456'); // default password

    $query = mysqli_query($conn, "UPDATE masyarakat SET password='$password' WHERE nik='$nik'");
    if ($query) {
        echo "<script>alert('Password berhasil direset ke 123456'); document.location.href='index.php?page=masyarakat';</script>";
    } else {
        echo "<script>alert('Gagal reset password'); document.location.href='index.php?page=masyarakat';</script>";
    }
}

//untuk hapus masyarakat
if (isset($_POST['hapus_masyarakat'])) {
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);

    $query = mysqli_query($conn, "DELETE FROM masyarakat WHERE nik='$nik'");
    if ($query) {
        echo "<script>alert('Data berhasil dihapus'); document.location.href='index.php?page=masyarakat';</script>";
    } else {
        echo "<script>alert('Gagal hapus data'); document.location.href='index.php?page=masyarakat';</script>";
    }
}

//untuk edit petugas
if (isset($_POST['edit_petugas'])) {
    check_access(['admin']);
    $id_petugas = mysqli_real_escape_string($conn, $_POST['id_petugas']);
    $nama_petugas = mysqli_real_escape_string($conn, $_POST['nama_petugas']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $telp = mysqli_real_escape_string($conn, $_POST['telp']);
    $level = mysqli_real_escape_string($conn, $_POST['level']);
    $raw_wilayah = isset($_POST['wilayah']) ? trim($_POST['wilayah']) : '';

    // Normalize wilayah agar konsisten dengan form pengaduan
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

    // Pencegahan Human Error: Admin tidak boleh menurunkan level dirinya sendiri
    if ($id_petugas == $_SESSION['id_petugas'] && $level != 'admin') {
        echo "<script>alert('Anda tidak boleh menurunkan level admin sendiri'); document.location.href='index.php?page=petugas';</script>";
        exit;
    }

    // Verifikasi password admin untuk perubahan sensitif (level admin) - Role-Based Access Control
    $target_level = mysqli_fetch_assoc(mysqli_query($conn, "SELECT level FROM petugas WHERE id_petugas='$id_petugas'"))['level'];
    if ($target_level == 'admin' && isset($_POST['admin_password'])) {
        $admin_pass_input = md5($_POST['admin_password']);
        $admin_pass_db = mysqli_fetch_assoc(mysqli_query($conn, "SELECT password FROM petugas WHERE id_petugas='{$_SESSION['id_petugas']}'"))['password'];
        if ($admin_pass_input != $admin_pass_db) {
            echo "<script>alert('Password admin salah'); document.location.href='index.php?page=petugas';</script>";
            exit;
        }
    }

    $query = mysqli_query($conn, "UPDATE petugas SET nama_petugas='$nama_petugas', username='$username', telp='$telp', level='$level', wilayah='$wilayah' WHERE id_petugas='$id_petugas'");
    if ($query) {
        echo "<script>alert('Data berhasil diupdate'); document.location.href='index.php?page=petugas';</script>";
    } else {
        echo "<script>alert('Gagal update data'); document.location.href='index.php?page=petugas';</script>";
    }
}

//untuk reset password petugas
if (isset($_POST['reset_petugas'])) {
    check_access(['admin']);
    $id_petugas = mysqli_real_escape_string($conn, $_POST['id_petugas']);
    $password = md5('123456'); // default password

    // Verifikasi password admin untuk reset admin
    $target_level = mysqli_fetch_assoc(mysqli_query($conn, "SELECT level FROM petugas WHERE id_petugas='$id_petugas'"))['level'];
    if ($target_level == 'admin' && isset($_POST['admin_password'])) {
        $admin_pass_input = md5($_POST['admin_password']);
        $admin_pass_db = mysqli_fetch_assoc(mysqli_query($conn, "SELECT password FROM petugas WHERE id_petugas='{$_SESSION['id_petugas']}'"))['password'];
        if ($admin_pass_input != $admin_pass_db) {
            echo "<script>alert('Password admin salah'); document.location.href='index.php?page=petugas';</script>";
            exit;
        }
    }

    $query = mysqli_query($conn, "UPDATE petugas SET password='$password' WHERE id_petugas='$id_petugas'");
    if ($query) {
        echo "<script>alert('Password berhasil direset ke 123456'); document.location.href='index.php?page=petugas';</script>";
    } else {
        echo "<script>alert('Gagal reset password'); document.location.href='index.php?page=petugas';</script>";
    }
}

//untuk hapus petugas
if (isset($_POST['hapus_petugas'])) {
    $id_petugas = mysqli_real_escape_string($conn, $_POST['id_petugas']);

    // Role-Based Access Control: Mencegah penghapusan akun admin
    $level = mysqli_fetch_assoc(mysqli_query($conn, "SELECT level FROM petugas WHERE id_petugas='$id_petugas'"))['level'];
    if ($level == 'admin') {
        echo "<script>alert('Tidak boleh menghapus akun admin'); document.location.href='index.php?page=petugas';</script>";
        exit;
    }

    // Lakukan penghapusan data petugas jika validasi lolos
    $query = mysqli_query($conn, "DELETE FROM petugas WHERE id_petugas='$id_petugas'");
    if ($query) {
        echo "<script>alert('Data berhasil dihapus'); document.location.href='index.php?page=petugas';</script>";
    } else {
        echo "<script>alert('Gagal hapus data'); document.location.href='index.php?page=petugas';</script>";
    }
}

// Handler untuk validasi pengaduan oleh kepala_lingkungan
if (isset($_POST['validasi_pengaduan'])) {
    check_access(['kepala_lingkungan']);
    $id_pengaduan = mysqli_real_escape_string($conn, $_POST['id_pengaduan']);
    $action = mysqli_real_escape_string($conn, $_POST['action'] ?? '');
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan_validasi'] ?? '');
    $id_kepala = $_SESSION['id_petugas'];

        // Use helper validate_complaint to handle approve/reject/unapprove rules
        $ok = validate_complaint($conn, $id_pengaduan, $action, $catatan, $id_kepala);
        if (is_array($ok) && !empty($ok['success'])) {
            if ($action == 'terima') {
                $msg = 'Laporan berhasil divalidasi. Menunggu approval dari Lurah.';
            } elseif ($action == 'tolak') {
                $msg = 'Laporan ditolak.';
            } elseif ($action == 'batalkan_validasi') {
                $msg = 'Validasi dibatalkan. Laporan dikembalikan ke status awal.';
            } elseif ($action == 'unapprove') {
                $msg = 'Approve dibatalkan.';
            } else {
                $msg = 'Aksi validasi berhasil.';
            }
            echo "<script>alert('$msg'); document.location.href='index.php?page=pengaduan_kepala';</script>";
        } else {
            $err = is_array($ok) && !empty($ok['error']) ? $ok['error'] : 'Aksi gagal. Mungkin laporan sudah didisposisi sehingga tidak bisa dibatalkan atau aksi tidak valid.';
            echo "<script>alert('$err'); document.location.href='index.php?page=pengaduan_kepala';</script>";
        }
}

// Handler untuk dismiss rejected report dari KL dashboard
if (isset($_POST['dismiss_report'])) {
    check_access(['kepala_lingkungan']);
    $id_pengaduan = mysqli_real_escape_string($conn, $_POST['id_pengaduan']);
    $dismiss_reason = isset($_POST['dismiss_reason']) ? mysqli_real_escape_string($conn, $_POST['dismiss_reason']) : '';
    $id_kepala = $_SESSION['id_petugas'];
    
    $ok = dismiss_rejected_report($conn, $id_pengaduan, $id_kepala, $dismiss_reason);
    if (is_array($ok) && !empty($ok['success'])) {
        echo "<script>alert('Laporan berhasil dihapus dari dashboard dan alasan dikirim ke masyarakat.'); document.location.href='index.php?page=pengaduan_kepala';</script>";
    } else {
        $err = is_array($ok) && !empty($ok['error']) ? $ok['error'] : 'Gagal menghapus laporan.';
        echo "<script>alert('$err'); document.location.href='index.php?page=pengaduan_kepala';</script>";
    }
}

// Handler untuk tanggapan awal oleh kepala_lingkungan
if (isset($_POST['tanggapan_kepala'])) {
    check_access(['kepala_lingkungan']);
    $id_pengaduan = mysqli_real_escape_string($conn, $_POST['id_pengaduan']);
    $tanggapan = mysqli_real_escape_string($conn, $_POST['tanggapan_awal']);
    $arahan = mysqli_real_escape_string($conn, $_POST['arahan_teknis']);
    $id_kepala = $_SESSION['id_petugas'];

    // Insert tanggapan
    $query = mysqli_query($conn, "INSERT INTO tanggapan (id_pengaduan, tgl_tanggapan, tanggapan, id_petugas) VALUES ('$id_pengaduan', NOW(), 'Tanggapan Awal: $tanggapan. Arahan: $arahan', '$id_kepala')");
    if ($query) {
        echo "<script>alert('Tanggapan berhasil ditambah'); document.location.href='index.php?page=pengaduan_kepala';</script>";
    } else {
        echo "<script>alert('Tanggapan gagal'); document.location.href='index.php?page=pengaduan_kepala';</script>";
    }
}

// Handler untuk update progress oleh kepala_lingkungan
if (isset($_POST['update_progress'])) {
    // Server-side guard: role
    if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'kepala_lingkungan') {
        echo "<script>alert('Akses ditolak'); document.location.href='index.php?page=pengaduan_kepala';</script>";
        exit;
    }
    
    $id_pengaduan = (int)$_POST['id_pengaduan'];
    $progress = mysqli_real_escape_string($conn, $_POST['progress_update']);
    $progress_status = mysqli_real_escape_string($conn, $_POST['progress_status']);
    $id_kepala = (int)$_SESSION['id_petugas'];
    
    // Server-side guard: ownership & current state
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT progress_status, id_kepala_lingkungan, awaiting_petugas_validation, is_assigned, validated_by_petugas, is_approved, status FROM pengaduan WHERE id_pengaduan=$id_pengaduan"));
    if (!$row) {
        echo "<script>alert('Data tidak ditemukan'); document.location.href='index.php?page=pengaduan_kepala';</script>";
        exit;
    }
    if ((int)$row['id_kepala_lingkungan'] !== $id_kepala) {
        echo "<script>alert('Bukan tanggung jawab Anda'); document.location.href='index.php?page=pengaduan_kepala';</script>";
        exit;
    }
    // Lock if already validated by KL themselves (no more petugas validation)
    // Allow KL to update progress freely until they mark as completed
    
    // Idempotency: jika status sama, jangan update
    if ($row['progress_status'] === $progress_status) {
        echo "<script>alert('Status progress sudah sesuai, tidak ada perubahan.'); document.location.href='index.php?page=pengaduan_kepala';</script>";
        exit;
    }
    
    // Only allow if approved by Lurah (new workflow: Lurah approves, no disposisi needed)
    if (empty($row) || !isset($row['is_approved']) || $row['is_approved'] != 1 || $row['status'] != 'opened') {
        echo "<script>alert('Progress dikunci: laporan belum di-approve oleh Lurah.'); document.location.href='index.php?page=pengaduan_kepala';</script>";
        exit;
    }
    
    // Transaction for atomicity
    mysqli_begin_transaction($conn);
    try {
        // Append to progress_log
        $current_log = $row['progress_log'] ?? '';
        $new_log = $current_log ? $current_log . "\n" . date('Y-m-d H:i:s') . ": $progress" : date('Y-m-d H:i:s') . ": $progress";
        
        // Update query
        $additional_query = "";
        $old_status = $row['progress_status'];
        if ($old_status == 'pending') {
            $additional_query .= ", progress_ever_changed=1";
        }
        if ($progress_status == 'completed') {
            $additional_query .= ", awaiting_petugas_validation=1, can_close=0";
        } elseif ($progress_status == 'on_progress' && $old_status == 'completed') {
            // Reset jika kembali ke on_progress
            $additional_query .= ", awaiting_petugas_validation=0, can_close=0, validated_by_petugas=0";
        }
        
        $query = mysqli_query($conn, "UPDATE pengaduan SET progress_log='$new_log', progress_status='$progress_status', last_progress_update=NOW(), updated_progress_by=$id_kepala $additional_query WHERE id_pengaduan=$id_pengaduan");
        if (!$query) throw new Exception('Update failed');
        
        // Insert tanggapan/audit
        $tanggapan_msg = "KL update progress to $progress_status: $progress";
        mysqli_query($conn, "INSERT INTO tanggapan (id_pengaduan, tgl_tanggapan, tanggapan, id_petugas) VALUES ($id_pengaduan, NOW(), '$tanggapan_msg', $id_kepala)");
        
        // Notification: completed by KL, ready for closure
        if ($progress_status == 'completed') {
            mysqli_query($conn, "INSERT INTO notifications (nik, type, message, link, created_at) VALUES ('-', 'report_completed', 'Laporan #$id_pengaduan telah diselesaikan oleh Kepala Lingkungan, siap untuk ditutup', 'admin/index.php?page=pengaduan_kepala', NOW())");
        }
        
        mysqli_commit($conn);
        echo "<script>alert('Progress berhasil diupdate ke $progress_status'); document.location.href='index.php?page=pengaduan_kepala';</script>";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Progress update failed: " . $e->getMessage());
        echo "<script>alert('Gagal update progress'); document.location.href='index.php?page=pengaduan_kepala';</script>";
    }
}

// Handler untuk Lurah
if (isset($_POST['disposisi_lurah']) || isset($_POST['tolak_lurah']) || isset($_POST['update_status_lurah'])) {
    check_access(['lurah']);

    $id_pengaduan = mysqli_real_escape_string($conn, $_POST['id_pengaduan']);
    $tanggapan = isset($_POST['tanggapan']) ? mysqli_real_escape_string($conn, $_POST['tanggapan']) : '';

    if (isset($_POST['disposisi_lurah'])) {
        $id_kepala_lingkungan = mysqli_real_escape_string($conn, $_POST['id_kepala_lingkungan']);

        // Update pengaduan: set id_kepala_lingkungan, id_lurah, status to 'opened'
        $query = mysqli_query($conn, "UPDATE pengaduan SET id_kepala_lingkungan='$id_kepala_lingkungan', id_lurah='{$_SESSION['id_petugas']}', status='opened' WHERE id_pengaduan='$id_pengaduan'");

        if ($query) {
            // Buat tanggapan jika ada
            if (!empty($tanggapan)) {
                create_tanggapan($conn, [
                    'id_pengaduan' => $id_pengaduan,
                    'tanggapan' => $tanggapan,
                    'id_petugas' => $_SESSION['id_petugas']
                ]);
            }
            echo "<script>alert('Pengaduan berhasil didisposisi ke Kepala Lingkungan'); document.location.href='index.php?page=pengaduan_lurah';</script>";
        } else {
            echo "<script>alert('Gagal disposisi pengaduan'); document.location.href='index.php?page=pengaduan_lurah';</script>";
        }
    } elseif (isset($_POST['tolak_lurah'])) {
        // Update status to 'closed'
        $query = mysqli_query($conn, "UPDATE pengaduan SET status='closed', id_lurah='{$_SESSION['id_petugas']}' WHERE id_pengaduan='$id_pengaduan'");

        if ($query) {
            // Buat tanggapan penolakan
            create_tanggapan($conn, [
                'id_pengaduan' => $id_pengaduan,
                'tanggapan' => $tanggapan,
                'id_petugas' => $_SESSION['id_petugas']
            ]);
            echo "<script>alert('Pengaduan berhasil ditolak'); document.location.href='index.php?page=pengaduan_lurah';</script>";
        } else {
            echo "<script>alert('Gagal tolak pengaduan'); document.location.href='index.php?page=pengaduan_lurah';</script>";
        }
    } elseif (isset($_POST['update_status_lurah'])) {
        $status = mysqli_real_escape_string($conn, $_POST['status']);

        // Update status
        $query = mysqli_query($conn, "UPDATE pengaduan SET status='$status' WHERE id_pengaduan='$id_pengaduan'");

        if ($query) {
            // Buat tanggapan jika ada
            if (!empty($tanggapan)) {
                create_tanggapan($conn, [
                    'id_pengaduan' => $id_pengaduan,
                    'tanggapan' => $tanggapan,
                    'id_petugas' => $_SESSION['id_petugas']
                ]);
            }
            echo "<script>alert('Status pengaduan berhasil diupdate'); document.location.href='index.php?page=pengaduan_lurah';</script>";
        } else {
            echo "<script>alert('Gagal update status pengaduan'); document.location.href='index.php?page=pengaduan_lurah';</script>";
        }
    }
}

// Handler untuk Staff Kelurahan lapor progress - DISABLED (petugas role removed)
// Staff no longer exists in the system; Kepala_Lingkungan handles all progress updates
if (isset($_POST['lapor_progress_staff'])) {
    echo "<script>alert('Halaman petugas dinonaktifkan.'); document.location.href='../index.php?page=login';</script>";
    exit;
}

// Handle Kepala Lingkungan actions
if (isset($_POST['validate'])) {
    check_access(['kepala_lingkungan']);
    $id_pengaduan = $_POST['id_pengaduan'];
    $action = $_POST['action'];
    $catatan = $_POST['catatan'] ?? '';

    $res = validate_complaint($conn, $id_pengaduan, $action, $catatan, $_SESSION['id_petugas']);
    if (is_array($res) && !empty($res['success'])) {
        if ($action == 'terima') {
            forward_to_lurah($conn, $id_pengaduan, $_SESSION['id_petugas']);
            $msg = 'Laporan diterima dan diteruskan ke Lurah';
        } else {
            $msg = 'Laporan ditolak';
        }
        echo "<script>alert('$msg'); document.location.href='index.php?page=pengaduan';</script>";
        exit;
    } else {
        $err = is_array($res) && !empty($res['error']) ? $res['error'] : 'Aksi validasi gagal';
        echo "<script>alert('$err'); document.location.href='index.php?page=pengaduan';</script>";
        exit;
    }
}

// Handler for KL close action (from data_pengaduan_kepala)
if (isset($_POST['kl_close'])) {
    check_access(['kepala_lingkungan']);
    $id_pengaduan = mysqli_real_escape_string($conn, $_POST['id_pengaduan']);
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan_close'] ?? '');
    $proof_path = null;

    // Kepala Lingkungan can now close directly (petugas validation removed)

    // Handle file upload if provided (server-side validation)
    $upload = validate_and_store_image($_FILES['completion_proof'] ?? null);
    if (!$upload['success']) {
        echo "<script>alert('Upload error: {$upload['error']}'); document.location.href='index.php?page=pengaduan_kepala';</script>";
        exit;
    }
    $proof_path = $upload['filename'] ?? null;

    // Call helper to perform close
    $res = kl_close_complaint($conn, $id_pengaduan, $_SESSION['id_petugas'], $catatan, $proof_path);
        if (is_array($res) && !empty($res['success'])) {
            echo "<script>alert('Laporan berhasil ditutup.'); document.location.href='index.php?page=pengaduan_kepala';</script>";
            exit;
        } else {
            $err = is_array($res) && !empty($res['error']) ? $res['error'] : 'Gagal menutup laporan.';
            echo "<script>alert('$err'); document.location.href='index.php?page=pengaduan_kepala';</script>";
            exit;
        }
}

// Handle Lurah disposisi - DEPRECATED (petugas role removed)
// Lurah disposisi now routes via pengaduan_lurah.php which handles it directly
if (isset($_POST['disposisi'])) {
    // This endpoint is now legacy; actual disposisi is handled server-side in pengaduan_lurah.php
    echo "<script>alert('Disposisi diproses melalui halaman pengaduan_lurah'); document.location.href='index.php?page=pengaduan_lurah';</script>";
    exit;
}

if (isset($_POST['close_complaint'])) {
    // Lurah tidak diizinkan menutup laporan dalam alur ini
    check_access(['lurah']);
    echo "<script>alert('Aksi tidak diizinkan: Lurah tidak dapat menutup laporan.'); document.location.href='index.php?page=pengaduan_lurah';</script>";
    exit;
}

// Handle Staff actions - DISABLED (petugas role removed)
// Staff/petugas no longer exist in the system
if (isset($_POST['update_progress'])) {
    // Check if coming from petugas or kepala_lingkungan
    if (isset($_SESSION['level']) && $_SESSION['level'] === 'kepala_lingkungan') {
        // KL update_progress already handled above
        echo "<script>alert('Progress handler mismatch; refresh halaman'); history.back();</script>";
        exit;
    }
    // Block petugas access
    echo "<script>alert('Halaman petugas dinonaktifkan.'); document.location.href='../index.php?page=login';</script>";
    exit;
}


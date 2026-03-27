<?php
// config/functions.php - Helper functions for the complaint management system

// ============================================================
// DateTime Helper Functions (timezone-aware, strtotime-independent)
// ============================================================

/**
 * Format MySQL DATETIME to readable format (DD/MM/YYYY HH:MM)
 * Avoids relying on strtotime() which can have timezone issues
 * 
 * @param string $timestamp MySQL DATETIME string (YYYY-MM-DD HH:MM:SS)
 * @return string Formatted date string (DD/MM/YYYY HH:MM) or '-' if empty
 */
function format_datetime($timestamp) {
    if (empty($timestamp) || $timestamp == '0000-00-00 00:00:00') {
        return '-';
    }
    // Allow formats: YYYY-MM-DD, YYYY-MM-DD HH:MM, YYYY-MM-DD HH:MM:SS
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $timestamp, $matches)) {
        $year = $matches[1];
        $month = $matches[2];
        $day = $matches[3];
        return "$day/$month/$year"; // date-only, no time component
    }
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2})$/', $timestamp, $matches)) {
        $year = $matches[1];
        $month = $matches[2];
        $day = $matches[3];
        $hour = $matches[4];
        $min = $matches[5];
        return "$day/$month/$year $hour:$min";
    }
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2}):(\d{2})$/', $timestamp, $matches)) {
        $year = $matches[1];
        $month = $matches[2];
        $day = $matches[3];
        $hour = $matches[4];
        $min = $matches[5];
        return "$day/$month/$year $hour:$min";
    }
    // Fallback: try strtotime with local timezone if possible
    $ts = strtotime($timestamp);
    if ($ts !== false) {
        return date('d/m/Y H:i', $ts);
    }
    return 'N/A';
}

/**
 * Convert MySQL DATETIME to Unix timestamp for reliable comparison
 * Uses mktime() for accuracy instead of strtotime()
 * 
 * @param string $datetime_str MySQL DATETIME string (YYYY-MM-DD HH:MM:SS)
 * @return int Unix timestamp or 0 if invalid
 */
function datetime_to_timestamp($datetime_str) {
    if (empty($datetime_str) || $datetime_str == '0000-00-00 00:00:00') {
        return 0;
    }
    // Allow YYYY-MM-DD, YYYY-MM-DD HH:MM, and YYYY-MM-DD HH:MM:SS
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $datetime_str, $matches)) {
        return mktime(0, 0, 0, (int)$matches[2], (int)$matches[3], (int)$matches[1]);
    }
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2})$/', $datetime_str, $matches)) {
        return mktime((int)$matches[4], (int)$matches[5], 0, (int)$matches[2], (int)$matches[3], (int)$matches[1]);
    }
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2}):(\d{2})$/', $datetime_str, $matches)) {
        return mktime((int)$matches[4], (int)$matches[5], (int)$matches[6], (int)$matches[2], (int)$matches[3], (int)$matches[1]);
    }
    $ts = strtotime($datetime_str);
    return ($ts !== false) ? $ts : 0;
}

/**
 * Pick the best datetime among candidate and fallbacks (avoid 00:00 default)
 *
 * @param string $candidate
 * @param array $fallbacks
 * @return string
 */
function pick_best_datetime($candidate, $fallbacks = []) {
    if (empty($candidate) || $candidate == '0000-00-00 00:00:00') {
        $candidate = null;
    }

    // If candidate is 'YYYY-MM-DD' or 'YYYY-MM-DD 00:00:00', prefer fallback with time component if available.
    $is_sound_date = false;
    if ($candidate && preg_match('/^\d{4}-\d{2}-\d{2}$/', $candidate)) {
        $is_sound_date = true;
    }
    if ($candidate && preg_match('/^\d{4}-\d{2}-\d{2}\s+00:00:00$/', $candidate)) {
        $is_sound_date = true;
    }

    if ($is_sound_date) {
        foreach ($fallbacks as $fb) {
            if (empty($fb) || $fb == '0000-00-00 00:00:00') {
                continue;
            }
            if (!preg_match('/^\d{4}-\d{2}-\d{2}(\s+00:00:00)?$/', $fb)) {
                return $fb;
            }
            if (preg_match('/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}$/', $fb) && !preg_match('/00:00:00$/', $fb)) {
                return $fb;
            }
        }
    }

    if (!empty($candidate)) {
        return $candidate;
    }

    foreach ($fallbacks as $fb) {
        if (!empty($fb) && $fb != '0000-00-00 00:00:00') {
            return $fb;
        }
    }

    return '';
}

// ============================================================
// End DateTime Helper Functions
// ============================================================

// Validate and store uploaded image files (server-side)
function validate_and_store_image($file) {
    // Return structure: ['success'=>bool,'error'=>string,'filename'=>string|null]
    if (!isset($file) || empty($file) || (isset($file['error']) && $file['error'] == UPLOAD_ERR_NO_FILE)) {
        return ['success' => true, 'error' => '', 'filename' => null]; // No file provided is allowed by caller
    }

    if (!isset($file['error']) || !isset($file['tmp_name'])) {
        return ['success' => false, 'error' => 'File upload tidak valid (struktur file salah).', 'filename' => null];
    }

    // Handle PHP upload errors
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['success' => false, 'error' => 'File terlalu besar. Maksimum 5MB.', 'filename' => null];
        case UPLOAD_ERR_PARTIAL:
            return ['success' => false, 'error' => 'Upload terputus sebagian. Coba ulangi.', 'filename' => null];
        case UPLOAD_ERR_NO_TMP_DIR:
            return ['success' => false, 'error' => 'Server tidak memiliki temporary folder untuk upload.', 'filename' => null];
        case UPLOAD_ERR_CANT_WRITE:
            return ['success' => false, 'error' => 'Gagal menulis file ke disk.', 'filename' => null];
        default:
            return ['success' => false, 'error' => 'Terjadi kesalahan saat upload file.', 'filename' => null];
    }

    // Size check (5MB)
    $maxBytes = 5 * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        return ['success' => false, 'error' => 'File terlalu besar. Maksimum 5MB.', 'filename' => null];
    }

    // Ensure it's an image
    $tmp = $file['tmp_name'];
    $imgInfo = @getimagesize($tmp);
    if ($imgInfo === false) {
        return ['success' => false, 'error' => 'File bukan gambar yang valid.', 'filename' => null];
    }

    // Validate mime/extension
    $mime = $imgInfo['mime'];
    $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    if (!array_key_exists($mime, $allowedMimes)) {
        return ['success' => false, 'error' => 'Tipe gambar tidak didukung. Gunakan jpg, png, gif, atau webp.', 'filename' => null];
    }

    $ext = $allowedMimes[$mime];
    // Generate safe filename
    $name = time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;

    // Target directory (relative to this file)
    $targetDir = __DIR__ . '/../database/img/';
    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0755, true);
    }

    $targetPath = $targetDir . $name;
    if (!move_uploaded_file($tmp, $targetPath)) {
        return ['success' => false, 'error' => 'Gagal menyimpan file ke server.', 'filename' => null];
    }

    return ['success' => true, 'error' => '', 'filename' => $name];
}

// Function to create a new complaint
function create_pengaduan($koneksi, $data) {
    // Escape inputs for security
    $nik = mysqli_real_escape_string($koneksi, $data['nik']);
    $judul = mysqli_real_escape_string($koneksi, $data['judul_pengaduan']);
    $isi = mysqli_real_escape_string($koneksi, $data['isi_laporan']);
    $foto = mysqli_real_escape_string($koneksi, $data['foto']);
    $wilayah = mysqli_real_escape_string($koneksi, $data['wilayah']);
    $status = 'pending'; // Forced to 'pending'
    
    // Normalize wilayah globally
    $wilayah = normalize_wilayah($wilayah);

    // Do not auto-assign to Kepala Lingkungan - wait for manual validation
    $id_kepala_lingkungan = 'NULL';
    
    // Insert laporan tanpa auto-assign kepala lingkungan
    // Use NOW() from MySQL for consistent timezone handling
    $query = "INSERT INTO pengaduan (tgl_pengaduan, nik, judul_pengaduan, isi_laporan, foto, status, id_kepala_lingkungan, id_lurah, id_staff, wilayah) 
              VALUES (NOW(), '$nik', '$judul', '$isi', '$foto', '$status', $id_kepala_lingkungan, NULL, NULL, '$wilayah')";
    return mysqli_query($koneksi, $query);
}

// Normalize wilayah value to standard form e.g. A/1/Lingkungan 1 -> Lingkungan 1
function normalize_wilayah($wilayah) {
    $raw = trim($wilayah);
    if ($raw === '') {
        return 'Lingkungan tidak ditentukan';
    }

    // Bersihkan awalan/varian
    $raw = preg_replace('/^wilayah\s*/i', '', $raw);
    $raw = trim($raw);

    $mapping = [
        'A' => 'Lingkungan 1',
        'B' => 'Lingkungan 2',
        'C' => 'Lingkungan 3',
        'D' => 'Lingkungan 4',
        'E' => 'Lingkungan 5',
        '1' => 'Lingkungan 1',
        '2' => 'Lingkungan 2',
        '3' => 'Lingkungan 3',
        '4' => 'Lingkungan 4',
        '5' => 'Lingkungan 5',
    ];

    $raw = strtoupper($raw);
    if (isset($mapping[$raw])) {
        return $mapping[$raw];
    }

    // Jika format numeric / string berisi angka, ambil angka terakhir
    if (preg_match('/([1-5])/', $raw, $matches)) {
        return 'Lingkungan ' . $matches[1];
    }

    return 'Lingkungan tidak ditentukan';
}
// Function to set id_kepala_lingkungan for a complaint
function set_kepala_lingkungan($koneksi, $id_pengaduan, $id_petugas) {
    $id_pengaduan = mysqli_real_escape_string($koneksi, $id_pengaduan);
    $id_petugas = mysqli_real_escape_string($koneksi, $id_petugas);
    
    $query = "UPDATE pengaduan SET id_kepala_lingkungan = '$id_petugas' WHERE id_pengaduan = '$id_pengaduan'";
    return mysqli_query($koneksi, $query);
}

// Mark a complaint as opened when Kepala Lingkungan first views it
function mark_opened($koneksi, $id_pengaduan, $id_kepala) {
    $id_pengaduan = mysqli_real_escape_string($koneksi, $id_pengaduan);
    $id_kepala = mysqli_real_escape_string($koneksi, $id_kepala);
    // Only update opened_at/opened_by on first view, without changing validation state.
    $check = mysqli_query($koneksi, "SELECT status FROM pengaduan WHERE id_pengaduan='$id_pengaduan' LIMIT 1");
    $row = $check ? mysqli_fetch_assoc($check) : null;
    if ($row && $row['status'] === 'pending') {
        $query = "UPDATE pengaduan SET opened_at=NOW(), opened_by='$id_kepala' WHERE id_pengaduan='$id_pengaduan'";
        $res = mysqli_query($koneksi, $query);
        if ($res) {
            create_tanggapan($koneksi, ['id_pengaduan' => $id_pengaduan, 'tanggapan' => 'Laporan dibuka oleh Kepala Lingkungan', 'id_petugas' => $id_kepala]);
        }
        return $res;
    }
    return false;
}

// Function to forward complaint to Lurah after KL validation
function forward_to_lurah($koneksi, $id_pengaduan, $id_kepala) {
    // Find Lurah (assuming there's only one)
    $lurah_query = mysqli_query($koneksi, "SELECT id_petugas FROM petugas WHERE level='lurah' LIMIT 1");
    if ($lurah_query && mysqli_num_rows($lurah_query) > 0) {
        $lurah_data = mysqli_fetch_assoc($lurah_query);
        $id_lurah = $lurah_data['id_petugas'];
        // Set id_lurah
        $query = "UPDATE pengaduan SET id_lurah = '$id_lurah' WHERE id_pengaduan = '$id_pengaduan'";
        return mysqli_query($koneksi, $query);
    }
    return false;
}

// Function to update complaint status
function update_status($koneksi, $id_pengaduan, $status) {
    // Generic status updater (restrict to known values)
    $allowed_status = ['pending', 'rejected', 'opened', 'closed'];
    if (!in_array($status, $allowed_status)) {
        return false; // Invalid status
    }

    $id_pengaduan = mysqli_real_escape_string($koneksi, $id_pengaduan);
    $status = mysqli_real_escape_string($koneksi, $status);

    $query = "UPDATE pengaduan SET status = '$status' WHERE id_pengaduan = '$id_pengaduan'";
    return mysqli_query($koneksi, $query);
}

// Function to create a response (tanggapan)
function create_tanggapan($koneksi, $data) {
    $id_pengaduan = mysqli_real_escape_string($koneksi, $data['id_pengaduan']);
    $tanggapan = mysqli_real_escape_string($koneksi, $data['tanggapan']);
    $id_petugas = mysqli_real_escape_string($koneksi, $data['id_petugas']);
    
    // Use NOW() from MySQL for consistent timezone handling
    $query = "INSERT INTO tanggapan (id_pengaduan, tgl_tanggapan, tanggapan, id_petugas) 
              VALUES ('$id_pengaduan', NOW(), '$tanggapan', '$id_petugas')";
    return mysqli_query($koneksi, $query);
}

// Function to check access based on user level
function check_access($allowed_levels = []) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['level']) || !in_array($_SESSION['level'], $allowed_levels)) {
        echo "<script>alert('Anda tidak memiliki akses ke halaman ini'); document.location.href='../index.php';</script>";
        exit;
    }
}

// Function to validate complaint by Kepala Lingkungan
function validate_complaint($koneksi, $id_pengaduan, $action, $catatan, $id_kepala) {
    $action = mysqli_real_escape_string($koneksi, $action);
    $catatan = mysqli_real_escape_string($koneksi, $catatan);
    
    // Check if KL is authorized for this complaint's wilayah
    $complaint_check = mysqli_query($koneksi, "SELECT wilayah FROM pengaduan WHERE id_pengaduan='$id_pengaduan'");
    if (!$complaint_check || mysqli_num_rows($complaint_check) == 0) {
        return ['success' => false, 'error' => 'Pengaduan tidak ditemukan'];
    }
    $complaint = mysqli_fetch_assoc($complaint_check);
    $kl_check = mysqli_query($koneksi, "SELECT wilayah FROM petugas WHERE id_petugas='$id_kepala' AND level='kepala_lingkungan'");
    if (!$kl_check || mysqli_num_rows($kl_check) == 0) {
        return ['success' => false, 'error' => 'Kepala Lingkungan tidak valid'];
    }
    $kl = mysqli_fetch_assoc($kl_check);
    if ($complaint['wilayah'] != $kl['wilayah']) {
        return ['success' => false, 'error' => 'Kepala Lingkungan tidak berwenang untuk wilayah ini'];
    }
    
    // Actions taken by Kepala Lingkungan. Note: opening is handled separately when KL views.
    if ($action == 'terima') {
        // KL validation: only set id_kepala_lingkungan, keep status 'pending', add tanggapan
        // Do NOT set is_approved=1 here - that's for Lurah approval
        // Also, remove any previous 'Validasi dibatalkan' tanggapan to allow re-flow
        mysqli_query($koneksi, "DELETE FROM tanggapan WHERE id_pengaduan='$id_pengaduan' AND tanggapan LIKE 'Validasi dibatalkan%'");
        $query = "UPDATE pengaduan SET id_kepala_lingkungan='$id_kepala' WHERE id_pengaduan='$id_pengaduan'";
    } elseif ($action == 'tolak') {
        // Reject by KL: final
        $query = "UPDATE pengaduan SET is_rejected=1, rejected_by='$id_kepala', rejection_reason='$catatan', rejected_at=NOW(), status='rejected', id_kepala_lingkungan='$id_kepala' WHERE id_pengaduan='$id_pengaduan'";
    } elseif ($action == 'batalkan_validasi') {
        // Cancel KL validation: reset kepala_lingkungan assignment, status reverts to pending, and reset Lurah approval if any, and reset progress
        // Remove KL validation markers so button can reappear
        mysqli_query($koneksi, "DELETE FROM tanggapan WHERE id_pengaduan='$id_pengaduan' AND id_petugas='$id_kepala' AND (tanggapan LIKE 'Validated by KL%' OR tanggapan LIKE 'Rejected by KL%')");
        $query = "UPDATE pengaduan SET id_kepala_lingkungan=NULL, status='pending', is_approved=0, id_lurah=NULL, progress_status='pending', progress_log=NULL, is_rejected=0, rejected_by=NULL, rejection_reason=NULL, rejected_at=NULL WHERE id_pengaduan='$id_pengaduan'";
    } elseif ($action == 'unapprove') {
        // Unapprove only allowed if Lurah hasn't assigned (deprecated - kept for backward compat)
        $check = mysqli_query($koneksi, "SELECT is_assigned FROM pengaduan WHERE id_pengaduan='$id_pengaduan' LIMIT 1");
        $row = $check ? mysqli_fetch_assoc($check) : null;
        if ($row && $row['is_assigned'] == 1) {
            return ['success' => false, 'error' => 'Tidak dapat membatalkan approve setelah disposisi oleh Lurah.']; // Cannot unapprove after disposisi
        }
        $query = "UPDATE pengaduan SET is_approved=0, approved_by=NULL, approved_at=NULL, approval_notes=NULL, status='opened' WHERE id_pengaduan='$id_pengaduan'";
    } else {
        return ['success' => false, 'error' => 'Aksi tidak dikenal'];
    }

    $result = mysqli_query($koneksi, $query);
    if ($result) {
        if ($action == 'terima') {
            $entry = 'Validated by KL';
            if (!empty($catatan)) {
                $entry .= ': ' . $catatan;
            }
            create_tanggapan($koneksi, ['id_pengaduan' => $id_pengaduan, 'tanggapan' => $entry, 'id_petugas' => $id_kepala]);
        } elseif ($action == 'tolak') {
            $entry = 'Rejected by KL';
            if (!empty($catatan)) {
                $entry .= ': ' . $catatan;
            }
            create_tanggapan($koneksi, ['id_pengaduan' => $id_pengaduan, 'tanggapan' => $entry, 'id_petugas' => $id_kepala]);
        } elseif ($action == 'batalkan_validasi') {
            create_tanggapan($koneksi, ['id_pengaduan' => $id_pengaduan, 'tanggapan' => 'Validasi dibatalkan oleh Kepala Lingkungan', 'id_petugas' => $id_kepala]);
        }
        return ['success' => true, 'error' => ''];
    }
    return ['success' => false, 'error' => 'Database error saat melakukan validasi'];
}

// Function: deprecated - petugas role removed; delegates to kl_assign_complaint
function disposisi_to_staff($koneksi, $id_pengaduan, $id_staff, $id_lurah, $catatan) {
    return kl_assign_complaint($koneksi, $id_pengaduan, $id_lurah, $catatan);
}

// Function to assign Kepala_Lingkungan by Lurah (replaces disposisi_to_staff post-refactor)
function kl_assign_complaint($koneksi, $id_pengaduan, $id_lurah, $catatan) {
    $catatan = mysqli_real_escape_string($koneksi, $catatan);
    $query = "UPDATE pengaduan SET is_assigned=1, assigned_by='$id_lurah', assigned_petugas_id=NULL, assigned_at=NOW(), id_lurah='$id_lurah', id_staff=NULL, can_unapprove=0, is_workable=1, non_workable_reason=NULL WHERE id_pengaduan='$id_pengaduan'";
    $result = mysqli_query($koneksi, $query);
    if ($result && !empty($catatan)) {
        create_tanggapan($koneksi, ['id_pengaduan' => $id_pengaduan, 'tanggapan' => 'Disposisi ke Kepala Lingkungan: ' . $catatan, 'id_petugas' => $id_lurah]);
    }
    return $result ? ['success' => true, 'error' => ''] : ['success' => false, 'error' => 'Gagal melakukan disposisi ke Kepala Lingkungan'];
}

// Function to update progress by Kepala_Lingkungan (new - replaces update_progress_staff)
function update_progress_kl($koneksi, $id_pengaduan, $progress_desc, $progress_status, $id_kepala) {
    $progress_desc = mysqli_real_escape_string($koneksi, $progress_desc);
    $progress_status = mysqli_real_escape_string($koneksi, $progress_status);
    $current_log = "";
    $query_get = mysqli_query($koneksi, "SELECT progress_log FROM pengaduan WHERE id_pengaduan='$id_pengaduan'");
    if ($query_get && $row = mysqli_fetch_assoc($query_get)) {
        $current_log = $row['progress_log'] . "\n";
    }
    $new_log = $current_log . date('Y-m-d H:i:s') . " - KL Update: " . $progress_desc . " (Status: $progress_status)";
    $query = "UPDATE pengaduan SET progress_log='$new_log', progress_status='$progress_status', last_progress_update=NOW(), updated_progress_by='$id_kepala' WHERE id_pengaduan='$id_pengaduan'";
    $result = mysqli_query($koneksi, $query);
    if ($result) {
        create_tanggapan($koneksi, ['id_pengaduan' => $id_pengaduan, 'tanggapan' => 'Progress Update: ' . $progress_desc . ' (Status: ' . $progress_status . ')', 'id_petugas' => $id_kepala]);
        return ['success' => true, 'error' => ''];
    }
    return ['success' => false, 'error' => 'Database error saat update progress'];
}

// Backward compatibility wrapper (deprecated - petugas role removed)
function update_progress_staff($koneksi, $id_pengaduan, $progress_desc, $progress_status, $id_staff) {
    return update_progress_kl($koneksi, $id_pengaduan, $progress_desc, $progress_status, $id_staff);
}

// Function to report progress to Lurah by Kepala Lingkungan
function report_progress_to_lurah($koneksi, $id_pengaduan, $report, $id_kepala) {
    $report = mysqli_real_escape_string($koneksi, $report);
    // KL can report progress; also update progress_status and last update
    // Normalize message prefix so frontend filters can detect KL progress messages
    $kl_message = 'KL update progress: ' . $report;
    $query = "UPDATE pengaduan SET progress_notes=CONCAT(IFNULL(progress_notes,''), '\n', NOW(), ' - KL: ', '$report'), last_progress_update=NOW(), updated_progress_by='$id_kepala' WHERE id_pengaduan='$id_pengaduan'";
    mysqli_query($koneksi, $query);

    // Create a KL tanggapan with a standardized prefix so masyarakat view can pick it up when appropriate
    // But mark it as internal by prefixing 'KL_INTERNAL:' so views can filter it out when forwarded to petugas
    create_tanggapan($koneksi, ['id_pengaduan' => $id_pengaduan, 'tanggapan' => 'KL_INTERNAL: ' . $kl_message, 'id_petugas' => $id_kepala]);

    // If the KL indicates completion/ready for validation, forward a lightweight instruction to assigned petugas (if exists)
    $lower = strtolower($report);
    $is_completion = (strpos($lower, 'complete') !== false || strpos($lower, 'selesai') !== false || strpos($lower, 'completed') !== false || strpos($lower, 'validasi') !== false);
    if ($is_completion) {
        // find assigned staff
        $q = mysqli_query($koneksi, "SELECT id_staff FROM pengaduan WHERE id_pengaduan='$id_pengaduan' LIMIT 1");
        $row = $q ? mysqli_fetch_assoc($q) : null;
        if ($row && !empty($row['id_staff'])) {
            $id_staff = $row['id_staff'];
            // create a tanggapan targeted at petugas — do not include KL internal notes
            $petugas_msg = 'KL requests validation: ' . $report;
            create_tanggapan($koneksi, ['id_pengaduan' => $id_pengaduan, 'tanggapan' => $petugas_msg, 'id_petugas' => $id_staff]);
        }
    }

    return true;
}

// KL validates completion (new - replaces staff_validate_completion since petugas removed)
function kl_validate_completion($koneksi, $id_pengaduan, $id_kepala, $catatan) {
    $catatan = mysqli_real_escape_string($koneksi, $catatan);
    $query = "UPDATE pengaduan SET validated_by_petugas=1, petugas_validation_notes='$catatan', petugas_validated_at=NOW(), can_close=1 WHERE id_pengaduan='$id_pengaduan'";
    $result = mysqli_query($koneksi, $query);
    if ($result) {
        create_tanggapan($koneksi, ['id_pengaduan' => $id_pengaduan, 'tanggapan' => 'Validation by Kepala Lingkungan: ' . $catatan, 'id_petugas' => $id_kepala]);
    }
    return $result ? ['success' => true, 'error' => ''] : ['success' => false, 'error' => 'Gagal menyimpan validasi penyelesaian'];
}

// Backward compatibility wrapper (deprecated - petugas role removed)
function staff_validate_completion($koneksi, $id_pengaduan, $id_staff, $catatan) {
    return kl_validate_completion($koneksi, $id_pengaduan, $id_staff, $catatan);
}

// Kepala Lingkungan closes the complaint (no longer requires petugas validation since petugas removed)
function kl_close_complaint($koneksi, $id_pengaduan, $id_kepala, $catatan, $proof_path = NULL) {
    $catatan = mysqli_real_escape_string($koneksi, $catatan);
    // Fetch required flags
    $check = mysqli_query($koneksi, "SELECT status, is_approved, nik FROM pengaduan WHERE id_pengaduan='$id_pengaduan' LIMIT 1");
    $row = $check ? mysqli_fetch_assoc($check) : null;
    if (!$row) {
        return ['success' => false, 'error' => 'Pengaduan tidak ditemukan'];
    }
    $status = $row['status'];
    $is_approved = $row['is_approved'];
    if ($status !== 'opened') return ['success' => false, 'error' => 'Status harus OPENED'];
    if (empty($is_approved) || $is_approved != 1) return ['success' => false, 'error' => 'Laporan belum diapprove oleh Kepala Lingkungan'];

    $proof = $proof_path ? mysqli_real_escape_string($koneksi, $proof_path) : NULL;
    $query = "UPDATE pengaduan SET status='closed', closed_by='$id_kepala', closed_at=NOW(), completion_notes='$catatan', completion_proof=" . ($proof ? "'$proof'" : "NULL") . " WHERE id_pengaduan='$id_pengaduan'";
    $result = mysqli_query($koneksi, $query);
    if ($result) {
        create_tanggapan($koneksi, ['id_pengaduan' => $id_pengaduan, 'tanggapan' => 'Closed by KL: ' . $catatan, 'id_petugas' => $id_kepala]);
        // Insert a simple notification for masyarakat
        $nik = $row['nik'];
        // Create notifications table if not exists (lightweight)
        mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nik CHAR(16) NOT NULL,
            type VARCHAR(50) NOT NULL,
            message TEXT NOT NULL,
            link VARCHAR(255) DEFAULT NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        $link = 'masyarakat/index.php?page=tanggapan&id_pengaduan=' . $id_pengaduan;
        $msg = 'Laporan Anda telah selesai ditangani';
        $ins = mysqli_query($koneksi, "INSERT INTO notifications (nik, type, message, link) VALUES ('$nik', 'report_closed', '$msg', '$link')");
    }
    return $result ? ['success' => true, 'error' => ''] : ['success' => false, 'error' => 'Database error saat menutup laporan'];
}

// Function to get notification count for dashboard
function get_notification_count($koneksi, $level, $id_petugas, $wilayah = null) {
    if ($level == 'kepala_lingkungan') {
        // New reports in wilayah or reports opened and assigned to KL
        $query = "SELECT COUNT(*) as count FROM pengaduan WHERE (status='pending' OR (status='opened' AND id_kepala_lingkungan='$id_petugas')) AND wilayah='$wilayah'";
    } elseif ($level == 'lurah') {
        // Reports that are opened and approved (ready for disposisi)
        $query = "SELECT COUNT(*) as count FROM pengaduan WHERE status='opened' AND is_approved=1";
    } elseif ($level == 'petugas') {
        $query = "SELECT COUNT(*) as count FROM pengaduan WHERE status='opened' AND id_staff='$id_petugas'";
    } else {
        return 0;
    }
    $result = mysqli_query($koneksi, $query);
    return $result ? mysqli_fetch_assoc($result)['count'] : 0;
}

// Function to dismiss rejected report from KL dashboard (hide only, data remains for masyarakat)
function dismiss_rejected_report($koneksi, $id_pengaduan, $id_kepala, $reason = '') {
    $id_pengaduan = mysqli_real_escape_string($koneksi, $id_pengaduan);
    $id_kepala = mysqli_real_escape_string($koneksi, $id_kepala);
    $reason = mysqli_real_escape_string($koneksi, $reason);
    
    // Check if report is rejected
    $check = mysqli_query($koneksi, "SELECT status FROM pengaduan WHERE id_pengaduan='$id_pengaduan' LIMIT 1");
    $row = $check ? mysqli_fetch_assoc($check) : null;
    
    if (!$row || $row['status'] != 'rejected') {
        return ['success' => false, 'error' => 'Laporan harus dalam status ditolak untuk dihapus'];
    }
    
    // Hide from KL dashboard
    $update_query = "UPDATE pengaduan SET hidden_from_kl = 1 WHERE id_pengaduan = '$id_pengaduan'";
    $update_result = mysqli_query($koneksi, $update_query);
    
    if (!$update_result) {
        return ['success' => false, 'error' => 'Gagal menyembunyikan laporan'];
    }
    
    // Create tanggapan entry to record dismissal (for audit trail)
    $result = create_tanggapan($koneksi, [
        'id_pengaduan' => $id_pengaduan, 
        'tanggapan' => 'Report dismissed from KL dashboard', 
        'id_petugas' => $id_kepala
    ]);
    
    // Always create a public tanggapan for masyarakat (with reason jika ada)
    $public_message = 'Laporan Ditolak oleh Kepala Lingkungan';
    if (!empty($reason)) {
        $public_message .= ': ' . $reason;
    }
    $result2 = create_tanggapan($koneksi, [
        'id_pengaduan' => $id_pengaduan,
        'tanggapan' => $public_message,
        'id_petugas' => $id_kepala
    ]);
    if (!$result2) {
        return ['success' => false, 'error' => 'Gagal membuat tanggapan publik'];
    }
    
    return $result ? ['success' => true, 'error' => ''] : ['success' => false, 'error' => 'Database error saat menghapus laporan'];
}
?>
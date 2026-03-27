# AI Coding Agent Instructions for Aplikasi Pengaduan Masyarakat

## Project Overview
This is a PHP-based public complaint management system ("Aplikasi Pengaduan Masyarakat") built with MySQL and Bootstrap. It handles three user roles: public users (masyarakat), officers (petugas), and administrators (admin).

## Architecture & Data Flow
- **User Roles**: 
  - `masyarakat`: Public users who submit complaints
  - `petugas`/`admin`: Officers who verify and respond to complaints, with sub-levels: `admin`, `lurah`, `Kepala_Lingkungan`
- **Core Tables**:
  - `masyarakat`: Public user accounts (nik, nama, username, password, telp, level)
  - `pengaduan`: Complaints (id, tgl, nik, judul, isi_laporan, foto, status, id_kepala_lingkungan, id_lurah, id_staff)
  - `petugas`: Officer accounts (id, nama, username, password, telp, level)
  - `tanggapan`: Responses (id, id_pengaduan, tgl, tanggapan, id_petugas)
- **Status Flow**: `pending` (submitted) → validated by Kepala_Lingkungan → `opened` or `closed` by lurah
- **File Structure**:
  - Root: Main router (`index.php`) and public pages
  - `admin/`: Admin/petugas dashboard with data management (data_pengaduan.php for Kepala_Lingkungan, pengaduan_lurah.php for lurah)
  - `masyarakat/`: Public user dashboard for submitting/viewing complaints
  - `config/`: Database connection (koneksi.php), auth actions (aksi_login.php), helper functions (functions.php)
  - `layouts/`: Shared HTML templates
  - `assets/`: CSS/JS/Bootstrap files
  - `database/`: SQL schema and uploaded images

### Extended Roles & Complaint Flow (skripsi version)

- Additional officer levels in `petugas.level`: `lurah`, `Kepala_Lingkungan` (besides `admin`).
- Extended `pengaduan` table (newer schema): adds `id_kepala_lingkungan`, `id_lurah`, `id_staff` (INT, nullable) and `status` enum('pending','opened','closed').
- Business flow:
  - masyarakat submits complaint → `status = 'pending'`.
  - Kepala_Lingkungan validates complaint and is recorded in `id_kepala_lingkungan`, may add initial `tanggapan`.
  - lurah reviews validated complaints, updates `status` to `opened` or `closed`, and is recorded in `id_lurah`.
  - Each follow-up action is logged in `tanggapan` with `id_petugas` pointing to the officer who responded.
- New code should respect these levels and fields while still following the existing patterns (PHP + mysqli, session-based auth, router `?page=...`).

## Key Conventions & Patterns

### Authentication & Sessions
- Login via `config/aksi_login.php` with level selection (masyarakat/petugas)
- Session variables: `$_SESSION['login']` set to role, plus role-specific IDs (nik for masyarakat, id_petugas for officers), and `$_SESSION['level']` for petugas levels
- Protected pages check `if (empty($_SESSION['login'] == "role"))` and redirect to login, or use `check_access(['level'])` from functions.php
- Passwords stored as `md5()` hashes (update to more secure hashing if modernizing)

### Database Operations
- Connection: `include "../config/koneksi.php";` (connects to `db_apem`)
- Queries: Direct `mysqli_query()` calls, no prepared statements
- Joins: Use INNER JOIN for related data (e.g., pengaduan + masyarakat)
- Updates: Status changes trigger related actions (e.g., tanggapan insert sets status to 'selesai')

### File Uploads
- Images stored in `database/img/` with randomized prefixes: `rand(0,999) . '-' . $foto`
- Form enctype: `enctype="multipart/form-data"`
- Move uploaded file: `move_uploaded_file($tmp, $lokasi . $nama_foto)`

### UI Patterns
- Bootstrap modals for CRUD actions (verification, responses, deletion)
- Tables with responsive design and status badges
- JavaScript alerts for user feedback: `echo "<script>alert('message'); document.location.href='page';</script>";`
- Date handling: `date('Y-m-d')` for submissions

### Routing
- GET parameter routing: `?page=action` in index.php files
- Admin router: `admin/index.php?page=pengaduan|pengaduan_lurah|tanggapan|masyarakat|petugas`
- Masyarakat router: `masyarakat/index.php?page=tanggapan|aduan`

## Development Workflow
1. **Setup**: Import `database/db_aduan.sql` into MySQL database named `db_apem`
2. **Run**: Serve via Apache (XAMPP) at `localhost/aduan`
3. **Access**: Login as admin (username: admin, password: admin) or register as masyarakat
4. **Debug**: Check PHP error logs; use `var_dump()` for debugging as no formal logging exists

## Common Patterns
- **Session Checks**: Always at top of protected PHP files
- **Database Includes**: `include "../config/koneksi.php";` before queries
- **Form Processing**: Check `isset($_POST['action'])` then process and redirect with alert
- **Status Logic**: Use if/elseif chains for status display and action availability
- **File Paths**: Relative paths like `../database/img/` for assets

## Security Notes
- Current implementation uses MD5 for passwords (not secure)
- No input sanitization beyond `htmlspecialchars()` in login
- Direct SQL queries vulnerable to injection
- Consider modernizing to prepared statements and better hashing when updating

## Key Files to Reference
- `config/koneksi.php`: Database connection
- `config/functions.php`: Helper functions for CRUD operations
- `admin/data_pengaduan.php`: Complaint validation for Kepala_Lingkungan
- `admin/pengaduan_lurah.php`: Complaint approval for lurah
- `masyarakat/home.php`: Complaint submission form
- `database/db_aduan.sql`: Complete schema and sample data</content>
<parameter name="filePath">c:\xampp\htdocs\aduan\.github\copilot-instructions.md
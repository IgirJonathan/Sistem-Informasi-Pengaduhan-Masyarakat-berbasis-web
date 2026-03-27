-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 18 Des 2025 pada 09.23
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_aduan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `masyarakat`
--

CREATE TABLE `masyarakat` (
  `nik` char(16) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(32) NOT NULL,
  `telp` varchar(12) NOT NULL,
  `level` varchar(10) NOT NULL,
  `wilayah` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `masyarakat`
--

INSERT INTO `masyarakat` (`nik`, `nama`, `username`, `password`, `telp`, `level`, `wilayah`) VALUES
('2171042203889001', 'MUSRIL ', 'MUSRIL', '0ad7f4a3a2b61335d25c360b77e64e9b', '0812896782', 'masyarakat', 'Wilayah A'),
('2171042203889002', 'NIKO SAPUTRA', 'NIKO SAPUTRA', 'a121fd517d56e3e2c6598ffd9f84dccd', '086723546334', 'masyarakat', 'Wilayah B'),
('2171042203889003', 'WAHYU PRADANA', 'WAHYU ', '6d6461fadf989b4a60ce0e2765f70203', '088356745234', 'masyarakat', 'Wilayah A'),
('2171042203889004', 'ALEXA PANJAITAN', 'ALEXA', 'd643ae55a89a27ee0c235e16b6fed305', '087767215376', 'masyarakat', 'Wilayah B'),
('2171042203889005', 'NANI MUSRAH', 'NANI', '202cb962ac59075b964b07152d234b70', '088273642342', 'masyarakat', 'Wilayah A');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaduan`
--

CREATE TABLE `pengaduan` (
  `id_pengaduan` int(11) NOT NULL,
  `tgl_pengaduan` date NOT NULL,
  `nik` char(16) NOT NULL,
  `judul_pengaduan` varchar(50) NOT NULL,
  `isi_laporan` varchar(255) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('pending','rejected','opened','closed') NOT NULL DEFAULT 'pending',
  `id_kepala_lingkungan` int(11) DEFAULT NULL,
  `opened_at` datetime DEFAULT NULL,
  `opened_by` int(11) DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `approval_notes` text DEFAULT NULL,
  `can_unapprove` tinyint(1) DEFAULT 1,
  `is_rejected` tinyint(1) DEFAULT 0,
  `rejected_by` int(11) DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `rejected_at` datetime DEFAULT NULL,
  `is_assigned` tinyint(1) DEFAULT 0,
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_petugas_id` int(11) DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `disposition_notes` text DEFAULT NULL,
  `progress_status` enum('pending','on_progress','awaiting_completion') DEFAULT 'pending',
  `progress_notes` text DEFAULT NULL,
  `last_progress_update` datetime DEFAULT NULL,
  `updated_progress_by` int(11) DEFAULT NULL,
  `validated_by_petugas` tinyint(1) DEFAULT 0,
  `petugas_validation_notes` text DEFAULT NULL,
  `petugas_validated_at` datetime DEFAULT NULL,
  `can_close` tinyint(1) DEFAULT 0,
  `closed_by` int(11) DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `completion_proof` varchar(255) DEFAULT NULL,
  `completion_notes` text DEFAULT NULL,
  `is_workable` tinyint(1) DEFAULT 1,
  `non_workable_reason` text DEFAULT NULL,
  `id_lurah` int(11) DEFAULT NULL,
  `id_staff` int(11) DEFAULT NULL,
  `wilayah` varchar(50) DEFAULT NULL,
  `progress_log` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengaduan`
--

INSERT INTO `pengaduan` (`id_pengaduan`, `tgl_pengaduan`, `nik`, `judul_pengaduan`, `isi_laporan`, `foto`, `status`, `id_kepala_lingkungan`, `id_lurah`, `id_staff`) VALUES
(111, '2025-06-02', '2171041109089002', 'LAPORAN KERUSAKAN JALAN', 'JALAN DI BELAKANG RUMAH RUSAK', '472-—Pngtree—car key_6960501.png', 'pending', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `petugas`
--

CREATE TABLE `petugas` (
  `id_petugas` int(11) NOT NULL,
  `nama_petugas` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `telp` varchar(13) NOT NULL,
  `level` enum('admin','lurah','kepala_lingkungan','petugas') NOT NULL,
  `wilayah` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `petugas`
--

INSERT INTO `petugas` (`id_petugas`, `nama_petugas`, `username`, `password`, `telp`, `level`) VALUES
(1, 'admin', 'admin', '21232f297a57a5a743894a0e4a801fc3', '555', 'admin');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tanggapan`
--

CREATE TABLE `tanggapan` (
  `id_tanggapan` int(11) NOT NULL,
  `id_pengaduan` int(11) NOT NULL,
  `tgl_tanggapan` date NOT NULL,
  `tanggapan` text NOT NULL,
  `id_petugas` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tanggapan`
--

INSERT INTO `tanggapan` (`id_tanggapan`, `id_pengaduan`, `tgl_tanggapan`, `tanggapan`, `id_petugas`) VALUES
(38, 111, '2025-06-02', 'akan segera diproses', 1);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `masyarakat`
--
ALTER TABLE `masyarakat`
  ADD PRIMARY KEY (`nik`);

--
-- Indeks untuk tabel `pengaduan`
--
ALTER TABLE `pengaduan`
  ADD PRIMARY KEY (`id_pengaduan`),
  ADD KEY `id_kepala_lingkungan` (`id_kepala_lingkungan`,`id_lurah`,`id_staff`);

--
-- Indeks untuk tabel `petugas`
--
ALTER TABLE `petugas`
  ADD PRIMARY KEY (`id_petugas`);

--
-- Indeks untuk tabel `tanggapan`
--
ALTER TABLE `tanggapan`
  ADD PRIMARY KEY (`id_tanggapan`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `pengaduan`
--
ALTER TABLE `pengaduan`
  MODIFY `id_pengaduan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT untuk tabel `petugas`
--
ALTER TABLE `petugas`
  MODIFY `id_petugas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `tanggapan`
--
ALTER TABLE `tanggapan`
  MODIFY `id_tanggapan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 13, 2026 at 03:33 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ppks_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `archived_at` timestamp NULL DEFAULT NULL,
  `archived_by` bigint(20) UNSIGNED DEFAULT NULL,
  `tanggal_masuk` date NOT NULL,
  `sumber_aduan` varchar(255) NOT NULL,
  `tkp_kronologi` text NOT NULL,
  `kondisi_fisik` text DEFAULT NULL,
  `problem_awal` text DEFAULT NULL,
  `kategori_ppks` varchar(255) DEFAULT NULL,
  `nama_lengkap` varchar(255) NOT NULL DEFAULT 'Tanpa Nama / X',
  `nama_alias` varchar(255) DEFAULT NULL,
  `foto_klien` varchar(255) DEFAULT NULL,
  `data_dukung` varchar(255) DEFAULT NULL,
  `locked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `rujukan` varchar(255) DEFAULT NULL,
  `tanggal_keluar` date DEFAULT NULL,
  `no_hp_penanggung_jawab` varchar(255) DEFAULT NULL,
  `status_akhir` enum('pulang','dirujuk') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `status`, `is_archived`, `archived_at`, `archived_by`, `tanggal_masuk`, `sumber_aduan`, `tkp_kronologi`, `kondisi_fisik`, `problem_awal`, `kategori_ppks`, `nama_lengkap`, `nama_alias`, `foto_klien`, `data_dukung`, `locked_at`, `created_at`, `updated_at`, `rujukan`, `tanggal_keluar`, `no_hp_penanggung_jawab`, `status_akhir`) VALUES
(1, 'selesai', 0, NULL, NULL, '2026-09-30', 'Satpol PP', 'test', 'testtest', 'test', 'Lansia Terlantar', 'test', 'test', 'client-photos/HBSnxlq8kujexfoDgHenr5Q2KWU5ctRmSY4EYwj3.jpg', 'client-documents/N0yccABE0lKh5QV85Fms07zhspaQL3Tcenmaj1of.jpg', '2026-09-30 06:11:52', '2026-09-30 06:09:50', '2026-09-30 06:32:52', 'Kembali ke Keluarga', '2026-09-30', '086545342123', 'pulang'),
(2, 'dirujuk', 1, '2026-10-01 02:39:02', 4, '2026-10-01', 'Masyarakat', 'test', 'Tampak Kurus', 'trauma', 'Korban Kekerasan', 'testing', 'test', 'client-photos/0H6o76Yqj7bXZJDus7le68w6aYBJIOvMG1pKDHOx.jpg', 'client-documents/QorLU74DxgVqhd6N5f0HaWLXRot9p1mnWn3lhvQD.jpg', '2026-10-01 01:32:06', '2026-10-01 01:30:03', '2026-10-01 02:39:02', 'Dirujuk ke Panti X', '2026-10-01', '086545342128', 'dirujuk'),
(3, 'selesai', 1, '2026-10-01 03:17:30', 3, '2026-10-01', 'Satpol PP', 'test', 'Luka Lecet', 'Terlantar di jalan', 'Anak Terlantar', 'awawawa', 'awa', 'client-photos/KQp7pJDq7eubBQWZl2BZllNaYZKITKpKE2eh4AO3.jpg', 'client-documents/v7L8mfpwpWgVKd7xT7bb8ZpnLTF3hRwsOA9ilcdi.jpg', '2026-10-01 02:03:20', '2026-10-01 02:02:48', '2026-10-01 03:17:30', 'Kembali ke Keluarga', '2026-10-01', '086545342123', 'pulang'),
(4, 'selesai', 1, '2026-10-01 03:03:24', 3, '2026-10-01', 'Masyarakat', 'test', 'Tampak Kurus', 'Test', 'ODGJ Terlantar', 'testingtung', 'Testo', 'client-photos/Zusu6W40lOxxNLcOoWMZw5NGVzlkKNv53xm5tZYM.jpg', 'client-documents/yZtvb2acF1odh1dLolFz3PYm9rwjo7GCqgU1tf8e.jpg', '2026-10-01 02:46:43', '2026-10-01 02:45:44', '2026-10-01 03:03:24', 'Kembali ke Keluarga', '2026-10-01', '086545342128', 'pulang'),
(5, 'dirujuk', 1, '2026-10-01 03:29:43', 3, '2026-10-01', 'Warga', 'Test', 'test', 'test', 'Korban Kekerasan', 'yuyuyuyu', 'yuyu', 'client-photos/Gxeg1xfU2GUVh71h17okuEIQEYunJCsxA3acWyYm.jpg', 'client-documents/kBvTwFRLNNo1Tf6mWzTGo7mepAfUCMKNNlpQVHJk.jpg', '2026-10-01 03:25:58', '2026-10-01 03:25:33', '2026-10-01 03:29:43', 'Pemindahan Rumah Rehabilitasi', '2026-10-01', '086545342124', 'dirujuk'),
(6, 'dirujuk', 1, '2026-10-01 03:40:16', 3, '2026-10-01', 'Satpol PP', 'test', 'test', 'test', 'Korban Kekerasan', 'testung', 'testung', 'client-photos/6mRVSxNhTDQlVPhKhJzxNVhHiKSoi24HvV90CM1G.jpg', 'client-documents/QPtygdV2Qyb5lgl8q1y9VoXCAl5B9igWkUO6MYzR.jpg', '2026-10-01 03:35:58', '2026-10-01 03:35:41', '2026-10-01 03:40:16', 'Panti X', '2026-10-01', '086545342129', 'dirujuk'),
(7, 'draft', 0, NULL, NULL, '2026-10-01', 'Masyarakat', 'test', 'kurus', 'test', 'Anak Terlantar', 'testttttt', 'test22', 'client-photos/MhG534aIAysdHbdyMxVyqWoDuwl6ajWIc8Q9FheL.png', 'client-documents/qO6ojdjowFSX1yZkFHYTRwOSPVCwrMeq1SwLH5iT.png', NULL, '2026-10-01 07:54:56', '2026-10-01 07:54:56', NULL, NULL, NULL, NULL),
(8, 'dirujuk', 1, '2026-10-02 07:29:11', 3, '2026-10-02', 'Masyarakat', 'ya begitulah', 'Tampak Kurus', 'Gitu', 'ODGJ Terlantar', 'Gana', 'Gana', 'client-photos/PgKJTEtu7lBRsdOoFmgWuDigPFj3jaDRJ9dDrOYM.png', 'client-documents/0rMEmJRn8wfhgZrsfEszGUUcH2xpBfr1Z3cKIpAg.png', '2026-10-02 07:14:18', '2026-10-02 07:09:04', '2026-10-02 07:29:11', 'Pemindahan Rumah Rehabilitasi', '2026-10-02', '08654534212122', 'dirujuk'),
(9, 'selesai', 1, '2026-10-06 01:51:04', 3, '2026-10-06', 'Masyarakat', 'coba', 'coba', 'Marah', 'ODGJ Terlantar', 'coba aja', 'coba', 'client-photos/pvRme07Y99AkvjGKOjQWY54X2qRvtAHpiPc2Nkqf.jpg', 'client-documents/QN8kId9oeticxmC4yTwxWqor6XEwpesgIagHCAWX.jpg', '2026-10-06 01:42:06', '2026-10-06 01:40:19', '2026-10-06 01:51:04', 'Kembali ke Keluarga', '2026-10-06', '0865453421299', 'pulang'),
(10, 'draft', 0, NULL, NULL, '2026-10-06', 'Masyarakat', 'Tidak terkontrol di desa', '-', '-', 'ODGJ Terlantar', 'Tanpa Nama / X', '-', 'client-photos/FurI84sl5lf8t5L8YgtWVF3trhw1aOe6yYYYZNHK.jpg', NULL, '2026-10-06 03:43:25', '2026-10-06 03:41:37', '2026-10-06 03:43:25', NULL, NULL, NULL, NULL),
(11, 'penanganan', 0, NULL, NULL, '2026-10-08', 'Warga', 'Terlantar Tidur Pinggir jalan', 'Kurus', 'terlantar dan tidur di depan properti pribadi milik masyarakat', 'Lansia Terlantar', 'Gerry Miskovit', 'Gerry', 'client-photos/XeByafXdRkPqnUKkShfTmeQ8LmYRRjdS83BeA1Iq.jpg', 'client-documents/44QfZUbhA7BNy0ElZUFKJwYRiF0lhAFLWEC9fkR7.jpg', '2026-10-08 02:09:42', '2026-10-08 02:06:35', '2026-10-08 03:30:14', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `client_assessments`
--

CREATE TABLE `client_assessments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `nik` varchar(16) DEFAULT NULL,
  `tempat_lahir` varchar(255) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `umur` smallint(5) UNSIGNED DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `alamat_domisili` text DEFAULT NULL,
  `detail_alamat_pengakuan` text DEFAULT NULL,
  `status_pendidikan` varchar(255) DEFAULT NULL,
  `kategori_umur` varchar(255) DEFAULT NULL,
  `status_perkawinan` varchar(255) DEFAULT NULL,
  `pendidikan_terakhir` varchar(255) DEFAULT NULL,
  `pekerjaan` varchar(255) DEFAULT NULL,
  `sumber_penghasilan` varchar(255) DEFAULT NULL,
  `kepemilikan_bansos` varchar(255) DEFAULT NULL,
  `kondisi_ekonomi` text DEFAULT NULL,
  `kondisi_tempat_tinggal` text DEFAULT NULL,
  `riwayat_masalah` text DEFAULT NULL,
  `relasi_keluarga` enum('baik','kurang_baik','terputus') DEFAULT NULL,
  `riwayat_trauma` text DEFAULT NULL,
  `masalah_keluarga` text DEFAULT NULL,
  `masalah_sekolah` text DEFAULT NULL,
  `masalah_sosial` text DEFAULT NULL,
  `cara_pandang_klien` text DEFAULT NULL,
  `rencana_kedepan` text DEFAULT NULL,
  `dukungan_sosial` text DEFAULT NULL,
  `kondisi_emosi` varchar(255) DEFAULT NULL,
  `cara_komunikasi` varchar(255) DEFAULT NULL,
  `arus_percakapan` varchar(255) DEFAULT NULL,
  `ingatan` varchar(255) DEFAULT NULL,
  `arah_perilaku` varchar(255) DEFAULT NULL,
  `orientasi_realita` enum('baik','terganggu') DEFAULT NULL,
  `catatan_observasi` text DEFAULT NULL,
  `simpulan_awal` text NOT NULL,
  `rekomendasi_penanganan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `client_assessments`
--

INSERT INTO `client_assessments` (`id`, `client_id`, `nik`, `tempat_lahir`, `tanggal_lahir`, `umur`, `jenis_kelamin`, `alamat_domisili`, `detail_alamat_pengakuan`, `status_pendidikan`, `kategori_umur`, `status_perkawinan`, `pendidikan_terakhir`, `pekerjaan`, `sumber_penghasilan`, `kepemilikan_bansos`, `kondisi_ekonomi`, `kondisi_tempat_tinggal`, `riwayat_masalah`, `relasi_keluarga`, `riwayat_trauma`, `masalah_keluarga`, `masalah_sekolah`, `masalah_sosial`, `cara_pandang_klien`, `rencana_kedepan`, `dukungan_sosial`, `kondisi_emosi`, `cara_komunikasi`, `arus_percakapan`, `ingatan`, `arah_perilaku`, `orientasi_realita`, `catatan_observasi`, `simpulan_awal`, `rekomendasi_penanganan`, `created_at`, `updated_at`) VALUES
(1, 1, '8989898898777744', 'Kabupaten Tuban', '1999-11-17', NULL, 'L', 'Rembes', 'Rembes dekat pasar', 'Tamat / Tidak Bersekolah Lagi', 'lansia', 'Belum Menikah', 'SMK', 'Petani', 'Orang Tua', 'Tidak ada', 'Ekonomi berkecukupan sederhana', 'Sederhana', 'trauma', 'baik', 'test', 'test', 'test', 'testtest', 'test', 'test', 'Orang tua dan kerabat terdekat', 'Mudah Tersinggung', 'Menghindari Kontak Mata', 'Berpindah-pindah topik', 'Lupa Kejadian Tertentu', 'Gelisah', 'terganggu', 'test', 'test', 'test', '2026-09-30 06:15:17', '2026-09-30 06:15:17'),
(2, 2, '1234567891011122', 'Kabupaten Tuban', '1999-06-01', 27, 'L', 'Rembes', 'Rembes depan pasar', 'Tamat / Tidak Bersekolah Lagi', 'dewasa', 'Lajang', 'SMK', 'Nelayan', 'Orang Tua', 'Tidak ada', 'Cukup dan sederhana', 'Sederhana', 'Trauma', 'baik', 'Tidak ada', 'Faktor Ekonomi', 'Tidak ada', 'Gampang tersinggung', 'Gugup', 'test', 'Orang tua dan kerabat terdekat', 'Mudah Tersinggung', 'Menghindari Kontak Mata', 'Terhambat', 'Baik', 'Gelisah', 'terganggu', 'test', 'test', 'Testing', '2026-10-01 01:37:49', '2026-10-01 01:37:49'),
(3, 3, '1212134545233232', 'Kabupaten Tuban', '1997-12-28', 31, 'P', 'Bejagung', 'Bejagung Pondok', 'Tamat / Tidak Bersekolah Lagi', 'dewasa', 'Lajang', 'SMK', 'Petani', 'Orang Tua', 'Tidak ada', 'Sederhana', 'Sederhana', 'Terlantar', 'baik', 'Tidak ada', 'Terlantar, karna tidak ingat jalan rumah/ pulang', 'Tidak ada', 'Tidak ada', 'Pelupa', 'Rehabilitasi sementara', 'Orang tua dan kerabat terdekat', 'Tenang', 'Koorperatif', 'Lancar', 'Lupa Kejadian Tertentu', 'Terarah', 'baik', 'test', 'test', 'test', '2026-10-01 02:06:44', '2026-10-01 02:06:44'),
(4, 4, '1212121212121212', 'Kabupaten Tuban', '1999-12-01', 23, 'L', 'Karangsari', 'Karangsari', 'Putus Sekolah', 'dewasa', 'Lajang', 'SMK', 'Nelayan', 'Orang Tua', 'Tidak ada', 'sederhana', 'Sederhana', 'Trauma', 'baik', 'Tidak ada', 'Ekonomi', 'Tidak ada', 'tidak ada', 'Gugup', 'test', 'Orang tua dan kerabat terdekat', 'Cemas', 'Pasif', 'Terhambat', 'Lupa Kejadian Tertentu', 'Gelisah', 'terganggu', 'TEST', 'TEST', 'TEST', '2026-10-01 02:50:41', '2026-10-01 02:50:41'),
(5, 5, '1212121212121213', 'Kabupaten Tuban', '1999-12-01', 28, 'P', 'gedungombo', 'pasar baru', 'Tamat / Tidak Bersekolah Lagi', 'dewasa', 'Belum Menikah', 'SMK', 'Karyawan Swasta', 'Orang Tua', 'Tidak ada', 'sederhana', 'Sederhana', 'test', 'baik', 'test', 'test', 'test', 'test', 'test', 'test', 'Orang tua dan kerabat terdekat', 'Cemas', 'Menghindari Kontak Mata', 'Lancar', 'Baik', 'Gelisah', 'baik', 'test', 'test', 'test', '2026-10-01 03:28:11', '2026-10-01 03:28:11'),
(6, 6, '1212121212121216', 'Kabupaten Tuban', '1999-12-01', 28, 'L', 'rembes', 'rembes', 'Tamat / Tidak Bersekolah Lagi', 'dewasa', 'Belum Menikah', 'SMK', 'Karyawan Swasta', 'Orang Tua', 'Tidak ada', 'sederhana', 'Sederhana', 'test', 'baik', 'test', 'test', 'test', 'test', 'test', 'test', 'Orang tua dan kerabat terdekat', 'Mudah Tersinggung', 'Menghindari Kontak Mata', 'Lancar', 'Lupa Kejadian Tertentu', 'Agresif', 'terganggu', 'test', 'test', 'test', '2026-10-01 03:38:46', '2026-10-01 03:38:46'),
(7, 8, '1212121212121211', 'Kabupaten Tuban', '1999-12-02', 28, 'P', 'Plumpang', 'Plumpang dekat lapangan', 'Tamat / Tidak Bersekolah Lagi', 'dewasa', 'Belum Menikah', 'SMA', 'Petani', 'Orang Tua', 'Tidak ada', 'sederhana', 'Sederhana', 'ODGJ Terlantar', 'kurang_baik', 'Tidak ada, hanya ada stress karena hutang', 'Hutang', 'tidak ada', 'Hutang', 'Cemas dan tidak bisa ngomong dengan jelas', 'Rehabilitasi', 'Keluarga dan rekan terdekat', 'Cemas', 'Pasif', 'Berpindah-pindah topik', 'Lupa Kejadian Tertentu', 'Agresif', 'terganggu', 'Gitu', 'Ya', 'Rehabilitasi', '2026-10-02 07:17:47', '2026-10-02 07:18:13'),
(8, 9, '1212121212121210', 'Kabupaten Tuban', '1999-12-24', 28, 'L', 'Rembes RT01 RW03', 'Depan pasar', 'Tamat / Tidak Bersekolah Lagi', 'dewasa', 'Belum Menikah', 'SMA', 'Karyawan Swasta', 'Bekerja', 'Tidak ada', 'Sederhana', 'Sederhana', 'Emosi', 'baik', 'coba', 'coba', 'coba', 'coba', 'coba', 'rehabilitasi', 'Orang tua dan kerabat terdekat', 'Cemas', 'Pasif', 'Berpindah-pindah topik', 'Lupa Kejadian Tertentu', 'Gelisah', 'terganggu', 'coba', 'coba', 'coba', '2026-10-06 01:47:46', '2026-10-06 01:47:46'),
(9, 11, '1412230003212121', 'Kabupaten Tuban', '1999-01-01', 27, 'L', 'Kingking', 'Kingking RT 01/RW 05', NULL, 'dewasa', 'Belum Menikah', 'Tidak Sekolah', 'Karyawan Swasta', 'Bantuan Keluarga', 'Tidak ada', 'Sederhana', 'Tidak ada tempat tinggal tetap', 'Terlantar di Jalan', 'terputus', '-', '-', '-', '-', 'memaknai masalahnya sendiri', 'Untuk Sembuh dan kembali Normal', 'Masyarakat sekitar', 'Tenang', 'Koorperaatif', 'Lancar', 'Lupa Kejadian Tertentu', 'Terarah', 'baik', '-', 'Rehabilitasi', 'Bimbingan', '2026-10-08 03:05:56', '2026-10-08 03:05:56');

-- --------------------------------------------------------

--
-- Table structure for table `client_exit_documents`
--

CREATE TABLE `client_exit_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('surat_keluar','rujukan') NOT NULL,
  `status` enum('menunggu_persetujuan','disetujui','ditolak') NOT NULL DEFAULT 'menunggu_persetujuan',
  `nomor_surat` varchar(255) DEFAULT NULL,
  `tanggal_surat` date NOT NULL,
  `tujuan` varchar(255) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `tanggal_keluar` date NOT NULL,
  `no_hp_penanggung_jawab` varchar(20) NOT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `catatan_penolakan` text DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `client_exit_documents`
--

INSERT INTO `client_exit_documents` (`id`, `client_id`, `created_by`, `approved_by`, `type`, `status`, `nomor_surat`, `tanggal_surat`, `tujuan`, `keterangan`, `tanggal_keluar`, `no_hp_penanggung_jawab`, `pdf_path`, `catatan_penolakan`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 4, 'surat_keluar', 'disetujui', '001/RPS/IX/2026', '2026-09-30', 'Kembali ke Keluarga', 'test', '2026-09-30', '086545342123', NULL, NULL, '2026-09-30 06:34:53', '2026-09-30 06:30:25', '2026-09-30 06:34:53'),
(2, 2, 3, 4, 'rujukan', 'disetujui', '002/RPS/IX/2026', '2026-10-01', 'Pemindahan Rumah Rehabilitasi', 'testing', '2026-10-01', '086545342128', NULL, NULL, '2026-10-01 02:20:52', '2026-10-01 01:40:37', '2026-10-01 02:20:52'),
(3, 3, 3, 4, 'surat_keluar', 'disetujui', '003/RPS/IX/2026', '2026-10-01', 'Kembali ke Keluarga', 'test', '2026-10-01', '086545342123', 'exit-documents/3.pdf', NULL, '2026-10-01 03:18:07', '2026-10-01 02:13:20', '2026-10-01 03:18:07'),
(4, 4, 3, 4, 'surat_keluar', 'disetujui', '001/RPS/IX/2026', '2026-10-01', 'Kembali ke Keluarga', 'test', '2026-10-01', '086545342128', 'exit-documents/4.pdf', NULL, '2026-10-01 03:04:31', '2026-10-01 03:03:24', '2026-10-01 03:04:31'),
(5, 5, 3, 4, 'rujukan', 'disetujui', '005/RPS/IX/2026', '2026-10-01', 'Pemindahan Rumah Rehabilitasi', 'test', '2026-10-01', '086545342124', 'exit-documents/5.pdf', NULL, '2026-10-01 03:32:24', '2026-10-01 03:29:43', '2026-10-01 03:32:24'),
(6, 6, 3, 4, 'rujukan', 'disetujui', '006/RPS/IX/2026', '2026-10-01', 'Panti X', 'test', '2026-10-01', '086545342129', 'exit-documents/6.pdf', NULL, '2026-10-01 03:41:04', '2026-10-01 03:40:16', '2026-10-01 03:41:04'),
(7, 8, 3, 4, 'rujukan', 'disetujui', '090/RPS/IX/2026', '2026-10-02', 'Pemindahan Rumah Rehabilitasi', 'test', '2026-10-02', '08654534212122', 'exit-documents/7.pdf', NULL, '2026-10-02 07:29:35', '2026-10-02 07:20:43', '2026-10-02 07:29:35'),
(8, 9, 3, 4, 'surat_keluar', 'disetujui', '010/RPS/IX/2026', '2026-10-06', 'Kembali ke Keluarga', 'Coba', '2026-10-06', '0865453421299', 'exit-documents/8.pdf', NULL, '2026-10-06 01:52:17', '2026-10-06 01:51:04', '2026-10-06 01:52:17');

-- --------------------------------------------------------

--
-- Table structure for table `client_followups`
--

CREATE TABLE `client_followups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tanggal_tindakan` date NOT NULL,
  `catatan_tindakan` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `client_followups`
--

INSERT INTO `client_followups` (`id`, `client_id`, `user_id`, `tanggal_tindakan`, `catatan_tindakan`, `created_at`, `updated_at`) VALUES
(1, 1, 3, '2026-09-30', 'Bimbingan Konseling Pribadi', '2026-09-30 06:29:39', '2026-09-30 06:29:39'),
(2, 2, 3, '2026-10-01', 'Bimbingan Konseling pribadi', '2026-10-01 01:39:24', '2026-10-01 01:39:24'),
(3, 8, 3, '2026-10-02', 'Rehabilitasi', '2026-10-02 07:19:09', '2026-10-02 07:19:09'),
(4, 8, 3, '2026-10-02', 'Bimbingan Konseling', '2026-10-02 07:19:18', '2026-10-02 07:19:18'),
(5, 9, 3, '2026-10-06', 'Bimbingan Konseling pribadi', '2026-10-06 01:49:30', '2026-10-06 01:49:30'),
(6, 11, 3, '2026-10-08', 'Bimbingan Konseling Pribadi', '2026-10-08 03:30:14', '2026-10-08 03:30:14');

-- --------------------------------------------------------

--
-- Table structure for table `client_revisions`
--

CREATE TABLE `client_revisions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `alasan` text NOT NULL,
  `perubahan` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changes`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `client_revisions`
--

INSERT INTO `client_revisions` (`id`, `client_id`, `user_id`, `alasan`, `perubahan`, `created_at`, `updated_at`, `changes`) VALUES
(1, 2, 4, 'dari keluarga dipindahkan ke Panti X', NULL, '2026-10-01 02:39:02', '2026-10-01 02:39:02', '{\"rujukan\":{\"label\":\"Rujukan \\/ Tujuan\",\"from\":\"Kembali ke Keluarga\",\"to\":\"Dirujuk ke Panti X\"},\"tanggal_keluar\":{\"label\":\"Tanggal Keluar\",\"from\":\"2026-09-30T17:00:00.000000Z\",\"to\":\"2026-10-01\"}}');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_09_23_030706_create_permission_tables', 1),
(5, '2026_09_23_034436_create_clients_table', 1),
(6, '2026_09_24_014701_create_client_assessments_table', 1),
(7, '2026_09_24_030242_create_client_followups_table', 1),
(8, '2026_09_24_030347_add_closure_fields_to_clients_table', 1),
(9, '2026_09_24_040000_add_stage_one_fields_to_clients_table', 1),
(10, '2026_09_24_050000_expand_client_statuses', 1),
(11, '2026_09_24_060000_add_is_active_to_users_table', 1),
(12, '2026_09_24_070000_create_client_exit_documents_table', 1),
(13, '2026_09_24_080000_add_archive_fields_to_clients_table', 1),
(14, '2026_09_26_144439_create_notifications_table', 1),
(15, '2026_09_26_162644_remove_redundant_fields_from_clients_table', 1),
(16, '2026_09_28_100000_add_stage_two_fields_to_client_assessments_table', 1),
(17, '2026_09_28_110000_add_ekonomi_keluarga_umur_to_client_assessments_table', 1),
(18, '2026_09_28_110001_make_riwayat_masalah_rekomendasi_nullable', 1),
(19, '2026_09_29_090000_add_kategori_ppks to_clients_table', 2),
(20, '2026_09_29_090000_add_kategori_ppks_to_clients_table', 2),
(21, '2026_09_30_100000_create_client_revisions_table', 2),
(22, '2026_10_01_100000_add_changes_to_client_revisions_table', 3),
(23, '2026_10_01_110000_make_legacy_perubahan_nullable_on_client_revisions_table', 4),
(24, '2026_10_01_120000_add_pdf_path_to_client_exit_documents_table', 5);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2),
(3, 'App\\Models\\User', 3),
(4, 'App\\Models\\User', 4);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('113c855c-00c9-4d37-87fb-b45499574d35', 'App\\Notifications\\SuratKeluarNotification', 'App\\Models\\User', 4, '{\"document_id\":5,\"client_id\":5,\"event\":\"diajukan\",\"message\":\"Surat rujukan untuk yuyuyuyu diajukan Admin RPS dan menunggu persetujuan.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/exit-documents\\/5\"}', '2026-10-02 07:22:09', '2026-10-01 03:29:44', '2026-10-02 07:22:09'),
('11ee647a-bba1-4e74-9735-3240905ed32e', 'App\\Notifications\\SuratKeluarNotification', 'App\\Models\\User', 4, '{\"document_id\":7,\"client_id\":8,\"event\":\"diajukan\",\"message\":\"Surat rujukan untuk Gana diajukan Admin RPS dan menunggu persetujuan.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/exit-documents\\/7\"}', '2026-10-02 07:22:09', '2026-10-02 07:20:44', '2026-10-02 07:22:09'),
('1d8ee327-4a59-4172-949b-c590882b229b', 'App\\Notifications\\SuratKeluarNotification', 'App\\Models\\User', 4, '{\"document_id\":7,\"client_id\":8,\"event\":\"diajukan\",\"message\":\"Surat rujukan untuk Gana diajukan Admin RPS dan menunggu persetujuan.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/exit-documents\\/7\"}', '2026-10-02 07:23:07', '2026-10-02 07:22:43', '2026-10-02 07:23:07'),
('20797a57-57fc-4118-a2b8-77b8f90b84aa', 'App\\Notifications\\SuratKeluarNotification', 'App\\Models\\User', 3, '{\"document_id\":6,\"client_id\":6,\"event\":\"disetujui\",\"message\":\"Surat rujukan untuk testung telah disetujui Kepala RPS.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/exit-documents\\/mine\"}', '2026-10-01 03:49:51', '2026-10-01 03:41:04', '2026-10-01 03:49:51'),
('23018d3e-6bc4-4092-965b-6ff6adcd732e', 'App\\Notifications\\KlienBaruMasuk', 'App\\Models\\User', 2, '{\"client_id\":8,\"nama_lengkap\":\"gana\",\"message\":\"Klien baru \\\"gana\\\" perlu di-assessment.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/clients\\/8\"}', '2026-10-02 07:14:15', '2026-10-02 07:09:04', '2026-10-02 07:14:15'),
('24083ef3-764e-49b4-b008-3cdea7321bc7', 'App\\Notifications\\SuratKeluarNotification', 'App\\Models\\User', 4, '{\"document_id\":4,\"client_id\":4,\"event\":\"diajukan\",\"message\":\"Surat keluar untuk testingtung diajukan Admin RPS dan menunggu persetujuan.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/exit-documents\\/4\"}', '2026-10-02 07:22:09', '2026-10-01 03:03:25', '2026-10-02 07:22:09'),
('2412424e-28da-4e07-9123-b921e98ab28e', 'App\\Notifications\\SuratKeluarNotification', 'App\\Models\\User', 3, '{\"document_id\":7,\"client_id\":8,\"event\":\"disetujui\",\"message\":\"Surat rujukan untuk Gana telah disetujui Kepala RPS.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/exit-documents\\/mine\"}', '2026-10-02 07:30:24', '2026-10-02 07:29:35', '2026-10-02 07:30:24'),
('26976226-8262-4a30-9be0-f1c3b6630dd2', 'App\\Notifications\\KlienBaruMasuk', 'App\\Models\\User', 2, '{\"client_id\":1,\"nama_lengkap\":\"test\",\"message\":\"Klien baru \\\"test\\\" perlu di-assessment.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/clients\\/1\"}', '2026-09-30 06:26:50', '2026-09-30 06:09:51', '2026-09-30 06:26:50'),
('3c376579-4e1f-4e77-a94b-a77ab5ff7ba9', 'App\\Notifications\\AssessmentSelesai', 'App\\Models\\User', 3, '{\"client_id\":5,\"nama_lengkap\":\"yuyuyuyu\",\"message\":\"Assessment klien \\\"yuyuyuyu\\\" telah selesai dan siap ditangani.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/clients\\/5\"}', '2026-10-02 07:18:41', '2026-10-01 03:28:11', '2026-10-02 07:18:41'),
('3e036c02-17f0-40db-a9b6-969436c6ef77', 'App\\Notifications\\AssessmentSelesai', 'App\\Models\\User', 3, '{\"client_id\":9,\"nama_lengkap\":\"coba aja\",\"message\":\"Assessment klien \\\"coba aja\\\" telah selesai dan siap ditangani.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/clients\\/9\"}', '2026-10-06 01:48:50', '2026-10-06 01:47:46', '2026-10-06 01:48:50'),
('41bcc66a-0322-41c0-94fe-a1c17e12d3f3', 'App\\Notifications\\SuratKeluarNotification', 'App\\Models\\User', 3, '{\"document_id\":8,\"client_id\":9,\"event\":\"disetujui\",\"message\":\"Surat keluar untuk coba aja telah disetujui Kepala RPS.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/exit-documents\\/mine\"}', '2026-10-06 01:53:15', '2026-10-06 01:52:18', '2026-10-06 01:53:15'),
('445356b5-c7c5-4434-901f-c48f00646128', 'App\\Notifications\\KlienBaruMasuk', 'App\\Models\\User', 2, '{\"client_id\":9,\"nama_lengkap\":\"coba aja\",\"message\":\"Klien baru \\\"coba aja\\\" perlu di-assessment.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/clients\\/9\"}', '2026-10-06 07:39:40', '2026-10-06 01:40:21', '2026-10-06 07:39:40'),
('48b2dbe2-d51f-4436-93dd-191df7940a3a', 'App\\Notifications\\KlienBaruMasuk', 'App\\Models\\User', 2, '{\"client_id\":4,\"nama_lengkap\":\"testingtung\",\"message\":\"Klien baru \\\"testingtung\\\" perlu di-assessment.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/clients\\/4\"}', '2026-10-02 06:30:19', '2026-10-01 02:45:44', '2026-10-02 06:30:19'),
('4a8b2e35-5ad8-4c7d-8777-f0e552e0ad76', 'App\\Notifications\\SuratKeluarNotification', 'App\\Models\\User', 4, '{\"document_id\":6,\"client_id\":6,\"event\":\"diajukan\",\"message\":\"Surat rujukan untuk testung diajukan Admin RPS dan menunggu persetujuan.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/exit-documents\\/6\"}', '2026-10-02 07:22:09', '2026-10-01 03:40:16', '2026-10-02 07:22:09'),
('4b477f06-b883-45c9-967e-94fcd91f1bee', 'App\\Notifications\\SuratKeluarNotification', 'App\\Models\\User', 3, '{\"document_id\":4,\"client_id\":4,\"event\":\"disetujui\",\"message\":\"Surat keluar untuk testingtung telah disetujui Kepala RPS.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/exit-documents\\/mine\"}', '2026-10-01 03:04:59', '2026-10-01 03:04:31', '2026-10-01 03:04:59'),
('578535c5-e355-4e23-a46e-38d15946fe3c', 'App\\Notifications\\KlienBaruMasuk', 'App\\Models\\User', 2, '{\"client_id\":6,\"nama_lengkap\":\"testung\",\"message\":\"Klien baru \\\"testung\\\" perlu di-assessment.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/clients\\/6\"}', '2026-10-02 06:30:19', '2026-10-01 03:35:41', '2026-10-02 06:30:19'),
('601967e3-45d4-4ab2-8b8e-14ed1da3802b', 'App\\Notifications\\KlienBaruMasuk', 'App\\Models\\User', 2, '{\"client_id\":10,\"nama_lengkap\":\"Tanpa Nama \\/ X\",\"message\":\"Klien baru \\\"Tanpa Nama \\/ X\\\" perlu di-assessment.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/clients\\/10\"}', '2026-10-06 07:39:40', '2026-10-06 03:41:37', '2026-10-06 07:39:40'),
('63f06726-c4ea-4e33-9c04-4e216716d495', 'App\\Notifications\\SuratKeluarNotification', 'App\\Models\\User', 4, '{\"document_id\":7,\"client_id\":8,\"event\":\"diajukan\",\"message\":\"Surat rujukan untuk Gana diajukan Admin RPS dan menunggu persetujuan.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/exit-documents\\/7\"}', '2026-10-02 07:29:42', '2026-10-02 07:29:11', '2026-10-02 07:29:42'),
('68baa5d9-ccfb-4189-9aae-ad9ed4f68266', 'App\\Notifications\\AssessmentSelesai', 'App\\Models\\User', 3, '{\"client_id\":11,\"nama_lengkap\":\"Gerry Miskovit\",\"message\":\"Assessment klien \\\"Gerry Miskovit\\\" telah selesai dan siap ditangani.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/clients\\/11\"}', NULL, '2026-10-08 03:05:56', '2026-10-08 03:05:56'),
('6e002fa2-edb0-44a6-a096-a29be76968b9', 'App\\Notifications\\KlienBaruMasuk', 'App\\Models\\User', 2, '{\"client_id\":3,\"nama_lengkap\":\"awawawa\",\"message\":\"Klien baru \\\"awawawa\\\" perlu di-assessment.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/clients\\/3\"}', '2026-10-01 02:06:49', '2026-10-01 02:02:51', '2026-10-01 02:06:49'),
('744eb68b-a4b7-44c2-8874-15ef2a647965', 'App\\Notifications\\AssessmentSelesai', 'App\\Models\\User', 3, '{\"client_id\":6,\"nama_lengkap\":\"testung\",\"message\":\"Assessment klien \\\"testung\\\" telah selesai dan siap ditangani.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/clients\\/6\"}', '2026-10-02 07:18:41', '2026-10-01 03:38:46', '2026-10-02 07:18:41'),
('7dcc85f8-4b89-4466-9cf9-2f46780eb467', 'App\\Notifications\\SuratKeluarNotification', 'App\\Models\\User', 3, '{\"document_id\":3,\"client_id\":3,\"event\":\"disetujui\",\"message\":\"Surat keluar untuk awawawa telah disetujui Kepala RPS.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/exit-documents\\/mine\"}', '2026-10-02 07:18:41', '2026-10-01 03:18:07', '2026-10-02 07:18:41'),
('ae351568-9b4b-4ed0-8aba-dff45447e3e9', 'App\\Notifications\\SuratKeluarNotification', 'App\\Models\\User', 3, '{\"document_id\":5,\"client_id\":5,\"event\":\"disetujui\",\"message\":\"Surat rujukan untuk yuyuyuyu telah disetujui Kepala RPS.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/exit-documents\\/mine\"}', '2026-10-02 07:18:41', '2026-10-01 03:32:24', '2026-10-02 07:18:41'),
('b08e48c5-b205-4ff9-84ad-75e61cdecffd', 'App\\Notifications\\SuratKeluarNotification', 'App\\Models\\User', 4, '{\"document_id\":3,\"client_id\":3,\"event\":\"diajukan\",\"message\":\"Surat keluar untuk awawawa diajukan Admin RPS dan menunggu persetujuan.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/exit-documents\\/3\"}', '2026-10-02 07:22:09', '2026-10-01 03:17:31', '2026-10-02 07:22:09'),
('b67e8293-3082-4320-b5a5-ab483b6fe19a', 'App\\Notifications\\KlienBaruMasuk', 'App\\Models\\User', 2, '{\"client_id\":7,\"nama_lengkap\":\"testttttt\",\"message\":\"Klien baru \\\"testttttt\\\" perlu di-assessment.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/clients\\/7\"}', '2026-10-02 06:30:19', '2026-10-01 07:54:58', '2026-10-02 06:30:19'),
('c08901dd-b008-4cff-9f9b-ceb234f9196d', 'App\\Notifications\\SuratKeluarNotification', 'App\\Models\\User', 4, '{\"document_id\":8,\"client_id\":9,\"event\":\"diajukan\",\"message\":\"Surat keluar untuk coba aja diajukan Admin RPS dan menunggu persetujuan.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/exit-documents\\/8\"}', '2026-10-06 01:51:58', '2026-10-06 01:51:10', '2026-10-06 01:51:58'),
('c66f101b-e604-4edd-85a1-a76320ea8eb4', 'App\\Notifications\\KlienBaruMasuk', 'App\\Models\\User', 2, '{\"client_id\":11,\"nama_lengkap\":\"Gerry Miskovit\",\"message\":\"Klien baru \\\"Gerry Miskovit\\\" perlu di-assessment.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/clients\\/11\"}', '2026-10-08 02:33:48', '2026-10-08 02:06:37', '2026-10-08 02:33:48'),
('cb875666-1c1a-476f-a63e-511617e4908a', 'App\\Notifications\\SuratKeluarNotification', 'App\\Models\\User', 3, '{\"document_id\":7,\"client_id\":8,\"event\":\"ditolak\",\"message\":\"Surat rujukan untuk Gana ditolak Kepala RPS. Catatan: perbaiki Mohon diperbaiki dan diajukan ulang.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/exit-documents\\/mine\"}', '2026-10-02 07:29:04', '2026-10-02 07:28:44', '2026-10-02 07:29:04'),
('d1d66bb6-def7-443e-b959-946bf02f5d7e', 'App\\Notifications\\KlienBaruMasuk', 'App\\Models\\User', 2, '{\"client_id\":2,\"nama_lengkap\":\"testing\",\"message\":\"Klien baru \\\"testing\\\" perlu di-assessment.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/clients\\/2\"}', '2026-10-01 01:30:35', '2026-10-01 01:30:06', '2026-10-01 01:30:35'),
('d620d475-3156-4662-ab32-d654e2a87a4f', 'App\\Notifications\\KlienBaruMasuk', 'App\\Models\\User', 2, '{\"client_id\":5,\"nama_lengkap\":\"yuyuyuyu\",\"message\":\"Klien baru \\\"yuyuyuyu\\\" perlu di-assessment.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/clients\\/5\"}', '2026-10-02 06:30:19', '2026-10-01 03:25:33', '2026-10-02 06:30:19'),
('f5cbedb3-0adb-4308-8c91-321a1d048cee', 'App\\Notifications\\AssessmentSelesai', 'App\\Models\\User', 3, '{\"client_id\":8,\"nama_lengkap\":\"Gana\",\"message\":\"Assessment klien \\\"Gana\\\" telah selesai dan siap ditangani.\",\"url\":\"http:\\/\\/127.0.0.1:8000\\/clients\\/8\"}', '2026-10-02 07:18:41', '2026-10-02 07:17:47', '2026-10-02 07:18:41');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'petugas-piket', 'web', '2026-09-29 19:23:17', '2026-09-29 19:23:17'),
(2, 'peksos', 'web', '2026-09-29 19:23:17', '2026-09-29 19:23:17'),
(3, 'admin-rps', 'web', '2026-09-29 19:23:17', '2026-09-29 19:23:17'),
(4, 'kepala-rps', 'web', '2026-09-29 19:23:17', '2026-09-29 19:23:17');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('IBHayOZCvsrcTc6CFJm0lYdzRiKkfV2u3Y73AaUH', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMHpNWklhM1k3U2V5VVRPZE9QaHMxWFd6UGRHaW1RSVhOWXFIdjc4NCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=', 1791538981);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `is_active`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Petugas Piket', 'petugas@ppks.test', NULL, '$2y$12$5zQiywwA24/nRDz1NF36iOoKisTtsZdKMxIFty7YegyVfXAo1A0a6', 1, NULL, '2026-09-29 19:23:17', '2026-09-29 19:23:17'),
(2, 'Peksos', 'peksos@ppks.test', NULL, '$2y$12$WFtsobhTvzEErG.XHj2/tOKQn19s4xuV0B5ZKhIUD/ZLnJyKcm1wG', 1, NULL, '2026-09-29 19:23:18', '2026-09-29 19:23:18'),
(3, 'Admin RPS', 'admin@ppks.test', NULL, '$2y$12$Q4OAu7kZOkTskGZu7WXFHevDvQxOPPbRF2x/fcfH35Y1PXO15ZS3i', 1, NULL, '2026-09-29 19:23:19', '2026-09-29 19:23:19'),
(4, 'Kepala RPS', 'kepala@ppks.test', NULL, '$2y$12$BKL54ESVeBXYNCQ1JvPtX..WP0l.5kAHXFs3byxtvnES9IjSIYEpu', 1, NULL, '2026-09-29 19:23:19', '2026-09-29 19:23:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `clients_archived_by_foreign` (`archived_by`);

--
-- Indexes for table `client_assessments`
--
ALTER TABLE `client_assessments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_assessments_client_id_foreign` (`client_id`);

--
-- Indexes for table `client_exit_documents`
--
ALTER TABLE `client_exit_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_exit_documents_client_id_foreign` (`client_id`),
  ADD KEY `client_exit_documents_created_by_foreign` (`created_by`),
  ADD KEY `client_exit_documents_approved_by_foreign` (`approved_by`);

--
-- Indexes for table `client_followups`
--
ALTER TABLE `client_followups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_followups_client_id_foreign` (`client_id`),
  ADD KEY `client_followups_user_id_foreign` (`user_id`);

--
-- Indexes for table `client_revisions`
--
ALTER TABLE `client_revisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_revisions_client_id_foreign` (`client_id`),
  ADD KEY `client_revisions_user_id_foreign` (`user_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `client_assessments`
--
ALTER TABLE `client_assessments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `client_exit_documents`
--
ALTER TABLE `client_exit_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `client_followups`
--
ALTER TABLE `client_followups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `client_revisions`
--
ALTER TABLE `client_revisions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_archived_by_foreign` FOREIGN KEY (`archived_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `client_assessments`
--
ALTER TABLE `client_assessments`
  ADD CONSTRAINT `client_assessments_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `client_exit_documents`
--
ALTER TABLE `client_exit_documents`
  ADD CONSTRAINT `client_exit_documents_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `client_exit_documents_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `client_exit_documents_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `client_followups`
--
ALTER TABLE `client_followups`
  ADD CONSTRAINT `client_followups_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `client_followups_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `client_revisions`
--
ALTER TABLE `client_revisions`
  ADD CONSTRAINT `client_revisions_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `client_revisions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

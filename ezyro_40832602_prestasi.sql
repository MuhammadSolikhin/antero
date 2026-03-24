-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql108.ezyro.com
-- Generation Time: Mar 23, 2026 at 01:27 AM
-- Server version: 11.4.10-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ezyro_40832602_prestasi`
--

-- --------------------------------------------------------

--
-- Table structure for table `achievements`
--

CREATE TABLE `achievements` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `nama_kejuaraan` varchar(150) NOT NULL,
  `championship_year` int(11) DEFAULT NULL,
  `tingkat` enum('Daerah','Nasional','Internasional') NOT NULL,
  `juara_ke` int(11) NOT NULL,
  `file_sertifikat` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `tgl_verifikasi` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `achievements`
--

INSERT INTO `achievements` (`id`, `student_id`, `nama_kejuaraan`, `championship_year`, `tingkat`, `juara_ke`, `file_sertifikat`, `status`, `created_at`, `tgl_verifikasi`) VALUES
(3, 3, 'Invitasi Taekwondo Antar Klub', NULL, 'Daerah', 1, 'dummy.jpg', 'rejected', '2025-12-17 06:26:14', NULL),
(4, 3, 'Invitasi Taekwondo Antar Klub', NULL, 'Daerah', 1, 'dummy.jpg', 'rejected', '2025-12-17 06:26:14', NULL),
(5, 3, 'Piala Walikota 2024', NULL, 'Daerah', 2, 'dummy.jpg', 'approved', '2025-12-17 06:26:14', NULL),
(10, 1, 'efiuef', NULL, 'Daerah', 2, '1765978784_1244286.jpg', 'approved', '2025-12-17 06:39:44', '2025-12-17 20:41:14'),
(12, 1, 'tes 2', 2025, 'Daerah', 1, '1766467692_IMG-20251218-WA0058.jpg', 'approved', '2025-12-22 22:28:12', NULL),
(13, 1, 'tes2', 2025, 'Daerah', 1, '1766547866_WhatsApp Image 2025-12-23 at 22.52.09.jpeg', 'approved', '2025-12-23 20:44:26', NULL),
(15, 1, 'Bharaduta Championship 5', 2025, 'Nasional', 2, '1768285909_Kejurnas Bharaduta Ribut.pdf', 'approved', '2026-01-12 23:31:49', NULL),
(16, 17, 'CNN INDONESIA TAEKWONDO CHAMPIONSHIP 2024-PIALA MENPORA', 2024, 'Nasional', 2, '1770189915_1770189884304944660002842619996.jpg', 'approved', '2026-02-04 07:25:15', NULL),
(17, 17, 'LIGA TAEKWONDO DKI JAKARTA SERIES -8', 2024, 'Daerah', 2, '1770190000_17701899790994810353096190009743.jpg', 'approved', '2026-02-04 07:26:40', NULL),
(18, 17, 'LIGA TAEKWONDO DKI JAKARTA SERIES - 10', 2025, 'Daerah', 1, '1770190092_1770190065538907518046165795141.jpg', 'approved', '2026-02-04 07:28:12', NULL),
(19, 17, 'LIGA TAEKWONDO DKI JAKARTA SERIES - 11', 2025, 'Daerah', 3, '1770190168_17701901453847251436922432325676.jpg', 'approved', '2026-02-04 07:29:28', NULL),
(20, 17, 'KONI CUP SERIES - 6', 2025, 'Nasional', 3, '1770190227_17701902114997562637417314814802.jpg', 'approved', '2026-02-04 07:30:27', NULL),
(21, 18, 'Liga DKI series 11', 2025, 'Daerah', 1, '1770275125_Piagam Liga 11 DKI.pdf', 'approved', '2026-02-05 07:05:25', NULL),
(22, 18, 'KONI CUP Series 6', 2025, 'Nasional', 1, '1770275397_Piagam KONI Series 6.pdf', 'approved', '2026-02-05 07:09:57', NULL),
(23, 16, 'KEJUARAAN TAEKWONDO JAKARTA TIMUR ', 2024, 'Daerah', 1, '1771418470_IMG_20260218_193606.jpg', 'approved', '2026-02-18 12:41:10', NULL),
(24, 16, 'BHARADUTA CHAMPIONSHIP 5', 2024, 'Nasional', 1, '1771418604_IMG_20260218_193645.jpg', 'rejected', '2026-02-18 12:43:24', NULL),
(25, 16, 'BHARADUTA CHAMPIONSHIP 5 ', 2024, 'Nasional', 3, '1771418642_IMG_20260218_193645.jpg', 'approved', '2026-02-18 12:44:02', NULL),
(26, 16, 'LIGA TAEKWONDO DKI JAKARTA 2', 2022, 'Nasional', 3, '1771418801_IMG_20260218_193653.jpg', 'approved', '2026-02-18 12:46:41', NULL),
(27, 16, 'Everest Taekwondo Championship Piala Menpora 2022', 2022, 'Nasional', 1, '1771418898_IMG_20260218_193704.jpg', 'approved', '2026-02-18 12:48:18', NULL),
(28, 16, 'THE BEST TAEKWONDO CHAMPIONSHIP ', 2020, 'Nasional', 1, '1771418945_IMG_20260218_193712.jpg', 'approved', '2026-02-18 12:49:05', NULL),
(29, 16, 'Kejuaraan Taekwondo LIGA DKI JAKARTA ', 2022, 'Nasional', 1, '1771419020_IMG_20260218_193741.jpg', 'approved', '2026-02-18 12:50:20', NULL),
(30, 16, 'Taekwondo Championship Piala Menpora 2019', 2019, 'Nasional', 1, '1771419077_IMG_20260218_193748.jpg', 'rejected', '2026-02-18 12:51:17', NULL),
(31, 16, 'The Kick Indonesian Championship 2019', 2019, 'Nasional', 1, '1771419129_IMG_20260218_193802.jpg', 'approved', '2026-02-18 12:52:09', NULL),
(32, 16, 'LIGA TAEKWONDO DKI JAKARTA SERIES 11', 2025, 'Nasional', 3, '1771419194_IMG_20260218_193814.jpg', 'approved', '2026-02-18 12:53:14', NULL),
(33, 16, 'ISTC 2', 2018, 'Nasional', 1, '1771419260_IMG_20260218_193828.jpg', 'approved', '2026-02-18 12:54:20', NULL),
(34, 16, 'LIGA TAEKWONDO DKI JAKARTA SERIES 8', 2024, 'Nasional', 3, '1771419301_IMG_20260218_193834.jpg', 'approved', '2026-02-18 12:55:01', NULL),
(35, 16, 'Taekwondo Championship Piala Menpora 2019', 2019, 'Nasional', 2, '1771472320_IMG_20260218_193748.jpg', 'approved', '2026-02-19 03:38:40', NULL),
(36, 16, 'Taekwondo Championship Piala Menpora 2019', 2019, 'Nasional', 2, '1771472330_IMG_20260218_193748.jpg', 'rejected', '2026-02-19 03:38:50', NULL),
(37, 16, 'Taekwondo Championship Piala Menpora 2019', 2019, 'Nasional', 2, '1771472348_IMG_20260218_193748.jpg', 'rejected', '2026-02-19 03:39:08', NULL),
(38, 18, 'Liga DKI series 12', 2026, 'Daerah', 2, '1774189636_Piagam Liga 12 DKI.pdf', 'pending', '2026-03-22 14:27:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `club_info`
--

CREATE TABLE `club_info` (
  `id` int(11) NOT NULL,
  `club_name` varchar(100) DEFAULT 'Ankero Taekwondo Club',
  `instagram` varchar(255) DEFAULT NULL,
  `tiktok` varchar(255) DEFAULT NULL,
  `youtube` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `club_info`
--

INSERT INTO `club_info` (`id`, `club_name`, `instagram`, `tiktok`, `youtube`, `updated_at`) VALUES
(1, 'Antero Taekwondo Club', ' https://www.instagram.com/anterotaekwondoclub?igsh=MW5jbml1MXNpdmpsaw==', 'https://www.tiktok.com/@anterotaekwondoclub?_r=1&_t=ZS-92W9R5hFtci', 'http://www.youtube.com/@anterotaekwondoclub238', '2025-12-25 06:54:33');

-- --------------------------------------------------------

--
-- Table structure for table `coaches`
--

CREATE TABLE `coaches` (
  `id` int(11) NOT NULL,
  `nama_pelatih` varchar(100) NOT NULL,
  `riwayat_pelatihan` text NOT NULL,
  `tingkatan` varchar(50) NOT NULL,
  `info_sertifikat` text DEFAULT NULL,
  `foto_pelatih` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coaches`
--

INSERT INTO `coaches` (`id`, `nama_pelatih`, `riwayat_pelatihan`, `tingkatan`, `info_sertifikat`, `foto_pelatih`, `created_at`) VALUES
(9, 'Nazhira Raka Putri', 'Pelatihan Dasar 2010, Pelatihan Lanjut 2020', 'DAN I', '1772083573_Nazhira Raka Putri_Sertifikat Dan 1.pdf', '1768545531_WhatsApp Image 2026-01-16 at 13.38.19.jpeg', '2025-12-20 02:33:15'),
(10, 'Ribut Wahyudi', 'Pelatihan Dasar 2010, Pelatihan Lanjut 2021', 'DAN I', '1771402448_DAN-1 Ribut.pdf', '1772084145_IMG_20251114_162914.jpg', '2025-12-20 02:33:15'),
(11, 'Ajeng Restu Arini', 'Pelatihan Dasar 2010, Pelatihan Lanjut 2022', 'DAN III', '-', '1768545239_WhatsApp Image 2026-01-16 at 13.32.56.jpeg', '2025-12-20 02:33:15'),
(14, 'Dzulqarnain Tsaqib', '-', 'DAN I', '-', '1768548616_WhatsApp Image 2026-01-16 at 14.01.57.jpeg', '2026-01-16 00:30:16'),
(19, 'Jose Tamarind', '-', 'DAN III', '1771823281_Sertifikat DAN 3_Jose Tamarind.pdf', '1772084462_TKD.JPEG', '2026-02-23 05:08:01'),
(20, 'Andika Gusti Sunyatmoko', '-', 'DAN IV', '1772083741_sertifikat DAN 4 Andika Gusti.jpg.jpeg', '1772083741_3x4 Merah.JPG.jpeg', '2026-02-26 05:29:01'),
(22, 'Rena Rahma Safitri', '-', 'DAN II', '1772083865_WhatsApp Image 2026-02-23 at 13.16.08.jpeg', '1772083937_Screenshot 2026-02-26 123205.png', '2026-02-26 05:31:05'),
(25, 'Bayu Kristiawan', '-', 'DAN V', '1773132428_Dan 5 Master Bayu.pdf', '1773132704_WhatsApp Image 2026-03-10 at 15.51.12.jpeg', '2026-03-10 08:47:08');

-- --------------------------------------------------------

--
-- Table structure for table `coach_trainings`
--

CREATE TABLE `coach_trainings` (
  `id` int(11) NOT NULL,
  `coach_id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `level` enum('Daerah','Nasional','Internasional') NOT NULL,
  `description` text NOT NULL,
  `certificate_file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coach_trainings`
--

INSERT INTO `coach_trainings` (`id`, `coach_id`, `year`, `level`, `description`, `certificate_file`, `created_at`) VALUES
(55, 10, 2025, 'Nasional', 'Pelatihan Strength And Conditioning', '1771402448_Sertifikat Pelatih Fisik Nasional.pdf', '2026-02-26 05:35:45'),
(56, 10, 2025, 'Nasional', 'Workshop Nasional Sport Injury & Mental Health Awareness', '1771402448_Sertifikat Nasional Sport Injury Ribut Wahyudi.pdf', '2026-02-26 05:35:45'),
(57, 10, 2025, 'Daerah', 'Coaching Clinic Pelatih Fisik Taekwondo Kyorugi', '1771402448_AFD_Sertifikat_Ribut Wahyudi.pdf', '2026-02-26 05:35:45'),
(60, 19, 2024, 'Daerah', 'Taekwondo Kyorugi Competition Rules & Sport Health Nutrition', '1771823281_Coaching Clinic Competition Rule Sanim Jose.jpeg', '2026-02-26 05:43:34'),
(61, 19, 2025, 'Nasional', 'Diklat Wasit Nasional Kyorugi', '1772084615_Sertifikat Wasit Nasional - Jose Tamarin.pdf', '2026-02-26 05:43:34'),
(65, 25, 2021, 'Daerah', 'Webinar Peranan Biomekanika Terhadap Prestasi Atlet', '1773132579_Webinar 2.pdf', '2026-03-10 08:51:44'),
(66, 25, 2020, 'Nasional', 'Webinar Nasional Persiapan Taekwondo Menghadapi Olimpiade 2021', '1773132428_Webinar.pdf', '2026-03-10 08:51:44');

-- --------------------------------------------------------

--
-- Table structure for table `dojangs`
--

CREATE TABLE `dojangs` (
  `id` int(11) NOT NULL,
  `nama_dojang` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `google_maps` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `coach_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dojangs`
--

INSERT INTO `dojangs` (`id`, `nama_dojang`, `alamat`, `google_maps`, `created_at`, `coach_id`) VALUES
(3, 'SDN Manggarai 05', 'Jl. Swadaya I, RW No, RT.6/RW.1, 09, Manggarai, Kec. Tebet, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12850', 'https://maps.app.goo.gl/idWfAMLEDJGQuxWe8', '2025-12-17 06:26:14', NULL),
(4, 'TK Al-Burdah', 'Jl. Dr. KRT Radjiman Widyodiningrat No.58, RT.6/RW.6, Rw. Terate, Kec. Cakung, Kota Jakarta Timur, Daerah Khusus Ibukota Jakarta 13920', 'https://maps.app.goo.gl/5gnrZHhe1yTPGvF96', '2025-12-20 02:26:47', 11),
(5, 'SMPIT Gema Nurani', 'Jl. Raya Kaliabang Tengah No.75B, Kaliabang Tengah, Kec. Bekasi Utara, Kota Bks, Jawa Barat 17125', 'https://maps.app.goo.gl/JjEhxaYaHp1YxcEk9', '2025-12-20 02:26:47', NULL),
(6, 'SDIT Gema Nurani', 'Jl. Raya Kaliabang Tengah No.75B, Kaliabang Tengah, Kec. Bekasi Utara, Kota Bks, Jawa Barat 17125', 'https://maps.app.goo.gl/TXivrD813xcP4doK9', '2025-12-20 02:26:47', NULL),
(7, 'Harapan Baru 7', '', '', '2025-12-20 02:26:47', 25),
(8, 'Asera One South Harapan Indah', 'Jl. Asera Boulevard, Pusaka Rakyat, Kec. Tarumajaya, Kabupaten Bekasi, Jawa Barat 17214', 'https://maps.app.goo.gl/R1qEpRkbm8jZJLJK7', '2025-12-20 02:26:47', 10),
(9, 'Boulevard Harapan Indah', 'Jl. Palem Botol I, RT.002/RW.024, Pejuang, Kecamatan Medan Satria, Kota Bks, Jawa Barat 17131', 'https://maps.app.goo.gl/LemYZSqqzvNTQhPX9', '2025-12-20 02:26:47', 25),
(10, 'SDN Menteng Dalam 11', 'Jl. Prof. DR. Soepomo No.RT 005/001 5, RT.5/RW.1, Menteng Dalam, Kec. Tebet, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12870', 'https://maps.app.goo.gl/5cBKndBySM5ZGELA6', '2025-12-20 02:26:47', NULL),
(11, 'SDN Tebet Barat 08', 'Jl. Tebet Barat X No.4 14, RT.14/RW.5, Tebet Bar., Kec. Tebet, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12810', 'https://maps.app.goo.gl/9VQiEsQeg6mwhCwa8', '2025-12-20 02:26:47', NULL),
(12, 'SDN Menteng Dalam 07', 'RT.6/RW.14, Kb. Baru, Kec. Tebet, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12870', 'https://maps.app.goo.gl/yhVnGvpwst3u8q1TA', '2025-12-20 02:26:47', NULL),
(13, 'SDN Bukit Duri 05', 'Jl. Peruk No.32, RT.8/RW.3, Bukit Duri, Kec. Tebet, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12840', 'https://maps.app.goo.gl/nptEMFf3bAMTvwm8A', '2025-12-20 02:26:47', 25);

-- --------------------------------------------------------

--
-- Table structure for table `flyers`
--

CREATE TABLE `flyers` (
  `id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `flyers`
--

INSERT INTO `flyers` (`id`, `image`, `is_active`, `created_at`) VALUES
(1, 'flyer_1767018469.jpg', 0, '2025-12-29 07:27:50'),
(2, 'flyer_1767671239.jpg', 0, '2026-01-05 20:47:19'),
(3, 'flyer_1768285071.png', 0, '2026-01-12 23:17:51'),
(4, 'flyer_1771253378.jpg', 0, '2026-02-16 14:49:38'),
(5, 'flyer_1772084678.jpeg', 0, '2026-02-26 05:44:38'),
(6, 'flyer_1774096719.jpg', 1, '2026-03-21 12:38:39');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `source` varchar(150) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `content`, `source`, `image`, `created_at`) VALUES
(1, 'Fattah Meraih Medali Emas di Hari Pertama untuk Team Antero Taekwondo Club', 'Fattaah Nur Halim atau yang akrab disapa dengan nama Fattaah. Pada Kejuaraan Taekwondo KONI CUP Series 6 Indonesia Taekwondo Championship 2025 dalam kategori kyorugi prestasi putra Cadet U 49 kg,Fattah berhasil meraih Medali Emas pertama di hari pertama untuk team Antero Taekwondo Club.\r\n\r\nFattaah, seorang atlet Taekwondo  yang telah lama menekuni dalam olahraga ini, kini ia kembali menunjukkan keunggulannya. Kejuaraan ini, yang berlangsung mulai dari tanggal 18 hingga 21 Desember 2025, di GOR POPKI Cibubur, Jakarta Timur, menjadi ajang unggulan bagi para atlet Taekwondo di Indonesia.\r\n\r\nDalam pertandingan yang sengit dan penuh persaingan ketat, Fattaah berhasil mengalahkan beberapa pesaingnya dan meraih prestasi yang optimal. Medali Emas yang diraihnya dalam kategori kyorugi menjadi bukti nyata kecintaannya pada olahraga Taekwondo dan dedikasi kerasnya dalam berlatih.\r\n\r\n\"Alhamdulillah, saya senang banget bisa ikut kejuaraan ini dan berhasil meraih medali emas pertama dihari pertama untuk team Antero taekwondo club dan saya berharap untuk teman2 yg bertanding dihari berikutnya bisa memperoleh medali emas juga,\"ujar Fattaah dengan mata berbinar penuh kebahagiaan.\r\n\r\nBegitu juga untuk para pelatih TC Antero Taekwondo Club ikut merasa senang dan berbahagia atas pencapaian atletnya.\r\n\r\n\"Alhamdulillah,untuk hari pertama dikejuaraan KONi cup series 6 ini,kita memperoleh 1 medali emas kyorugi prestasi atas nama Fattaah Nur Halim dan 1 medali emas kyorugi pemula diraih dari atlet atas nama Zaky zhafran,saya ucapkan Terimakasih telah berjuang keras,\" ucap kwanjangnim bayu salah satu pelatih TC Antero Taekwondo Club.\r\n\r\n\"Bagi yang belum berhasil, jangan putus asa, terus berusaha latihan lebih keras lagi,dan untuk para atlet yg besok bertanding persiapan diri,semoga memperoleh hasil yang terbaik,\"ucapnya lagi.(Angga)', 'https://www.jakartaforum.web.id/2025/12/fattah-meraih-medali-emas-di-hari.html', '1766132312_IMG-20251218-WA0058.jpg', '2025-12-19 01:18:32'),
(2, 'Fattah Nur Halim Sumbang medali Emas pertama untuk Antero Taekwondo Club.', 'Postnewstime,JAKARTA,-Fattaah Nur Halim atau yang akrab disapa dengan nama Fattaah. Pada Kejuaraan Taekwondo KONI CUP Series 6 Indonesia Taekwondo Championship 2025 dalam kategori kyorugi prestasi putra Cadet U 49 kg,Fattah berhasil meraih Medali Emas pertama di hari pertama untuk team Antero Taekwondo Club.\r\n\r\nFattaah, seorang atlet Taekwondo  yang telah lama menekuni dalam olahraga ini, kini ia kembali menunjukkan keunggulannya. Kejuaraan ini, yang berlangsung mulai dari tanggal 18 hingga 21 Desember 2025, di GOR POPKI Cibubur, Jakarta Timur, menjadi ajang unggulan bagi para atlet Taekwondo di Indonesia.\r\n\r\nDalam pertandingan yang sengit dan penuh persaingan ketat, Fattaah berhasil mengalahkan beberapa pesaingnya dan meraih prestasi yang optimal. Medali Emas yang diraihnya dalam kategori kyorugi menjadi bukti nyata kecintaannya pada olahraga Taekwondo dan dedikasi kerasnya dalam berlatih.\r\n\r\n\"Alhamdulillah, saya senang banget bisa ikut kejuaraan ini dan berhasil meraih medali emas pertama dihari pertama untuk team Antero taekwondo club dan saya berharap untuk teman2 yg bertanding dihari berikutnya bisa memperoleh medali emas juga,\"ujar Fattaah dengan mata berbinar penuh kebahagiaan.\r\n\r\nBegitu juga untuk para pelatih TC Antero Taekwondo Club ikut merasa senang dan berbahagia atas pencapaian atletnya.\r\n\r\n\"Alhamdulillah,untuk hari pertama dikejuaraan KONi cup series 6 ini,kita memperoleh 1 medali emas kyorugi prestasi atas nama Fattaah Nur Halim dan 1 medali emas kyorugi pemula diraih dari atlet atas nama Zaky zhafran,saya ucapkan Terimakasih telah berjuang keras,\" ucap kwanjangnim bayu salah satu pelatih TC Antero Taekwondo Club.\r\n\r\n\"Bagi yang belum berhasil, jangan putus asa, terus berusaha latihan lebih keras lagi,dan untuk para atlet yg besok bertanding persiapan diri,semoga memperoleh hasil yang terbaik,\"ucapnya lagi.\r\n(Adams)', 'https://postnewstimeonline.blogspot.com/2025/12/fattah-nur-halim-sumbang-medali-emas.html', '1766205305_1766077010804087-1.webp', '2025-12-19 21:35:05'),
(3, 'Rayakan Hari Jadi Ke 8, Antero Taekwondo Club Gelar Syukuran', 'Mengusung tema â€œKebersamaan,Disiplin,dan perjuangan tanpa batasâ€ Antero Taekwondo Club (ATC) merayakan Hari Ulang Tahun ke-8,minggu (11/01/2026) bertempat di Tranning Center Antero Taekwondo Club lapangan futsal RW 014 Harapan Baru Regency,bekasi\r\n\r\nDalam suasana yang penuh kesederhanaan dan kekeluargaan, acara tersebut mempererat ikatan antara pelatih,atlet dan orang tua.\r\n\r\nâ€œAntero Taekwondo Club berdirinya 31 Desember 2023,12 hari yg lalu,Kami merayakan ulang tahun ini dengan memperkokoh hubungan antara orang tua, pelatih, dan para atlet.\r\n\r\nMulai dari doa bersama, peniupan lilin, hingga main games,dan makan bersama,â€ ungkap pemilik Antero Taekwondo Club Kwanjangnim bayu\r\n\r\nPerayaan ulang tahun Antero taekwondo club ke 8 tahun kali ini,Antero Taekwondo Club juga memberi Apresiasi dan penghargaan kepada para Atlet sebagai bentuk penghargaan atas dedikasi ,disiplin dan prestasi atletnya.\r\n\r\nDiantaranya adalah :\r\natlet terbaik putra : M.Fadli Hafizh\r\natlet terbaim putri : Naysilla Putri\r\natlet disiplin : Doni Tri Erlangga\r\natlet berprestasi : Syifa Agisna Maulida\r\n\r\nHarapannya, dengan momentum ulang tahun ini, Antero Taekwondo Club dapat terus berkembang dan melahirkan lebih banyak atlet taekwondo yang berprestasi.\r\n\r\nDengan semangat yang tak kenal lelah, Antero Taekwondo Club berkomitmen siap melangkah ke depan menuju prestasi yang lebih gemilang.\r\n\r\nâ€œSemoga Antero Taekwondo Club terus tumbuh dan menginspirasi untuk menjadi atlet taekwondo yang sukses dan berdedikasi,â€ tambah kwanjangnim bayu\r\n\r\nDan tak lupa juga,kwanjangnim Bayu menyampaikan ucapan terima kasih kepada semua pihak yang telah mendukung Antero Taekwondo Club hingga mencapai usia 8 tahun dan meraih berbagai prestasi.\r\n\r\nâ€œTentunya tanpa dukungan dan support dari semua pihak, semua ini tidak bisa kami capai sendiri,â€ ungkapnya.', 'https://radiomuaranetwork.id/2026/01/11/rayakan-hari-jadi-ke-8-antero-taekwondo-club-gelar-syukuran/', '1768283570_IMG-20260111-WA0137.jpg', '2026-01-12 22:52:50'),
(4, 'Lionel,Raih Juara 1 dan Penghargaan Atlet Putra Junior Terbaik Di Kejuaraan Liga DKI Jakarta series 12', 'Jakarta Forum - Prestasi membanggakan kembali diraih Atlet Antero Taekwondo Club(ATC),Lionel Brana Laisila,berhasil meraih Juara 1 Kelas junior Under  45 kg Putra sekaligus dinobatkan sebagai Atlet Terbaik junior Putra pada ajang Kejuaraan Liga DKI Jakarta series 12\r\n\r\nKejuaraan bergengsi yang berlangsung di GOR Ciracas,Jakarta Timur, pada jum\'atâ€“Minggu (13â€“15 Februari 2026) ini diikuti oleh kurang lebih 2500 dari atlet pra cadet hingga senior.\r\n\r\n\r\n\r\nLiga ini, yang diselenggarakan oleh Pengurus Provinsi Taekwondo Indonesia (Pengorov TI) DKI  Jakarta yang gelar rutin tiga kali dalam setahun, bertujuan sebagai ajang pembinaan atlet muda, persiapan menuju jenjang nasional dan internasional, sekaligus mengasah mental dan teknik bertanding mereka di bawah arahan para pelatih profesional.\r\n\r\n\r\n\r\nDengan semangat juang dan kerja keras yang luar biasa,Lionel sukses menunjukkan performa terbaiknya hingga menyabet dua gelar bergengsi sekaligus. Gelar Atlet Terbaik junior Putra yang disandangnya merupakan pengakuan atas keunggulan menyeluruh yang ditunjukkannya. \r\n\r\nPenilaian tidak hanya berdasarkan kemenangan semata, tetapi juga sportivitas, kualitas teknik bertanding, dan konsistensi performa. Dalam semua aspek tersebut, Lionel terbukti unggul dibandingkan pesaing-pesaingnya.\r\n\r\nPenampilan Lionel di kejuaraan Liga DKI jakarta series 12 ini mampu memukau penonton, gerakan serangan kaki dan tangan yang sangat cepat mampu membuat lawannya kewalahan dalam bertahan.\r\n\r\nLionel, yang kini duduk di kelas 10 SMAN 76,jakarta, memperlihatkan performa impresif dengan menyapu bersih lima pertandingan di setiap babaknya,termasuk babak final. \r\n\r\nLionel menuturkan rasa syukurnya atas pencapaian ini, seraya mengungkapkan tantangan berat yang ia hadapi di setiap pertandingan.\r\n\r\nâ€œSaya sangat bersyukur dengan hasilnya. Semua lawan di setiap babak sangat tangguh, tidak ada yang mudah, namun saya berusaha fokus, tenang dan yang terpenting mendengarkan instruksi pelatih,â€ ungkapnya usai menerima medali.\r\n\r\nSalah satu pelatih Antero Taekwondo Club,Kwanjangnim Bayu juga menuturkan rasa bersyukurnya atas prestasi yg diperoleh para atletnya.\r\n \"Alhamdulillah tabarakallah untuk lionel mendapat penghargaan The best player male junior, Terimakasih atas pencapaianmu di Liga 12 DKI Jakarta.\r\n\r\nMempertahankan prestasi lebih sulit, jangan cepat puas dan terlalu euforia, tatap langkah kedepan lebih baik lagi, hal ini merupakan buah dari kerja keras mereka yang rutin berlatih.\r\n\r\nKepada pelatih serta orang tua,jangan pernah menyerah dan teruslah mendukung anak-anaknya sehingga mampu melahirkan prestasi lainnya,â€ pungkasnya', 'Jakartaforum', '1771252329_WhatsApp Image 2026-02-15 at 19.29.29.jpeg', '2026-02-16 14:32:09');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dojang_id` int(11) DEFAULT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `tempat_lahir` varchar(50) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `tingkatan_sabuk` varchar(50) NOT NULL,
  `status` enum('aktif','tidak aktif') DEFAULT 'aktif',
  `foto_sertifikat` varchar(255) DEFAULT NULL,
  `alamat_domisili` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `dojang_id`, `nama_lengkap`, `tempat_lahir`, `tanggal_lahir`, `tingkatan_sabuk`, `status`, `foto_sertifikat`, `alamat_domisili`, `created_at`, `is_deleted`, `deleted_at`) VALUES
(1, 2, 8, 'Ribut Wahyudi', 'Jakarta', '1990-01-06', 'DAN I', 'aktif', 'cert_2_1768285620.pdf', 'Panjibuwono City, Cluster Sriwedari Blok CS-6/32 Babelan, Bekasi Utara', '2025-12-17 06:26:14', 0, NULL),
(3, 4, NULL, 'Rudi Hartono', 'Jakarta', '2007-08-17', 'Putih/Geup-10', 'aktif', NULL, 'Jl. Contoh No. 3', '2025-12-17 06:26:14', 0, NULL),
(16, 19, 13, 'Dzulqarnain Tsaqib Kuncahyo', 'Jakarta', '2008-01-09', 'DAN I', 'aktif', 'cert_19_1771417595.jpg', 'Jln Lapangan Roos III No.1 RT 003/005, Bukit Duri Tebet, Jakarta Selatan', '2026-01-16 00:35:39', 0, NULL),
(17, 20, 13, 'Ameera nurfaiqah dayana prasetia', 'Jakarta', '2014-04-23', 'Hijau Strip/Geup-6', 'aktif', 'cert_20_1770189706.jpg', 'Jl.tebet timur dalam IX F no.10 tebet', '2026-02-04 07:21:46', 0, NULL),
(18, 22, 13, 'KALIA PUTRI MAHESWARI', 'Jakarta', '2015-12-09', 'Hijau/Geup-7', 'aktif', 'cert_22_1770277032.pdf', 'Jl. Tebet Dalam IV No. 34, RT/ RW: 012/ 01, Kel. Tebet Barat, Kec. Tebet, Jakarta Selatan', '2026-02-05 07:02:44', 0, NULL),
(20, 26, NULL, 'tes1', 'tangerang', '2019-05-05', 'Merah/Geup-3', 'aktif', NULL, 'jagvfi lfyuhikjlmg', '2026-03-04 02:34:13', 0, NULL),
(21, 27, 13, 'Bayu Kristiawan', 'Jakarta', '1978-03-27', 'DAN V', 'aktif', 'cert_27_1773133109.jpg', 'JL. Durian Dalam No. 36 RT 002 RW 008 Rawa Bebek Kota Bekasi', '2026-03-10 08:58:29', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_belt_history`
--

CREATE TABLE `student_belt_history` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `tingkatan_sabuk` varchar(100) NOT NULL,
  `foto_sertifikat` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_belt_history`
--

INSERT INTO `student_belt_history` (`id`, `student_id`, `tingkatan_sabuk`, `foto_sertifikat`, `created_at`) VALUES
(1, 1, 'Putih/Geup-10', NULL, '2026-01-05 00:29:48'),
(3, 3, 'Putih/Geup-10', NULL, '2026-01-05 00:29:48'),
(16, 1, 'DAN II', 'cert_2_1768185547.png', '2026-01-11 18:39:07'),
(17, 1, 'DAN I', 'cert_2_1768285620.pdf', '2026-01-12 22:27:00'),
(18, 16, 'DAN I', 'cert_19_1768548939.jpeg', '2026-01-15 23:35:39'),
(19, 17, 'Hijau Strip/Geup-6', 'cert_20_1770189706.jpg', '2026-02-03 23:21:46'),
(20, 18, 'Hijau/Geup-7', 'cert_22_1770274965.pdf', '2026-02-04 23:02:44'),
(21, 18, 'Hijau/Geup-7', 'cert_22_1770277011.pdf', '2026-02-04 23:36:51'),
(22, 18, 'Hijau/Geup-7', 'cert_22_1770277032.pdf', '2026-02-04 23:37:12'),
(23, 16, 'DAN I', 'cert_19_1771417561.jpg', '2026-02-18 04:26:01'),
(24, 16, 'DAN I', 'cert_19_1771417595.jpg', '2026-02-18 04:26:35'),
(25, 21, 'DAN V', 'cert_27_1773133109.jpg', '2026-03-10 01:58:29');

-- --------------------------------------------------------

--
-- Table structure for table `student_dojang_history`
--

CREATE TABLE `student_dojang_history` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `old_dojang_id` int(11) DEFAULT NULL,
  `new_dojang_id` int(11) DEFAULT NULL,
  `change_date` datetime DEFAULT current_timestamp(),
  `reason` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `student_dojang_history`
--

INSERT INTO `student_dojang_history` (`id`, `student_id`, `old_dojang_id`, `new_dojang_id`, `change_date`, `reason`) VALUES
(1, 19, 1, 14, '2026-02-17 23:22:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_trainings`
--

CREATE TABLE `student_trainings` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `year` int(11) NOT NULL,
  `certificate_file` varchar(255) NOT NULL,
  `status` enum('pending','verified','rejected') DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_trainings`
--

INSERT INTO `student_trainings` (`id`, `student_id`, `name`, `year`, `certificate_file`, `status`, `admin_note`, `created_at`) VALUES
(1, 3, 'test', 2024, '1768656214_3.png', 'verified', '', '2026-01-17 13:23:34'),
(2, 1, 'wkwk', 2024, '1771552270_1.jpeg', 'pending', NULL, '2026-02-20 01:51:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','siswa') NOT NULL DEFAULT 'siswa',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `foto_profil` varchar(255) DEFAULT NULL,
  `login_count` int(11) DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`, `foto_profil`, `login_count`, `last_login`, `is_deleted`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$zDV0E0SGG6OFdfEK67SbMu2SxpbyVJY1/vwuEDY2XpHyEA.9zKoKm', 'admin', '2025-12-17 06:26:14', 'profile_1_1767671267.png', 86, '2026-03-21 05:38:09', 0),
(2, 'siswa1', 'school@gmail.com', '$2y$10$LSTFVDcTFn21sJmF4KVjJON.Virx4B0/eL6QGeHsagXknDqqbPm22', 'siswa', '2025-12-17 06:26:14', 'profile_2_1768285620.jpg', 34, '2026-03-10 01:57:39', 0),
(4, 'siswa3', NULL, '$2y$10$AOy00SR/e4JpZU0g.PaGx.rac6nKnBti6XY..jwlKjvXhXk7vPMPy', 'siswa', '2025-12-17 06:26:14', NULL, 1, '2026-01-17 20:22:57', 0),
(19, 'dztsaqibk', 'dzultsaqib@gmail.com', '$2y$10$diKC12ta5ZXFWK21NN8pOeunDPsyTorOQDuO584UzYgC/NnB4a4G6', 'siswa', '2026-01-16 00:32:48', 'profile_19_1768548997.jpeg', 5, '2026-02-18 04:22:06', 0),
(20, 'Ameera', 'satriana.susi@yahoo.co.id', '$2y$10$rDePY98bECmWMEEafoJ/U.FAhlEFg3ttLa.zesqWLclAq687yxpSC', 'siswa', '2026-02-04 07:13:55', NULL, 1, '2026-02-03 23:14:20', 0),
(21, 'Muhammad Bilal Alfarizi', 'itikadarsih@gmail.com', '$2y$10$mWv.at9WTkD8Q2w3R7P/K.1kM5oQLaxJfH2u3e43Xi4tJI1PXHNn6', 'siswa', '2026-02-05 01:02:06', NULL, 4, '2026-02-16 07:05:49', 0),
(22, 'KALIA', 'tgh.senoaji@gmail.com', '$2y$10$PNE9rYo.aUho1zPXgExUV.4SqQXQxKuq8B0Go9it05HA9CEEVZ9dq', 'siswa', '2026-02-05 06:50:06', 'profile_22_1770274965.jpeg', 6, '2026-03-22 07:25:31', 0),
(23, 'Nabilah Salwa Azâ€™zahra', 'nabilahsalwa2006@gmail.com', '$2y$10$NeGh66DHixXn0RS10JX7GOY2ar/.kV.qO5sv0NzAXbtNHTckwdRI6', 'siswa', '2026-02-16 14:54:35', NULL, 1, '2026-02-16 06:54:52', 0),
(24, 'Raihanabyasa', 'raihanabyasawb@gmail.com', '$2y$10$Nc5gW5L.xNbgQva0oqWdTOstHkMVtme6tieRvR6n01ZyoYhMS9.XS', 'siswa', '2026-02-16 14:55:32', NULL, 1, '2026-02-16 06:55:41', 0),
(26, 'tes1188', NULL, '$2y$10$B0nwTNZ.oTdZNSUPT25ztu.Defm66sS/AV81AU1hsAJ0zC7snAKza', 'siswa', '2026-03-04 02:34:13', NULL, 0, NULL, 0),
(27, 'Kwanjangnim', 'bayukristiawan.wfdd@gmail.com', '$2y$10$WI4zXd2yS249SHcn/IvJqONoirY2e0zcxgwXq6EiXoTan9jsACl5.', 'siswa', '2026-03-10 08:12:52', 'profile_27_1773133109.jpg', 8, '2026-03-14 03:08:05', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `club_info`
--
ALTER TABLE `club_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coaches`
--
ALTER TABLE `coaches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coach_trainings`
--
ALTER TABLE `coach_trainings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coach_id` (`coach_id`);

--
-- Indexes for table `dojangs`
--
ALTER TABLE `dojangs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coach_id` (`coach_id`);

--
-- Indexes for table `flyers`
--
ALTER TABLE `flyers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `students_fk_dojang` (`dojang_id`);

--
-- Indexes for table `student_belt_history`
--
ALTER TABLE `student_belt_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_dojang_history`
--
ALTER TABLE `student_dojang_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `old_dojang_id` (`old_dojang_id`),
  ADD KEY `new_dojang_id` (`new_dojang_id`);

--
-- Indexes for table `student_trainings`
--
ALTER TABLE `student_trainings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `achievements`
--
ALTER TABLE `achievements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `club_info`
--
ALTER TABLE `club_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `coaches`
--
ALTER TABLE `coaches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `coach_trainings`
--
ALTER TABLE `coach_trainings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `dojangs`
--
ALTER TABLE `dojangs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `flyers`
--
ALTER TABLE `flyers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `student_belt_history`
--
ALTER TABLE `student_belt_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `student_dojang_history`
--
ALTER TABLE `student_dojang_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_trainings`
--
ALTER TABLE `student_trainings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `achievements`
--
ALTER TABLE `achievements`
  ADD CONSTRAINT `achievements_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `coach_trainings`
--
ALTER TABLE `coach_trainings`
  ADD CONSTRAINT `coach_trainings_ibfk_1` FOREIGN KEY (`coach_id`) REFERENCES `coaches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_fk_dojang` FOREIGN KEY (`dojang_id`) REFERENCES `dojangs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_belt_history`
--
ALTER TABLE `student_belt_history`
  ADD CONSTRAINT `student_belt_history_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_trainings`
--
ALTER TABLE `student_trainings`
  ADD CONSTRAINT `student_trainings_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

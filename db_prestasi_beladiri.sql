-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table db_prestasi_beladiri.achievements
CREATE TABLE IF NOT EXISTS `achievements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `nama_kejuaraan` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `championship_year` int DEFAULT NULL,
  `tingkat` enum('Daerah','Nasional','Internasional') COLLATE utf8mb4_general_ci NOT NULL,
  `juara_ke` int NOT NULL,
  `file_sertifikat` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tgl_verifikasi` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `achievements_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_prestasi_beladiri.achievements: ~9 rows (approximately)
INSERT INTO `achievements` (`id`, `student_id`, `nama_kejuaraan`, `championship_year`, `tingkat`, `juara_ke`, `file_sertifikat`, `status`, `created_at`, `tgl_verifikasi`) VALUES
	(1, 1, 'Invitasi Taekwondo Antar Klub', NULL, 'Daerah', 1, 'dummy.jpg', 'rejected', '2025-12-17 06:26:14', NULL),
	(3, 3, 'Invitasi Taekwondo Antar Klub', NULL, 'Daerah', 1, 'dummy.jpg', 'rejected', '2025-12-17 06:26:14', NULL),
	(4, 3, 'Invitasi Taekwondo Antar Klub', NULL, 'Daerah', 1, 'dummy.jpg', 'rejected', '2025-12-17 06:26:14', NULL),
	(5, 3, 'Piala Walikota 2024', NULL, 'Daerah', 2, 'dummy.jpg', 'approved', '2025-12-17 06:26:14', NULL),
	(10, 1, 'efiuef', NULL, 'Daerah', 2, '1765978784_1244286.jpg', 'approved', '2025-12-17 06:39:44', '2025-12-17 20:41:14'),
	(11, 1, 'tes', 2025, 'Daerah', 1, '1766465922_IMG-20251218-WA0058.jpg', 'rejected', '2025-12-22 21:58:42', NULL),
	(12, 1, 'tes 2', 2025, 'Daerah', 1, '1766467692_IMG-20251218-WA0058.jpg', 'approved', '2025-12-22 22:28:12', NULL),
	(13, 1, 'tes2', 2025, 'Daerah', 1, '1766547866_WhatsApp Image 2025-12-23 at 22.52.09.jpeg', 'approved', '2025-12-23 20:44:26', NULL),
	(15, 1, 'Bharaduta Championship 5', 2025, 'Nasional', 2, '1768285909_Kejurnas Bharaduta Ribut.pdf', 'approved', '2026-01-12 23:31:49', NULL);

-- Dumping structure for table db_prestasi_beladiri.club_info
CREATE TABLE IF NOT EXISTS `club_info` (
  `id` int NOT NULL AUTO_INCREMENT,
  `club_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT 'Ankero Taekwondo Club',
  `instagram` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tiktok` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `youtube` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_prestasi_beladiri.club_info: ~1 rows (approximately)
INSERT INTO `club_info` (`id`, `club_name`, `instagram`, `tiktok`, `youtube`, `updated_at`) VALUES
	(1, 'Antero Taekwondo Club', ' https://www.instagram.com/anterotaekwondoclub?igsh=MW5jbml1MXNpdmpsaw==', 'https://www.tiktok.com/@anterotaekwondoclub?_r=1&_t=ZS-92W9R5hFtci', 'http://www.youtube.com/@anterotaekwondoclub238', '2025-12-25 06:54:33');

-- Dumping structure for table db_prestasi_beladiri.coaches
CREATE TABLE IF NOT EXISTS `coaches` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_pelatih` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `riwayat_pelatihan` text COLLATE utf8mb4_general_ci NOT NULL,
  `tingkatan` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `info_sertifikat` text COLLATE utf8mb4_general_ci,
  `foto_pelatih` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_prestasi_beladiri.coaches: ~6 rows (approximately)
INSERT INTO `coaches` (`id`, `nama_pelatih`, `riwayat_pelatihan`, `tingkatan`, `info_sertifikat`, `foto_pelatih`, `created_at`) VALUES
	(9, 'Nazhira Raka Putri', 'Pelatihan Dasar 2010, Pelatihan Lanjut 2020', 'DAN I', '-', '1768545531_WhatsApp Image 2026-01-16 at 13.38.19.jpeg', '2025-12-20 02:33:15'),
	(10, 'Ribut Wahyudi', 'Pelatihan Dasar 2010, Pelatihan Lanjut 2021', 'DAN I', 'Sertifikat Coaching Clinic Pelatih Fisik Taekwondo Kyorugi', '1768286541_IMG-20260111-WA0137.jpg', '2025-12-20 02:33:15'),
	(11, 'Ajeng Restu Arini', 'Pelatihan Dasar 2010, Pelatihan Lanjut 2022', 'DAN III', '-', '1768545239_WhatsApp Image 2026-01-16 at 13.32.56.jpeg', '2025-12-20 02:33:15'),
	(12, 'Eko Prasetyo', 'Pelatihan Dasar 2010, Pelatihan Lanjut 2023', 'DAN I', 'Sertifikat Pelatih Nasional Tahun 2023', '', '2025-12-20 02:33:15'),
	(13, 'Sri Wahyuni', 'Pelatihan Dasar 2010, Pelatihan Lanjut 2024', 'DAN I', 'Sertifikat Pelatih Nasional Tahun 2024', '', '2025-12-20 02:33:15'),
	(14, 'Dzulqarnain Tsaqib', '-', 'DAN I', '-', '1768548616_WhatsApp Image 2026-01-16 at 14.01.57.jpeg', '2026-01-16 00:30:16');

-- Dumping structure for table db_prestasi_beladiri.coach_trainings
CREATE TABLE IF NOT EXISTS `coach_trainings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `coach_id` int NOT NULL,
  `year` int NOT NULL,
  `level` enum('Daerah','Nasional','Internasional') COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `certificate_file` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `coach_id` (`coach_id`),
  CONSTRAINT `coach_trainings_ibfk_1` FOREIGN KEY (`coach_id`) REFERENCES `coaches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_prestasi_beladiri.coach_trainings: ~6 rows (approximately)
INSERT INTO `coach_trainings` (`id`, `coach_id`, `year`, `level`, `description`, `certificate_file`, `created_at`) VALUES
	(18, 13, 2025, 'Daerah', 'ieucheich', NULL, '2026-01-05 06:25:36'),
	(19, 12, 2023, 'Nasional', 'Pelatihan Wasit Nasional', NULL, '2026-01-05 06:25:44'),
	(37, 10, 2025, 'Nasional', 'Pelatihan Strength And Conditioning', NULL, '2026-01-12 23:48:09'),
	(38, 10, 2025, 'Nasional', 'Workshop Nasional Sport Injury & Mental Health Awareness', NULL, '2026-01-12 23:48:09'),
	(39, 10, 2025, 'Daerah', 'Coaching Clinic Pelatih Fisik Taekwondo Kyorugi', NULL, '2026-01-12 23:48:09'),
	(40, 11, 2023, 'Nasional', 'Pelatihan Wasit Nasional', NULL, '2026-01-15 23:33:59');

-- Dumping structure for table db_prestasi_beladiri.dojangs
CREATE TABLE IF NOT EXISTS `dojangs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_dojang` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_general_ci,
  `google_maps` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `coach_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `coach_id` (`coach_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_prestasi_beladiri.dojangs: ~13 rows (approximately)
INSERT INTO `dojangs` (`id`, `nama_dojang`, `alamat`, `google_maps`, `created_at`, `coach_id`) VALUES
	(1, 'Garuda Taekwondo Club', 'Jl. Merdeka No. 45, Jakarta Pusat', ' NULL', '2025-12-17 06:26:14', NULL),
	(2, 'Satria Dojang', 'Jl. Sudirman No. 10, Jakarta Selatan', ' NULL', '2025-12-17 06:26:14', NULL),
	(3, 'SDN Manggarai 05', 'Jl. Swadaya I, RW No, RT.6/RW.1, 09, Manggarai, Kec. Tebet, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12850', 'https://maps.app.goo.gl/idWfAMLEDJGQuxWe8', '2025-12-17 06:26:14', NULL),
	(4, 'TK Al-Burdah', 'Jl. Dr. KRT Radjiman Widyodiningrat No.58, RT.6/RW.6, Rw. Terate, Kec. Cakung, Kota Jakarta Timur, Daerah Khusus Ibukota Jakarta 13920', 'https://maps.app.goo.gl/5gnrZHhe1yTPGvF96', '2025-12-20 02:26:47', NULL),
	(5, 'SMPIT Gema Nurani', 'Jl. Raya Kaliabang Tengah No.75B, Kaliabang Tengah, Kec. Bekasi Utara, Kota Bks, Jawa Barat 17125', 'https://maps.app.goo.gl/JjEhxaYaHp1YxcEk9', '2025-12-20 02:26:47', NULL),
	(6, 'SDIT Gema Nurani', 'Jl. Raya Kaliabang Tengah No.75B, Kaliabang Tengah, Kec. Bekasi Utara, Kota Bks, Jawa Barat 17125', 'https://maps.app.goo.gl/TXivrD813xcP4doK9', '2025-12-20 02:26:47', NULL),
	(7, 'Harapan Baru 7', '', '', '2025-12-20 02:26:47', NULL),
	(8, 'Asera One South Harapan Indah', 'Jl. Asera Boulevard, Pusaka Rakyat, Kec. Tarumajaya, Kabupaten Bekasi, Jawa Barat 17214', 'https://maps.app.goo.gl/R1qEpRkbm8jZJLJK7', '2025-12-20 02:26:47', NULL),
	(9, 'Boulevard Harapan Indah', 'Jl. Palem Botol I, RT.002/RW.024, Pejuang, Kecamatan Medan Satria, Kota Bks, Jawa Barat 17131', 'https://maps.app.goo.gl/LemYZSqqzvNTQhPX9', '2025-12-20 02:26:47', NULL),
	(10, 'SDN Menteng Dalam 11', 'Jl. Prof. DR. Soepomo No.RT 005/001 5, RT.5/RW.1, Menteng Dalam, Kec. Tebet, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12870', 'https://maps.app.goo.gl/5cBKndBySM5ZGELA6', '2025-12-20 02:26:47', NULL),
	(11, 'SDN Tebet Barat 08', 'Jl. Tebet Barat X No.4 14, RT.14/RW.5, Tebet Bar., Kec. Tebet, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12810', 'https://maps.app.goo.gl/9VQiEsQeg6mwhCwa8', '2025-12-20 02:26:47', NULL),
	(12, 'SDN Menteng Dalam 07', 'RT.6/RW.14, Kb. Baru, Kec. Tebet, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12870', 'https://maps.app.goo.gl/yhVnGvpwst3u8q1TA', '2025-12-20 02:26:47', NULL),
	(13, 'SDN Bukit Duri 05', 'Jl. Peruk No.32, RT.8/RW.3, Bukit Duri, Kec. Tebet, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12840', 'https://maps.app.goo.gl/nptEMFf3bAMTvwm8A', '2025-12-20 02:26:47', 11);

-- Dumping structure for table db_prestasi_beladiri.flyers
CREATE TABLE IF NOT EXISTS `flyers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `image` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_prestasi_beladiri.flyers: ~3 rows (approximately)
INSERT INTO `flyers` (`id`, `image`, `is_active`, `created_at`) VALUES
	(1, 'flyer_1767018469.jpg', 0, '2025-12-29 07:27:50'),
	(2, 'flyer_1767671239.jpg', 0, '2026-01-05 20:47:19'),
	(3, 'flyer_1768285071.png', 1, '2026-01-12 23:17:51');

-- Dumping structure for table db_prestasi_beladiri.news
CREATE TABLE IF NOT EXISTS `news` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `content` text COLLATE utf8mb4_general_ci NOT NULL,
  `source` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_prestasi_beladiri.news: ~3 rows (approximately)
INSERT INTO `news` (`id`, `title`, `content`, `source`, `image`, `created_at`) VALUES
	(1, 'Fattah Meraih Medali Emas di Hari Pertama untuk Team Antero Taekwondo Club', 'Fattaah Nur Halim atau yang akrab disapa dengan nama Fattaah. Pada Kejuaraan Taekwondo KONI CUP Series 6 Indonesia Taekwondo Championship 2025 dalam kategori kyorugi prestasi putra Cadet U 49 kg,Fattah berhasil meraih Medali Emas pertama di hari pertama untuk team Antero Taekwondo Club.\r\n\r\nFattaah, seorang atlet Taekwondo  yang telah lama menekuni dalam olahraga ini, kini ia kembali menunjukkan keunggulannya. Kejuaraan ini, yang berlangsung mulai dari tanggal 18 hingga 21 Desember 2025, di GOR POPKI Cibubur, Jakarta Timur, menjadi ajang unggulan bagi para atlet Taekwondo di Indonesia.\r\n\r\nDalam pertandingan yang sengit dan penuh persaingan ketat, Fattaah berhasil mengalahkan beberapa pesaingnya dan meraih prestasi yang optimal. Medali Emas yang diraihnya dalam kategori kyorugi menjadi bukti nyata kecintaannya pada olahraga Taekwondo dan dedikasi kerasnya dalam berlatih.\r\n\r\n"Alhamdulillah, saya senang banget bisa ikut kejuaraan ini dan berhasil meraih medali emas pertama dihari pertama untuk team Antero taekwondo club dan saya berharap untuk teman2 yg bertanding dihari berikutnya bisa memperoleh medali emas juga,"ujar Fattaah dengan mata berbinar penuh kebahagiaan.\r\n\r\nBegitu juga untuk para pelatih TC Antero Taekwondo Club ikut merasa senang dan berbahagia atas pencapaian atletnya.\r\n\r\n"Alhamdulillah,untuk hari pertama dikejuaraan KONi cup series 6 ini,kita memperoleh 1 medali emas kyorugi prestasi atas nama Fattaah Nur Halim dan 1 medali emas kyorugi pemula diraih dari atlet atas nama Zaky zhafran,saya ucapkan Terimakasih telah berjuang keras," ucap kwanjangnim bayu salah satu pelatih TC Antero Taekwondo Club.\r\n\r\n"Bagi yang belum berhasil, jangan putus asa, terus berusaha latihan lebih keras lagi,dan untuk para atlet yg besok bertanding persiapan diri,semoga memperoleh hasil yang terbaik,"ucapnya lagi.(Angga)', 'https://www.jakartaforum.web.id/2025/12/fattah-meraih-medali-emas-di-hari.html', '1766132312_IMG-20251218-WA0058.jpg', '2025-12-19 01:18:32'),
	(2, 'Fattah Nur Halim Sumbang medali Emas pertama untuk Antero Taekwondo Club.', 'Postnewstime,JAKARTA,-Fattaah Nur Halim atau yang akrab disapa dengan nama Fattaah. Pada Kejuaraan Taekwondo KONI CUP Series 6 Indonesia Taekwondo Championship 2025 dalam kategori kyorugi prestasi putra Cadet U 49 kg,Fattah berhasil meraih Medali Emas pertama di hari pertama untuk team Antero Taekwondo Club.\r\n\r\nFattaah, seorang atlet Taekwondo  yang telah lama menekuni dalam olahraga ini, kini ia kembali menunjukkan keunggulannya. Kejuaraan ini, yang berlangsung mulai dari tanggal 18 hingga 21 Desember 2025, di GOR POPKI Cibubur, Jakarta Timur, menjadi ajang unggulan bagi para atlet Taekwondo di Indonesia.\r\n\r\nDalam pertandingan yang sengit dan penuh persaingan ketat, Fattaah berhasil mengalahkan beberapa pesaingnya dan meraih prestasi yang optimal. Medali Emas yang diraihnya dalam kategori kyorugi menjadi bukti nyata kecintaannya pada olahraga Taekwondo dan dedikasi kerasnya dalam berlatih.\r\n\r\n"Alhamdulillah, saya senang banget bisa ikut kejuaraan ini dan berhasil meraih medali emas pertama dihari pertama untuk team Antero taekwondo club dan saya berharap untuk teman2 yg bertanding dihari berikutnya bisa memperoleh medali emas juga,"ujar Fattaah dengan mata berbinar penuh kebahagiaan.\r\n\r\nBegitu juga untuk para pelatih TC Antero Taekwondo Club ikut merasa senang dan berbahagia atas pencapaian atletnya.\r\n\r\n"Alhamdulillah,untuk hari pertama dikejuaraan KONi cup series 6 ini,kita memperoleh 1 medali emas kyorugi prestasi atas nama Fattaah Nur Halim dan 1 medali emas kyorugi pemula diraih dari atlet atas nama Zaky zhafran,saya ucapkan Terimakasih telah berjuang keras," ucap kwanjangnim bayu salah satu pelatih TC Antero Taekwondo Club.\r\n\r\n"Bagi yang belum berhasil, jangan putus asa, terus berusaha latihan lebih keras lagi,dan untuk para atlet yg besok bertanding persiapan diri,semoga memperoleh hasil yang terbaik,"ucapnya lagi.\r\n(Adams)', 'https://postnewstimeonline.blogspot.com/2025/12/fattah-nur-halim-sumbang-medali-emas.html', '1766205305_1766077010804087-1.webp', '2025-12-19 21:35:05'),
	(3, 'Rayakan Hari Jadi Ke 8, Antero Taekwondo Club Gelar Syukuran', 'Mengusung tema â€œKebersamaan,Disiplin,dan perjuangan tanpa batasâ€ Antero Taekwondo Club (ATC) merayakan Hari Ulang Tahun ke-8,minggu (11/01/2026) bertempat di Tranning Center Antero Taekwondo Club lapangan futsal RW 014 Harapan Baru Regency,bekasi\r\n\r\nDalam suasana yang penuh kesederhanaan dan kekeluargaan, acara tersebut mempererat ikatan antara pelatih,atlet dan orang tua.\r\n\r\nâ€œAntero Taekwondo Club berdirinya 31 Desember 2023,12 hari yg lalu,Kami merayakan ulang tahun ini dengan memperkokoh hubungan antara orang tua, pelatih, dan para atlet.\r\n\r\nMulai dari doa bersama, peniupan lilin, hingga main games,dan makan bersama,â€ ungkap pemilik Antero Taekwondo Club Kwanjangnim bayu\r\n\r\nPerayaan ulang tahun Antero taekwondo club ke 8 tahun kali ini,Antero Taekwondo Club juga memberi Apresiasi dan penghargaan kepada para Atlet sebagai bentuk penghargaan atas dedikasi ,disiplin dan prestasi atletnya.\r\n\r\nDiantaranya adalah :\r\natlet terbaik putra : M.Fadli Hafizh\r\natlet terbaim putri : Naysilla Putri\r\natlet disiplin : Doni Tri Erlangga\r\natlet berprestasi : Syifa Agisna Maulida\r\n\r\nHarapannya, dengan momentum ulang tahun ini, Antero Taekwondo Club dapat terus berkembang dan melahirkan lebih banyak atlet taekwondo yang berprestasi.\r\n\r\nDengan semangat yang tak kenal lelah, Antero Taekwondo Club berkomitmen siap melangkah ke depan menuju prestasi yang lebih gemilang.\r\n\r\nâ€œSemoga Antero Taekwondo Club terus tumbuh dan menginspirasi untuk menjadi atlet taekwondo yang sukses dan berdedikasi,â€ tambah kwanjangnim bayu\r\n\r\nDan tak lupa juga,kwanjangnim Bayu menyampaikan ucapan terima kasih kepada semua pihak yang telah mendukung Antero Taekwondo Club hingga mencapai usia 8 tahun dan meraih berbagai prestasi.\r\n\r\nâ€œTentunya tanpa dukungan dan support dari semua pihak, semua ini tidak bisa kami capai sendiri,â€ ungkapnya.', 'https://radiomuaranetwork.id/2026/01/11/rayakan-hari-jadi-ke-8-antero-taekwondo-club-gelar-syukuran/', '1768283570_IMG-20260111-WA0137.jpg', '2026-01-12 22:52:50');

-- Dumping structure for table db_prestasi_beladiri.password_resets
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_prestasi_beladiri.password_resets: ~0 rows (approximately)

-- Dumping structure for table db_prestasi_beladiri.students
CREATE TABLE IF NOT EXISTS `students` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `dojang_id` int DEFAULT NULL,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `tempat_lahir` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `tingkatan_sabuk` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `foto_sertifikat` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat_domisili` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `students_fk_dojang` (`dojang_id`),
  CONSTRAINT `students_fk_dojang` FOREIGN KEY (`dojang_id`) REFERENCES `dojangs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_prestasi_beladiri.students: ~3 rows (approximately)
INSERT INTO `students` (`id`, `user_id`, `dojang_id`, `nama_lengkap`, `tempat_lahir`, `tanggal_lahir`, `tingkatan_sabuk`, `foto_sertifikat`, `alamat_domisili`, `created_at`) VALUES
	(1, 2, 8, 'Ribut Wahyudi', 'Jakarta', '1990-01-06', 'DAN I', 'cert_2_1768285620.pdf', 'Panjibuwono City, Cluster Sriwedari Blok CS-6/32 Babelan, Bekasi Utara', '2025-12-17 06:26:14'),
	(3, 4, 2, 'Rudi Hartono', 'Jakarta', '2007-08-17', 'Putih/Geup-10', NULL, 'Jl. Contoh No. 3', '2025-12-17 06:26:14'),
	(16, 19, 13, 'Dzulqarnain Tsaqib Kuncahyo', 'Jakarta', '2008-01-09', 'DAN I', 'cert_19_1768548939.jpeg', 'Jln Lapangan Roos III No.1 RT 003/005, Bukit Duri Tebet, Jakarta Selatan', '2026-01-16 00:35:39');

-- Dumping structure for table db_prestasi_beladiri.student_belt_history
CREATE TABLE IF NOT EXISTS `student_belt_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `tingkatan_sabuk` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `foto_sertifikat` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `student_belt_history_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_prestasi_beladiri.student_belt_history: ~5 rows (approximately)
INSERT INTO `student_belt_history` (`id`, `student_id`, `tingkatan_sabuk`, `foto_sertifikat`, `created_at`) VALUES
	(1, 1, 'Putih/Geup-10', NULL, '2026-01-05 00:29:48'),
	(3, 3, 'Putih/Geup-10', NULL, '2026-01-05 00:29:48'),
	(16, 1, 'DAN II', 'cert_2_1768185547.png', '2026-01-11 18:39:07'),
	(17, 1, 'DAN I', 'cert_2_1768285620.pdf', '2026-01-12 22:27:00'),
	(18, 16, 'DAN I', 'cert_19_1768548939.jpeg', '2026-01-15 23:35:39');

-- Dumping structure for table db_prestasi_beladiri.student_trainings
CREATE TABLE IF NOT EXISTS `student_trainings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `year` int NOT NULL,
  `certificate_file` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('pending','verified','rejected') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `admin_note` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `student_trainings_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_prestasi_beladiri.student_trainings: ~0 rows (approximately)
INSERT INTO `student_trainings` (`id`, `student_id`, `name`, `year`, `certificate_file`, `status`, `admin_note`, `created_at`) VALUES
	(1, 3, 'test', 2024, '1768656214_3.png', 'verified', '', '2026-01-17 13:23:34');

-- Dumping structure for table db_prestasi_beladiri.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','siswa') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'siswa',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `foto_profil` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `login_count` int DEFAULT '0',
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_prestasi_beladiri.users: ~4 rows (approximately)
INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`, `foto_profil`, `login_count`, `last_login`) VALUES
	(1, 'admin', 'admin@gmail.com', '$2y$10$zDV0E0SGG6OFdfEK67SbMu2SxpbyVJY1/vwuEDY2XpHyEA.9zKoKm', 'admin', '2025-12-17 06:26:14', 'profile_1_1767671267.png', 22, '2026-01-17 20:23:53'),
	(2, 'siswa1', 'school.muhammad.solikhin@gmail.com', '$2y$10$LSTFVDcTFn21sJmF4KVjJON.Virx4B0/eL6QGeHsagXknDqqbPm22', 'siswa', '2025-12-17 06:26:14', 'profile_2_1768285620.jpg', 15, '2026-01-16 10:52:54'),
	(4, 'siswa3', NULL, '$2y$10$AOy00SR/e4JpZU0g.PaGx.rac6nKnBti6XY..jwlKjvXhXk7vPMPy', 'siswa', '2025-12-17 06:26:14', NULL, 1, '2026-01-17 20:22:57'),
	(19, 'dztsaqibk', 'dzultsaqib@gmail.com', '$2y$10$diKC12ta5ZXFWK21NN8pOeunDPsyTorOQDuO584UzYgC/NnB4a4G6', 'siswa', '2026-01-16 00:32:48', 'profile_19_1768548997.jpeg', 2, '2026-01-16 10:44:21');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;

-- ============================================================
-- MIGRATION SCRIPT: Lokal → Hosting (ezyro_40832602_prestasi)
-- Tujuan: Migrasi struktur terbaru TANPA mengubah data existing
-- Tanggal: 2026-03-23
-- ============================================================
-- INSTRUKSI: Jalankan script ini di phpMyAdmin hosting
--            pada database ezyro_40832602_prestasi
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. TABEL: club_info
--    Tambah kolom baru: background_image
-- ============================================================
ALTER TABLE `club_info`
  ADD COLUMN IF NOT EXISTS `background_image` varchar(255) DEFAULT NULL;

-- ============================================================
-- 2. TABEL: coaches
--    Tambah kolom baru: user_id (relasi ke tabel users)
-- ============================================================
ALTER TABLE `coaches`
  ADD COLUMN IF NOT EXISTS `user_id` int(11) DEFAULT NULL;

-- Tambah Foreign Key constraint
ALTER TABLE `coaches`
  ADD CONSTRAINT `fk_coach_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- ============================================================
-- 3. TABEL: coach_trainings
--    Ubah tipe kolom level: VARCHAR(100) → ENUM
--    CATATAN: Data yang ada ('Daerah','Nasional','Internasional')
--             sudah kompatibel dengan ENUM baru, aman diubah
-- ============================================================
ALTER TABLE `coach_trainings`
  MODIFY COLUMN `level` enum('Daerah','Nasional','Internasional') NOT NULL;

-- ============================================================
-- 4. TABEL: users
--    Tambah nilai 'pelatih' ke kolom role ENUM
-- ============================================================
ALTER TABLE `users`
  MODIFY COLUMN `role` enum('admin','siswa','pelatih') NOT NULL DEFAULT 'siswa';

-- ============================================================
-- 5. TABEL: student_dojang_history
--    Ubah storage engine dari MyISAM ke InnoDB
--    Ubah charset dari latin1 ke utf8mb4
--    Tambah Foreign Key constraints yang hilang
-- ============================================================
ALTER TABLE `student_dojang_history` ENGINE = InnoDB;
ALTER TABLE `student_dojang_history` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Hapus data orphaned yang student_id-nya tidak ada di tabel students
DELETE FROM `student_dojang_history`
  WHERE `student_id` NOT IN (SELECT `id` FROM `students`);

-- Set NULL untuk old_dojang_id / new_dojang_id yang tidak ada di dojangs
UPDATE `student_dojang_history`
  SET `old_dojang_id` = NULL
  WHERE `old_dojang_id` IS NOT NULL
    AND `old_dojang_id` NOT IN (SELECT `id` FROM `dojangs`);

UPDATE `student_dojang_history`
  SET `new_dojang_id` = NULL
  WHERE `new_dojang_id` IS NOT NULL
    AND `new_dojang_id` NOT IN (SELECT `id` FROM `dojangs`);

-- Tambah Foreign Key constraints untuk student_dojang_history
ALTER TABLE `student_dojang_history`
  ADD CONSTRAINT `student_dojang_history_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_dojang_history_ibfk_2` FOREIGN KEY (`old_dojang_id`) REFERENCES `dojangs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `student_dojang_history_ibfk_3` FOREIGN KEY (`new_dojang_id`) REFERENCES `dojangs` (`id`) ON DELETE SET NULL;

-- ============================================================
-- SELESAI: Re-enable foreign key checks
-- ============================================================
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- VERIFIKASI (opsional, jalankan setelah migrasi):
-- Cek struktur tabel yang diubah
-- ============================================================
-- SHOW COLUMNS FROM `club_info`;
-- SHOW COLUMNS FROM `coaches`;
-- SHOW COLUMNS FROM `coach_trainings`;
-- SHOW COLUMNS FROM `users`;
-- SHOW CREATE TABLE `student_dojang_history`;

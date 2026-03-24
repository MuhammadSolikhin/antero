<?php
session_start();
require_once '../config/database.php';

// Cek apakah user sudah login dan role adalah pelatih
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pelatih') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Ambil info detail pelatih dengan user_id ini
$coach_query = $conn->query("SELECT * FROM coaches WHERE user_id = $user_id LIMIT 1");
$coach = $coach_query->fetch_assoc();
?>

<div class="container-fluid px-4 py-5">
    <div class="row mb-5">
        <div class="col-md-12">
            <div class="glass-card p-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="bg-white p-3 rounded-circle shadow-sm overflow-hidden d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <?php if ($coach && $coach['foto_pelatih']): ?>
                                <img src="../assets/uploads/<?php echo $coach['foto_pelatih']; ?>" class="w-100 h-100 object-fit-cover rounded-circle">
                            <?php else: ?>
                                <i class="bi bi-person-video3 display-5 text-primary"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <h2 class="fw-bold mb-0">Selamat Datang, <?php echo $coach ? $coach['nama_pelatih'] : $username; ?>!</h2>
                        <p class="text-muted mb-0">Anda login sebagai <span class="text-primary fw-bold">Pelatih</span> Antero Taekwondo Club</p>
                    </div>
                </div>
                
                <?php if (!$coach): ?>
                <div class="alert alert-warning mt-4 mb-0 border-0 shadow-sm d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-warning"></i>
                    <div>
                        <strong>Perhatian!</strong> Akun ini belum terhubung dengan profil pelatih spesifik. Silakan minta Administrator untuk menghubungkan akun ini melalui menu Data User.
                    </div>
                </div>
                <?php else: ?>
                <div class="mt-4 pt-4 border-top">
                    <div class="row text-center text-md-start">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <small class="text-uppercase text-muted fw-bold d-block mb-1">Tingkatan / Sabuk</small>
                            <span class="badge bg-dark px-3 py-2 fs-6"><?php echo $coach['tingkatan']; ?></span>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <small class="text-uppercase text-muted fw-bold d-block mb-1">Sertifikat</small>
                            <?php if ($coach['info_sertifikat']): ?>
                                <a href="../assets/uploads/certificates/<?php echo $coach['info_sertifikat']; ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Lihat Dokumen</a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <small class="text-uppercase text-muted fw-bold d-block mb-1">Status Keanggotaan</small>
                            <span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Aktif</span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="glass-card p-4 text-center">
                <img src="../assets/img/logo.png" class="mb-3" style="max-height: 100px; opacity: 0.5;">
                <h4 class="text-muted">Akses Dashboard Pelatih Sedang Dalam Pengembangan</h4>
                <p class="text-muted mb-0">Nantinya Anda akan dapat mengelola penilaian riwayat dan memantau siswa dari sini.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

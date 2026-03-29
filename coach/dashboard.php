<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pelatih') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch coach info
$coach_query = $conn->query("SELECT * FROM coaches WHERE user_id = $user_id LIMIT 1");
$coach = $coach_query->fetch_assoc();

// Stats
$coach_id = $coach ? $coach['id'] : 0;

// Dojang managed by this coach
$my_dojangs = $conn->query("SELECT * FROM dojangs WHERE coach_id = $coach_id");
$total_my_dojangs = $my_dojangs ? $my_dojangs->num_rows : 0;

// Total students in my dojangs
$total_my_students = 0;
if ($coach_id) {
    $res = $conn->query("SELECT COUNT(*) as total FROM students WHERE dojang_id IN (SELECT id FROM dojangs WHERE coach_id = $coach_id) AND is_deleted = 0");
    $total_my_students = $res ? $res->fetch_assoc()['total'] : 0;
}

// Total trainings
$total_trainings = 0;
if ($coach_id) {
    $res = $conn->query("SELECT COUNT(*) as total FROM coach_trainings WHERE coach_id = $coach_id");
    $total_trainings = $res ? $res->fetch_assoc()['total'] : 0;
}

// Total achievements
$total_achievements = 0;
if ($coach_id) {
    $res = $conn->query("SELECT COUNT(*) as total FROM coach_achievements WHERE coach_id = $coach_id");
    $total_achievements = $res ? $res->fetch_assoc()['total'] : 0;
}

// Recent students from my dojangs
$recent_students = null;
if ($coach_id) {
    $recent_students = $conn->query("SELECT s.nama_lengkap, s.tingkatan_sabuk, d.nama_dojang, s.created_at
        FROM students s
        JOIN dojangs d ON s.dojang_id = d.id
        WHERE d.coach_id = $coach_id AND s.is_deleted = 0
        ORDER BY s.created_at DESC LIMIT 5");
}

// Recent achievements
$recent_achievements = null;
if ($coach_id) {
    $recent_achievements = $conn->query("SELECT * FROM coach_achievements WHERE coach_id = $coach_id ORDER BY created_at DESC LIMIT 3");
}

// Flyer
$flyer = $conn->query("SELECT * FROM flyers WHERE is_active = 1 LIMIT 1")->fetch_assoc();
$show_flyer = false;
if ($flyer && !isset($_SESSION['flyer_seen'])) {
    $show_flyer = true;
    $_SESSION['flyer_seen'] = true;
}
?>

<div class="container-fluid px-4 py-5">
    <!-- Welcome Card -->
    <div class="row mb-4">
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
                        <h2 class="fw-bold mb-0">Selamat Datang, <?php echo $coach ? htmlspecialchars($coach['nama_pelatih']) : $username; ?>!</h2>
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
                        <div class="col-md-3 mb-3 mb-md-0">
                            <small class="text-uppercase text-muted fw-bold d-block mb-1">Tingkatan / Sabuk</small>
                            <span class="badge bg-dark px-3 py-2 fs-6"><?php echo htmlspecialchars($coach['tingkatan']); ?></span>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <small class="text-uppercase text-muted fw-bold d-block mb-1">Sertifikat</small>
                            <?php if ($coach['info_sertifikat']): ?>
                                <a href="../assets/uploads/certificates/<?php echo $coach['info_sertifikat']; ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Lihat Dokumen</a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <small class="text-uppercase text-muted fw-bold d-block mb-1">Tinggi / Berat</small>
                            <span class="fw-bold">
                                <?php 
                                $tb = $coach['tinggi_badan'] ?? null;
                                $bb = $coach['berat_badan'] ?? null;
                                if ($tb || $bb) {
                                    echo ($tb ? $tb . ' cm' : '-') . ' / ' . ($bb ? $bb . ' kg' : '-');
                                } else {
                                    echo '<span class="text-muted fw-normal">Belum diisi</span>';
                                }
                                ?>
                            </span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-uppercase text-muted fw-bold d-block mb-1">Status</small>
                            <span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Aktif</span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($coach): ?>
    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-6">
            <div class="glass-card p-4 text-center h-100">
                <div class="bg-primary bg-opacity-10 rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="bi bi-building fs-3 text-primary"></i>
                </div>
                <h2 class="display-5 fw-bold text-primary mb-1"><?php echo $total_my_dojangs; ?></h2>
                <p class="text-muted small mb-2">Dojang Saya</p>
                <a href="my_dojang.php" class="btn btn-outline-primary btn-sm rounded-pill">Lihat</a>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="glass-card p-4 text-center h-100">
                <div class="bg-success bg-opacity-10 rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="bi bi-people-fill fs-3 text-success"></i>
                </div>
                <h2 class="display-5 fw-bold text-success mb-1"><?php echo $total_my_students; ?></h2>
                <p class="text-muted small mb-2">Siswa di Dojang</p>
                <a href="my_dojang.php" class="btn btn-outline-success btn-sm rounded-pill">Lihat</a>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="glass-card p-4 text-center h-100">
                <div class="bg-warning bg-opacity-10 rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="bi bi-trophy-fill fs-3 text-warning"></i>
                </div>
                <h2 class="display-5 fw-bold text-warning mb-1"><?php echo $total_achievements; ?></h2>
                <p class="text-muted small mb-2">Prestasi</p>
                <a href="achievement.php" class="btn btn-outline-warning btn-sm rounded-pill">Lihat</a>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="glass-card p-4 text-center h-100">
                <div class="bg-info bg-opacity-10 rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="bi bi-journal-bookmark-fill fs-3 text-info"></i>
                </div>
                <h2 class="display-5 fw-bold text-info mb-1"><?php echo $total_trainings; ?></h2>
                <p class="text-muted small mb-2">Riwayat Pelatihan</p>
                <a href="trainings.php" class="btn btn-outline-info btn-sm rounded-pill">Lihat</a>
            </div>
        </div>
    </div>

    <!-- Recent Data Row -->
    <div class="row g-4 mb-4">
        <!-- Recent Students in My Dojang -->
        <div class="col-md-7">
            <div class="glass-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold m-0"><i class="bi bi-people text-primary"></i> Siswa Terbaru di Dojang Saya</h5>
                    <a href="my_dojang.php" class="btn btn-sm btn-outline-primary rounded-pill">Lihat Semua</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle small">
                        <thead class="table-light">
                            <tr>
                                <th>Nama</th>
                                <th>Dojang</th>
                                <th>Sabuk</th>
                                <th>Bergabung</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_students && $recent_students->num_rows > 0): ?>
                                <?php while ($rs = $recent_students->fetch_assoc()): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo htmlspecialchars($rs['nama_lengkap']); ?></td>
                                        <td><small><?php echo htmlspecialchars($rs['nama_dojang']); ?></small></td>
                                        <td><span class="badge bg-info text-dark"><?php echo $rs['tingkatan_sabuk']; ?></span></td>
                                        <td><small class="text-muted"><?php echo date('d/m/Y', strtotime($rs['created_at'])); ?></small></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Belum ada siswa di dojang Anda.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Achievements -->
        <div class="col-md-5">
            <div class="glass-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold m-0"><i class="bi bi-trophy text-warning"></i> Prestasi Terbaru</h5>
                    <a href="achievement.php" class="btn btn-sm btn-outline-warning rounded-pill">Lihat Semua</a>
                </div>
                <?php if ($recent_achievements && $recent_achievements->num_rows > 0): ?>
                    <?php while ($ra = $recent_achievements->fetch_assoc()): ?>
                        <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="min-width: 40px; height: 40px;">
                                <i class="bi bi-trophy-fill text-warning"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1 small"><?php echo htmlspecialchars($ra['nama_kejuaraan']); ?></h6>
                                <div>
                                    <span class="badge bg-secondary"><?php echo $ra['tingkat']; ?></span>
                                    <span class="badge bg-warning text-dark">Juara <?php echo $ra['juara_ke']; ?></span>
                                    <small class="text-muted ms-1"><?php echo $ra['championship_year']; ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-trophy display-4 d-block mb-2 opacity-25"></i>
                        <p class="mb-2">Belum ada data prestasi.</p>
                        <a href="achievement.php" class="btn btn-sm btn-primary rounded-pill">Input Prestasi</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Club Social Media -->
    <?php
    $club = $conn->query("SELECT * FROM club_info WHERE id = 1")->fetch_assoc();
    if ($club):
    ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="glass-card p-4 text-center">
                <h4 class="fw-bold mb-3"><?php echo htmlspecialchars($club['club_name']); ?> Social Media</h4>
                <div class="d-flex justify-content-center gap-5">
                    <?php if (!empty($club['instagram'])): ?>
                        <a href="<?php echo $club['instagram']; ?>" target="_blank"
                            class="text-decoration-none text-danger hover-scale transition-all">
                            <i class="bi bi-instagram display-4"></i>
                            <div class="fw-bold mt-2">Instagram</div>
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($club['tiktok'])): ?>
                        <a href="<?php echo $club['tiktok']; ?>" target="_blank"
                            class="text-decoration-none text-dark hover-scale transition-all">
                            <i class="bi bi-tiktok display-4"></i>
                            <div class="fw-bold mt-2">TikTok</div>
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($club['youtube'])): ?>
                        <a href="<?php echo $club['youtube']; ?>" target="_blank"
                            class="text-decoration-none text-danger hover-scale transition-all">
                            <i class="bi bi-youtube display-4"></i>
                            <div class="fw-bold mt-2">YouTube</div>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- News Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="glass-card p-4 d-flex align-items-center shadow-sm">
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-megaphone-fill fs-4 text-primary"></i>
                </div>
                <div>
                    <h4 class="fw-bold m-0">Pengumuman & Berita</h4>
                    <small class="text-muted d-block mt-1">Informasi dan update terbaru seputar aktivitas Antero Taekwondo Club</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-5 g-4">
        <?php
        $news = $conn->query("SELECT * FROM news ORDER BY created_at DESC LIMIT 5");
        if ($news->num_rows > 0):
            while ($n = $news->fetch_assoc()):
        ?>
            <div class="col">
                <div class="glass-card h-100 p-0 overflow-hidden shadow-sm hover-shadow transition-all bg-white d-flex flex-column">
                    <?php if ($n['image']): ?>
                        <div style="height: 160px; overflow: hidden;">
                            <img src="../assets/uploads/<?php echo $n['image']; ?>" class="w-100 h-100 object-fit-cover">
                        </div>
                    <?php else: ?>
                        <div class="bg-light w-100 d-flex align-items-center justify-content-center text-muted" style="height: 160px;">
                            <i class="bi bi-image fs-1"></i>
                        </div>
                    <?php endif; ?>
                    <div class="p-3 d-flex flex-column flex-grow-1">
                        <small class="text-muted"><i class="bi bi-calendar-event me-1"></i>
                            <?php echo date('d M Y', strtotime($n['created_at'])); ?></small>
                        <h6 class="fw-bold mt-2 mb-2 text-truncate" title="<?php echo htmlspecialchars($n['title']); ?>"><?php echo htmlspecialchars($n['title']); ?></h6>
                        <?php if ($n['source']): ?>
                            <small class="d-block text-primary mb-3 text-truncate" title="<?php echo htmlspecialchars($n['source']); ?>"><i class="bi bi-link-45deg"></i>
                                <?php echo htmlspecialchars($n['source']); ?></small>
                        <?php endif; ?>
                        <p class="text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4;">
                            <?php echo strip_tags($n['content']); ?>
                        </p>
                        <div class="mt-auto">
                            <button class="btn btn-sm btn-outline-primary rounded-pill stretched-link w-100" data-bs-toggle="modal"
                                data-bs-target="#newsModal<?php echo $n['id']; ?>">Baca Selengkapnya</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- News Detail Modal -->
            <div class="modal fade" id="newsModal<?php echo $n['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header border-0">
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-0">
                            <?php if ($n['image']): ?>
                                <img src="../assets/uploads/<?php echo $n['image']; ?>" class="w-100">
                            <?php endif; ?>
                            <div class="p-5">
                                <h2 class="fw-bold mb-3"><?php echo htmlspecialchars($n['title']); ?></h2>
                                <div class="mb-4">
                                    <small class="text-muted"><i class="bi bi-calendar-check me-2"></i> Diposting pada
                                        <?php echo date('d F Y, H:i', strtotime($n['created_at'])); ?></small>
                                    <?php if ($n['source']): ?>
                                        <br><small class="text-primary"><i class="bi bi-link-45deg me-2"></i> Sumber:
                                            <?php echo htmlspecialchars($n['source']); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="text-muted" style="line-height: 1.8;">
                                    <?php echo nl2br(htmlspecialchars($n['content'])); ?>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary rounded-pill px-4"
                                data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php
            endwhile;
        else:
        ?>
            <div class="col-12">
                <div class="alert alert-info bg-light border-0"><i class="bi bi-info-circle me-2"></i> Belum ada pengumuman terbaru.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<!-- Flyer Modal -->
<?php if ($show_flyer && $flyer): ?>
    <div class="modal fade" id="flyerModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-header border-0 p-0">
                    <button type="button"
                        class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-3 bg-white p-2 rounded-circle"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 text-center position-relative">
                    <img src="../assets/uploads/flyers/<?= $flyer['image'] ?>" class="img-fluid rounded shadow-lg"
                        style="max-height: 80vh;">
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var myModal = new bootstrap.Modal(document.getElementById('flyerModal'));
            myModal.show();
        });
    </script>
<?php endif; ?>

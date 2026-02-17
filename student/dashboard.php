<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'siswa') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';

$user_id = $_SESSION['user_id'];
// Check if student data exists
$sql = "SELECT * FROM students WHERE user_id = $user_id";
$result = $conn->query($sql);
$student = $result->fetch_assoc();
$has_biodata = ($result->num_rows > 0);

// Get Achievement Stats
if ($has_biodata) {
    $student_id = $student['id'];
    $sql_ach = "SELECT count(*) as total, 
                sum(case when status='approved' then 1 else 0 end) as approved,
                sum(case when status='pending' then 1 else 0 end) as pending
                FROM achievements WHERE student_id = $student_id";
    $stats = $conn->query($sql_ach)->fetch_assoc();
}

// Check for active flyer
$flyer = $conn->query("SELECT * FROM flyers WHERE is_active = 1 LIMIT 1")->fetch_assoc();
$show_flyer = false;
if ($flyer && !isset($_SESSION['flyer_seen'])) {
    $show_flyer = true;
    $_SESSION['flyer_seen'] = true;
}
?>

<div class="container-fluid px-4 py-5">
    <div class="row mb-5">
        <div class="col-md-12">
            <div class="glass-card p-4">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="bg-white p-3 rounded-circle shadow-sm">
                            <i class="bi bi-person-circle display-6 text-primary"></i>
                        </div>
                    </div>
                    <div>
                        <h2 class="fw-bold mb-0">Halo, <?php echo $_SESSION['username']; ?>!</h2>
                        <p class="text-muted mb-0">Selamat datang kembali di <span class="text-primary fw-bold">Panel
                                Siswa</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Club Social Media -->
    <?php
    $club = $conn->query("SELECT * FROM club_info WHERE id = 1")->fetch_assoc();
    if ($club):
        ?>
        <div class="row mb-5">
            <div class="col-12">
                <div class="glass-card p-4 text-center">
                    <h4 class="fw-bold mb-3"><?php echo $club['club_name']; ?> Social Media</h4>
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

    <!-- ... (rest of stats) ... -->

    <!-- News Section -->
    <div class="row mt-5">
        <div class="col-12 mb-4">
            <div class="glass-card p-3 d-inline-block">
                <h4 class="fw-bold m-0"><i class="bi bi-megaphone-fill text-primary me-2"></i> Pengumuman & Berita</h4>
            </div>
        </div>

        <?php
        $news = $conn->query("SELECT * FROM news ORDER BY created_at DESC LIMIT 3");
        if ($news->num_rows > 0):
            while ($n = $news->fetch_assoc()):
                ?>
                <div class="col-md-4 mb-4">
                    <div class="glass-card h-100 p-0 overflow-hidden shadow-sm hover-shadow transition-all bg-white">
                        <?php if ($n['image']): ?>
                            <div style="height: 200px; overflow: hidden;">
                                <img src="../assets/uploads/<?php echo $n['image']; ?>" class="w-100 h-100 object-fit-cover">
                            </div>
                        <?php endif; ?>
                        <div class="p-4">
                            <small class="text-muted"><i class="bi bi-calendar-event me-1"></i>
                                <?php echo date('d M Y', strtotime($n['created_at'])); ?></small>
                            <h5 class="fw-bold mt-2 mb-2"><?php echo $n['title']; ?></h5>
                            <?php if ($n['source']): ?>
                                <small class="d-block text-primary mb-3"><i class="bi bi-link-45deg"></i>
                                    <?php echo $n['source']; ?></small>
                            <?php endif; ?>
                            <p class="text-muted small mb-3">
                                <?php echo substr(strip_tags($n['content']), 0, 100) . '...'; ?>
                            </p>
                            <button class="btn btn-sm btn-outline-primary rounded-pill stretched-link" data-bs-toggle="modal"
                                data-bs-target="#newsModal<?php echo $n['id']; ?>">Baca Selengkapnya</button>
                        </div>
                    </div>
                </div>

                <!-- Detail Modal -->
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
                                    <h2 class="fw-bold mb-3"><?php echo $n['title']; ?></h2>
                                    <div class="mb-4">
                                        <small class="text-muted"><i class="bi bi-calendar-check me-2"></i> Diposting pada
                                            <?php echo date('d F Y, H:i', strtotime($n['created_at'])); ?></small>
                                        <?php if ($n['source']): ?>
                                            <br><small class="text-primary"><i class="bi bi-link-45deg me-2"></i> Sumber:
                                                <?php echo $n['source']; ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted" style="line-height: 1.8;">
                                        <?php echo nl2br($n['content']); ?>
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
                <div class="alert alert-info bg-light border-0"><i class="bi bi-info-circle me-2"></i> Belum ada pengumuman
                    terbaru.</div>
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
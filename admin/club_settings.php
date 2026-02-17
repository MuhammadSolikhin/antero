<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$success_msg = '';
$error_msg = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $club_name = $_POST['club_name'];
    $instagram = $_POST['instagram'];
    $tiktok = $_POST['tiktok'];
    $youtube = $_POST['youtube'];

    $stmt = $conn->prepare("UPDATE club_info SET club_name = ?, instagram = ?, tiktok = ?, youtube = ? WHERE id = 1");
    $stmt->bind_param("ssss", $club_name, $instagram, $tiktok, $youtube);

    if ($stmt->execute()) {
        $success_msg = "Pengaturan berhasil disimpan!";
    } else {
        $error_msg = "Gagal menyimpan pengaturan: " . $conn->error;
    }
}

// Fetch Current Data
$club = $conn->query("SELECT * FROM club_info WHERE id = 1")->fetch_assoc();
if (!$club) {
    // Should not happen due to setup script, but fallback
    $conn->query("INSERT INTO club_info (id, club_name) VALUES (1, 'Ankero Taekwondo Club')");
    $club = $conn->query("SELECT * FROM club_info WHERE id = 1")->fetch_assoc();
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container-fluid px-4 py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="glass-card p-5">
                <h3 class="fw-bold mb-4"><i class="bi bi-gear-fill text-primary me-2"></i> Pengaturan Sosmed</h3>

                <?php if ($success_msg): ?>
                    <div class="alert alert-success border-0 shadow-sm rounded-pill px-4 mb-4">
                        <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_msg): ?>
                    <div class="alert alert-danger border-0 shadow-sm rounded-pill px-4 mb-4">
                        <i class="bi bi-exclamation-circle-fill me-2"></i> <?php echo $error_msg; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Nama Club</label>
                        <input type="text" name="club_name" class="form-control rounded-pill px-3 py-2"
                            value="<?php echo htmlspecialchars($club['club_name'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold"><i class="bi bi-instagram map-icon me-1"></i> Instagram
                            URL</label>
                        <input type="url" name="instagram" class="form-control rounded-pill px-3 py-2"
                            value="<?php echo htmlspecialchars($club['instagram'] ?? ''); ?>"
                            placeholder="https://instagram.com/...">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold"><i class="bi bi-tiktok map-icon me-1"></i> TikTok URL</label>
                        <input type="url" name="tiktok" class="form-control rounded-pill px-3 py-2"
                            value="<?php echo htmlspecialchars($club['tiktok'] ?? ''); ?>"
                            placeholder="https://tiktok.com/...">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold"><i class="bi bi-youtube map-icon me-1"></i> YouTube
                            URL</label>
                        <input type="url" name="youtube" class="form-control rounded-pill px-3 py-2"
                            value="<?php echo htmlspecialchars($club['youtube'] ?? ''); ?>"
                            placeholder="https://youtube.com/...">
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary rounded-pill py-2 fw-bold">Simpan
                            Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
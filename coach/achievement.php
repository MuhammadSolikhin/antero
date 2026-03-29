<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pelatih') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$coach = $conn->query("SELECT id FROM coaches WHERE user_id = $user_id")->fetch_assoc();

if (!$coach) {
    $_SESSION['swal_icon'] = 'warning';
    $_SESSION['swal_title'] = 'Perhatian!';
    $_SESSION['swal_text'] = 'Data pelatih belum ditemukan. Hubungi admin.';
    header("Location: dashboard.php");
    exit();
}

$coach_id = $coach['id'];

// Handle Delete
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    // Get file name first
    $del_data = $conn->query("SELECT file_sertifikat FROM coach_achievements WHERE id=$del_id AND coach_id=$coach_id")->fetch_assoc();
    if ($del_data) {
        // Delete file
        $file_path = "../assets/uploads/" . $del_data['file_sertifikat'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        $conn->query("DELETE FROM coach_achievements WHERE id=$del_id AND coach_id=$coach_id");
        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text'] = 'Data prestasi berhasil dihapus.';
    }
    header("Location: achievement.php");
    exit();
}

// Handle Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kejuaraan = $conn->real_escape_string($_POST['nama_kejuaraan']);
    $tahun = intval($_POST['championship_year']);
    $tingkat = $_POST['tingkat'];
    $juara = intval($_POST['juara_ke']);

    // File Upload
    $target_dir = "../assets/uploads/";
    if (!is_dir($target_dir))
        mkdir($target_dir, 0777, true);

    if (empty($_FILES["file_sertifikat"]["name"])) {
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_title'] = 'Gagal!';
        $_SESSION['swal_text'] = 'Wajib mengupload bukti sertifikat!';
    } else {
        $file_name = time() . '_' . basename($_FILES["file_sertifikat"]["name"]);
        $target_file = $target_dir . $file_name;
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'pdf'])) {
            $_SESSION['swal_icon'] = 'error';
            $_SESSION['swal_title'] = 'Gagal!';
            $_SESSION['swal_text'] = 'Hanya file JPG, JPEG, PNG & PDF yang diperbolehkan.';
            $uploadOk = 0;
        }

        if ($uploadOk) {
            if (move_uploaded_file($_FILES["file_sertifikat"]["tmp_name"], $target_file)) {
                $sql = "INSERT INTO coach_achievements (coach_id, nama_kejuaraan, championship_year, tingkat, juara_ke, file_sertifikat) 
                        VALUES ($coach_id, '$nama_kejuaraan', '$tahun', '$tingkat', '$juara', '$file_name')";
                if ($conn->query($sql)) {
                    $_SESSION['swal_icon'] = 'success';
                    $_SESSION['swal_title'] = 'Berhasil!';
                    $_SESSION['swal_text'] = 'Data prestasi berhasil disimpan!';
                } else {
                    $_SESSION['swal_icon'] = 'error';
                    $_SESSION['swal_title'] = 'Error!';
                    $_SESSION['swal_text'] = $conn->error;
                }
            } else {
                $_SESSION['swal_icon'] = 'error';
                $_SESSION['swal_title'] = 'Gagal!';
                $_SESSION['swal_text'] = 'Gagal mengupload file.';
            }
        }
    }
    header("Location: achievement.php");
    exit();
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';

// Pagination
$limit = 5;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

$total_results = $conn->query("SELECT COUNT(*) as count FROM coach_achievements WHERE coach_id = $coach_id")->fetch_assoc()['count'];
$total_pages = ceil($total_results / $limit);

$prestasi = $conn->query("SELECT * FROM coach_achievements WHERE coach_id = $coach_id ORDER BY created_at DESC LIMIT $start, $limit");
?>

<div class="container py-5">
    <div class="row">
        <!-- Form Input -->
        <div class="col-md-5 mb-4">
            <div class="glass-card p-4">
                <h4 class="fw-bold mb-3"><i class="bi bi-plus-circle-dotted text-primary"></i> Input Prestasi Baru</h4>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Nama Kejuaraan</label>
                        <input type="text" name="nama_kejuaraan" class="form-control" required
                            placeholder="Contoh: Popda 2024">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tahun Kejuaraan</label>
                        <input type="number" name="championship_year" class="form-control" required placeholder="2024"
                            min="2000" max="2099" value="<?php echo date('Y'); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tingkatan</label>
                        <select name="tingkat" class="form-select" required>
                            <option value="Daerah">Daerah</option>
                            <option value="Nasional">Nasional</option>
                            <option value="Internasional">Internasional</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Juara Ke-</label>
                        <select name="juara_ke" class="form-select" required>
                            <option value="1">Juara 1 🥇</option>
                            <option value="2">Juara 2 🥈</option>
                            <option value="3">Juara 3 🥉</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bukti Sertifikat / Piagam</label>
                        <input type="file" name="file_sertifikat" class="form-control" required>
                        <div class="form-text">Format: JPG/PNG/PDF. Max 2MB.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">SIMPAN PRESTASI</button>
                </form>
            </div>
        </div>

        <!-- History/List -->
        <div class="col-md-7">
            <div class="glass-card p-4 mb-3">
                <h4 class="fw-bold mb-0">Riwayat Prestasi</h4>
            </div>
            <?php if ($prestasi->num_rows > 0): ?>
                <?php while ($row = $prestasi->fetch_assoc()): ?>
                    <div class="card mb-3 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="card-title fw-bold mb-1"><?php echo htmlspecialchars($row['nama_kejuaraan']); ?> <small
                                            class="text-muted fw-normal">(<?php echo $row['championship_year']; ?>)</small></h5>
                                    <p class="text-muted mb-1">
                                        <span class="badge bg-secondary"><?php echo $row['tingkat']; ?></span>
                                        <span class="badge bg-warning text-dark">Juara <?php echo $row['juara_ke']; ?></span>
                                    </p>
                                    <small class="text-muted"><i class="bi bi-calendar"></i>
                                        <?php echo date('d M Y', strtotime($row['created_at'])); ?></small>
                                </div>
                                <div class="text-end">
                                    <a href="../assets/uploads/<?php echo $row['file_sertifikat']; ?>" target="_blank"
                                        class="btn btn-sm btn-outline-info rounded-pill"><i class="bi bi-eye"></i> Bukti</a>
                                    <div class="mt-2">
                                        <a href="achievement.php?delete=<?php echo $row['id']; ?>"
                                            class="btn btn-sm btn-outline-danger rounded-pill"
                                            onclick="return confirm('Yakin hapus data prestasi ini?')"><i class="bi bi-trash"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                                <a class="page-link rounded-pill px-3 me-2"
                                    href="<?php echo ($page <= 1) ? '#' : '?page=' . ($page - 1); ?>">Previous</a>
                            </li>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                                    <a class="page-link rounded-circle mx-1 d-flex align-items-center justify-content-center"
                                        style="width: 35px; height: 35px;" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                                <a class="page-link rounded-pill px-3 ms-2"
                                    href="<?php echo ($page >= $total_pages) ? '#' : '?page=' . ($page + 1); ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info">Belum ada data prestasi. Ayo input sekarang!</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['flyer_image'])) {
    $target_dir = "../assets/uploads/flyers/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    $file_ext = strtolower(pathinfo($_FILES['flyer_image']['name'], PATHINFO_EXTENSION));
    if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
        $new_name = "flyer_" . time() . "." . $file_ext;
        if (move_uploaded_file($_FILES['flyer_image']['tmp_name'], $target_dir . $new_name)) {
            // Deactivate all previous active flyers
            $conn->query("UPDATE flyers SET is_active = 0");

            // Insert new active flyer
            $sql = "INSERT INTO flyers (image, is_active) VALUES ('$new_name', 1)";
            if ($conn->query($sql)) {
                $_SESSION['swal_icon'] = 'success';
                $_SESSION['swal_title'] = 'Berhasil!';
                $_SESSION['swal_text'] = 'Flyer berhasil diupload dan diaktifkan!';
            }
        } else {
            $_SESSION['swal_icon'] = 'error';
            $_SESSION['swal_title'] = 'Gagal Upload!';
            $_SESSION['swal_text'] = 'Terjadi kesalahan saat memindahkan file.';
        }
    } else {
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_title'] = 'Format Salah!';
        $_SESSION['swal_text'] = 'Hanya file JPG, PNG, GIF yang diperbolehkan.';
    }
    header("Location: flyer.php");
    exit();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $data = $conn->query("SELECT image FROM flyers WHERE id=$id")->fetch_assoc();
    if ($data) {
        $path = "../assets/uploads/flyers/" . $data['image'];
        if (file_exists($path)) unlink($path);
        
        $conn->query("DELETE FROM flyers WHERE id=$id");
        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Terhapus!';
        $_SESSION['swal_text'] = 'Flyer berhasil dihapus.';
    }
    header("Location: flyer.php");
    exit();
}

// Handle Toggle Active
if (isset($_GET['activate'])) {
    $id = $_GET['activate'];
    // Deactivate all
    $conn->query("UPDATE flyers SET is_active = 0");
    // Activate selected
    $conn->query("UPDATE flyers SET is_active = 1 WHERE id=$id");
    
    $_SESSION['swal_icon'] = 'success';
    $_SESSION['swal_title'] = 'Berhasil!';
    $_SESSION['swal_text'] = 'Flyer diaktifkan!';
    header("Location: flyer.php");
    exit();
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';

// Fetch Active Flyer
$active_flyer = $conn->query("SELECT * FROM flyers WHERE is_active = 1 LIMIT 1")->fetch_assoc();

// Fetch History (Inactive)
$history_flyers = $conn->query("SELECT * FROM flyers WHERE is_active = 0 ORDER BY created_at DESC");
?>

<div class="container py-5">
    <div class="row">
        <!-- Upload Form -->
        <div class="col-md-4">
            <div class="glass-card p-4">
                <h4 class="fw-bold mb-3"><i class="bi bi-upload text-primary"></i> Upload Flyer</h4>
                <p class="text-muted small">Upload flyer baru. Flyer ini akan otomatis menjadi <strong>Aktif</strong> dan muncul di dashboard siswa.</p>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Pilih Gambar Flyer</label>
                        <input type="file" name="flyer_image" class="form-control" required accept="image/*">
                        <div class="form-text">Format: JPG, PNG, GIF. Max: 2MB.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Upload & Aktifkan</button>
                </form>
            </div>
        </div>

        <!-- Active Flyer Display -->
        <div class="col-md-8">
            <div class="glass-card p-4 mb-4">
                <h4 class="fw-bold mb-3 text-success"><i class="bi bi-broadcast"></i> Flyer Aktif Saat Ini</h4>
                <?php if ($active_flyer): ?>
                    <div class="position-relative">
                        <img src="../assets/uploads/flyers/<?= $active_flyer['image'] ?>" class="img-fluid rounded shadow w-100" style="max-height: 400px; object-fit: contain; background: #f8f9fa;">
                        <span class="position-absolute top-0 end-0 badge bg-success m-3 shadow">AKTIF</span>
                    </div>
                    <div class="mt-2 text-end">
                         <a href="flyer.php?delete=<?= $active_flyer['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(event, this.href)"><i class="bi bi-trash"></i> Hapus</a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning text-center">
                        <i class="bi bi-exclamation-triangle display-4 d-block mb-2"></i>
                        Belum ada flyer yang aktif.
                    </div>
                <?php endif; ?>
            </div>

            <!-- History List -->
            <div class="glass-card p-4">
                <h5 class="fw-bold mb-3">Riwayat Flyer</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Preview</th>
                                <th>Tanggal Upload</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($history_flyers->num_rows > 0): ?>
                                <?php while($row = $history_flyers->fetch_assoc()): ?>
                                    <tr>
                                        <td style="width: 100px;">
                                            <img src="../assets/uploads/flyers/<?= $row['image'] ?>" class="img-thumbnail" style="height: 60px;">
                                        </td>
                                        <td><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
                                        <td>
                                            <a href="flyer.php?activate=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill"><i class="bi bi-check-circle"></i> Aktifkan</a>
                                            <a href="flyer.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill ms-1" onclick="confirmDelete(event, this.href)"><i class="bi bi-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center text-muted">Belum ada riwayat.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

// HANDLE UPLOAD PHOTO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_photo'])) {
    if (!empty($_FILES['foto_profil']['name'])) {
        $target_dir = "../assets/uploads/profiles/";
        if (!is_dir($target_dir))
            mkdir($target_dir, 0777, true);

        $file_ext = strtolower(pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION));
        $valid_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $valid_ext)) {
            $new_name = "profile_" . $user_id . "_" . time() . "." . $file_ext;
            if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $target_dir . $new_name)) {
                // Delete old photo if exists
                if ($user['foto_profil'] && file_exists($target_dir . $user['foto_profil'])) {
                    unlink($target_dir . $user['foto_profil']);
                }

                $conn->query("UPDATE users SET foto_profil='$new_name' WHERE id=$user_id");
                $_SESSION['swal_icon'] = 'success';
                $_SESSION['swal_title'] = 'Berhasil!';
                $_SESSION['swal_text'] = 'Foto Profil berhasil diperbarui!';
                header("Location: profile.php");
                exit();
            }
        } else {
            $_SESSION['swal_icon'] = 'error';
            $_SESSION['swal_title'] = 'Gagal!';
            $_SESSION['swal_text'] = 'Format file tidak didukung/invalid.';
        }
    }
}

// HANDLE PASSWORD
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if (password_verify($current_pass, $user['password'])) {
        if ($new_pass === $confirm_pass) {
            $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password='$hashed_pass' WHERE id=$user_id");
            $_SESSION['swal_icon'] = 'success';
            $_SESSION['swal_title'] = 'Berhasil!';
            $_SESSION['swal_text'] = 'Password berhasil diubah!';
        } else {
            $_SESSION['swal_icon'] = 'error';
            $_SESSION['swal_title'] = 'Gagal!';
            $_SESSION['swal_text'] = 'Konfirmasi password baru tidak cocok.';
        }
    } else {
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_title'] = 'Gagal!';
        $_SESSION['swal_text'] = 'Password saat ini salah.';
    }
    header("Location: profile.php");
    exit();
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 mb-4">
            <div class="glass-card p-4 text-center">
                <h4 class="fw-bold mb-4">Profile Saya</h4>

                <div class="mb-4 position-relative d-inline-block">
                    <?php
                    $photo_path = $user['foto_profil'] ? "../assets/uploads/profiles/" . $user['foto_profil'] : "../assets/img/default-profile.png";
                    // If default image doesn't exist, use a placeholder URL or ensure asset exists
                    if (!file_exists($photo_path) && $user['foto_profil'])
                        $photo_path = "https://ui-avatars.com/api/?name=Admin&background=random";
                    if (!$user['foto_profil'])
                        $photo_path = "https://ui-avatars.com/api/?name=" . $_SESSION['username'] . "&background=random";
                    ?>
                    <img src="<?php echo $photo_path; ?>" class="rounded-circle shadow-lg object-fit-cover"
                        style="width: 150px; height: 150px; border: 4px solid white;">

                    <button class="btn btn-primary rounded-circle position-absolute bottom-0 end-0 shadow-sm"
                        style="width: 40px; height: 40px;" onclick="document.getElementById('fileInput').click()">
                        <i class="bi bi-camera-fill"></i>
                    </button>
                </div>

                <h5 class="fw-bold"><?php echo $_SESSION['username']; ?></h5>
                <span class="badge bg-primary rounded-pill mb-4 px-3">ADMINISTRATOR</span>

                <form method="POST" enctype="multipart/form-data" id="photoForm">
                    <input type="file" name="foto_profil" id="fileInput" class="d-none"
                        onchange="document.getElementById('photoForm').submit();" name="update_photo">
                    <input type="hidden" name="update_photo" value="1">
                </form>

                <hr>

                <div class="text-start">
                    <label class="form-label text-muted small fw-bold">USERNAME</label>
                    <p class="fw-bold"><?php echo $user['username']; ?></p>

                    <label class="form-label text-muted small fw-bold">LOGIN TERAKHIR</label>
                    <p class="fw-bold"><?php echo date('d F Y H:i'); // Just for display, or fetch from DB if tracked ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="glass-card p-4">
                <h4 class="fw-bold mb-4"><i class="bi bi-shield-lock"></i> Ganti Password</h4>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Password Saat Ini</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="change_password" class="btn btn-primary rounded-pill">Simpan
                            Password Baru</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
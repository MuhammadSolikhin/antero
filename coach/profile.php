<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pelatih') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
$coach_query = $conn->query("SELECT * FROM coaches WHERE user_id = $user_id LIMIT 1");
$coach = $coach_query->fetch_assoc();

// HANDLE UPLOAD PHOTO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_photo'])) {
    if (!empty($_FILES['foto_profil']['name'])) {
        $target_dir = "../assets/uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_ext = strtolower(pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION));
        $valid_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $valid_ext)) {
            $new_name = "coach_" . $user_id . "_" . time() . "." . $file_ext;
            if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $target_dir . $new_name)) {
                
                // If the coach profile exists, update the foto_pelatih
                if ($coach) {
                    // Delete old photo if exists
                    if ($coach['foto_pelatih'] && file_exists($target_dir . $coach['foto_pelatih'])) {
                        unlink($target_dir . $coach['foto_pelatih']);
                    }
                    $coach_id = $coach['id'];
                    $conn->query("UPDATE coaches SET foto_pelatih='$new_name' WHERE id=$coach_id");
                } else {
                    // Just update users table foto_profil as fallback
                    $user_target_dir = "../assets/uploads/profiles/";
                    if (!is_dir($user_target_dir)) mkdir($user_target_dir, 0777, true);
                    copy($target_dir . $new_name, $user_target_dir . $new_name);
                    $conn->query("UPDATE users SET foto_profil='$new_name' WHERE id=$user_id");
                }

                $_SESSION['swal_icon'] = 'success';
                $_SESSION['swal_title'] = 'Berhasil!';
                $_SESSION['swal_text'] = 'Foto Profil berhasil diperbarui!';
                header("Location: profile.php");
                exit();
            }
        } else {
            $_SESSION['swal_icon'] = 'error';
            $_SESSION['swal_title'] = 'Gagal!';
            $_SESSION['swal_text'] = 'Format file tidak didukung.';
        }
    }
}

// HANDLE UPLOAD CERTIFICATE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_certificate'])) {
    if (!empty($_FILES['sertifikat_file']['name']) && $coach) {
        $target_dir = "../assets/uploads/certificates/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_ext = strtolower(pathinfo($_FILES['sertifikat_file']['name'], PATHINFO_EXTENSION));
        $valid_ext = ['pdf', 'jpg', 'jpeg', 'png'];

        if (in_array($file_ext, $valid_ext)) {
            $new_name = time() . "_coach_" . $coach['id'] . "." . $file_ext;
            if (move_uploaded_file($_FILES['sertifikat_file']['tmp_name'], $target_dir . $new_name)) {
                
                // Delete old certificate if exists
                if ($coach['info_sertifikat'] && file_exists($target_dir . $coach['info_sertifikat'])) {
                    unlink($target_dir . $coach['info_sertifikat']);
                }

                $coach_id = $coach['id'];
                $conn->query("UPDATE coaches SET info_sertifikat='$new_name' WHERE id=$coach_id");

                $_SESSION['swal_icon'] = 'success';
                $_SESSION['swal_title'] = 'Berhasil!';
                $_SESSION['swal_text'] = 'Sertifikat sabuk berhasil diperbarui!';
                header("Location: profile.php");
                exit();
            }
        } else {
            $_SESSION['swal_icon'] = 'error';
            $_SESSION['swal_title'] = 'Gagal!';
            $_SESSION['swal_text'] = 'Format file sertifikat tidak didukung.';
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

// HANDLE EDIT PROFILE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_profile'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $conn->query("UPDATE users SET email='$email' WHERE id=$user_id");

    if ($coach) {
        $nama = $conn->real_escape_string($_POST['nama_pelatih']);
        $tingkatan = $conn->real_escape_string($_POST['tingkatan']);
        $tinggi = isset($_POST['tinggi_badan']) && $_POST['tinggi_badan'] !== '' ? floatval($_POST['tinggi_badan']) : 'NULL';
        $berat = isset($_POST['berat_badan']) && $_POST['berat_badan'] !== '' ? floatval($_POST['berat_badan']) : 'NULL';
        $coach_id = $coach['id'];
        $conn->query("UPDATE coaches SET nama_pelatih='$nama', tingkatan='$tingkatan', tinggi_badan=$tinggi, berat_badan=$berat WHERE id=$coach_id");
    }
    
    $_SESSION['swal_icon'] = 'success';
    $_SESSION['swal_title'] = 'Berhasil!';
    $_SESSION['swal_text'] = 'Profil berhasil diupdate!';
    header("Location: profile.php");
    exit();
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <!-- Profile Info Card -->
        <div class="col-md-5 mb-4">
            <div class="glass-card p-4 text-center">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">Profile Saya</h4>
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="bi bi-pencil-square"></i> Edit
                    </button>
                </div>

                <div class="mb-4 position-relative d-inline-block">
                    <?php
                    // Determine which photo to show
                    $photo_path = "";
                    if ($coach && $coach['foto_pelatih'] && file_exists("../assets/uploads/" . $coach['foto_pelatih'])) {
                        $photo_path = "../assets/uploads/" . $coach['foto_pelatih'];
                    } elseif ($user['foto_profil'] && file_exists("../assets/uploads/profiles/" . $user['foto_profil'])) {
                        $photo_path = "../assets/uploads/profiles/" . $user['foto_profil'];
                    } else {
                        // Create a placeholder based on username
                        $photo_name = $coach ? urlencode($coach['nama_pelatih']) : urlencode($user['username']);
                        $photo_path = "https://ui-avatars.com/api/?name=" . $photo_name . "&background=random";
                    }
                    ?>
                    <img src="<?php echo $photo_path; ?>" class="rounded-circle shadow-lg object-fit-cover"
                        style="width: 150px; height: 150px; border: 4px solid white;">

                    <button class="btn btn-primary rounded-circle position-absolute bottom-0 end-0 shadow-sm"
                        style="width: 40px; height: 40px;" onclick="document.getElementById('fileInput').click()">
                        <i class="bi bi-camera-fill"></i>
                    </button>
                    
                    <form method="POST" enctype="multipart/form-data" id="photoForm">
                        <input type="file" name="foto_profil" id="fileInput" class="d-none"
                            onchange="document.getElementById('photoForm').submit();">
                        <input type="hidden" name="update_photo" value="1">
                    </form>
                </div>

                <h5 class="fw-bold"><?php echo $coach ? htmlspecialchars($coach['nama_pelatih']) : htmlspecialchars($_SESSION['username']); ?></h5>
                <span class="badge bg-success rounded-pill mb-4 px-3">PELATIH</span>

                <hr>

                <div class="text-start">
                    <label class="form-label text-muted small fw-bold">USERNAME LOGIN</label>
                    <p class="fw-bold"><?php echo htmlspecialchars($user['username']); ?></p>

                    <label class="form-label text-muted small fw-bold">EMAIL</label>
                    <p class="fw-bold"><?php echo htmlspecialchars($user['email']); ?></p>
                    
                    <?php if ($coach): ?>
                    <label class="form-label text-muted small fw-bold mt-2">TINGKATAN SABUK</label>
                    <p class="fw-bold mb-3"><span class="badge bg-dark"><?php echo htmlspecialchars($coach['tingkatan']); ?></span></p>
                    
                    <hr>
                    
                    <label class="form-label text-muted small fw-bold">SERTIFIKAT SABUK</label>
                    <div class="mb-3">
                        <?php if ($coach['info_sertifikat']): ?>
                            <a href="../assets/uploads/certificates/<?php echo $coach['info_sertifikat']; ?>" target="_blank" class="btn btn-sm btn-outline-info rounded-pill w-100 mb-2">
                                <i class="bi bi-file-earmark-text"></i> Lihat Sertifikat Saat Ini
                            </a>
                        <?php else: ?>
                            <p class="text-muted small italic">Sertifikat belum diunggah.</p>
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data" class="mt-2 text-start">
                        <label class="form-label small">Upload/Ganti Sertifikat</label>
                        <div class="input-group input-group-sm">
                            <input type="file" class="form-control" name="sertifikat_file" accept=".pdf,.jpg,.jpeg,.png" required>
                            <button class="btn btn-outline-secondary" type="submit" name="update_certificate">Simpan</button>
                        </div>
                        <small class="text-muted" style="font-size: 0.70rem;">Format: PDF, JPG, PNG.</small>
                    </form>
                    
                    <hr>

                    <label class="form-label text-muted small fw-bold">TINGGI BADAN</label>
                    <p class="fw-bold"><?php echo ($coach['tinggi_badan'] ?? null) ? htmlspecialchars($coach['tinggi_badan']) . ' cm' : '<span class="text-muted fw-normal">Belum diisi</span>'; ?></p>

                    <label class="form-label text-muted small fw-bold">BERAT BADAN</label>
                    <p class="fw-bold mb-0"><?php echo ($coach['berat_badan'] ?? null) ? htmlspecialchars($coach['berat_badan']) . ' kg' : '<span class="text-muted fw-normal">Belum diisi</span>'; ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="col-md-6 mb-4">
            <div class="glass-card p-4 h-100">
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
                    <div class="d-grid mt-4">
                        <button type="submit" name="change_password" class="btn btn-primary rounded-pill py-2">
                            Simpan Password Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Profile -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <?php if ($coach): ?>
                    <div class="mb-3 text-start">
                        <label class="form-label">Nama Pelatih</label>
                        <input type="text" name="nama_pelatih" class="form-control" value="<?php echo htmlspecialchars($coach['nama_pelatih']); ?>" required>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3 text-start">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <?php if ($coach): ?>
                    <div class="mb-3 text-start">
                        <label class="form-label">Tingkatan Sabuk</label>
                        <input type="text" name="tingkatan" class="form-control" value="<?php echo htmlspecialchars($coach['tingkatan']); ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3 text-start">
                            <label class="form-label">Tinggi Badan (cm)</label>
                            <input type="number" name="tinggi_badan" class="form-control" step="0.1" min="0" max="300"
                                value="<?php echo htmlspecialchars($coach['tinggi_badan'] ?? ''); ?>" placeholder="Contoh: 170.0">
                        </div>
                        <div class="col-md-6 mb-3 text-start">
                            <label class="form-label">Berat Badan (kg)</label>
                            <input type="number" name="berat_badan" class="form-control" step="0.1" min="0" max="500"
                                value="<?php echo htmlspecialchars($coach['berat_badan'] ?? ''); ?>" placeholder="Contoh: 65.0">
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit_profile" class="btn btn-primary rounded-pill px-4 shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama_lengkap'];
    $ttl_place = $_POST['tempat_lahir'];
    $ttl_date = $_POST['tanggal_lahir'];
    $dojang = $_POST['dojang_id'];
    $sabuk = $_POST['tingkatan_sabuk'];
    $alamat = $_POST['alamat_domisili'];
    $email = $conn->real_escape_string($_POST['email']);

    // Check if update or insert
    $check = $conn->query("SELECT id, tingkatan_sabuk, dojang_id FROM students WHERE user_id = $user_id");

    $is_belt_changed = false;
    $student_id = 0;
    $is_new_student = false;

    if ($check->num_rows > 0) {
        $existing_data = $check->fetch_assoc();
        $student_id = $existing_data['id'];
        $current_sabuk = $existing_data['tingkatan_sabuk'];

        if ($current_sabuk != $sabuk) {
            $is_belt_changed = true;
        }

        // Check for Dojang Change
        $current_dojang_id = $existing_data['dojang_id'] ?? 0;

        // Base SQL for update
        $sql = "UPDATE students SET 
                nama_lengkap='$nama', tempat_lahir='$ttl_place', tanggal_lahir='$ttl_date', 
                dojang_id='$dojang', tingkatan_sabuk='$sabuk', alamat_domisili='$alamat' 
                WHERE user_id=$user_id";
    } else {
        // Insert new student
        $is_new_student = true;
        $is_belt_changed = true; // New student needs belt history
        $current_dojang_id = 0; // New student has no history
        $sql = "INSERT INTO students (user_id, dojang_id, nama_lengkap, tempat_lahir, tanggal_lahir, tingkatan_sabuk, alamat_domisili) 
                VALUES ($user_id, '$dojang', '$nama', '$ttl_place', '$ttl_date', '$sabuk', '$alamat')";
    }

    // Update Email in users table
    $conn->query("UPDATE users SET email='$email' WHERE id=$user_id");

    // Handle Certificate Upload Logic
    $cert_file_name = null;
    $upload_error = null;

    if (!empty($_FILES['bukti_sabuk']['name'])) {
        $target_dir = "../assets/uploads/certificates/";
        if (!is_dir($target_dir))
            mkdir($target_dir, 0777, true);

        $file_ext = strtolower(pathinfo($_FILES['bukti_sabuk']['name'], PATHINFO_EXTENSION));
        if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'pdf'])) {
            $new_name = "cert_" . $user_id . "_" . time() . "." . $file_ext;
            if (move_uploaded_file($_FILES['bukti_sabuk']['tmp_name'], $target_dir . $new_name)) {
                $cert_file_name = $new_name;
            } else {
                $upload_error = "Gagal upload sertifikat.";
            }
        } else {
            $upload_error = "Format sertifikat tidak valid.";
        }
    }

    // Validation: If belt changed, certificate is MANDATORY (Except for White Belt)
    if ($is_belt_changed && !$cert_file_name && $sabuk != 'Putih/Geup-10') {
        $_SESSION['swal_icon'] = 'warning';
        $_SESSION['swal_title'] = 'Perubahan Ditolak';
        $_SESSION['swal_text'] = 'Anda mengubah tingkatan sabuk, wajib upload bukti sertifikat baru!';
        header("Location: biodata.php");
        exit();
    }

    // Execute Primary Update/Insert
    if ($conn->query($sql)) {
        if (!$student_id)
            $student_id = $conn->insert_id;

        // Insert into History if belt changed or new certificate uploaded
        if ($is_belt_changed || $cert_file_name) {
            $cert_sql_val = $cert_file_name ? "'$cert_file_name'" : "NULL";
            $hist_sql = "INSERT INTO student_belt_history (student_id, tingkatan_sabuk, foto_sertifikat) 
                          VALUES ($student_id, '$sabuk', $cert_sql_val)";
            $conn->query($hist_sql);

            // Also update the main table's 'foto_sertifikat' column for backward compatibility
            if ($cert_file_name) {
                $conn->query("UPDATE students SET foto_sertifikat='$cert_file_name' WHERE id=$student_id");
            }
        }

        // Insert into Dojang History if changed
        if ($current_dojang_id != $dojang && $current_dojang_id != 0) {
            $conn->query("INSERT INTO student_dojang_history (student_id, old_dojang_id, new_dojang_id) 
                           VALUES ($student_id, '$current_dojang_id', '$dojang')");
        }

        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text'] = 'Biodata berhasil disimpan!';
    } else {
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_title'] = 'Gagal!';
        $_SESSION['swal_text'] = $conn->error;
    }

    // Handle Profile Picture
    if (!empty($_FILES['foto_profil']['name'])) {
        $target_dir = "../assets/uploads/profiles/";
        if (!is_dir($target_dir))
            mkdir($target_dir, 0777, true);

        $file_ext = strtolower(pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION));
        if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $new_name = "profile_" . $user_id . "_" . time() . "." . $file_ext;
            if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $target_dir . $new_name)) {
                // Remove old pic
                $old_data = $conn->query("SELECT foto_profil FROM users WHERE id=$user_id")->fetch_assoc();
                $old_pic = $old_data['foto_profil'];
                if ($old_pic && file_exists($target_dir . $old_pic))
                    unlink($target_dir . $old_pic);

                $conn->query("UPDATE users SET foto_profil='$new_name' WHERE id=$user_id");
            }
        }
    }

    header("Location: biodata.php");
    exit();
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';

// Fetch Dojangs for dropdown
$dojangs = $conn->query("SELECT * FROM dojangs");

// Fetch Current Data (Joined with Dojangs and Users for email)
$data = $conn->query("SELECT s.*, d.nama_dojang, u.email 
                      FROM students s 
                      LEFT JOIN dojangs d ON s.dojang_id = d.id 
                      JOIN users u ON s.user_id = u.id
                      WHERE s.user_id = $user_id")->fetch_assoc();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <!-- Profile Card -->
        <div class="col-md-5 mb-4">
            <div class="glass-card p-4 h-100 position-relative animate__animated animate__fadeInUp">
                <div class="text-center mb-4">
                    <?php
                    $u_pic = $conn->query("SELECT foto_profil FROM users WHERE id=$user_id")->fetch_assoc()['foto_profil'];
                    $photo_src = ($u_pic) ? "../assets/uploads/profiles/$u_pic" : "https://ui-avatars.com/api/?name=" . $_SESSION['username'] . "&background=random";
                    ?>
                    <div class="position-relative d-inline-block">
                        <img src="<?= $photo_src ?>" class="rounded-circle shadow mb-3 object-fit-cover"
                            style="width: 140px; height: 140px; border: 4px solid rgba(255,255,255,0.8);">
                    </div>
                    <h4 class="fw-bold mb-1"><?= $data['nama_lengkap'] ?? 'Belum ada nama' ?></h4>
                    <span class="badge bg-gradient-primary rounded-pill px-3 py-2 shadow-sm">
                        <?= $data['tingkatan_sabuk'] ?? 'Sabuk Belum Diatur' ?>
                    </span>
                </div>

                <div class="list-group list-group-flush bg-transparent">
                    <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted"><i class="bi bi-envelope me-2"></i> Email</span>
                        <span class="fw-semibold text-end text-truncate" style="max-width: 200px;"
                            title="<?= $data['email'] ?? '-' ?>">
                            <?= $data['email'] ?? '-' ?>
                        </span>
                    </div>
                    <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted"><i class="bi bi-geo-alt me-2"></i> Tempat Lahir</span>
                        <span class="fw-semibold"><?= $data['tempat_lahir'] ?? '-' ?></span>
                    </div>
                    <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted"><i class="bi bi-calendar-event me-2"></i> Tanggal Lahir</span>
                        <span
                            class="fw-semibold"><?= $data['tanggal_lahir'] ? date('d F Y', strtotime($data['tanggal_lahir'])) : '-' ?></span>
                    </div>
                    <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted"><i class="bi bi-house-door me-2"></i> Asal Dojang</span>
                        <span class="fw-semibold text-end text-truncate" style="max-width: 150px;"
                            title="<?= $data['nama_dojang'] ?? '-' ?>">
                            <?= $data['nama_dojang'] ?? '-' ?>
                        </span>
                    </div>
                    <div class="list-group-item bg-transparent px-0">
                        <span class="text-muted d-block mb-1"><i class="bi bi-map me-2"></i> Alamat Domisili</span>
                        <p class="mb-0 small text-break"><?= $data['alamat_domisili'] ?? '-' ?></p>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="button" class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm"
                        data-bs-toggle="modal" data-bs-target="#editBiodataModal">
                        <i class="bi bi-pencil-square me-2"></i> Update Biodata
                    </button>
                </div>
            </div>
        </div>

        <!-- Dojang History Column -->
        <div class="col-md-5 mb-4">
             <div class="glass-card p-4 h-100 animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                 <h5 class="fw-bold mb-4"><i class="bi bi-clock-history text-primary"></i> Riwayat Perpindahan Dojang</h5>
                 
                 <div class="table-responsive">
                    <table class="table table-borderless table-striped small align-middle">
                        <thead class="table-light rounded-3">
                            <tr>
                                <th>Tanggal</th>
                                <th>Dari</th>
                                <th>Ke</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($data['id'])) {
                                $d_hist = $conn->query("SELECT h.*, d1.nama_dojang as old_dojang, d2.nama_dojang as new_dojang 
                                             FROM student_dojang_history h 
                                             LEFT JOIN dojangs d1 ON h.old_dojang_id = d1.id 
                                             LEFT JOIN dojangs d2 ON h.new_dojang_id = d2.id 
                                             WHERE h.student_id = " . $data['id'] . " ORDER BY h.change_date DESC");

                                if ($d_hist && $d_hist->num_rows > 0):
                                    while ($dh = $d_hist->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($dh['change_date'])); ?></td>
                                <td><?php echo $dh['old_dojang'] ?: '-'; ?></td>
                                <td><?php echo $dh['new_dojang'] ?: '-'; ?></td>
                            </tr>
                            <?php 
                                    endwhile;
                                else:
                            ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">Belum ada riwayat perpindahan.</td>
                            </tr>
                            <?php 
                                endif;
                            } else {
                                echo '<tr><td colspan="3" class="text-center text-muted">Data siswa belum lengkap.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                 </div>
             </div>
        </div>
    </div>
</div>

<!-- Modal Update Biodata -->
<div class="modal fade" id="editBiodataModal" tabindex="-1" aria-labelledby="editBiodataLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content glass-card border-0">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="editBiodataLabel"><i
                        class="bi bi-person-lines-fill text-primary me-2"></i> Edit Biodata</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" enctype="multipart/form-data">
                    <!-- Photo Upload Section -->
                    <div class="text-center mb-4">
                        <img src="<?= $photo_src ?>" id="modalProfileImage"
                            class="rounded-circle shadow mb-3 object-fit-cover"
                            style="width: 100px; height: 100px; border: 3px solid #eee;">
                        <div>
                            <label for="foto_profil" class="btn btn-sm btn-outline-primary rounded-pill">
                                <i class="bi bi-camera"></i> Ganti Foto Profil
                            </label>
                            <input type="file" name="foto_profil" id="foto_profil" class="d-none">
                            <div class="form-text small mt-1">Format: JPG, PNG. Max 2MB. (Biarkan kosong jika tidak
                                ganti)</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                            value="<?php echo $data['email'] ?? ''; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control"
                            value="<?php echo $data['nama_lengkap'] ?? ''; ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control"
                                value="<?php echo $data['tempat_lahir'] ?? ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control"
                                value="<?php echo $data['tanggal_lahir'] ?? ''; ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Asal Dojang</label>
                        <select name="dojang_id" class="form-select" required>
                            <option value="">-- Pilih Dojang --</option>
                            <?php
                            $dojangs->data_seek(0);
                            while ($d = $dojangs->fetch_assoc()): ?>
                                <option value="<?php echo $d['id']; ?>" <?php echo (isset($data['dojang_id']) && $data['dojang_id'] == $d['id']) ? 'selected' : ''; ?>>
                                    <?php echo $d['nama_dojang']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <div class="form-text">Jika dojang tidak ada, hubungi Admin.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tingkatan Sabuk</label>
                        <select name="tingkatan_sabuk" class="form-select" required>
                            <option value="">-- Pilih Sabuk --</option>
                            <?php
                            $belts = [
                                'Putih/Geup-10',
                                'Kuning/Geup-9',
                                'Kuning Strip/Geup-8',
                                'Hijau/Geup-7',
                                'Hijau Strip/Geup-6',
                                'Biru/Geup-5',
                                'Biru Strip/Geup-4',
                                'Merah/Geup-3',
                                'Merah - 1/Geup-2',
                                'Merah - 2/Geup-1'
                            ];
                            $roman = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];
                            for ($i = 1; $i <= 10; $i++) {
                                $belts[] = "DAN $roman[$i]";
                            }

                            foreach ($belts as $b) {
                                $sel = (isset($data['tingkatan_sabuk']) && $data['tingkatan_sabuk'] == $b) ? 'selected' : '';
                                echo "<option value='$b' $sel>$b</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bukti Sertifikat Sabuk <span class="text-danger small">* (Wajib jika
                                ganti sabuk, Kecuali Sabuk Putih)</span></label>
                        <input type="file" name="bukti_sabuk" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        <div class="form-text">Upload sertifikat baru jika Anda mengubah tingkatan sabuk.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Alamat Domisili</label>
                        <textarea name="alamat_domisili" class="form-control" rows="3"
                            required><?php echo $data['alamat_domisili'] ?? ''; ?></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill fw-bold">SIMPAN PERUBAHAN</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('foto_profil').addEventListener('change', function (event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('modalProfileImage').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>
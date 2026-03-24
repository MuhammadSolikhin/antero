<?php
session_start();
require_once '../config/database.php';

// Cek autentikasi dan role pelatih
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pelatih') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil coach_id untuk pelatih ini
$coach_query = $conn->query("SELECT id FROM coaches WHERE user_id = $user_id LIMIT 1");
if ($coach_query->num_rows == 0) {
    // Jika belum dihubungkan dengan data pelatih, tampilkan pesan warning (ditangani di view bawah)
    $coach_id = false;
} else {
    $coach_data = $coach_query->fetch_assoc();
    $coach_id = $coach_data['id'];
}

// ==========================================
// HANDLE ACTIONS (HANYA JIKA COACH ID VALID)
// ==========================================
if ($coach_id) {
    // 1. TAMBAH RIWAYAT PELATIHAN
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_training'])) {
        $year = intval($_POST['year']);
        $level = $conn->real_escape_string($_POST['level']);
        $description = $conn->real_escape_string($_POST['description']);
        $certificate_file = null;

        // Handle Upload Sertifikat Pelatihan
        if (isset($_FILES['certificate_file']) && $_FILES['certificate_file']['name'] != '') {
            $target_dir = "../assets/uploads/trainings/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            
            $file_ext = strtolower(pathinfo($_FILES['certificate_file']['name'], PATHINFO_EXTENSION));
            $valid_ext = ['pdf', 'jpg', 'jpeg', 'png'];
            
            if (in_array($file_ext, $valid_ext)) {
                $file_name = time() . '_' . basename($_FILES['certificate_file']['name']);
                if (move_uploaded_file($_FILES['certificate_file']['tmp_name'], $target_dir . $file_name)) {
                    $certificate_file = $file_name;
                }
            } else {
                $_SESSION['swal_icon'] = 'error';
                $_SESSION['swal_title'] = 'Gagal!';
                $_SESSION['swal_text'] = 'Format file tidak didukung.';
                header("Location: trainings.php");
                exit();
            }
        }

        $stmt = $conn->prepare("INSERT INTO coach_trainings (coach_id, year, level, description, certificate_file) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $coach_id, $year, $level, $description, $certificate_file);
        
        if ($stmt->execute()) {
            $_SESSION['swal_icon'] = 'success';
            $_SESSION['swal_title'] = 'Berhasil!';
            $_SESSION['swal_text'] = 'Riwayat pelatihan berhasil ditambahkan!';
        } else {
            $_SESSION['swal_icon'] = 'error';
            $_SESSION['swal_title'] = 'Error!';
            $_SESSION['swal_text'] = 'Gagal menyimpan data.';
        }
        header("Location: trainings.php");
        exit();
    }

    // 2. EDIT RIWAYAT PELATIHAN
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_training'])) {
        $training_id = intval($_POST['training_id']);
        $year = intval($_POST['year']);
        $level = $conn->real_escape_string($_POST['level']);
        $description = $conn->real_escape_string($_POST['description']);
        
        // Ambil data file lama untuk jaga-jaga
        $old_data = $conn->query("SELECT certificate_file FROM coach_trainings WHERE id = $training_id AND coach_id = $coach_id")->fetch_assoc();
        
        // Handle Upload Sertifikat Baru
        $cert_query_part = "";
        if (isset($_FILES['certificate_file']) && $_FILES['certificate_file']['name'] != '') {
            $target_dir = "../assets/uploads/trainings/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            
            $file_ext = strtolower(pathinfo($_FILES['certificate_file']['name'], PATHINFO_EXTENSION));
            $valid_ext = ['pdf', 'jpg', 'jpeg', 'png'];
            
            if (in_array($file_ext, $valid_ext)) {
                $file_name = time() . '_' . basename($_FILES['certificate_file']['name']);
                if (move_uploaded_file($_FILES['certificate_file']['tmp_name'], $target_dir . $file_name)) {
                    $cert_query_part = ", certificate_file='$file_name'";
                    
                    // Hapus file lama jika ada
                    if ($old_data['certificate_file'] && file_exists($target_dir . $old_data['certificate_file'])) {
                        unlink($target_dir . $old_data['certificate_file']);
                    }
                }
            } else {
                $_SESSION['swal_icon'] = 'error';
                $_SESSION['swal_title'] = 'Gagal!';
                $_SESSION['swal_text'] = 'Format file tidak didukung.';
                header("Location: trainings.php");
                exit();
            }
        }

        $sql = "UPDATE coach_trainings SET year=$year, level='$level', description='$description' $cert_query_part WHERE id=$training_id AND coach_id=$coach_id";
        if ($conn->query($sql)) {
            $_SESSION['swal_icon'] = 'success';
            $_SESSION['swal_title'] = 'Berhasil!';
            $_SESSION['swal_text'] = 'Riwayat pelatihan berhasil diupdate!';
        } else {
            $_SESSION['swal_icon'] = 'error';
            $_SESSION['swal_title'] = 'Error!';
            $_SESSION['swal_text'] = 'Gagal mengupdate data.';
        }
        header("Location: trainings.php");
        exit();
    }

    // 3. HAPUS RIWAYAT PELATIHAN
    if (isset($_GET['delete'])) {
        $training_id = intval($_GET['delete']);
        
        // Dapatkan nama file untuk dihapus
        $old_data = $conn->query("SELECT certificate_file FROM coach_trainings WHERE id=$training_id AND coach_id=$coach_id")->fetch_assoc();
        
        // Pastikan hanya bisa menghapus miliknya sendiri
        if ($conn->query("DELETE FROM coach_trainings WHERE id=$training_id AND coach_id=$coach_id")) {
            // Hapus file sertifikat jika ada
            if ($old_data && $old_data['certificate_file']) {
                $target_dir = "../assets/uploads/trainings/";
                if (file_exists($target_dir . $old_data['certificate_file'])) {
                    unlink($target_dir . $old_data['certificate_file']);
                }
            }
            $_SESSION['swal_icon'] = 'success';
            $_SESSION['swal_title'] = 'Berhasil!';
            $_SESSION['swal_text'] = 'Riwayat pelatihan dihapus!';
        } else {
            $_SESSION['swal_icon'] = 'error';
            $_SESSION['swal_title'] = 'Error!';
            $_SESSION['swal_text'] = 'Gagal menghapus data.';
        }
        header("Location: trainings.php");
        exit();
    }
}

// ==========================================
// VIEW
// ==========================================
require_once '../includes/header.php';
require_once '../includes/navbar.php';

// Ambil daftar pelatihan jika coach_id valid
$trainings = null;
if ($coach_id) {
    $trainings = $conn->query("SELECT * FROM coach_trainings WHERE coach_id = $coach_id ORDER BY year DESC");
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="glass-card px-4 py-3 mb-3 d-flex justify-content-between align-items-center">
                <h3 class="fw-bold mb-0"><i class="bi bi-journal-bookmark-fill text-primary"></i> Riwayat Pelatihan & Sertifikasi</h3>
                
                <?php if ($coach_id): ?>
                <button class="btn btn-primary rounded-pill shadow-sm" data-bs-toggle="modal"
                    data-bs-target="#addTrainingModal">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Pelatihan
                </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-12">
            <div class="glass-card p-4">
                
                <?php if (!$coach_id): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-person-exclamation display-1 text-muted mb-3"></i>
                        <h4>Profil Belum Terhubung</h4>
                        <p class="text-muted">Akun Anda belum dikaitkan dengan profil Pelatih manapun. <br>Harap minta Administrator untuk menautkan akun Anda demi bisa menggunakan fitur ini.</p>
                    </div>
                <?php elseif ($trainings->num_rows == 0): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-journal-x display-1 text-muted mb-3"></i>
                        <h4>Belum ada Riwayat Pelatihan</h4>
                        <p class="text-muted">Tambahkan riwayat pelatihan, sertifikasi, atau diklat Anda melalui tombol di pojok kanan atas.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tahun</th>
                                    <th>Level/Sertifikat</th>
                                    <th>Deskripsi Acara</th>
                                    <th>File Bukti</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $trainings->fetch_assoc()): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo $row['year']; ?></td>
                                        <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($row['level']); ?></span></td>
                                        <td><?php echo nl2br(htmlspecialchars($row['description'])); ?></td>
                                        <td>
                                            <?php if ($row['certificate_file']): ?>
                                                <a href="../assets/uploads/trainings/<?php echo $row['certificate_file']; ?>"
                                                    target="_blank" class="btn btn-sm btn-outline-info rounded-pill"><i
                                                        class="bi bi-file-earmark-text"></i> Lihat File</a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-warning rounded-pill me-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editTrainingModal<?php echo $row['id']; ?>"><i
                                                    class="bi bi-pencil-square"></i></button>
                                            <a href="trainings.php?delete=<?php echo $row['id']; ?>"
                                                class="btn btn-sm btn-outline-danger rounded-pill"
                                                onclick="confirmDelete(event, this.href)"><i class="bi bi-trash"></i></a>
                                        </td>
                                    </tr>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php if ($coach_id): ?>
<!-- Modal Add Training -->
<div class="modal fade" id="addTrainingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Tambah Riwayat Pelatihan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="trainings.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tahun</label>
                            <input type="number" name="year" class="form-control" value="<?php echo date('Y'); ?>" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Level / Nama Sertifikat</label>
                            <input type="text" name="level" class="form-control" placeholder="Contoh: Nasional, Diklat Dasar" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi / Kegiatan</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Tuliskan detail pelatihan/diklat yang diikuti..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bukti Sertifikat / Dokumen <small class="text-muted">(opsional)</small></label>
                        <input type="file" name="certificate_file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_training" class="btn btn-primary rounded-pill px-4 shadow-sm">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php 
// Modals Edit Training (Rendered outside containers to prevent clipping)
if ($coach_id && $trainings->num_rows > 0):
    $trainings->data_seek(0); // Reset pointer
    while ($row = $trainings->fetch_assoc()): 
?>
    <!-- Modal Edit Training -->
    <div class="modal fade" id="editTrainingModal<?php echo $row['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Edit Riwayat Pelatihan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="trainings.php" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="training_id" value="<?php echo $row['id']; ?>">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tahun</label>
                                <input type="number" name="year" class="form-control" value="<?php echo $row['year']; ?>" required>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Level / Nama Sertifikat</label>
                                <input type="text" name="level" class="form-control" placeholder="Contoh: Nasional" value="<?php echo htmlspecialchars($row['level']); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi / Kegiatan</label>
                            <textarea name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($row['description']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Update Bukti Sertifikat <small class="text-muted">(opsional)</small></label>
                            <input type="file" name="certificate_file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <small class="text-muted">Biarkan kosong jika tidak ingin mengubah file sertifikat saat ini.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 border-top-0 pt-0">
                        <button type="button" class="btn btn-secondary rounded-pill px-4"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_training" class="btn btn-primary rounded-pill px-4 shadow-sm">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php 
    endwhile; 
endif; 
?>

<script>
    function confirmDelete(event, url) {
        event.preventDefault();
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data pelatihan ini akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }
</script>

<?php require_once '../includes/footer.php'; ?>

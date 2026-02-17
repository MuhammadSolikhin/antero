<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'siswa') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$student = $conn->query("SELECT id FROM students WHERE user_id = $user_id")->fetch_assoc();
$student_id = $student['id'];

// Handle Add Training
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_training'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $year = intval($_POST['year']);

    // File Upload
    $file_name = "";
    if (isset($_FILES['certificate_file']) && $_FILES['certificate_file']['error'] == 0) {
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($_FILES['certificate_file']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $target_dir = "../assets/uploads/student_certificates/";
            if (!file_exists($target_dir))
                mkdir($target_dir, 0777, true);

            $file_name = time() . "_" . $student_id . "." . $ext;
            if (move_uploaded_file($_FILES['certificate_file']['tmp_name'], $target_dir . $file_name)) {
                $sql = "INSERT INTO student_trainings (student_id, name, year, certificate_file) VALUES ($student_id, '$name', $year, '$file_name')";
                if ($conn->query($sql)) {
                    $_SESSION['swal_icon'] = 'success';
                    $_SESSION['swal_title'] = 'Berhasil';
                    $_SESSION['swal_text'] = 'Data pelatihan berhasil dikirim untuk verifikasi.';
                } else {
                    $_SESSION['swal_icon'] = 'error';
                    $_SESSION['swal_title'] = 'Gagal';
                    $_SESSION['swal_text'] = 'Database Error: ' . $conn->error;
                }
            } else {
                $_SESSION['swal_icon'] = 'error';
                $_SESSION['swal_title'] = 'Gagal';
                $_SESSION['swal_text'] = 'Gagal mengupload file.';
            }
        } else {
            $_SESSION['swal_icon'] = 'error';
            $_SESSION['swal_title'] = 'Format Salah';
            $_SESSION['swal_text'] = 'Hanya file PDF, JPG, JPEG, dan PNG yang diperbolehkan.';
        }
    } else {
        $_SESSION['swal_icon'] = 'warning';
        $_SESSION['swal_title'] = 'File Wajib';
        $_SESSION['swal_text'] = 'Mohon upload sertifikat bukti pelatihan.';
    }

    header("Location: trainings.php");
    exit();
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container-fluid px-4 py-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="glass-card p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold m-0 text-primary">Pelatihan / Diklat</h2>
                    <p class="text-muted m-0">Riwayat pelatihan yang pernah diikuti</p>
                </div>
                <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal"
                    data-bs-target="#addTrainingModal">
                    <i class="bi bi-plus-lg me-2"></i> Tambah Data
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="glass-card p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Pelatihan / Diklat</th>
                                <th>Tahun</th>
                                <th>Sertifikat</th>
                                <th>Status Verifikasi</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = $conn->query("SELECT * FROM student_trainings WHERE student_id = $student_id ORDER BY year DESC, created_at DESC");
                            if ($query->num_rows > 0):
                                $no = 1;
                                while ($row = $query->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td>
                                            <?php echo $no++; ?>
                                        </td>
                                        <td class="fw-bold">
                                            <?php echo $row['name']; ?>
                                        </td>
                                        <td>
                                            <?php echo $row['year']; ?>
                                        </td>
                                        <td>
                                            <a href="../assets/uploads/student_certificates/<?php echo $row['certificate_file']; ?>"
                                                target="_blank" class="btn btn-sm btn-outline-info rounded-pill">
                                                <i class="bi bi-file-earmark-text me-1"></i> Lihat
                                            </a>
                                        </td>
                                        <td>
                                            <?php if ($row['status'] == 'pending'): ?>
                                                <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>
                                                    Menunggu</span>
                                            <?php elseif ($row['status'] == 'verified'): ?>
                                                <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>
                                                    Terverifikasi</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger"><i class="bi bi-x-circle-fill me-1"></i>
                                                    Ditolak</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($row['admin_note']): ?>
                                                <small class="text-danger"><i class="bi bi-info-circle me-1"></i>
                                                    <?php echo $row['admin_note']; ?>
                                                </small>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php
                                endwhile;
                            else:
                                ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-journal-x display-1 d-block mb-3 opacity-25"></i>
                                        Belum ada data pelatihan yang ditambahkan.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addTrainingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Tambah Data Pelatihan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Pelatihan / Diklat</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Diklat Wasit Daerah"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tahun Pelaksanaan</label>
                        <input type="number" name="year" class="form-control" placeholder="2024" min="2000"
                            max="<?php echo date('Y'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload Sertifikat <span class="text-danger">*</span></label>
                        <input type="file" name="certificate_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png"
                            required>
                        <div class="form-text">Format: PDF, JPG, PNG (Max 5MB). Wajib diisi.</div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_training" class="btn btn-primary rounded-pill px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
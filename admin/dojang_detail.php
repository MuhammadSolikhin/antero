<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$id_dojang = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_dojang == 0) {
    header("Location: master_dojang.php");
    exit();
}

// Get Dojang Info
$dojang = $conn->query("SELECT * FROM dojangs WHERE id=$id_dojang")->fetch_assoc();
if (!$dojang) {
    header("Location: master_dojang.php");
    exit();
}

// Fetch All Dojangs for Dropdown
$all_dojangs = [];
$d_res = $conn->query("SELECT id, nama_dojang FROM dojangs ORDER BY nama_dojang ASC");
while ($d = $d_res->fetch_assoc()) {
    $all_dojangs[] = $d;
}

// FUNCTIONS
function generateUsername($name)
{
    $name = strtolower(str_replace(' ', '', $name));
    return $name . rand(100, 999);
}

// HANDLE ACTIONS

// 1. Add Student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    $nama = $conn->real_escape_string($_POST['nama_lengkap']);
    $ttl_place = $_POST['tempat_lahir'];
    $ttl_date = $_POST['tanggal_lahir'];
    $sabuk = $_POST['tingkatan_sabuk'];
    $alamat = $_POST['alamat_domisili'];

    // Auto-create User
    $username = generateUsername($nama);
    $password = password_hash('123456', PASSWORD_DEFAULT);
    $role = 'siswa';
    while ($conn->query("SELECT id FROM users WHERE username='$username'")->num_rows > 0) {
        $username = generateUsername($nama);
    }

    // File Upload (Certificate)
    $file_name = null;
    if (!empty($_FILES['foto_sertifikat']['name'])) {
        $upload_dir = '../assets/uploads/certificates/';
        if (!is_dir($upload_dir))
            mkdir($upload_dir, 0777, true);

        $file_ext = strtolower(pathinfo($_FILES['foto_sertifikat']['name'], PATHINFO_EXTENSION));
        $valid_ext = ['jpg', 'jpeg', 'png', 'pdf'];

        if (in_array($file_ext, $valid_ext)) {
            $new_name = time() . '_' . uniqid() . '.' . $file_ext;
            if (move_uploaded_file($_FILES['foto_sertifikat']['tmp_name'], $upload_dir . $new_name)) {
                $file_name = $new_name;
            }
        }
    }

    $conn->begin_transaction();
    try {
        $conn->query("INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')");
        $user_id = $conn->insert_id;

        $sql = "INSERT INTO students (user_id, dojang_id, nama_lengkap, tempat_lahir, tanggal_lahir, tingkatan_sabuk, foto_sertifikat, alamat_domisili) 
                VALUES ($user_id, $id_dojang, '$nama', '$ttl_place', '$ttl_date', '$sabuk', '$file_name', '$alamat')";
        $conn->query($sql);
        $student_id = $conn->insert_id;

        // Add to History
        $cert_val = $file_name ? "'$file_name'" : "NULL";
        $conn->query("INSERT INTO student_belt_history (student_id, tingkatan_sabuk, foto_sertifikat) VALUES ($student_id, '$sabuk', $cert_val)");

        $conn->commit();
        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text'] = "Siswa ditambahkan! Username: $username";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_text'] = $e->getMessage();
    }
    header("Location: dojang_detail.php?id=$id_dojang");
    exit();
}

// 2. Edit Student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_student'])) {
    $id_student = intval($_POST['id']);
    $nama = $conn->real_escape_string($_POST['nama_lengkap']);
    $ttl_place = $_POST['tempat_lahir'];
    $ttl_date = $_POST['tanggal_lahir'];
    $sabuk = $_POST['tingkatan_sabuk'];
    $alamat = $_POST['alamat_domisili'];
    $status = $_POST['status']; // Added status

    // Check old data for belt and dojang comparison
    $old_data = $conn->query("SELECT tingkatan_sabuk, dojang_id FROM students WHERE id=$id_student")->fetch_assoc();
    $old_sabuk = $old_data['tingkatan_sabuk'];
    $old_dojang_id = $old_data['dojang_id'];

    // Get new Dojang ID
    $new_dojang_id = isset($_POST['dojang_id']) ? intval($_POST['dojang_id']) : $old_dojang_id;

    // Handle File Update
    $file_update_sql = "";
    $new_cert_name = null;

    if (!empty($_FILES['foto_sertifikat']['name'])) {
        $upload_dir = '../assets/uploads/certificates/';
        $file_ext = strtolower(pathinfo($_FILES['foto_sertifikat']['name'], PATHINFO_EXTENSION));
        if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'pdf'])) {
            $new_name = time() . '_' . uniqid() . '.' . $file_ext;
            if (move_uploaded_file($_FILES['foto_sertifikat']['tmp_name'], $upload_dir . $new_name)) {
                $new_cert_name = $new_name;
                $file_update_sql = ", foto_sertifikat='$new_name'";
            }
        }
    }

    $sql = "UPDATE students SET 
            nama_lengkap='$nama', 
            tempat_lahir='$ttl_place', 
            tanggal_lahir='$ttl_date', 
            tingkatan_sabuk='$sabuk', 
            alamat_domisili='$alamat', 
            dojang_id='$new_dojang_id',
            status='$status' 
            $file_update_sql
            WHERE id=$id_student";

    if ($conn->query($sql)) {
        // Add to Belt History
        if ($old_sabuk != $sabuk || $new_cert_name) {
            $cert_val = $new_cert_name ? "'$new_cert_name'" : "NULL";
            $conn->query("INSERT INTO student_belt_history (student_id, tingkatan_sabuk, foto_sertifikat) VALUES ($id_student, '$sabuk', $cert_val)");
        }

        // Add to Dojang History
        if ($old_dojang_id != $new_dojang_id) {
            $h_sql = "INSERT INTO student_dojang_history (student_id, old_dojang_id, new_dojang_id) VALUES ($id_student, '$old_dojang_id', '$new_dojang_id')";
            $conn->query($h_sql);
        }

        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text'] = 'Data siswa berhasil diupdate!';
    } else {
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_title'] = 'Gagal!';
        $_SESSION['swal_text'] = $conn->error;
    }
    header("Location: dojang_detail.php?id=$id_dojang");
    exit();
}

// 3. Delete Student
if (isset($_GET['delete_student'])) {
    $id_student = intval($_GET['delete_student']);

    // Get info for cleanup
    $info = $conn->query("SELECT user_id, foto_sertifikat FROM students WHERE id=$id_student")->fetch_assoc();
    $user_id = $info['user_id'];
    $cert_file = $info['foto_sertifikat'];

    $conn->begin_transaction();
    try {
        $conn->query("UPDATE students SET is_deleted = 1, deleted_at = NOW() WHERE id=$id_student");
        $conn->query("UPDATE users SET is_deleted = 1 WHERE id=$user_id"); // Disable Login
        $conn->commit();

        // Remove File
        if ($cert_file && file_exists("../assets/uploads/certificates/" . $cert_file)) {
            unlink("../assets/uploads/certificates/" . $cert_file);
        }

        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_text'] = 'Siswa berhasil dipindahkan ke Sampah.';
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_text'] = $e->getMessage();
    }
    header("Location: dojang_detail.php?id=$id_dojang");
    exit();
}


// Include Select2 CSS
echo '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />';
echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />';

require_once '../includes/header.php';
require_once '../includes/navbar.php';

// Fetch Students
$students = $conn->query("SELECT s.*, u.username FROM students s JOIN users u ON s.user_id = u.id WHERE s.dojang_id = $id_dojang AND s.is_deleted = 0 ORDER BY s.nama_lengkap ASC");
?>

<div class="container py-5">
    <div class="glass-card px-4 py-3 mb-4 d-flex justify-content-between align-items-center">
        <div>
            <a href="master_dojang.php" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i>
                Kembali</a>
            <h3 class="fw-bold mb-0 mt-1"><i class="bi bi-building text-primary"></i>
                <?php echo $dojang['nama_dojang']; ?></h3>
            <span class="text-muted small"><i class="bi bi-geo-alt"></i> <?php echo $dojang['alamat']; ?></span>
        </div>
        <button class="btn btn-primary rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#addStudentModal">
            <i class="bi bi-person-plus-fill me-1"></i> Tambah Siswa
        </button>
    </div>

    <div class="glass-card p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Lengkap</th>
                        <th>Sabuk</th>
                        <th>TTL</th>
                        <th>Sertifikat</th>
                        <th>Username</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($students->num_rows > 0): ?>
                        <?php $no = 1;
                        while ($row = $students->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td class="fw-bold"><?php echo $row['nama_lengkap']; ?></td>
                                <td><span class="badge bg-info text-dark"><?php echo $row['tingkatan_sabuk']; ?></span></td>
                                <td><?php echo $row['tempat_lahir'] . ', ' . date('d/m/Y', strtotime($row['tanggal_lahir'])); ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info rounded-pill" data-bs-toggle="modal"
                                        data-bs-target="#historyModal<?php echo $row['id']; ?>">
                                        <i class="bi bi-clock-history"></i> Riwayat
                                    </button>
                                </td>
                                <td><span class="badge bg-secondary"><?php echo $row['username']; ?></span></td>
                                <td>
                                    <?php if ($row['status'] == 'aktif'): ?>
                                        <span class="badge bg-success cursor-pointer"
                                            onclick="toggleStatus(<?php echo $row['id']; ?>, 'aktif')"
                                            id="status-badge-<?php echo $row['id']; ?>" title="Klik untuk menonaktifkan"
                                            style="cursor: pointer;">
                                            <i class="bi bi-eye" id="status-icon-<?php echo $row['id']; ?>"></i>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary cursor-pointer"
                                            onclick="toggleStatus(<?php echo $row['id']; ?>, 'tidak aktif')"
                                            id="status-badge-<?php echo $row['id']; ?>" title="Klik untuk mengaktifkan"
                                            style="cursor: pointer;">
                                            <i class="bi bi-eye-slash" id="status-icon-<?php echo $row['id']; ?>"></i>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-warning rounded-pill me-1" data-bs-toggle="modal"
                                        data-bs-target="#editStudentModal<?php echo $row['id']; ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <a href="dojang_detail.php?id=<?php echo $id_dojang; ?>&delete_student=<?php echo $row['id']; ?>"
                                        class="btn btn-sm btn-outline-danger rounded-pill"
                                        onclick="confirmDelete(event, this.href)">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Belum ada siswa di Dojang ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Siswa Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info small">
                        Username login akan dibuat otomatis (Default Pass: 123456).
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
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
                                for ($i = 1; $i <= 10; $i++)
                                    $belts[] = "DAN " . ($i == 1 ? 'I' : ($i == 2 ? 'II' : ($i == 3 ? 'III' : ($i == 4 ? 'IV' : ($i == 5 ? 'V' : 'VI')))));
                                // Simplified Roman just for example, better use array
                                foreach ($belts as $b)
                                    echo "<option value='$b'>$b</option>";
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Foto Sertifikat (Bukti Sabuk)</label>
                        <input type="file" name="foto_sertifikat" class="form-control">
                        <div class="form-text">Format: JPG/PNG/PDF. Max 2MB.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat Domisili</label>
                        <textarea name="alamat_domisili" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status Keanggotaan</label>
                        <select name="status" class="form-select" required>
                            <option value="aktif">Aktif</option>
                            <option value="tidak aktif">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_student" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modals -->
<?php
$students->data_seek(0);
while ($row = $students->fetch_assoc()):
    ?>
    <!-- History Modal -->
    <div class="modal fade" id="historyModal<?php echo $row['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Riwayat Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6 class="fw-bold mb-3"><?php echo $row['nama_lengkap']; ?></h6>

                    <!-- Tabs Nav -->
                    <ul class="nav nav-tabs mb-3" id="historyTab<?php echo $row['id']; ?>" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="belt-tab-<?php echo $row['id']; ?>" data-bs-toggle="tab"
                                data-bs-target="#belt-content-<?php echo $row['id']; ?>" type="button" role="tab"><i
                                    class="bi bi-award"></i> Sabuk</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="dojang-tab-<?php echo $row['id']; ?>" data-bs-toggle="tab"
                                data-bs-target="#dojang-content-<?php echo $row['id']; ?>" type="button" role="tab"><i
                                    class="bi bi-geo-alt"></i> Dojang</button>
                        </li>
                    </ul>

                    <!-- Tabs Content -->
                    <div class="tab-content">
                        <!-- Belt History -->
                        <div class="tab-pane fade show active" id="belt-content-<?php echo $row['id']; ?>" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm small table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Sabuk</th>
                                            <th>Tanggal Update</th>
                                            <th class="text-center">Bukti</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $hist = $conn->query("SELECT * FROM student_belt_history WHERE student_id = " . $row['id'] . " ORDER BY created_at DESC");
                                        if ($hist->num_rows > 0):
                                            while ($h = $hist->fetch_assoc()):
                                                ?>
                                                <tr>
                                                    <td><?php echo $h['tingkatan_sabuk']; ?></td>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($h['created_at'])); ?></td>
                                                    <td class="text-center">
                                                        <?php if ($h['foto_sertifikat']): ?>
                                                            <a href="../assets/uploads/certificates/<?php echo $h['foto_sertifikat']; ?>"
                                                                target="_blank"
                                                                class="btn btn-sm btn-outline-danger py-0 px-2 rounded-pill">
                                                                <i class="bi bi-file-earmark-pdf"></i> Lihat
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php
                                            endwhile;
                                        else:
                                            ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-3">Belum ada riwayat kenaikan
                                                    sabuk.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Dojang History -->
                        <div class="tab-pane fade" id="dojang-content-<?php echo $row['id']; ?>" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm small table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Dojang Lama</th>
                                            <th>Dojang Baru</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $d_hist = $conn->query("SELECT h.*, d1.nama_dojang as old_dojang, d2.nama_dojang as new_dojang 
                                                     FROM student_dojang_history h 
                                                     LEFT JOIN dojangs d1 ON h.old_dojang_id = d1.id 
                                                     LEFT JOIN dojangs d2 ON h.new_dojang_id = d2.id 
                                                     WHERE h.student_id = " . $row['id'] . " ORDER BY h.change_date DESC");
                                        if ($d_hist->num_rows > 0):
                                            while ($dh = $d_hist->fetch_assoc()):
                                                ?>
                                                <tr>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($dh['change_date'])); ?></td>
                                                    <td><?php echo $dh['old_dojang'] ? $dh['old_dojang'] : '<span class="text-muted">-</span>'; ?>
                                                    </td>
                                                    <td><?php echo $dh['new_dojang'] ? $dh['new_dojang'] : '<span class="text-muted">-</span>'; ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-3">Belum ada riwayat
                                                    perpindahan.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <div class="modal fade" id="editStudentModal<?php echo $row['id']; ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                        <div class="alert alert-soft-primary p-2 mb-3 rounded-3 d-flex align-items-center small">
                            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                            <div>
                                Pastikan data yang dimasukkan sudah benar.
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold small text-muted">Dojang (Tempat Latihan)</label>
                                <div class="input-group">
                                    <select name="dojang_id" class="form-select border-start-0 ps-0 select2-dojang" required
                                        id="select2-dojang-<?php echo $row['id']; ?>">
                                        <?php foreach ($all_dojangs as $d): ?>
                                            <option value="<?php echo $d['id']; ?>" <?php echo ($d['id'] == $id_dojang) ? 'selected' : ''; ?>>
                                                <?php echo $d['nama_dojang']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-text text-danger small fst-italic mt-1"><i
                                        class="bi bi-exclamation-triangle"></i> Jika Dojang diubah, siswa akan dipindahkan
                                    dari daftar ini.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control"
                                    value="<?php echo $row['nama_lengkap']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Sabuk</label>
                                <select name="tingkatan_sabuk" class="form-select" required>
                                    <?php foreach ($belts as $b): ?>
                                        <option value="<?php echo $b; ?>" <?php echo ($row['tingkatan_sabuk'] == $b) ? 'selected' : ''; ?>>
                                            <?php echo $b; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Tempat Lahir</label>
                                <input type="text" name="tempat_lahir" class="form-control"
                                    value="<?php echo $row['tempat_lahir']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" class="form-control"
                                    value="<?php echo $row['tanggal_lahir']; ?>" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold small text-muted">Update Sertifikat (Opsional)</label>
                                <input type="file" name="foto_sertifikat" class="form-control">
                                <small class="text-muted d-block mt-1">Biarkan kosong jika tidak ingin mengubah
                                    sertifikat.</small>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold small text-muted">Alamat Domisili</label>
                                <textarea name="alamat_domisili" class="form-control" rows="2"
                                    required><?php echo $row['alamat_domisili']; ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_student" class="btn btn-primary rounded-pill px-4">Simpan
                            Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endwhile; ?>

<?php require_once '../includes/footer.php'; ?>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    // Initialize Select2 for all dojang dropdowns
    $(document).ready(function () {
        // We need to initialize Select2 when the modal is shown to handle visibility correctly
        $('[id^="editStudentModal"]').on('shown.bs.modal', function () {
            var modalId = $(this).attr('id');
            var studentId = modalId.replace('editStudentModal', '');

            $('#select2-dojang-' + studentId).select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#' + modalId),
                width: '100%'
            });
        });
    });
</script>

<script>
    function confirmDelete(event, url) {
        event.preventDefault();
        Swal.fire({
            title: 'Hapus Siswa?',
            text: "Data siswa akan dipindahkan ke Sampah.",
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

    function toggleStatus(id, currentStatus) {
        // Send AJAX request
        fetch('ajax_toggle_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: id,
                status: currentStatus
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update Badge and Icon based on new status
                    const badge = document.getElementById(`status-badge-${id}`);
                    const icon = document.getElementById(`status-icon-${id}`);

                    if (data.new_status === 'aktif') {
                        badge.classList.remove('bg-secondary');
                        badge.classList.add('bg-success');
                        badge.setAttribute('title', 'Klik untuk menonaktifkan');
                        badge.setAttribute('onclick', `toggleStatus(${id}, 'aktif')`);

                        icon.classList.remove('bi-eye-slash');
                        icon.classList.add('bi-eye');
                    } else {
                        badge.classList.remove('bg-success');
                        badge.classList.add('bg-secondary');
                        badge.setAttribute('title', 'Klik untuk mengaktifkan');
                        badge.setAttribute('onclick', `toggleStatus(${id}, 'tidak aktif')`);

                        icon.classList.remove('bi-eye');
                        icon.classList.add('bi-eye-slash');
                    }

                    // Show localized Toast or Swal
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan sistem'
                });
            });
    }
</script>
<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}


// FUNCTIONS
function generateUsername($name)
{
    // Basic username generator: lowercase, remove spaces, add random number
    $name = strtolower(str_replace(' ', '', $name));
    return $name . rand(100, 999);
}

// HANDLERS

// 1. Add Student (Create User + Student)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    $nama = $conn->real_escape_string($_POST['nama_lengkap']);
    $ttl_place = $_POST['tempat_lahir'];
    $ttl_date = $_POST['tanggal_lahir'];
    $dojang = !empty($_POST['dojang_id']) ? "'" . $_POST['dojang_id'] . "'" : "NULL";
    $sabuk = $_POST['tingkatan_sabuk'];
    $alamat = $_POST['alamat_domisili'];
    $status = $_POST['status'];

    // Create User first
    $username = generateUsername($nama);
    $password = password_hash('123456', PASSWORD_DEFAULT); // Default password
    $role = 'siswa';

    // Ensure username unique
    while ($conn->query("SELECT id FROM users WHERE username='$username'")->num_rows > 0) {
        $username = generateUsername($nama);
    }

    $conn->begin_transaction();
    try {
        $conn->query("INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')");
        $user_id = $conn->insert_id;

        $sql_student = "INSERT INTO students (user_id, dojang_id, nama_lengkap, tempat_lahir, tanggal_lahir, tingkatan_sabuk, alamat_domisili, status) 
                        VALUES ($user_id, $dojang, '$nama', '$ttl_place', '$ttl_date', '$sabuk', '$alamat', '$status')";
        $conn->query($sql_student);

        $conn->commit();
        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text'] = "Siswa berhasil ditambahkan! Username: $username";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_title'] = 'Gagal!';
        $_SESSION['swal_text'] = $e->getMessage();
    }
    header("Location: students.php");
    exit();
}

// 2. Edit Student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_student'])) {
    $id = intval($_POST['id']);
    // Get current dojang for history
    $curr_s = $conn->query("SELECT dojang_id FROM students WHERE id=$id")->fetch_assoc();
    $old_dojang_id = $curr_s['dojang_id'];

    $nama = $conn->real_escape_string($_POST['nama_lengkap']);
    $ttl_place = $_POST['tempat_lahir'];
    $ttl_date = $_POST['tanggal_lahir'];
    $dojang = !empty($_POST['dojang_id']) ? $_POST['dojang_id'] : "NULL";
    $sabuk = $_POST['tingkatan_sabuk'];
    $alamat = $_POST['alamat_domisili'];
    $status = $_POST['status'];

    $dojang_val = ($dojang === "NULL") ? "NULL" : "'$dojang'";

    $sql = "UPDATE students SET 
            nama_lengkap='$nama', tempat_lahir='$ttl_place', tanggal_lahir='$ttl_date', 
            dojang_id=$dojang_val, tingkatan_sabuk='$sabuk', alamat_domisili='$alamat', 
            status='$status' 
            WHERE id=$id";

    if ($conn->query($sql)) {
        // Record history if dojang changed
        if ($old_dojang_id != $dojang && $dojang !== "NULL") {
            // Handle NULL old_dojang_id carefully for SQL
            $old_val = $old_dojang_id ? "'$old_dojang_id'" : "NULL";
            $new_val = "'$dojang'";

            // Only insert if both are valid IDs or handle logic as needed. 
            // Simplest: only track if moving between actual dojangs or from one to another.
            // If moving to NULL (no dojang), we might want to track that too? 
            // The table structure for student_dojang_history columns might expect INT. 
            // Let's assume for now we only track valid ID changes for history or allow NULL if schema permits.
            // Checking schema: student_dojang_history columns usually int.
            // If schema allows NULL, great. If not, we might skip history for NULL.
            // Assuming history columns allow NULL based on context or we skip.
            // Let's safe check:
            if ($dojang !== "NULL") {
                $h_sql = "INSERT INTO student_dojang_history (student_id, old_dojang_id, new_dojang_id) VALUES ($id, $old_val, $new_val)";
                $conn->query($h_sql);
            }
        }

        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text'] = 'Data siswa berhasil diupdate!';
    } else {
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_title'] = 'Error!';
        $_SESSION['swal_text'] = $conn->error;
    }
    header("Location: students.php");
    exit();
}

// 3. Delete Student
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Get user_id first to delete linked user
    $get_user = $conn->query("SELECT user_id FROM students WHERE id=$id")->fetch_assoc();
    $user_id = $get_user['user_id'];

    $conn->begin_transaction();
    try {
        $conn->query("UPDATE students SET is_deleted = 1, deleted_at = NOW() WHERE id=$id");
        $conn->query("UPDATE users SET is_deleted = 1 WHERE id=$user_id");
        $conn->commit();
        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text'] = 'Data siswa berhasil dipindahkan ke Sampah.';
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_title'] = 'Gagal!';
        $_SESSION['swal_text'] = $e->getMessage();
    }
    header("Location: students.php");
    exit();
}

// DATA FETCHING
// Pagination & Search Logic
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$limit = 5;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$where = "";
if (!empty($search)) {
    // Show only active (not deleted) and MUST have a Dojang
    $where = "WHERE (s.nama_lengkap LIKE '%$search%' OR u.username LIKE '%$search%') AND s.is_deleted = 0 AND s.dojang_id IS NOT NULL";
} else {
    $where = "WHERE s.is_deleted = 0 AND s.dojang_id IS NOT NULL";
}

// Count Total
$total_sql = "SELECT count(*) as total FROM students s JOIN users u ON s.user_id = u.id $where";
$total_result = $conn->query($total_sql);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch Data
$students = $conn->query("SELECT s.*, d.nama_dojang, u.username FROM students s 
                          JOIN dojangs d ON s.dojang_id = d.id 
                          JOIN users u ON s.user_id = u.id 
                          $where
                          ORDER BY s.nama_lengkap ASC LIMIT $limit OFFSET $offset");
$dojangs = $conn->query("SELECT * FROM dojangs");
$dojang_options = [];
while ($d = $dojangs->fetch_assoc())
    $dojang_options[] = $d;

require_once '../includes/header.php';
require_once '../includes/navbar.php';

?>

<div class="container py-5">
    <div class="glass-card px-4 py-3 mb-4 d-flex justify-content-between align-items-center">
        <h3 class="fw-bold mb-0"><i class="bi bi-people text-primary"></i> Data Siswa</h3>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control rounded-pill me-2" placeholder="Cari Siswa..."
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="btn btn-primary rounded-pill"><i class="bi bi-search"></i></button>
            </form>
            <a href="students_trash.php" class="btn btn-outline-danger rounded-pill shadow-sm">
                <i class="bi bi-trash-fill me-1"></i> Sampah
            </a>
            <button class="btn btn-primary rounded-pill shadow-sm" data-bs-toggle="modal"
                data-bs-target="#addStudentModal">
                <i class="bi bi-person-plus-fill me-1"></i> Tambah Siswa
            </button>
        </div>
    </div>


    <div class="glass-card p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Dojang</th>
                        <th>Sabuk</th>
                        <th>TTL</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Calculate starting number
                    // $limit is 5
                    $no = $offset + 1;
                    while ($row = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><span class="badge bg-secondary"><?php echo $row['username']; ?></span></td>
                            <td class="fw-bold"><?php echo $row['nama_lengkap']; ?></td>
                            <td><?php echo $row['nama_dojang']; ?></td>
                            <td><span class="badge bg-info text-dark"><?php echo $row['tingkatan_sabuk']; ?></span></td>
                            <td><?php echo $row['tempat_lahir'] . ', ' . $row['tanggal_lahir']; ?></td>
                            <td>
                                <?php if ($row['status'] == 'aktif'): ?>
                                    <span class="badge bg-success" title="Aktif"><i class="bi bi-eye"></i></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary" title="Tidak Aktif"><i class="bi bi-eye-slash"></i></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-info rounded-pill me-1" data-bs-toggle="modal"
                                    data-bs-target="#detailStudentModal<?php echo $row['id']; ?>">
                                    <i class="bi bi-info-circle"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning rounded-pill me-1" data-bs-toggle="modal"
                                    data-bs-target="#editStudentModal<?php echo $row['id']; ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <a href="students.php?delete=<?php echo $row['id']; ?>"
                                    class="btn btn-sm btn-outline-danger rounded-pill"
                                    onclick="confirmDelete(event, this.href)">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages >= 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link"
                                href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>">Previous</a></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>"><a class="page-link"
                                href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>"><?php echo $i; ?></a></li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item"><a class="page-link"
                                href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Modals (Outside Table) -->
<?php
$students->data_seek(0);
while ($row = $students->fetch_assoc()):
    ?>
    <div class="modal fade" id="editStudentModal<?php echo $row['id']; ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Siswa: <?php echo $row['nama_lengkap']; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control"
                                    value="<?php echo $row['nama_lengkap']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Dojang</label>
                                <select name="dojang_id" class="form-select" required>
                                    <?php foreach ($dojang_options as $d): ?>
                                        <option value="<?php echo $d['id']; ?>" <?php echo ($d['id'] == $row['dojang_id']) ? 'selected' : ''; ?>>
                                            <?php echo $d['nama_dojang']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tempat Lahir</label>
                                <input type="text" name="tempat_lahir" class="form-control"
                                    value="<?php echo $row['tempat_lahir']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" class="form-control"
                                    value="<?php echo $row['tanggal_lahir']; ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tingkatan Sabuk</label>
                            <select name="tingkatan_sabuk" class="form-select" required>
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
                                    $sel = ($row['tingkatan_sabuk'] == $b) ? 'selected' : '';
                                    echo "<option value='$b' $sel>$b</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat Domisili</label>
                            <textarea name="alamat_domisili" class="form-control" rows="2"
                                required><?php echo $row['alamat_domisili']; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status Keanggotaan</label>
                            <select name="status" class="form-select" required>
                                <option value="aktif" <?php echo ($row['status'] == 'aktif') ? 'selected' : ''; ?>>Aktif
                                </option>
                                <option value="tidak aktif" <?php echo ($row['status'] == 'tidak aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_student" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endwhile; ?>

<!-- Detail Modals -->
<?php
$students->data_seek(0);
while ($row = $students->fetch_assoc()):
    ?>
    <div class="modal fade" id="detailStudentModal<?php echo $row['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Detail Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="35%" class="text-muted">Nama Lengkap</td>
                            <td width="5%">:</td>
                            <td class="fw-bold"><?php echo $row['nama_lengkap']; ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Username</td>
                            <td>:</td>
                            <td><?php echo $row['username']; ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>:</td>
                            <td>
                                <?php if ($row['status'] == 'aktif'): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tempat, Tanggal Lahir</td>
                            <td>:</td>
                            <td><?php echo $row['tempat_lahir'] . ', ' . date('d F Y', strtotime($row['tanggal_lahir'])); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Dojang</td>
                            <td>:</td>
                            <td><?php echo $row['nama_dojang']; ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tingkatan Sabuk</td>
                            <td>:</td>
                            <td><span class="badge bg-primary"><?php echo $row['tingkatan_sabuk']; ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Alamat</td>
                            <td>:</td>
                            <td><?php echo $row['alamat_domisili']; ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Bergabung Sejak</td>
                            <td>:</td>
                            <td><?php echo isset($row['created_at']) ? date('d F Y H:i', strtotime($row['created_at'])) : '-'; ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="px-3 pb-3">
                    <h6 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-clock-history text-primary"></i> Riwayat
                        Perpindahan Dojang</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover small">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Dojang Lama</th>
                                    <th>Dojang Baru</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $hist_sql = "SELECT h.*, d1.nama_dojang as old_dojang, d2.nama_dojang as new_dojang 
                                             FROM student_dojang_history h 
                                             LEFT JOIN dojangs d1 ON h.old_dojang_id = d1.id 
                                             LEFT JOIN dojangs d2 ON h.new_dojang_id = d2.id 
                                             WHERE h.student_id = " . $row['id'] . " ORDER BY h.change_date DESC";
                                $hist_res = $conn->query($hist_sql);
                                if ($hist_res && $hist_res->num_rows > 0):
                                    while ($hist = $hist_res->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($hist['change_date'])); ?></td>
                                            <td><?php echo $hist['old_dojang'] ? $hist['old_dojang'] : '<span class="text-muted">-</span>'; ?>
                                            </td>
                                            <td><?php echo $hist['new_dojang'] ? $hist['new_dojang'] : '<span class="text-muted">-</span>'; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Belum ada riwayat perpindahan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
<?php endwhile; ?>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Siswa Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle-fill"></i> Akun Login akan dibuat otomatis. <br>
                        Username: <b>[nama_tanpa_spasi][angka_acak]</b> (Ex: budi123) <br>
                        Password Default: <b>123456</b>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Dojang</label>
                            <select name="dojang_id" class="form-select" required>
                                <option value="">-- Pilih Dojang --</option>
                                <?php foreach ($dojang_options as $d): ?>
                                    <option value="<?php echo $d['id']; ?>"><?php echo $d['nama_dojang']; ?></option>
                                <?php endforeach; ?>
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
                                echo "<option value='$b'>$b</option>";
                            }
                            ?>
                        </select>
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

<?php require_once '../includes/footer.php'; ?>

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
</script>
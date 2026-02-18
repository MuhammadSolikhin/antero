<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// HANDLERS
// 1. Restore Student
if (isset($_GET['restore'])) {
    $id = intval($_GET['restore']);
    $conn->begin_transaction();
    try {
        $conn->query("UPDATE students SET is_deleted = 0, deleted_at = NULL WHERE id=$id");
        $uid = $conn->query("SELECT user_id FROM students WHERE id=$id")->fetch_assoc()['user_id'];
        $conn->query("UPDATE users SET is_deleted = 0 WHERE id=$uid");

        $conn->commit();
        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text'] = 'Data siswa berhasil dipulihkan.';
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_title'] = 'Gagal!';
        $_SESSION['swal_text'] = $e->getMessage();
    }
    header("Location: students_trash.php");
    exit();
}

// 2. Permanent Delete
if (isset($_GET['delete_permanent'])) {
    $id = intval($_GET['delete_permanent']);
    $conn->begin_transaction();
    try {
        $student = $conn->query("SELECT user_id, foto_sertifikat FROM students WHERE id=$id")->fetch_assoc();

        // Delete Certificate File
        if ($student['foto_sertifikat']) {
            $file_path = "../assets/uploads/certificates/" . $student['foto_sertifikat'];
            if (file_exists($file_path))
                unlink($file_path);
        }

        // Delete User and Student (Cascade should handle student, but let's be explicit or rely on FK)
        $conn->query("DELETE FROM users WHERE id=" . $student['user_id']);

        $conn->commit();
        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text'] = 'Data siswa dihapus permanen.';
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_title'] = 'Gagal!';
        $_SESSION['swal_text'] = $e->getMessage();
    }
    header("Location: students_trash.php");
    exit();
}

// 3. Assign Dojang (For students with NULL Dojang)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_dojang'])) {
    $id = intval($_POST['id']);
    $dojang_id = $_POST['dojang_id'];

    if (!empty($dojang_id)) {
        // Assign dojang AND ensure they are not deleted (just in case)
        $conn->query("UPDATE students SET dojang_id = '$dojang_id', is_deleted = 0 WHERE id = $id");
        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text'] = 'Siswa berhasil ditempatkan di Dojang.';
    }
    header("Location: students_trash.php");
    exit();
}


// DATA FETCHING
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// MODIFIED where clause: Deleted OR No Dojang
$where = "WHERE (s.is_deleted = 1 OR s.dojang_id IS NULL)";
if (!empty($search)) {
    $where .= " AND (s.nama_lengkap LIKE '%$search%' OR u.username LIKE '%$search%')";
}

// Count
$total_sql = "SELECT count(*) as total FROM students s JOIN users u ON s.user_id = u.id $where";
$total_result = $conn->query($total_sql);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch - Use LEFT JOIN dojangs because dojang_id might be NULL
$students = $conn->query("SELECT s.*, d.nama_dojang, u.username FROM students s 
                          LEFT JOIN dojangs d ON s.dojang_id = d.id 
                          JOIN users u ON s.user_id = u.id 
                          $where
                          ORDER BY s.deleted_at DESC, s.nama_lengkap ASC LIMIT $limit OFFSET $offset");

// Fetch dojangs for modal
$dojangs = $conn->query("SELECT * FROM dojangs");
$dojang_options = [];
while ($d = $dojangs->fetch_assoc())
    $dojang_options[] = $d;

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="glass-card px-4 py-3 mb-4 d-flex justify-content-between align-items-center">
        <h3 class="fw-bold mb-0 text-danger"><i class="bi bi-trash-fill"></i> Sampah Siswa</h3>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control rounded-pill me-2" placeholder="Cari Siswa..."
                    value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-danger rounded-pill"><i class="bi bi-search"></i></button>
            </form>
            <a href="students.php" class="btn btn-outline-primary rounded-pill shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="glass-card p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Lengkap</th>
                        <th>Dojang Terakhir</th>
                        <th>Alasan Masuk Sampah</th>
                        <th>Tgl Dihapus</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = $offset + 1;
                    if ($students->num_rows > 0):
                        while ($row = $students->fetch_assoc()):
                            $reason = "";
                            if ($row['is_deleted'] == 1) {
                                $reason = '<span class="badge bg-danger">Dihapus</span>';
                            } elseif (is_null($row['dojang_id'])) {
                                $reason = '<span class="badge bg-warning text-dark">Tanpa Dojang</span>';
                            }
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td class="fw-bold"><?php echo $row['nama_lengkap']; ?> <br> <small
                                        class="text-muted"><?php echo $row['username']; ?></small></td>
                                <td><?php echo $row['nama_dojang'] ? $row['nama_dojang'] : '<span class="text-muted">-</span>'; ?>
                                </td>
                                <td><?php echo $reason; ?></td>
                                <td>
                                    <?php
                                    if ($row['deleted_at']) {
                                        echo '<span class="badge bg-secondary">' . date('d/m/Y H:i', strtotime($row['deleted_at'])) . '</span>';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if (is_null($row['dojang_id'])): ?>
                                        <!-- If No Dojang, show "Assign Dojang" button -->
                                        <button class="btn btn-sm btn-success rounded-pill me-1" data-bs-toggle="modal"
                                            data-bs-target="#assignDojangModal<?php echo $row['id']; ?>" title="Pilih Dojang">
                                            <i class="bi bi-box-arrow-in-right"></i> Tempatkan
                                        </button>
                                    <?php else: ?>
                                        <!-- If Deleted, show Restore -->
                                        <a href="students_trash.php?restore=<?php echo $row['id']; ?>"
                                            class="btn btn-sm btn-success rounded-pill me-1"
                                            onclick="confirmRestore(event, this.href)" title="Pulihkan">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </a>
                                    <?php endif; ?>

                                    <a href="students_trash.php?delete_permanent=<?php echo $row['id']; ?>"
                                        class="btn btn-sm btn-outline-danger rounded-pill"
                                        onclick="confirmPermanent(event, this.href)" title="Hapus Permanen">
                                        <i class="bi bi-x-circle"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Sampah kosong.</td>
                        </tr>
                    <?php endif; ?>
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

<!-- Assign Dojang Modals -->
<?php
$students->data_seek(0);
while ($row = $students->fetch_assoc()):
    if (is_null($row['dojang_id'])):
        ?>
        <div class="modal fade" id="assignDojangModal<?php echo $row['id']; ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Pilih Dojang untuk <?php echo $row['nama_lengkap']; ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <div class="mb-3">
                                <label class="form-label">Dojang Tujuan</label>
                                <select name="dojang_id" class="form-select" required>
                                    <option value="">-- Pilih Dojang --</option>
                                    <?php foreach ($dojang_options as $d): ?>
                                        <option value="<?php echo $d['id']; ?>"><?php echo $d['nama_dojang']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="assign_dojang" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php
    endif;
endwhile;
?>

<?php require_once '../includes/footer.php'; ?>

<script>
    function confirmRestore(event, url) {
        event.preventDefault();
        Swal.fire({
            title: 'Pulihkan Siswa?',
            text: "Siswa akan kembali ke daftar aktif.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Pulihkan!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }

    function confirmPermanent(event, url) {
        event.preventDefault();
        Swal.fire({
            title: 'Hapus Permanen?',
            text: "Data tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus Permanen!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }
</script>
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
    $get_user = $conn->query("SELECT user_id FROM students WHERE id=$id")->fetch_assoc();
    $user_id = $get_user['user_id'];

    $conn->begin_transaction();
    try {
        $conn->query("UPDATE students SET is_deleted = 0, deleted_at = NULL WHERE id=$id");
        $conn->query("UPDATE users SET is_deleted = 0 WHERE id=$user_id");
        $conn->commit();
        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text'] = 'Data siswa berhasil dipulihkan.';
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_text'] = $e->getMessage();
    }
    header("Location: students_trash.php");
    exit();
}

// 2. Permanent Delete
if (isset($_GET['force_delete'])) {
    $id = intval($_GET['force_delete']);
    // Get info for cleanup
    $info = $conn->query("SELECT user_id, foto_sertifikat FROM students WHERE id=$id")->fetch_assoc();
    $user_id = $info['user_id'];
    $cert_file = $info['foto_sertifikat'];

    $conn->begin_transaction();
    try {
        $conn->query("DELETE FROM students WHERE id=$id");
        $conn->query("DELETE FROM users WHERE id=$user_id");
        $conn->commit();

        // Remove File
        if ($cert_file && file_exists("../assets/uploads/certificates/" . $cert_file)) {
            unlink("../assets/uploads/certificates/" . $cert_file);
        }

        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Terhapus!';
        $_SESSION['swal_text'] = 'Data siswa dihapus permanen.';
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_text'] = $e->getMessage();
    }
    header("Location: students_trash.php");
    exit();
}

// DATA FETCHING
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$where = "WHERE s.is_deleted = 1";
if (!empty($search)) {
    $where .= " AND (s.nama_lengkap LIKE '%$search%' OR u.username LIKE '%$search%')";
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
                          ORDER BY s.deleted_at DESC LIMIT $limit OFFSET $offset");

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="glass-card px-4 py-3 mb-4 d-flex justify-content-between align-items-center">
        <div>
            <a href="students.php" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i>
                Kembali</a>
            <h3 class="fw-bold mb-0 mt-1"><i class="bi bi-trash text-danger"></i> Sampah (Siswa Dihapus)</h3>
        </div>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control rounded-pill me-2" placeholder="Cari..."
                    value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary rounded-pill"><i class="bi bi-search"></i></button>
            </form>
            <a href="export_trash_students.php" class="btn btn-success rounded-pill shadow-sm">
                <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel
            </a>
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
                        <th>Dojang Terakhir</th>
                        <th>Tgl Dihapus</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($students->num_rows > 0): ?>
                        <?php
                        $no = $offset + 1;
                        while ($row = $students->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php echo $no++; ?>
                                </td>
                                <td><span class="badge bg-secondary">
                                        <?php echo $row['username']; ?>
                                    </span></td>
                                <td class="fw-bold">
                                    <?php echo $row['nama_lengkap']; ?>
                                </td>
                                <td>
                                    <?php echo $row['nama_dojang']; ?>
                                </td>
                                <td><span class="badge bg-danger bg-opacity-10 text-danger">
                                        <?php echo date('d/m/Y H:i', strtotime($row['deleted_at'])); ?>
                                    </span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info rounded-pill me-1" data-bs-toggle="modal"
                                        data-bs-target="#detailStudentModal<?php echo $row['id']; ?>">
                                        <i class="bi bi-info-circle"></i> Detail
                                    </button>
                                    <a href="students_trash.php?restore=<?php echo $row['id']; ?>"
                                        class="btn btn-sm btn-outline-success rounded-pill me-1"
                                        onclick="return confirm('Apakah Anda yakin ingin memulihkan siswa ini?')">
                                        <i class="bi bi-arrow-counterclockwise"></i> Pulihkan
                                    </a>
                                    <a href="students_trash.php?force_delete=<?php echo $row['id']; ?>"
                                        class="btn btn-sm btn-outline-danger rounded-pill"
                                        onclick="confirmPermanentDelete(event, this.href)">
                                        <i class="bi bi-x-circle"></i> Hapus Permanen
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Tidak ada data siswa di sampah.</td>
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
                                href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>">
                                <?php echo $i; ?>
                            </a></li>
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

<!-- Detail Modals (Copied from students.php) -->
<?php
$students->data_seek(0);
while ($row = $students->fetch_assoc()):
    ?>
    <div class="modal fade" id="detailStudentModal<?php echo $row['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Detail Siswa (Arsip)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="35%" class="text-muted">Nama Lengkap</td>
                            <td width="5%">:</td>
                            <td class="fw-bold">
                                <?php echo $row['nama_lengkap']; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Username</td>
                            <td>:</td>
                            <td>
                                <?php echo $row['username']; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tempat, Tanggal Lahir</td>
                            <td>:</td>
                            <td>
                                <?php echo $row['tempat_lahir'] . ', ' . date('d F Y', strtotime($row['tanggal_lahir'])); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Dojang</td>
                            <td>:</td>
                            <td>
                                <?php echo $row['nama_dojang']; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tingkatan Sabuk</td>
                            <td>:</td>
                            <td><span class="badge bg-primary">
                                    <?php echo $row['tingkatan_sabuk']; ?>
                                </span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Alamat</td>
                            <td>:</td>
                            <td>
                                <?php echo $row['alamat_domisili']; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Dihapus Pada</td>
                            <td>:</td>
                            <td class="text-danger fw-bold">
                                <?php echo date('d F Y H:i', strtotime($row['deleted_at'])); ?>
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
                                            <td>
                                                <?php echo date('d/m/Y H:i', strtotime($hist['change_date'])); ?>
                                            </td>
                                            <td>
                                                <?php echo $hist['old_dojang'] ? $hist['old_dojang'] : '<span class="text-muted">-</span>'; ?>
                                            </td>
                                            <td>
                                                <?php echo $hist['new_dojang'] ? $hist['new_dojang'] : '<span class="text-muted">-</span>'; ?>
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
                    <a href="students_trash.php?restore=<?php echo $row['id']; ?>" class="btn btn-success">Pulihkan</a>
                </div>
            </div>
        </div>
    </div>
<?php endwhile; ?>

<?php require_once '../includes/footer.php'; ?>

<script>
    function confirmPermanentDelete(event, url) {
        event.preventDefault();
        Swal.fire({
            title: 'Hapus Permanen?',
            text: "Data ini tidak akan bisa dikembalikan lagi!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus Permanen!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }
</script>
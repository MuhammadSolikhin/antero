<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';

// Handlers for Delete (Optional, good to have)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $get_file = $conn->query("SELECT file_sertifikat FROM achievements WHERE id=$id")->fetch_assoc();
    if ($get_file) {
        $file_path = "../assets/uploads/" . $get_file['file_sertifikat'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        $conn->query("DELETE FROM achievements WHERE id=$id");
        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text'] = 'Data prestasi berhasil dihapus.';
    }
    header("Location: achievements.php");
    exit();
}

// Pagination & Search
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$where = "";
if (!empty($search)) {
    $where = "WHERE s.nama_lengkap LIKE '%$search%' OR a.nama_kejuaraan LIKE '%$search%' OR d.nama_dojang LIKE '%$search%'";
}

// Count Total
$total_sql = "SELECT count(*) as total FROM achievements a 
              JOIN students s ON a.student_id = s.id 
              JOIN dojangs d ON s.dojang_id = d.id 
              $where";
$total_result = $conn->query($total_sql);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch Data
$sql = "SELECT a.*, s.nama_lengkap, d.nama_dojang 
        FROM achievements a 
        JOIN students s ON a.student_id = s.id 
        JOIN dojangs d ON s.dojang_id = d.id 
        $where 
        ORDER BY a.created_at DESC 
        LIMIT $limit OFFSET $offset";
$achievements = $conn->query($sql);

?>

<div class="container py-5">
    <div class="glass-card px-4 py-3 mb-4 d-flex justify-content-between align-items-center">
        <h3 class="fw-bold mb-0"><i class="bi bi-trophy-fill text-primary"></i> Data Prestasi Siswa</h3>
        <form method="GET" class="d-flex">
            <input type="text" name="search" class="form-control rounded-pill me-2"
                placeholder="Cari Siswa/Kejuaraan..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary rounded-pill"><i class="bi bi-search"></i></button>
        </form>
    </div>

    <div class="glass-card p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Siswa</th>
                        <th>Dojang</th>
                        <th>Kejuaraan</th>
                        <th>Tingkat</th>
                        <th>Juara</th>
                        <th>Status</th>
                        <th>Bukti</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = $offset + 1;
                    if ($achievements->num_rows > 0):
                        while ($row = $achievements->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td class="fw-bold"><?php echo $row['nama_lengkap']; ?></td>
                                <td><?php echo $row['nama_dojang']; ?></td>
                                <td>
                                    <?php echo $row['nama_kejuaraan']; ?> <br>
                                    <small class="text-muted"><?php echo $row['championship_year']; ?></small>
                                </td>
                                <td><?php echo $row['tingkat']; ?></td>
                                <td><span class="badge bg-warning text-dark">Juara <?php echo $row['juara_ke']; ?></span></td>
                                <td>
                                    <?php
                                    $badges = [
                                        'pending' => 'bg-warning text-dark',
                                        'approved' => 'bg-success',
                                        'rejected' => 'bg-danger'
                                    ];
                                    $labels = [
                                        'pending' => 'Menunggu',
                                        'approved' => 'Valid',
                                        'rejected' => 'Ditolak'
                                    ];
                                    ?>
                                    <span
                                        class="badge <?php echo $badges[$row['status']]; ?>"><?php echo $labels[$row['status']]; ?></span>
                                </td>
                                <td>
                                    <a href="../assets/uploads/<?php echo $row['file_sertifikat']; ?>" target="_blank"
                                        class="btn btn-sm btn-outline-info rounded-pill"><i class="bi bi-eye"></i> Lihat</a>
                                </td>
                                <td>
                                    <a href="achievements.php?delete=<?php echo $row['id']; ?>"
                                        class="btn btn-sm btn-outline-danger rounded-pill"
                                        onclick="return confirm('Hapus data prestasi ini?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile;
                    else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">Belum ada data prestasi.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
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

<?php require_once '../includes/footer.php'; ?>
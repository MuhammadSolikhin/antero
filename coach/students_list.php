<?php
session_start();
require_once '../config/database.php';

// Cek autentikasi dan role pelatih
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pelatih') {
    header("Location: ../login.php");
    exit();
}

// DATA FETCHING (Read-Only)
// Pagination & Search Logic
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$where = "";
if (!empty($search)) {
    // Show only active (not deleted) and MUST have a Dojang
    $where = "WHERE (s.nama_lengkap LIKE '%$search%' OR d.nama_dojang LIKE '%$search%' OR s.tingkatan_sabuk LIKE '%$search%') AND s.is_deleted = 0 AND s.dojang_id IS NOT NULL";
} else {
    $where = "WHERE s.is_deleted = 0 AND s.dojang_id IS NOT NULL";
}

// Count Total
$total_sql = "SELECT count(*) as total FROM students s JOIN dojangs d ON s.dojang_id = d.id $where";
$total_result = $conn->query($total_sql);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch Data - Include foto_sertifikat
$students = $conn->query("SELECT s.*, d.nama_dojang, u.username 
                          FROM students s 
                          JOIN dojangs d ON s.dojang_id = d.id 
                          JOIN users u ON s.user_id = u.id 
                          $where
                          ORDER BY s.nama_lengkap ASC LIMIT $limit OFFSET $offset");

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<!-- Pastikan Lightbox CSS di-include di header jika belum ada, atau di sini untuk modal image preview -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" integrity="sha512-ZKX+BvQihRJPA8CROKBhDNvoc2aDMOdAlcm7TUQY+35XYtrd3yh95QOOhsPDQY9QnKE0Wqag9y38OIgEvb88cA==" crossorigin="anonymous" referrerpolicy="no-referrer" />


<div class="container py-5">
    <div class="glass-card px-4 py-3 mb-4 d-flex justify-content-between align-items-center">
        <h3 class="fw-bold mb-0"><i class="bi bi-list-ul text-primary"></i> Data Seluruh Siswa</h3>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control rounded-pill me-2" placeholder="Cari Nama/Dojang/Sabuk..."
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="btn btn-primary rounded-pill"><i class="bi bi-search"></i></button>
            </form>
        </div>
    </div>

    <div class="glass-card p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th width="25%">Nama Lengkap</th>
                        <th width="20%">Dojang</th>
                        <th width="15%">Sabuk</th>
                        <th width="15%">Status</th>
                        <th width="20%">Aksi / Dokumen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = $offset + 1;
                    $students_data = []; // Store data in an array for reuse
                    if ($students->num_rows > 0):
                        while ($row = $students->fetch_assoc()): 
                            $students_data[] = $row; // Save to array
                    ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <span class="fw-bold"><?php echo htmlspecialchars($row['nama_lengkap']); ?></span>
                                    <small class="d-block text-muted"><?php echo htmlspecialchars($row['tempat_lahir']) . ', ' . date('d M Y', strtotime($row['tanggal_lahir'])); ?></small>
                                </td>
                                <td><i class="bi bi-building"></i> <?php echo htmlspecialchars($row['nama_dojang']); ?></td>
                                <td><span class="badge bg-dark fw-normal"><i class="bi bi-award"></i> <?php echo htmlspecialchars($row['tingkatan_sabuk']); ?></span></td>
                                <td>
                                    <?php if ($row['status'] == 'aktif'): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3">Tidak Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <!-- Action Buttons -->
                                    <button class="btn btn-sm btn-outline-primary rounded-pill me-1 mb-1 shadow-sm" data-bs-toggle="modal"
                                        data-bs-target="#detailStudentModal<?php echo $row['id']; ?>" title="Detail Siswa">
                                        <i class="bi bi-person-lines-fill"></i> Info
                                    </button>
                                    
                                    <?php if ($row['foto_sertifikat']): ?>
                                        <a href="../assets/uploads/certificates/<?php echo $row['foto_sertifikat']; ?>" target="_blank" class="btn btn-sm btn-outline-info rounded-pill shadow-sm mb-1" title="Lihat Sertifikat Sabuk">
                                            <i class="bi bi-file-earmark-text"></i> Sertifikat
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-secondary rounded-pill shadow-sm mb-1" disabled title="Belum ada sertifikat yang diupload">
                                            <i class="bi bi-file-earmark-x"></i> -
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; 
                    else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Data siswa tidak ditemukan.</td>
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
                        <li class="page-item"><a class="page-link rounded-start-pill"
                                href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">Previous</a></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>"><a class="page-link"
                                href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a></li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item"><a class="page-link rounded-end-pill"
                                href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Detail Modals Rendered Outside -->
<?php
if (!empty($students_data)):
    foreach ($students_data as $row):
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
                            <td class="fw-bold"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Username</td>
                            <td>:</td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
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
                            <td><?php echo htmlspecialchars($row['tempat_lahir']) . ', ' . date('d F Y', strtotime($row['tanggal_lahir'])); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Dojang</td>
                            <td>:</td>
                            <td><?php echo htmlspecialchars($row['nama_dojang']); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tingkatan Sabuk</td>
                            <td>:</td>
                            <td><span class="badge bg-primary"><?php echo htmlspecialchars($row['tingkatan_sabuk']); ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Alamat</td>
                            <td>:</td>
                            <td><?php echo nl2br(htmlspecialchars($row['alamat_domisili'])); ?></td>
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
                    <h6 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-clock-history text-primary"></i> Riwayat Perpindahan Dojang</h6>
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
                                            <td><?php echo $hist['old_dojang'] ? htmlspecialchars($hist['old_dojang']) : '<span class="text-muted">-</span>'; ?>
                                            </td>
                                            <td><?php echo $hist['new_dojang'] ? htmlspecialchars($hist['new_dojang']) : '<span class="text-muted">-</span>'; ?>
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

                <!-- History Sabuk Section -->
                <div class="px-3 pb-3">
                    <h6 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-award-fill text-primary"></i> Dokumen Sertifikat Sabuk</h6>
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3 mb-md-0 text-center">
                            <span class="d-block text-muted mb-1">Sabuk Saat Ini</span>
                            <span class="badge bg-dark fs-6 px-3 py-2"><?php echo htmlspecialchars($row['tingkatan_sabuk']); ?></span>
                        </div>
                        <div class="col-md-6 text-center border-start">
                            <span class="d-block text-muted mb-2">Preview Sertifikat</span>
                            <?php if ($row['foto_sertifikat']): 
                                $ext = strtolower(pathinfo($row['foto_sertifikat'], PATHINFO_EXTENSION));
                                if ($ext == 'pdf'):
                            ?>
                                <div class="bg-light rounded p-3 text-center border">
                                    <i class="bi bi-file-earmark-pdf text-danger mb-2 d-block" style="font-size: 2.5rem;"></i>
                                    <a href="../assets/uploads/certificates/<?php echo $row['foto_sertifikat']; ?>" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill">
                                        Lihat PDF
                                    </a>
                                </div>
                            <?php else: ?>
                                <a href="../assets/uploads/certificates/<?php echo $row['foto_sertifikat']; ?>" data-lightbox="sertifikat-<?php echo $row['id']; ?>" data-title="Sertifikat Sabuk <?php echo htmlspecialchars($row['nama_lengkap']); ?>">
                                    <img src="../assets/uploads/certificates/<?php echo $row['foto_sertifikat']; ?>" class="img-thumbnail shadow-sm" style="max-height: 120px; object-fit: cover;" alt="Sertifikat">
                                </a>
                                <small class="d-block mt-1 text-muted"><i class="bi bi-zoom-in"></i> Klik untuk perbesar</small>
                            <?php endif; ?>
                            <?php else: ?>
                                <div class="bg-light rounded p-3 text-muted">
                                    <i class="bi bi-image" style="font-size: 2rem;"></i>
                                    <span class="d-block mt-1 small">Belum ada sertifikat</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
<?php 
    endforeach;
endif; 
?>

<?php require_once '../includes/footer.php'; ?>

<!-- Include Lightbox JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js" integrity="sha512-Iwx/O1ORqO804369kUUgQzR2t57IuGkX0BqbG0R9wK1bY0O/M1Z2r00+hP7Lz/9P5y7QfB/vD7K1/vD7K1/vA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

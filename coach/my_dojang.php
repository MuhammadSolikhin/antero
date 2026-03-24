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
$coach_query = $conn->query("SELECT id, nama_pelatih FROM coaches WHERE user_id = $user_id LIMIT 1");
if ($coach_query->num_rows == 0) {
    $coach_id = false;
} else {
    $coach_data = $coach_query->fetch_assoc();
    $coach_id = $coach_data['id'];
}

// Ambil Dojang yang dilatih oleh Pelatih ini
$dojangs = [];
if ($coach_id) {
    $dojang_query = $conn->query("SELECT * FROM dojangs WHERE coach_id = $coach_id ORDER BY nama_dojang ASC");
    while ($d = $dojang_query->fetch_assoc()) {
        $dojangs[] = $d;
    }
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<!-- Pastikan Lightbox CSS di-include di header jika belum ada -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" integrity="sha512-ZKX+BvQihRJPA8CROKBhDNvoc2aDMOdAlcm7TUQY+35XYtrd3yh95QOOhsPDQY9QnKE0Wqag9y38OIgEvb88cA==" crossorigin="anonymous" referrerpolicy="no-referrer" />


<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="glass-card px-4 py-3 d-flex justify-content-between align-items-center">
                <h3 class="fw-bold mb-0"><i class="bi bi-people-fill text-primary"></i> Dojang Saya</h3>
                <span class="badge bg-primary rounded-pill px-3 py-2 text-white shadow-sm">
                    <i class="bi bi-geo-alt-fill"></i> <?php echo count($dojangs); ?> Dojang
                </span>
            </div>
        </div>
    </div>

    <div class="row">
        <?php if (!$coach_id): ?>
            <div class="col-12">
                <div class="glass-card p-5 text-center">
                    <i class="bi bi-person-exclamation display-1 text-muted mb-3"></i>
                    <h4>Profil Belum Terhubung</h4>
                    <p class="text-muted">Akun Anda belum dikaitkan dengan profil Pelatih manapun. <br>Harap minta Administrator untuk menautkan akun Anda.</p>
                </div>
            </div>
        <?php elseif (count($dojangs) == 0): ?>
            <div class="col-12">
                <div class="glass-card p-5 text-center">
                    <i class="bi bi-building-slash display-1 text-muted mb-3"></i>
                    <h4>Belum Ada Dojang</strong></h4>
                    <p class="text-muted">Anda saat ini belum terdaftar sebagai pelatih utama di dojang manapun. <br>Hanya Administrator yang dapat mengatur plot pelatih dojang.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="col-md-12 accordion" id="dojangAccordion">
                <?php 
                $all_students = [];
                foreach ($dojangs as $index => $dojang): 
                    $d_id = $dojang['id'];
                    // Query untuk mengambil murid yang terdaftar di dojang ini dan masih aktif (is_deleted=0)
                    $student_query = $conn->query("
                        SELECT s.*, u.username FROM students s 
                        JOIN users u ON s.user_id = u.id
                        WHERE s.dojang_id = $d_id AND s.is_deleted = 0 
                        ORDER BY s.tingkatan_sabuk DESC, s.nama_lengkap ASC
                    ");
                    $student_count = $student_query->num_rows;
                ?>
                <div class="accordion-item glass-card mb-3 border-0 overflow-hidden">
                    <h2 class="accordion-header" id="heading<?php echo $d_id; ?>">
                        <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?> fw-bold bg-transparent py-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $d_id; ?>" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $d_id; ?>">
                            <div class="d-flex w-100 justify-content-between align-items-center me-3">
                                <div>
                                    <i class="bi bi-building fw-bold text-primary me-2"></i> 
                                    <span class="fs-5"><?php echo htmlspecialchars($dojang['nama_dojang']); ?></span>
                                    <small class="d-block text-muted mt-1 fw-normal"><i class="bi bi-pin-map-fill"></i> <?php echo htmlspecialchars($dojang['alamat']); ?></small>
                                </div>
                                <span class="badge bg-success rounded-pill px-3 py-2"><?php echo $student_count; ?> Siswa Aktif</span>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse<?php echo $d_id; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $d_id; ?>" data-bs-parent="#dojangAccordion">
                        <div class="accordion-body border-top p-4 bg-light bg-opacity-50">
                            <?php if ($student_count == 0): ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-people fw-lighter display-5 text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Belum ada siswa yang mendaftar di Dojang ini.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive bg-white rounded-3 shadow-sm p-3">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%" class="text-center">No</th>
                                                <th width="30%">Nama Siswa</th>
                                                <th width="20%">Tingkatan Sabuk</th>
                                                <th width="15%">Status</th>
                                                <th width="30%">Aksi / Dokumen</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $no = 1;
                                            while ($s = $student_query->fetch_assoc()): 
                                                $s['nama_dojang'] = $dojang['nama_dojang'];
                                                $all_students[] = $s;
                                            ?>
                                            <tr>
                                                <td class="text-center"><?php echo $no++; ?></td>
                                                <td>
                                                    <span class="fw-bold d-block text-dark"><?php echo htmlspecialchars($s['nama_lengkap']); ?></span>
                                                    <small class="text-muted"><?php echo htmlspecialchars($s['tempat_lahir']) . ', ' . date('d M Y', strtotime($s['tanggal_lahir'])); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-dark fw-normal px-2 py-1"><i class="bi bi-award"></i> <?php echo htmlspecialchars($s['tingkatan_sabuk']); ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($s['status'] == 'aktif'): ?>
                                                        <span class="badge text-success bg-success bg-opacity-10 border border-success border-opacity-25 rounded-pill px-3"><i class="bi bi-circle-fill small me-1" style="font-size:0.5rem;"></i> Aktif</span>
                                                    <?php else: ?>
                                                        <span class="badge text-secondary bg-secondary bg-opacity-10 border border-secondary border-opacity-25 rounded-pill px-3"><i class="bi bi-circle-fill small me-1" style="font-size:0.5rem;"></i> Tidak Aktif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <!-- Action Buttons -->
                                                    <button class="btn btn-sm btn-outline-primary rounded-pill me-1 mb-1 shadow-sm" data-bs-toggle="modal"
                                                        data-bs-target="#detailStudentModal<?php echo $s['id']; ?>" title="Detail Siswa">
                                                        <i class="bi bi-person-lines-fill"></i> Info
                                                    </button>
                                                    
                                                    <?php if ($s['foto_sertifikat']): ?>
                                                        <a href="../assets/uploads/certificates/<?php echo $s['foto_sertifikat']; ?>" target="_blank" class="btn btn-sm btn-outline-info rounded-pill shadow-sm mb-1" title="Lihat Sertifikat Sabuk">
                                                            <i class="bi bi-file-earmark-text"></i> Sertifikat
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-outline-secondary rounded-pill shadow-sm mb-1" disabled title="Belum ada sertifikat yang diupload">
                                                            <i class="bi bi-file-earmark-x"></i> -
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

</div>

<!-- Detail Modals Rendered Outside -->
<?php
if (!empty($all_students)):
    foreach ($all_students as $row):
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
                                <a href="../assets/uploads/certificates/<?php echo $row['foto_sertifikat']; ?>" data-lightbox="sertifikat-dojang-<?php echo $row['id']; ?>" data-title="Sertifikat Sabuk <?php echo htmlspecialchars($row['nama_lengkap']); ?>">
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

<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle Verification / Rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $id = intval($_POST['id']);
        $action = $_POST['action'];
        $note = isset($_POST['admin_note']) ? $conn->real_escape_string($_POST['admin_note']) : null;

        $new_status = ($action == 'approve') ? 'verified' : 'rejected';

        $sql = "UPDATE student_trainings SET status='$new_status', admin_note='$note' WHERE id=$id";
        if ($conn->query($sql)) {
            $_SESSION['swal_icon'] = 'success';
            $_SESSION['swal_title'] = 'Berhasil';
            $_SESSION['swal_text'] = 'Status pelatihan diperbarui.';
        } else {
            $_SESSION['swal_icon'] = 'error';
            $_SESSION['swal_title'] = 'Gagal';
            $_SESSION['swal_text'] = 'Database Error: ' . $conn->error;
        }
        header("Location: verify_trainings.php");
        exit();
    }
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="glass-card p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold m-0 text-primary">Verifikasi Diklat</h2>
                    <p class="text-muted m-0">Validasi data pelatihan/diklat siswa</p>
                </div>
                <a href="export_trainings.php" class="btn btn-success rounded-pill px-4">
                    <i class="bi bi-file-earmark-excel me-2"></i> Export Excel
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <ul class="nav nav-tabs mb-4 px-3 border-0" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 me-2 bg-white shadow-sm" id="pending-tab" data-bs-toggle="tab"
                data-bs-target="#pending" type="button" role="tab" aria-selected="true">
                <i class="bi bi-hourglass-split me-2"></i> Menunggu
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 bg-white shadow-sm" id="all-tab" data-bs-toggle="tab" data-bs-target="#all"
                type="button" role="tab" aria-selected="false">
                <i class="bi bi-list-ul me-2"></i> Semua Data
            </button>
        </li>
    </ul>

    <div class="tab-content" id="myTabContent">
        <!-- Pending Tab -->
        <div class="tab-pane fade show active" id="pending" role="tabpanel">
            <div class="glass-card p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Siswa</th>
                                <th>Pelatihan</th>
                                <th>Tahun</th>
                                <th>Sertifikat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT st.*, s.nama_lengkap, d.nama_dojang 
                                    FROM student_trainings st 
                                    JOIN students s ON st.student_id = s.id 
                                    JOIN dojangs d ON s.dojang_id = d.id
                                    WHERE st.status = 'pending' 
                                    ORDER BY st.created_at ASC";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0):
                                while ($row = $result->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold">
                                                <?php echo $row['nama_lengkap']; ?>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo $row['nama_dojang']; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php echo $row['name']; ?>
                                        </td>
                                        <td>
                                            <?php echo $row['year']; ?>
                                        </td>
                                        <td>
                                            <a href="../assets/uploads/student_certificates/<?php echo $row['certificate_file']; ?>"
                                                target="_blank" class="btn btn-sm btn-outline-info rounded-pill">
                                                <i class="bi bi-eye me-1"></i> Cek File
                                            </a>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-success rounded-pill me-1"
                                                onclick="verifyTraining(<?php echo $row['id']; ?>, 'approve')">
                                                <i class="bi bi-check-lg"></i> Terima
                                            </button>
                                            <button class="btn btn-sm btn-danger rounded-pill"
                                                onclick="rejectTraining(<?php echo $row['id']; ?>)">
                                                <i class="bi bi-x-lg"></i> Tolak
                                            </button>
                                        </td>
                                    </tr>
                                <?php
                                endwhile;
                            else:
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Tidak ada data yang perlu
                                        diverifikasi.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- All Data Tab -->
        <div class="tab-pane fade" id="all" role="tabpanel">
            <div class="glass-card p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle datatable">
                        <thead class="table-light">
                            <tr>
                                <th>Siswa</th>
                                <th>Pelatihan</th>
                                <th>Tahun</th>
                                <th>Status</th>
                                <th>Tgl Submit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql_all = "SELECT st.*, s.nama_lengkap 
                                        FROM student_trainings st 
                                        JOIN students s ON st.student_id = s.id 
                                        ORDER BY st.created_at DESC LIMIT 100";
                            $res_all = $conn->query($sql_all);
                            while ($r = $res_all->fetch_assoc()):
                                ?>
                                <tr>
                                    <td>
                                        <?php echo $r['nama_lengkap']; ?>
                                    </td>
                                    <td>
                                        <?php echo $r['name']; ?>
                                    </td>
                                    <td>
                                        <?php echo $r['year']; ?>
                                    </td>
                                    <td>
                                        <?php if ($r['status'] == 'verified'): ?>
                                            <span class="badge bg-success">Verified</span>
                                        <?php elseif ($r['status'] == 'rejected'): ?>
                                            <span class="badge bg-danger">Rejected</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($r['created_at'])); ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Alasan Penolakan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="rejectId">
                    <input type="hidden" name="action" value="reject">
                    <textarea name="admin_note" class="form-control" rows="3" placeholder="Tulis alasan penolakan..."
                        required></textarea>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden Approve Form -->
<form method="POST" id="approveForm" style="display:none;">
    <input type="hidden" name="id" id="approveId">
    <input type="hidden" name="action" value="approve">
</form>

<script>
    function verifyTraining(id, action) {
        if (action === 'approve') {
            Swal.fire({
                title: 'Konfirmasi',
                text: "Terima data pelatihan ini?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                confirmButtonText: 'Ya, Terima'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('approveId').value = id;
                    document.getElementById('approveForm').submit();
                }
            });
        }
    }

    function rejectTraining(id) {
        document.getElementById('rejectId').value = id;
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    }
</script>

<?php require_once '../includes/footer.php'; ?>
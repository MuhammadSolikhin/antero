<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}



// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id']);
    $status = '';

    if ($action == 'approve') {
        $status = 'approved';
        $msg = 'Prestasi berhasil diverifikasi dan disetujui.';
    } elseif ($action == 'reject') {
        $status = 'rejected';
        $msg = 'Prestasi telah ditolak.';
    }

    if ($status) {
        if ($conn->query("UPDATE achievements SET status='$status' WHERE id=$id")) {
            $_SESSION['swal_icon'] = 'success';
            $_SESSION['swal_title'] = 'Berhasil!';
            $_SESSION['swal_text'] = $msg;
        } else {
            $_SESSION['swal_icon'] = 'error';
            $_SESSION['swal_title'] = 'Gagal!';
            $_SESSION['swal_text'] = $conn->error;
        }
        header("Location: verify.php");
        exit();
    }
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';

// Fetch Pending
$pending = $conn->query("SELECT a.*, s.nama_lengkap, d.nama_dojang 
                         FROM achievements a 
                         JOIN students s ON a.student_id = s.id 
                         JOIN dojangs d ON s.dojang_id = d.id 
                         WHERE a.status = 'pending'
                         ORDER BY a.created_at ASC");
?>

<div class="container py-5">
    <div class="glass-card px-4 py-3 mb-4">
        <h3 class="fw-bold mb-0">Verifikasi Prestasi Siswa</h3>
    </div>

    <?php if ($pending->num_rows == 0): ?>
        <div class="alert alert-success text-center p-5">
            <i class="bi bi-check-all display-1"></i>
            <h4 class="mt-3">Semua Aman!</h4>
            <p>Tidak ada data pending saat ini.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive glass-card p-3">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Tgl Input</th>
                        <th>Siswa</th>
                        <th>Dojang</th>
                        <th>Kejuaraan</th>
                        <th>Tingkat</th>
                        <th>Juara</th>
                        <th>Bukti</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $pending->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('d/m/y H:i', strtotime($row['created_at'])); ?></td>
                            <td class="fw-bold"><?php echo $row['nama_lengkap']; ?></td>
                            <td><?php echo $row['nama_dojang']; ?></td>
                            <td><?php echo $row['nama_kejuaraan']; ?></td>
                            <td><?php echo $row['tingkat']; ?></td>
                            <td><span class="badge bg-warning text-dark"><?php echo $row['juara_ke']; ?></span></td>
                            <td>
                                <a href="../assets/uploads/<?php echo $row['file_sertifikat']; ?>" target="_blank"
                                    class="btn btn-sm btn-info rounded-pill text-white">
                                    <i class="bi bi-file-earmark-image"></i> Cek
                                </a>
                            </td>
                            <td>
                                <a href="verify.php?action=approve&id=<?php echo $row['id']; ?>"
                                    class="btn btn-success btn-sm rounded-pill"
                                    onclick="confirmAction(event, this.href, 'approve')"><i class="bi bi-check-lg"></i>
                                    Terima</a>
                                <a href="verify.php?action=reject&id=<?php echo $row['id']; ?>"
                                    class="btn btn-danger btn-sm rounded-pill"
                                    onclick="confirmAction(event, this.href, 'reject')"><i class="bi bi-x-lg"></i> Tolak</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>

<script>
    function confirmAction(event, url, action) {
        event.preventDefault();
        let title = action === 'approve' ? 'Terima Prestasi?' : 'Tolak Prestasi?';
        let text = action === 'approve' ? 'Data akan diverifikasi sebagai valid.' : 'Data akan ditolak.';
        let icon = action === 'approve' ? 'success' : 'warning';
        let confirmBtn = action === 'approve' ? 'Ya, Terima!' : 'Ya, Tolak!';
        let btnColor = action === 'approve' ? '#198754' : '#dc3545';

        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: btnColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: confirmBtn,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }
</script>
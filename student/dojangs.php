<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';


// Fetch Dojangs
$dojangs = $conn->query("SELECT d.*, c.nama_pelatih FROM dojangs d LEFT JOIN coaches c ON d.coach_id = c.id ORDER BY d.nama_dojang ASC");
?>

<div class="container py-5">
    <div class="glass-card px-4 py-3 mb-4">
        <h3 class="fw-bold mb-0"><i class="bi bi-building text-primary"></i> Informasi Dojang</h3>
        <p class="text-muted mb-0">Daftar tempat latihan (Dojang) yang terdaftar.</p>
    </div>

    <div class="row g-4">
        <?php if ($dojangs->num_rows > 0): ?>
            <?php while ($row = $dojangs->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm glass-card">
                        <!-- Standard Image Placeholder if needed, but using map/icon for now -->
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-3"><?php echo $row['nama_dojang']; ?></h5>

                            <p class="card-text text-muted mb-4">
                                <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                                <?php echo $row['alamat']; ?>
                            </p>

                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <div>
                                    <?php if ($row['nama_pelatih']): ?>
                                        <small class="text-muted d-block mb-2">Pelatih:</small>
                                        <span class="badge bg-light text-dark border p-2"><i
                                                class="bi bi-person-badge-fill text-primary"></i>
                                            <?php echo $row['nama_pelatih']; ?></span>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($row['google_maps'])): ?>
                                    <a href="<?php echo $row['google_maps']; ?>" target="_blank"
                                        class="btn btn-sm btn-primary rounded-pill">
                                        <i class="bi bi-map-fill me-1"></i> Lihat Lokasi
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">Belum ada data Dojang.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
<?php
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/navbar.php';

$coaches = $conn->query("SELECT * FROM coaches ORDER BY nama_pelatih ASC");
?>

<div class="container py-5">
    <div class="glass-card mb-5 p-4 text-center">
        <h2 class="fw-bold text-primary mb-0">Daftar Pelatih Kami</h2>
        <p class="text-muted">Mengenal lebih dekat para pelatih profesional kami</p>
    </div>

    <div class="row g-4">
        <?php while ($row = $coaches->fetch_assoc()): ?>
            <div class="col-md-3">
                <div class="glass-card h-100 p-0 overflow-hidden shadow-sm hover-shadow bg-white">
                    <img src="<?php echo $row['foto_pelatih'] ? '../assets/uploads/' . $row['foto_pelatih'] : 'https://ui-avatars.com/api/?name=' . $row['nama_pelatih'] . '&background=random&size=200'; ?>"
                        class="w-100" style="height: 250px; object-fit: cover;" alt="Foto Pelatih">
                    <div class="p-4 text-center">
                        <h5 class="fw-bold mb-1"><?php echo $row['nama_pelatih']; ?></h5>
                        <p class="badge bg-primary rounded-pill mb-3"><?php echo $row['tingkatan']; ?></p>
                        <button class="btn btn-outline-primary btn-sm rounded-pill w-100" data-bs-toggle="modal"
                            data-bs-target="#coachModal<?php echo $row['id']; ?>">Lihat Detail Profil</button>
                    </div>
                </div>

                <!-- Modal Detail -->
                <div class="modal fade" id="coachModal<?php echo $row['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header border-0 pb-0">
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center mb-4">
                                    <img src="<?php echo $row['foto_pelatih'] ? '../assets/uploads/' . $row['foto_pelatih'] : 'https://ui-avatars.com/api/?name=' . $row['nama_pelatih'] . '&background=random&size=200'; ?>"
                                        class="rounded-circle shadow mb-3" width="250" height="250"
                                        style="object-fit: cover;">
                                    <h3 class="fw-bold"><?php echo $row['nama_pelatih']; ?></h3>
                                    <span class="badge bg-primary fs-6"><?php echo $row['tingkatan']; ?></span>
                                </div>

                                <div class="row g-4">

                                    <div class="col-md-12">
                                        <h6 class="fw-bold text-uppercase text-secondary small mb-3">Riwayat Pelatihan</h6>
                                        <?php
                                        // Fetch Trainings
                                        $c_id = $row['id'];
                                        $trainings = $conn->query("SELECT * FROM coach_trainings WHERE coach_id = $c_id ORDER BY year DESC");
                                        if ($trainings->num_rows > 0):
                                            ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Tahun</th>
                                                            <th>Tingkat</th>
                                                            <th>Deskripsi</th>
                                                            <th>Sertifikat</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php while ($t = $trainings->fetch_assoc()): ?>
                                                            <tr>
                                                                <td><?php echo $t['year']; ?></td>
                                                                <td><?php echo $t['level']; ?></td>
                                                                <td><?php echo $t['description']; ?></td>
                                                                 <td class="text-center">
                                                                    <?php if (!empty($t['certificate_file'])): ?>
                                                                        <a href="../assets/uploads/trainings/<?php echo $t['certificate_file']; ?>" target="_blank" class="btn btn-sm btn-outline-info rounded-pill py-0 px-2">
                                                                            <i class="bi bi-file-earmark-pdf"></i> Lihat
                                                                        </a>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">-</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-muted small fst-italic">Belum ada data pelatihan.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-secondary rounded-pill px-4"
                                    data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
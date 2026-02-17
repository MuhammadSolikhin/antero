<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}


// HANDLE ACTIONS

// 1. Add Coach
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_coach'])) {
    $nama = $_POST['nama_pelatih'];
    $tingkat = $_POST['tingkatan'];

    // Upload Belt Certificate (sertifikat_file)
    $sertif_name = "";
    if (isset($_FILES['sertifikat_file']) && $_FILES['sertifikat_file']['name'] != '') {
        $target_dir_cert = "../assets/uploads/certificates/";
        if (!file_exists($target_dir_cert))
            mkdir($target_dir_cert, 0777, true);

        $sertif_name = time() . '_' . basename($_FILES["sertifikat_file"]["name"]);
        move_uploaded_file($_FILES["sertifikat_file"]["tmp_name"], $target_dir_cert . $sertif_name);
    }

    // Upload Foto
    $foto_name = "";
    if (isset($_FILES['foto_pelatih']) && $_FILES['foto_pelatih']['name'] != '') {
        $target_dir = "../assets/uploads/";
        $foto_name = time() . '_' . basename($_FILES["foto_pelatih"]["name"]);
        move_uploaded_file($_FILES["foto_pelatih"]["tmp_name"], $target_dir . $foto_name);
    }

    $conn->begin_transaction();
    try {
        $riwayat_placeholder = "-";

        $sql = "INSERT INTO coaches (nama_pelatih, riwayat_pelatihan, tingkatan, info_sertifikat, foto_pelatih) 
                VALUES ('$nama', '$riwayat_placeholder', '$tingkat', '$sertif_name', '$foto_name')";
        $conn->query($sql);
        $coach_id = $conn->insert_id;

        // Insert Trainings
        if (isset($_POST['trainings']) && is_array($_POST['trainings'])) {
            foreach ($_POST['trainings'] as $idx => $t) {
                if (!empty($t['year']) && !empty($t['description'])) {
                    $year = $t['year'];
                    $level = $t['level'];
                    $desc = $conn->real_escape_string($t['description']);
                    $file_cert = null;

                    // Handle File Upload for this training index
                    if (isset($_FILES['trainings']['name'][$idx]['file']) && $_FILES['trainings']['name'][$idx]['file'] != '') {
                        $f_name = time() . '_' . basename($_FILES['trainings']['name'][$idx]['file']);
                        $f_tmp = $_FILES['trainings']['tmp_name'][$idx]['file'];
                        $f_target = "../assets/uploads/trainings/" . $f_name;
                        if (move_uploaded_file($f_tmp, $f_target)) {
                            $file_cert = $f_name;
                        }
                    }

                    $stmt = $conn->prepare("INSERT INTO coach_trainings (coach_id, year, level, description, certificate_file) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("iisss", $coach_id, $year, $level, $desc, $file_cert);
                    $stmt->execute();
                }
            }
        }

        $conn->commit();
        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text'] = 'Pelatih berhasil ditambahkan';
        header("Location: master_coach.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_title'] = 'Gagal!';
        $_SESSION['swal_text'] = $e->getMessage();
        header("Location: master_coach.php");
        exit();
    }
}

// 2. Edit Coach
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_coach'])) {
    $id = intval($_POST['id']);
    $nama = $_POST['nama_pelatih'];
    $tingkat = $_POST['tingkatan'];

    // Upload Belt Certificate (sertifikat_file)
    $sertif_query = "";
    if (isset($_FILES['sertifikat_file']) && $_FILES['sertifikat_file']['name'] != '') {
        $target_dir_cert = "../assets/uploads/certificates/";
        if (!file_exists($target_dir_cert))
            mkdir($target_dir_cert, 0777, true);

        $sertif_name = time() . '_' . basename($_FILES["sertifikat_file"]["name"]);
        if (move_uploaded_file($_FILES["sertifikat_file"]["tmp_name"], $target_dir_cert . $sertif_name)) {
            $sertif_query = ", info_sertifikat='$sertif_name'";
        }
    }

    $conn->begin_transaction();
    try {
        $sql = "UPDATE coaches SET nama_pelatih='$nama', tingkatan='$tingkat' $sertif_query WHERE id=$id";
        $conn->query($sql);

        // Update Foto
        if (isset($_FILES['foto_pelatih']) && $_FILES['foto_pelatih']['name'] != '') {
            $target_dir = "../assets/uploads/";
            $foto_name = time() . '_' . basename($_FILES["foto_pelatih"]["name"]);
            if (move_uploaded_file($_FILES["foto_pelatih"]["tmp_name"], $target_dir . $foto_name)) {
                $conn->query("UPDATE coaches SET foto_pelatih='$foto_name' WHERE id=$id");
            }
        }

        // Update Trainings (Delete All + Re-insert)
        $conn->query("DELETE FROM coach_trainings WHERE coach_id=$id");

        if (isset($_POST['trainings']) && is_array($_POST['trainings'])) {
            foreach ($_POST['trainings'] as $idx => $t) {
                if (!empty($t['year']) && !empty($t['description'])) {
                    $year = $t['year'];
                    $level = $t['level'];
                    $desc = $conn->real_escape_string($t['description']);
                    $file_cert = isset($t['existing_file']) ? $t['existing_file'] : null;

                    // Handle File Upload for this training index
                    if (isset($_FILES['trainings']['name'][$idx]['file']) && $_FILES['trainings']['name'][$idx]['file'] != '') {
                        $f_name = time() . '_' . basename($_FILES['trainings']['name'][$idx]['file']);
                        $f_tmp = $_FILES['trainings']['tmp_name'][$idx]['file'];
                        $f_target = "../assets/uploads/trainings/" . $f_name;
                        if (move_uploaded_file($f_tmp, $f_target)) {
                            $file_cert = $f_name;
                        }
                    }

                    $stmt = $conn->prepare("INSERT INTO coach_trainings (coach_id, year, level, description, certificate_file) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("iisss", $id, $year, $level, $desc, $file_cert);
                    $stmt->execute();
                }
            }
        }

        $conn->commit();
        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text'] = 'Pelatih berhasil diupdate';
        header("Location: master_coach.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_title'] = 'Gagal!';
        $_SESSION['swal_text'] = $e->getMessage();
        header("Location: master_coach.php");
        exit();
    }
}

// 3. Delete Coach
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($conn->query("DELETE FROM coaches WHERE id = $id")) {
        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text'] = 'Pelatih berhasil dihapus!';
    } else {
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_title'] = 'Gagal!';
        $_SESSION['swal_text'] = $conn->error;
    }
    header("Location: master_coach.php");
    exit();
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';

// HANDLE ACTIONS



// Pagination & Search Logic
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$limit = 5;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$where = "";
if (!empty($search)) {
    $where = "WHERE nama_pelatih LIKE '%$search%' OR tingkatan LIKE '%$search%'";
}

// Count Total
$total_sql = "SELECT count(*) as total FROM coaches $where";
$total_result = $conn->query($total_sql);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch Data
$coaches = $conn->query("SELECT * FROM coaches $where ORDER BY id DESC LIMIT $limit OFFSET $offset");
?>

<div class="container py-5">
    <div class="row">
        <!-- Add Form -->
        <div class="col-md-12 mb-4">
            <div class="glass-card px-4 py-3 mb-3 d-flex justify-content-between align-items-center">
                <h3 class="fw-bold mb-0"><i class="bi bi-person-badge-fill text-primary"></i> Data Pelatih</h3>
                <div class="d-flex gap-2">
                    <form method="GET" class="d-flex">
                        <input type="text" name="search" class="form-control rounded-pill me-2"
                            placeholder="Cari Pelatih..."
                            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit" class="btn btn-primary rounded-pill"><i class="bi bi-search"></i></button>
                    </form>
                    <button class="btn btn-primary rounded-pill shadow-sm" data-bs-toggle="modal"
                        data-bs-target="#addCoachModal">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Pelatih
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="glass-card p-4">

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Tingkat</th>
                                <th>Sertifikat</th>
                                <th>Foto</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = $offset + 1;
                            while ($row = $coaches->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td class="fw-bold"><?php echo $row['nama_pelatih']; ?></td>
                                    <td><span class="badge bg-info text-dark"><?php echo $row['tingkatan']; ?></span></td>
                                    <td>
                                        <?php if ($row['info_sertifikat']): ?>
                                            <a href="../assets/uploads/certificates/<?php echo $row['info_sertifikat']; ?>"
                                                target="_blank" class="btn btn-sm btn-outline-info rounded-pill"><i
                                                    class="bi bi-file-earmark-text"></i> Lihat</a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['foto_pelatih']): ?>
                                            <img src="../assets/uploads/<?php echo $row['foto_pelatih']; ?>" width="50"
                                                class="rounded-circle shadow-sm">
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-warning rounded-pill me-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editCoachModal<?php echo $row['id']; ?>"><i
                                                class="bi bi-pencil-square"></i></button>
                                        <a href="master_coach.php?delete=<?php echo $row['id']; ?>"
                                            class="btn btn-sm btn-outline-danger rounded-pill"
                                            onclick="confirmDelete(event, this.href)"><i class="bi bi-trash"></i></a>
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
    </div>
</div>

<!-- Modal Add Coach -->
<div class="modal fade" id="addCoachModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Pelatih Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" novalidate
                onsubmit="return validateTrainings(event, this)">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Pelatih</label>
                            <input type="text" name="nama_pelatih" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tingkatan</label>
                            <select name="tingkatan" class="form-control" required>
                                <option value="">-- Pilih Tingkatan --</option>
                                <?php
                                for ($i = 1; $i <= 10; $i++) {
                                    /* Convert to Roman */
                                    $roman = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];
                                    echo "<option value='DAN $roman[$i]'>DAN $roman[$i]</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sertifikat Sabuk</label>
                        <input type="file" name="sertifikat_file" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Foto</label>
                        <input type="file" name="foto_pelatih" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Riwayat Pelatihan</label>
                        <div class="table-responsive bg-light p-3 rounded">
                            <table class="table table-bordered table-sm mb-2" id="trainingTableAdd">
                                <thead>
                                    <tr>
                                        <th width="15%">Tahun</th>
                                        <th width="20%">Tingkat</th>
                                        <th>Nama Pelatihan / Deskripsi</th>
                                        <th>Lampiran</th>
                                        <th width="10%">Hapus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input required type="number" name="trainings[0][year]"
                                                class="form-control form-control-sm" placeholder="2024"></td>
                                        <td>
                                            <select required name="trainings[0][level]"
                                                class="form-select form-select-sm">
                                                <option value="Daerah">Daerah</option>
                                                <option value="Nasional">Nasional</option>
                                                <option value="Internasional">Internasional</option>
                                            </select>
                                        </td>
                                        <td><input required type="text" name="trainings[0][description]"
                                                class="form-control form-control-sm" placeholder="Nama Pelatihan...">
                                        </td>
                                        <td><input type="file" name="trainings[0][file]"
                                                class="form-control form-control-sm"></td>
                                        <td><button type="button" class="btn btn-sm btn-danger remove-row" disabled><i
                                                    class="bi bi-trash"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-sm btn-success"
                                onclick="addTrainingRow('trainingTableAdd')"><i class="bi bi-plus"></i> Tambah
                                Pelatihan</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_coach" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Coach Modals -->
<?php
$coaches->data_seek(0);
while ($row = $coaches->fetch_assoc()):
    // Fetch trainings for this coach
    $trainings = $conn->query("SELECT * FROM coach_trainings WHERE coach_id = " . $row['id'] . " ORDER BY year DESC");
    ?>
    <div class="modal fade" id="editCoachModal<?php echo $row['id']; ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Pelatih</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" novalidate
                    onsubmit="return validateTrainings(event, this)">
                    <div class="modal-body">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                        <!-- Show Current Photo -->
                        <div class="text-center mb-4">
                            <?php if ($row['foto_pelatih']): ?>
                                <img src="../assets/uploads/<?php echo $row['foto_pelatih']; ?>" class="rounded-circle shadow"
                                    width="120" height="120" style="object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center shadow-sm"
                                    style="width: 120px; height: 120px;">
                                    <i class="bi bi-person fs-1 text-secondary"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Pelatih</label>
                                <input type="text" name="nama_pelatih" class="form-control"
                                    value="<?php echo $row['nama_pelatih']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tingkatan</label>
                                <select name="tingkatan" class="form-control" required>
                                    <?php
                                    for ($i = 1; $i <= 10; $i++) {
                                        $roman = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];
                                        $val = "DAN $roman[$i]";
                                        $sel = ($row['tingkatan'] == $val) ? 'selected' : '';
                                        echo "<option value='$val' $sel>$val</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sertifikat Sabuk</label>
                            <?php if ($row['info_sertifikat']): ?>
                                <div class="mb-2">
                                    <a href="../assets/uploads/certificates/<?php echo $row['info_sertifikat']; ?>"
                                        target="_blank" class="btn btn-sm btn-outline-primary"><i
                                            class="bi bi-file-earmark-arrow-down"></i> Lihat File Saat Ini</a>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="sertifikat_file" class="form-control">
                            <small class="text-muted">Upload file baru untuk mengganti.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ganti Foto (Opsional)</label>
                            <input type="file" name="foto_pelatih" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Riwayat Pelatihan</label>
                            <div class="table-responsive bg-light p-3 rounded">
                                <table class="table table-bordered table-sm mb-2"
                                    id="trainingTableEdit<?php echo $row['id']; ?>">
                                    <thead>
                                        <tr>
                                            <th width="15%">Tahun</th>
                                            <th width="20%">Tingkat</th>
                                            <th>Nama Pelatihan / Deskripsi</th>
                                            <th>Lampiran</th>
                                            <th width="10%">Hapus</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $t_idx = 0;
                                        if ($trainings->num_rows > 0):
                                            while ($t = $trainings->fetch_assoc()):
                                                ?>
                                                <tr>
                                                    <td><input required type="number" name="trainings[<?php echo $t_idx; ?>][year]"
                                                            class="form-control form-control-sm" value="<?php echo $t['year']; ?>">
                                                    </td>
                                                    <td>
                                                        <select required name="trainings[<?php echo $t_idx; ?>][level]"
                                                            class="form-select form-select-sm">
                                                            <option value="Daerah" <?php echo ($t['level'] == 'Daerah') ? 'selected' : ''; ?>>Daerah</option>
                                                            <option value="Nasional" <?php echo ($t['level'] == 'Nasional') ? 'selected' : ''; ?>>Nasional</option>
                                                            <option value="Internasional" <?php echo ($t['level'] == 'Internasional') ? 'selected' : ''; ?>>Internasional</option>
                                                        </select>
                                                    </td>
                                                    <td><input required type="text"
                                                            name="trainings[<?php echo $t_idx; ?>][description]"
                                                            class="form-control form-control-sm"
                                                            value="<?php echo $t['description']; ?>"></td>
                                                    <td>
                                                        <?php if ($t['certificate_file']): ?>
                                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                                <a href="../assets/uploads/trainings/<?php echo $t['certificate_file']; ?>" target="_blank" class="badge bg-primary text-decoration-none">
                                                                    <i class="bi bi-eye"></i> Lihat
                                                                </a>
                                                                <input type="hidden" name="trainings[<?php echo $t_idx; ?>][existing_file]" value="<?php echo $t['certificate_file']; ?>">
                                                            </div>
                                                        <?php endif; ?>
                                                        <input type="file" name="trainings[<?php echo $t_idx; ?>][file]"
                                                            class="form-control form-control-sm mt-1">
                                                    </td>
                                                    <td><button type="button" class="btn btn-sm btn-danger"
                                                            onclick="confirmRemoveRow(this)"><i class="bi bi-trash"></i></button>
                                                    </td>
                                                </tr>
                                                <?php
                                                $t_idx++;
                                            endwhile;
                                        else:
                                            ?>
                                            <tr>
                                                <td><input required type="number" name="trainings[0][year]"
                                                        class="form-control form-control-sm" placeholder="2024"></td>
                                                <td>
                                                    <select required name="trainings[0][level]"
                                                        class="form-select form-select-sm">
                                                        <option value="Daerah">Daerah</option>
                                                        <option value="Nasional">Nasional</option>
                                                        <option value="Internasional">Internasional</option>
                                                    </select>
                                                </td>
                                                <td><input required type="text" name="trainings[0][description]"
                                                        class="form-control form-control-sm" placeholder="Nama Pelatihan...">
                                                </td>
                                                <td><input type="file" name="trainings[0][file]"
                                                        class="form-control form-control-sm"></td>
                                                <td><button type="button" class="btn btn-sm btn-danger remove-row"
                                                        onclick="confirmRemoveRow(this)"><i class="bi bi-trash"></i></button>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-sm btn-success"
                                    onclick="addTrainingRow('trainingTableEdit<?php echo $row['id']; ?>')"><i
                                        class="bi bi-plus"></i> Tambah Pelatihan</button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_coach" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endwhile; ?>

<!-- Dynamic Row Script -->
<script>
    function confirmDelete(event, url) {
        event.preventDefault();
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data ini akan dihapus permanen!",
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

    function validateTrainings(event, form) {
        const inputs = form.querySelectorAll('input[name^="trainings"], select[name^="trainings"]');
        let isValid = true;

        inputs.forEach(input => {
            // Skip file inputs and hidden inputs (like existing_file)
            if (input.type === 'file' || input.type === 'hidden') return;

            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
            }
        });

        if (!isValid) {
            event.preventDefault(); // Stop submission
            Swal.fire({
                icon: 'warning',
                title: 'Data Tidak Lengkap',
                text: 'Mohon lengkapi semua kolom data pelatihan (Tahun, Tingkat, & Nama Pelatihan)!',
            });
            return false;
        }
        return true;
    }

    function confirmRemoveRow(btn) {
        Swal.fire({
            title: 'Hapus Baris?',
            text: "Data pelatihan ini akan dihapus dari daftar!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.closest('tr').remove();
            }
        });
    }

    function addTrainingRow(tableId) {
        const table = document.getElementById(tableId).getElementsByTagName('tbody')[0];
        const rowCount = table.rows.length;
        const row = table.insertRow(rowCount);
        // Use timestamp to ensure unique index if rows are deleted/added messily, 
        // or just increment index. For simplicity in PHP array parsing, unique index is better.
        // simpler: just use a large random index or just append [] if PHP supports auto-indexing map, 
        // but we used trainings[index][key].
        // Let's use Date.now() for unique index.
        const idx = Date.now() + Math.floor(Math.random() * 1000);

        row.innerHTML = `
        <td><input required type="number" name="trainings[${idx}][year]" class="form-control form-control-sm" placeholder="2024"></td>
        <td>
            <select required name="trainings[${idx}][level]" class="form-select form-select-sm">
                <option value="Daerah">Daerah</option>
                <option value="Nasional">Nasional</option>
                <option value="Internasional">Internasional</option>
            </select>
        </td>
        <td><input required type="text" name="trainings[${idx}][description]" class="form-control form-control-sm" placeholder="Nama Pelatihan..."></td>
        <td><input type="file" name="trainings[${idx}][file]" class="form-control form-control-sm"></td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="confirmRemoveRow(this)"><i class="bi bi-trash"></i></button></td>
    `;
    }
</script>

<?php require_once '../includes/footer.php'; ?>
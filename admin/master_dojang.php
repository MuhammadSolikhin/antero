<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}


// Add Dojang
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_dojang'])) {
    $nama = $conn->real_escape_string($_POST['nama_dojang']);
    $alamat = $conn->real_escape_string($_POST['alamat']);
    $maps = $conn->real_escape_string($_POST['google_maps']);
    $maps = $conn->real_escape_string($_POST['google_maps']);
    $coach_id = !empty($_POST['coach_id']) ? intval($_POST['coach_id']) : 'NULL';

    if ($conn->query("INSERT INTO dojangs (nama_dojang, alamat, google_maps, coach_id) VALUES ('$nama', '$alamat', '$maps', $coach_id)")) {
        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text'] = 'Dojang berhasil ditambahkan!';
    } else {
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_title'] = 'Gagal!';
        $_SESSION['swal_text'] = $conn->error;
    }
    header("Location: master_dojang.php");
    exit();
}

// Edit Dojang
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_dojang'])) {
    $id = intval($_POST['id']);
    $nama = $conn->real_escape_string($_POST['nama_dojang']);
    $alamat = $conn->real_escape_string($_POST['alamat']);
    $maps = $conn->real_escape_string($_POST['google_maps']);
    $maps = $conn->real_escape_string($_POST['google_maps']);
    $coach_id = !empty($_POST['coach_id']) ? intval($_POST['coach_id']) : 'NULL';

    $sql = "UPDATE dojangs SET 
            nama_dojang='$nama', 
            alamat='$alamat', 
            google_maps='$maps',
            nama_dojang='$nama', 
            alamat='$alamat', 
            google_maps='$maps',
            coach_id=$coach_id
            WHERE id=$id";

    if ($conn->query($sql)) {
        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text'] = 'Data Dojang berhasil diupdate!';
    } else {
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_title'] = 'Gagal!';
        $_SESSION['swal_text'] = $conn->error;
    }
    header("Location: master_dojang.php");
    exit();
}

// Delete Dojang
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Check key constraint usually exists (students linked to dojang)
    // For now simple delete
    if ($conn->query("DELETE FROM dojangs WHERE id = $id")) {
        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text'] = 'Dojang berhasil dihapus!';
    } else {
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_title'] = 'Gagal!';
        $_SESSION['swal_text'] = $conn->error;
    }
    header("Location: master_dojang.php");
    exit();
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';

// Include jQuery and Select2
echo '<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>';
echo '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />';
echo '<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>';
echo '<style>.select2-container { z-index: 9999; } .select2-container .select2-selection--single { height: 38px; line-height: 38px; } .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; } .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }</style>';



// Pagination & Search Logic
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$limit = 5;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$where = "";
if (!empty($search)) {
    $where = "WHERE d.nama_dojang LIKE '%$search%' OR d.alamat LIKE '%$search%'";
}

// Fetch All Coaches for Dropdowns
$all_coaches = [];
$c_res = $conn->query("SELECT id, nama_pelatih FROM coaches ORDER BY nama_pelatih ASC");
while ($c = $c_res->fetch_assoc()) {
    $all_coaches[] = $c;
}

// Count Total for Pagination
$total_sql = "SELECT count(*) as total FROM dojangs d $where";
$total_result = $conn->query($total_sql);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch Data
$dojangs = $conn->query("SELECT d.*, c.nama_pelatih FROM dojangs d LEFT JOIN coaches c ON d.coach_id = c.id $where ORDER BY d.id DESC LIMIT $limit OFFSET $offset");
?>

<div class="container py-5">
    <div class="glass-card px-4 py-3 mb-4 d-flex justify-content-between align-items-center">
        <h3 class="fw-bold mb-0"><i class="bi bi-geo-alt-fill text-primary"></i> Data Dojang</h3>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control rounded-pill me-2" placeholder="Cari Dojang..."
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="btn btn-primary rounded-pill"><i class="bi bi-search"></i></button>
            </form>
            <button class="btn btn-primary rounded-pill shadow-sm" data-bs-toggle="modal"
                data-bs-target="#addDojangModal">
                <i class="bi bi-plus-lg me-1"></i> Tambah
            </button>
        </div>
    </div>

    <div class="glass-card p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Dojang</th>
                        <th>Alamat</th>
                        <th>Maps</th>
                        <th>Pelatih</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = $offset + 1;
                    while ($row = $dojangs->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td class="fw-bold"><?php echo $row['nama_dojang']; ?></td>
                            <td style="max-width: 300px;"><?php echo $row['alamat']; ?></td>
                            <td>
                                <?php if ($row['google_maps']): ?>
                                    <a href="<?php echo $row['google_maps']; ?>" target="_blank"
                                        class="btn btn-sm btn-outline-primary rounded-pill"><i class="bi bi-geo-alt-fill"></i>
                                        Maps</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['nama_pelatih']): ?>
                                    <span class="badge bg-info text-dark"><?php echo $row['nama_pelatih']; ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap">
                                <a href="dojang_detail.php?id=<?php echo $row['id']; ?>"
                                    class="btn btn-sm btn-outline-primary rounded-pill me-1"><i class="bi bi-eye"></i>
                                    Detail</a>
                                <button class="btn btn-sm btn-outline-warning rounded-pill me-1" data-bs-toggle="modal"
                                    data-bs-target="#editDojangModal<?php echo $row['id']; ?>"><i
                                        class="bi bi-pencil-square"></i></button>
                                <a href="master_dojang.php?delete=<?php echo $row['id']; ?>"
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

<!-- Add Modal -->
<div class="modal fade" id="addDojangModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Dojang Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Dojang</label>
                        <input type="text" name="nama_dojang" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Link Maps / Koordinat</label>
                        <input type="text" name="google_maps" class="form-control"
                            placeholder="https://maps.google.com/...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pelatih</label>
                        <select name="coach_id" class="form-select select2-init" style="width: 100%;">
                            <option value="">-- Pilih Pelatih --</option>
                            <?php foreach ($all_coaches as $coach): ?>
                                <option value="<?php echo $coach['id']; ?>"><?php echo $coach['nama_pelatih']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_dojang" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modals -->
<?php
$dojangs->data_seek(0);
while ($row = $dojangs->fetch_assoc()):
    ?>
    <div class="modal fade" id="editDojangModal<?php echo $row['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Dojang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <div class="mb-3">
                            <label>Nama Dojang</label>
                            <input type="text" name="nama_dojang" class="form-control"
                                value="<?php echo $row['nama_dojang']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>Alamat</label>
                            <textarea name="alamat" class="form-control" rows="3"><?php echo $row['alamat']; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Link Maps / Koordinat</label>
                            <input type="text" name="google_maps" class="form-control"
                                value="<?php echo $row['google_maps']; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pelatih</label>
                            <select name="coach_id" class="form-select select2-init" style="width: 100%;">
                                <option value="">-- Pilih Pelatih --</option>
                                <?php foreach ($all_coaches as $coach): ?>
                                    <option value="<?php echo $coach['id']; ?>" <?php echo ($row['coach_id'] == $coach['id']) ? 'selected' : ''; ?>>
                                        <?php echo $coach['nama_pelatih']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_dojang" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endwhile; ?>

<?php require_once '../includes/footer.php'; ?>

<script>
    $(document).ready(function () {
        // Initialize Select2 on all elements with class 'select2-init'
        // Loop through each to set the correct dropdownParent
        $('.select2-init').each(function () {
            $(this).select2({
                dropdownParent: $(this).closest('.modal'),
                width: '100%',
                placeholder: "-- Pilih Pelatih --",
                allowClear: true
            });
        });
    });

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
</script>
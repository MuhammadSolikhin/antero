<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// 1. Add User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if username exists
    $check = $conn->query("SELECT id FROM users WHERE username = '$username'");
    if ($check->num_rows > 0) {
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_title'] = 'Gagal!';
        $_SESSION['swal_text'] = 'Username sudah terdaftar!';
    } else {
        $sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password', '$role')";
        if ($conn->query($sql)) {
            $_SESSION['swal_icon'] = 'success';
            $_SESSION['swal_title'] = 'Berhasil!';
            $_SESSION['swal_text'] = 'User berhasil ditambahkan!';
        } else {
            $_SESSION['swal_icon'] = 'error';
            $_SESSION['swal_title'] = 'Error!';
            $_SESSION['swal_text'] = $conn->error;
        }
    }
    header("Location: users.php");
    exit();
}

// 2. Edit User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_user'])) {
    $id = intval($_POST['id']);
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $role = $_POST['role'];

    // Check if username taken by another user
    $check = $conn->query("SELECT id FROM users WHERE username = '$username' AND id != $id");
    if ($check->num_rows > 0) {
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_title'] = 'Gagal!';
        $_SESSION['swal_text'] = 'Username sudah digunakan user lain!';
    } else {
        $sql = "UPDATE users SET username='$username', email='$email', role='$role' WHERE id=$id";
        $conn->query($sql);

        // Update password if filled
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password='$password' WHERE id=$id");
        }
        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text'] = 'User berhasil diupdate!';
    }
    header("Location: users.php");
    exit();
}

// 3. Delete User
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Prevent delete self
    if ($id == $_SESSION['user_id']) {
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_title'] = 'Gagal!';
        $_SESSION['swal_text'] = 'Tidak dapat menghapus akun sendiri!';
    } else {
        $conn->query("DELETE FROM users WHERE id = $id");
        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text'] = 'User berhasil dihapus!';
    }
    header("Location: users.php");
    exit();
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';

// List Users
// Pagination & Search Logic
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$limit = 5;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$where = "";
if (!empty($search)) {
    $where = "WHERE username LIKE '%$search%' OR email LIKE '%$search%' OR role LIKE '%$search%'";
}

// Count Total
$total_sql = "SELECT count(*) as total FROM users $where";
$total_result = $conn->query($total_sql);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch Data
$users = $conn->query("SELECT * FROM users $where ORDER BY role ASC, username ASC LIMIT $limit OFFSET $offset");
?>

<div class="container py-5">
    <div class="glass-card px-4 py-3 mb-4 d-flex justify-content-between align-items-center">
        <h3 class="fw-bold mb-0"><i class="bi bi-people-fill text-primary"></i> Kelola User</h3>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control rounded-pill me-2" placeholder="Cari User..."
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="btn btn-primary rounded-pill"><i class="bi bi-search"></i></button>
            </form>
            <button class="btn btn-primary rounded-pill shadow-sm" data-bs-toggle="modal"
                data-bs-target="#addUserModal">
                <i class="bi bi-plus-lg me-1"></i> Tambah User
            </button>
        </div>
    </div>



    <div class="glass-card p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = $offset + 1;
                    while ($u = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td class="fw-bold"><?php echo $u['username']; ?></td>
                            <td><?php echo $u['email']; ?></td>
                            <td>
                                <?php if ($u['role'] == 'admin'): ?>
                                    <span class="badge bg-danger">ADMIN</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">SISWA</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-warning rounded-pill me-1" data-bs-toggle="modal"
                                    data-bs-target="#editUserModal<?php echo $u['id']; ?>">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <a href="users.php?delete=<?php echo $u['id']; ?>"
                                        class="btn btn-sm btn-outline-danger rounded-pill"
                                        onclick="confirmDelete(event, this.href)">
                                        <i class="bi bi-trash"></i> Hapus
                                    </a>
                                <?php endif; ?>
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
                    <li class="page-item <?php if ($page <= 1) {
                        echo 'disabled';
                    } ?>">
                        <a class="page-link"
                            href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php if ($page == $i) {
                            echo 'active';
                        } ?>">
                            <a class="page-link"
                                href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php if ($page >= $total_pages) {
                        echo 'disabled';
                    } ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Generate Edit User Modals -->
<?php
$users->data_seek(0); // Reset pointer
while ($u = $users->fetch_assoc()):
    ?>
    <div class="modal fade" id="editUserModal<?php echo $u['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" value="<?php echo $u['username']; ?>"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo $u['email']; ?>"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="siswa" <?php echo ($u['role'] == 'siswa') ? 'selected' : ''; ?>>Siswa</option>
                                <option value="admin" <?php echo ($u['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">New Password (Kosongkan jika tidak ubah)</label>
                            <input type="password" name="password" class="form-control" placeholder="******">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_user" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endwhile; ?>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah User Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" required>
                            <option value="siswa">Siswa</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_user" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<script>
    function confirmDelete(event, url) {
        event.preventDefault();
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data user ini akan dihapus permanen!",
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
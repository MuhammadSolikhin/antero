<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}


// HANDLE ACTIONS

// 1. Add News
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_news'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);
    $source = $conn->real_escape_string($_POST['source']);

    // Upload Image
    $image_name = "";
    if (isset($_FILES['image']) && $_FILES['image']['name'] != '') {
        $target_dir = "../assets/uploads/";
        if (!is_dir($target_dir))
            mkdir($target_dir, 0777, true);
        $image_name = time() . '_' . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image_name);
    }

    $sql = "INSERT INTO news (title, content, source, image) VALUES ('$title', '$content', '$source', '$image_name')";
    if ($conn->query($sql)) {
        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text'] = 'Berita berhasil dipublish!';
    } else {
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_title'] = 'Gagal!';
        $_SESSION['swal_text'] = $conn->error;
    }
    header("Location: news.php");
    exit();
}

// 2. Edit News
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_news'])) {
    $id = intval($_POST['id']);
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);
    $source = $conn->real_escape_string($_POST['source']);

    $sql = "UPDATE news SET title='$title', content='$content', source='$source' WHERE id=$id";
    if ($conn->query($sql)) {
        // Update Image if exists
        if (isset($_FILES['image']) && $_FILES['image']['name'] != '') {
            $target_dir = "../assets/uploads/";
            $image_name = time() . '_' . basename($_FILES["image"]["name"]);
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image_name)) {
                $conn->query("UPDATE news SET image='$image_name' WHERE id=$id");
            }
        }
        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text'] = 'Berita berhasil diupdate!';
    } else {
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_title'] = 'Gagal!';
        $_SESSION['swal_text'] = $conn->error;
    }
    header("Location: news.php");
    exit();
}

// 3. Delete News
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Optional: Delete image file from server too
    if ($conn->query("DELETE FROM news WHERE id = $id")) {
        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text'] = 'Berita berhasil dihapus!';
    } else {
        $_SESSION['swal_icon'] = 'error';
        $_SESSION['swal_title'] = 'Gagal!';
        $_SESSION['swal_text'] = $conn->error;
    }
    header("Location: news.php");
    exit();
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';

// HANDLE ACTIONS


// List News
// Pagination & Search Logic
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$limit = 5;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$where = "";
if (!empty($search)) {
    $where = "WHERE title LIKE '%$search%' OR content LIKE '%$search%'";
}

// Count Total
$total_sql = "SELECT count(*) as total FROM news $where";
$total_result = $conn->query($total_sql);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch Data
$news_list = $conn->query("SELECT * FROM news $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
?>

<div class="container py-5">
    <div class="glass-card px-4 py-3 mb-4 d-flex justify-content-between align-items-center">
        <h3 class="fw-bold mb-0"><i class="bi bi-newspaper text-primary"></i> Kelola Berita</h3>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control rounded-pill me-2" placeholder="Cari Berita..."
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="btn btn-primary rounded-pill"><i class="bi bi-search"></i></button>
            </form>
            <button class="btn btn-primary rounded-pill shadow-sm" data-bs-toggle="modal"
                data-bs-target="#addNewsModal">
                <i class="bi bi-plus-lg me-1"></i> Tulis Berita
            </button>
        </div>
    </div>

    <?php if (isset($msg))
        echo $msg; ?>
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted')
        echo "<div class='alert alert-success'>Berita dihapus.</div>"; ?>

    <div class="row g-4">
        <?php while ($row = $news_list->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="glass-card h-100 p-0 overflow-hidden shadow-sm">
                    <?php if ($row['image']): ?>
                        <img src="../assets/uploads/<?php echo $row['image']; ?>" class="w-100"
                            style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-light w-100 d-flex align-items-center justify-content-center text-muted"
                            style="height: 200px;">
                            <i class="bi bi-image fs-1"></i>
                        </div>
                    <?php endif; ?>

                    <div class="p-4">
                        <small class="text-muted"><i class="bi bi-calendar-event me-1"></i>
                            <?php echo date('d M Y, H:i', strtotime($row['created_at'])); ?></small>
                        <h5 class="fw-bold mt-2 mb-2 text-truncate"><?php echo $row['title']; ?></h5>
                        <?php if ($row['source']): ?>
                            <small class="d-block text-primary mb-3"><i class="bi bi-link-45deg"></i>
                                <?php echo $row['source']; ?></small>
                        <?php endif; ?>
                        <p class="text-muted small mb-4"
                            style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            <?php echo strip_tags($row['content']); ?>
                        </p>

                        <div class="d-flex justify-content-between">
                            <button class="btn btn-sm btn-outline-warning rounded-pill" data-bs-toggle="modal"
                                data-bs-target="#editNewsModal<?php echo $row['id']; ?>">
                                <i class="bi bi-pencil-square"></i> Edit
                            </button>
                            <a href="news.php?delete=<?php echo $row['id']; ?>"
                                class="btn btn-sm btn-outline-danger rounded-pill"
                                onclick="return confirm('Hapus berita ini?')">
                                <i class="bi bi-trash"></i> Hapus
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Edit Modal -->
                <div class="modal fade" id="editNewsModal<?php echo $row['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Berita</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Judul Berita</label>
                                        <input type="text" name="title" class="form-control"
                                            value="<?php echo $row['title']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Sumber Berita (Optional)</label>
                                        <input type="text" name="source" class="form-control"
                                            value="<?php echo $row['source']; ?>" placeholder="Contoh: Kompas.com">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Konten</label>
                                        <textarea name="content" class="form-control" rows="6"
                                            required><?php echo $row['content']; ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Ganti Gambar (Opsional)</label>
                                        <input type="file" name="image" class="form-control">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" name="edit_news" class="btn btn-primary">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages >= 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php if ($page <= 1) {
                    echo 'disabled';
                } ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($page == $i) {
                        echo 'active';
                    } ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>"><?php echo $i; ?></a>
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

<!-- Add News Modal -->
<div class="modal fade" id="addNewsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tulis Berita Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Judul Berita</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sumber Berita (Optional)</label>
                        <input type="text" name="source" class="form-control" placeholder="Contoh: Kompas.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konten</label>
                        <textarea name="content" class="form-control" rows="6" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar Utama</label>
                        <input type="file" name="image" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_news" class="btn btn-primary">Publish</button>
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
            text: "Berita ini akan dihapus permanen!",
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
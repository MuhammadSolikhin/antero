<?php if (isset($_SESSION['user_id'])): ?>
    </div> <!-- /#page-content-wrapper -->
    </div> <!-- /#wrapper -->
<?php endif; ?>

<footer class="bg-white text-center py-3 border-top mt-auto">
    <div class="container">
        <small class="text-muted">&copy; <?php echo date('Y'); ?> Sistem Data Prestasi Bela Diri</small>
    </div>
</footer>

<!-- jQuery (Required for Select2) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Global SweetAlert Logic for Flash Messages -->
<?php
if (isset($_SESSION['swal_icon']) && isset($_SESSION['swal_title'])) {
    $icon = $_SESSION['swal_icon'];
    $title = $_SESSION['swal_title'];
    $text = $_SESSION['swal_text'] ?? '';

    echo "
    <script>
        Swal.fire({
            icon: '$icon',
            title: '$title',
            text: '$text',
            showConfirmButton: false,
            timer: 2000
        });
    </script>
    ";

    // Clear session
    unset($_SESSION['swal_icon']);
    unset($_SESSION['swal_title']);
    unset($_SESSION['swal_text']);
}
?>

</body>

</html>
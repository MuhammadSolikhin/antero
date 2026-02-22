<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: student/dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Prestasi Bela Diri</title>
    <link rel="icon" type="image/jpeg" href="assets/img/logo.jpeg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: url('assets/img/background.jpeg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="glass-card p-5 text-center text-dark">
                    <div class="mb-4">
                        <img src="assets/img/logo.jpeg" alt="Logo" class="shadow-sm rounded"
                            style="width: 150px; height: auto;">
                    </div>
                    <h2 class="fw-bold mb-4">Form Login</h2>


                    <form action="auth/login_process.php" method="POST">
                        <div class="mb-3 text-start">
                            <label class="form-label fw-bold">Username / Email</label>
                            <input type="text" name="username" class="form-control rounded-pill" required>
                        </div>
                        <div class="mb-4 text-start">
                            <label class="form-label fw-bold">Password</label>
                            <input type="password" name="password" class="form-control rounded-pill" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2">LOGIN</button>
                    </form>

                    <div class="mt-3 text-end">
                        <a href="forgot_password.php" class="text-muted small text-decoration-none">Lupa Password?</a>
                    </div>

                    <div class="mt-4">
                        <p class="mb-0">Belum punya akun? <a href="register.php"
                                class="text-primary text-decoration-none fw-bold">Daftar Siswa</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logic for SweetAlert Error -->
    <?php if (isset($_SESSION['error'])): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal',
                text: '<?php echo $_SESSION['error']; ?>',
                confirmButtonColor: '#0d6efd'
            });
        </script>
        <?php unset($_SESSION['error']); endif; ?>

    <!-- Logic for Standard Swal Session (if coming from registration) -->
    <?php if (isset($_SESSION['swal_icon'])): ?>
        <script>
            Swal.fire({
                icon: '<?php echo $_SESSION['swal_icon']; ?>',
                title: '<?php echo $_SESSION['swal_title']; ?>',
                text: '<?php echo $_SESSION['swal_text'] ?? ''; ?>',
                confirmButtonColor: '#0d6efd'
            });
        </script>
        <?php unset($_SESSION['swal_icon']);
        unset($_SESSION['swal_title']);
        unset($_SESSION['swal_text']);
    endif; ?>

</body>

</html>
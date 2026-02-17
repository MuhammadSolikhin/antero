<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Prestasi Bela Diri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: url('assets/img/background.jpeg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="glass-card p-5 text-dark">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold">Lupa Password?</h3>
                        <p class="text-muted">Masukkan email Anda untuk menerima link reset password.</p>
                    </div>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger py-2">
                            <?php echo $_SESSION['error'];
                            unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success py-2">
                            <?php echo $_SESSION['success'];
                            unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="auth/send_reset_link.php" method="POST">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" class="form-control rounded-pill"
                                placeholder="contoh@email.com" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2">KIRIM LINK
                            RESET</button>
                    </form>

                    <div class="mt-4 text-center">
                        <a href="login.php" class="text-decoration-none fw-bold text-muted">Kembali ke Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
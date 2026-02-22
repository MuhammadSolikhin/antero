<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Siswa - Prestasi Bela Diri</title>
    <link rel="icon" type="image/jpeg" href="assets/img/logo.jpeg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: url('assets/img/background.jpeg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="glass-card p-5 text-dark">
                    <div class="text-center mb-4">
                        <img src="assets/img/logo.jpeg" alt="Logo" class="shadow-sm rounded"
                            style="width: 120px; height: auto;">
                    </div>
                    <h2 class="fw-bold mb-4 text-center">Form Pendaftaran</h2>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger py-2">
                            <?php echo $_SESSION['error'];
                            unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="auth/register_process.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Username (untuk Login)</label>
                            <input type="text" name="username" class="form-control rounded-pill" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" class="form-control rounded-pill" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Password</label>
                            <input type="password" name="password" class="form-control rounded-pill" required>
                        </div>
                        <hr class="border-secondary my-4">
                        <h5 class="mb-3 fw-bold">Biodata Awal</h5>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control rounded-pill" required>
                        </div>

                        <!-- Note: Detailed biodata can be filled in dashboard to keep registration simple, 
                             but user flow asked for input biodata. I'll include Dojang selection here 
                             or keep it simple and ask them to complete profile later.
                             Let's keep it simple: Register Account -> Login -> Fill Biodata.
                             Wait, User Flow says: Input Biodata -> Input Prestasi.
                             So it's better to just create account, then redirect to biodata form.
                        -->

                        <button type="submit"
                            class="btn btn-warning w-100 rounded-pill fw-bold py-2 mt-3 text-dark">DAFTAR
                            SEKARANG</button>
                    </form>

                    <div class="mt-4 text-center">
                        <p class="mb-0">Sudah punya akun? <a href="login.php"
                                class="text-primary text-decoration-none fw-bold">Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
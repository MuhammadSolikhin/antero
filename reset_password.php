<?php
session_start();
require_once 'config/database.php';

if (!isset($_GET['token'])) {
    die("Token tidak valid.");
}

$token = $_GET['token'];
$now = date("Y-m-d H:i:s");

// Verify Token
$stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > ?");
$stmt->bind_param("ss", $token, $now);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Link reset password tidak valid atau sudah kadaluarsa.");
}

$email = $result->fetch_assoc()['email'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Prestasi Bela Diri</title>
    <link rel="icon" type="image/jpeg" href="assets/img/logo.jpeg">
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
                        <h3 class="fw-bold">Buat Password Baru</h3>
                        <p class="text-muted">Silakan masukkan password baru untuk
                            <?= htmlspecialchars($email) ?>
                        </p>
                    </div>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger py-2">
                            <?php echo $_SESSION['error'];
                            unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="auth/process_reset_password.php" method="POST">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Password Baru</label>
                            <input type="password" name="password" class="form-control rounded-pill" required
                                minlength="6">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Konfirmasi Password</label>
                            <input type="password" name="password_confirm" class="form-control rounded-pill" required
                                minlength="6">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2">SIMPAN
                            PASSWORD</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
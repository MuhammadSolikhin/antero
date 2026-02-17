<?php
session_start();
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);

    // Check if email exists
    $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($check->num_rows > 0) {
        $token = bin2hex(random_bytes(50));
        $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // Save token
        $sql = "INSERT INTO password_resets (email, token, expires_at) VALUES ('$email', '$token', '$expires')";
        $conn->query($sql);

        // Generate Link
        $reset_link = BASE_URL . "reset_password.php?token=" . $token;

        // Email Configuration
        $subject = "Reset Password - Prestasi Bela Diri";

        // HTML Message
        $message = "
        <html>
        <head>
            <title>Reset Password</title>
        </head>
        <body>
            <h3>Permintaan Reset Password</h3>
            <p>Seseorang (mungkin Anda) telah meminta reset password untuk akun ini.</p>
            <p>Silakan klik link di bawah ini untuk membuat password baru:</p>
            <p><a href='$reset_link' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Reset Password</a></p>
            <p>Link ini akan kadaluarsa dalam 1 jam.</p>
            <p>Jika Anda tidak merasa meminta ini, abaikan saja email ini.</p>
        </body>
        </html>
        ";

        // Headers for HTML Email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: no-reply@prestasibeladiri.com" . "\r\n";

        // Try sending mail
        $mail_sent = @mail($email, $subject, $message, $headers);

        if ($mail_sent) {
            $_SESSION['success'] = "Kami telah mengirimkan link reset password ke email Anda ($email). Silakan cek Inbox atau Spam folder.";
        } else {
            // Mail failed (Common on free hosting like unaux/ezyro)
            // SHOW LINK DIRECTLY FOR TESTING
            $_SESSION['success'] = "
                <span class='text-danger fw-bold'>Gagal mengirim email (Server memblokir fitur mail).</span><br>
                Namun karena ini mode testing, berikut adalah link reset Anda:<br>
                <div class='mt-2'>
                    <a href='$reset_link' class='btn btn-success btn-sm'>KLIK UNTUK RESET PASSWORD</a>
                </div>
            ";
        }

    } else {
        // Email not found
        $_SESSION['error'] = "Email tidak terdaftar dalam sistem.";
    }

    header("Location: ../forgot_password.php");
    exit();
}
?>
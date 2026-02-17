<?php
session_start();
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if ($password !== $password_confirm) {
        $_SESSION['error'] = "Konfirmasi password tidak cocok!";
        header("Location: ../reset_password.php?token=$token");
        exit();
    }

    // Verify Token Again (Security)
    $now = date("Y-m-d H:i:s");
    $check = $conn->query("SELECT id FROM password_resets WHERE token = '$token' AND email = '$email' AND expires_at > '$now'");

    if ($check->num_rows > 0) {
        // Update Password
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password = '$new_hash' WHERE email = '$email'");

        // Delete Token
        $conn->query("DELETE FROM password_resets WHERE email = '$email'");

        $_SESSION['success'] = "Password berhasil diubah! Silakan login.";
        header("Location: ../login.php");
        exit();
    } else {
        $_SESSION['error'] = "Token tidak valid atau kadaluarsa.";
        header("Location: ../forgot_password.php");
        exit();
    }
}
?>
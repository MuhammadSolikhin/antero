<?php
session_start();
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama_lengkap = $conn->real_escape_string($_POST['nama_lengkap']);

    // Check availability (Username OR Email)
    $check = $conn->query("SELECT id FROM users WHERE username = '$username' OR email = '$email'");
    if ($check->num_rows > 0) {
        $_SESSION['error'] = "Username atau Email sudah digunakan!";
        header("Location: ../register.php");
        exit();
    }

    // Insert User
    $sql_user = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password', 'siswa')";
    if ($conn->query($sql_user) === TRUE) {
        $user_id = $conn->insert_id;

        // Note: Students data will be filled in dashboard

        $_SESSION['success'] = "Pendaftaran berhasil, silakan login!";
        header("Location: ../login.php");
    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
        header("Location: ../register.php");
    }
}
?>
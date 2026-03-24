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
        header("Location: ../register_coach.php");
        exit();
    }

    // Insert User with role 'pelatih'
    $sql_user = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password', 'pelatih')";
    if ($conn->query($sql_user) === TRUE) {
        $user_id = $conn->insert_id;

        // Insert initial data into coaches table
        // We need to provide empty values for required columns
        $sql_coach = "INSERT INTO coaches (user_id, nama_pelatih, riwayat_pelatihan, tingkatan) VALUES ($user_id, '$nama_lengkap', '', '')";
        if (!$conn->query($sql_coach)) {
            // Log error, but proceed with login (or handle gracefully)
            error_log("Gagal membuat profil pelatih: " . $conn->error);
        }

        $_SESSION['swal_icon'] = 'success';
        $_SESSION['swal_title'] = 'Berhasil!';
        $_SESSION['swal_text'] = 'Pendaftaran pelatih berhasil, silakan login!';
        header("Location: ../login.php");
    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
        header("Location: ../register_coach.php");
    }
}
?>

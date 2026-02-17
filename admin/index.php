<?php
session_start();
if (isset($_SESSION['role']) && $_SESSION['role'] == 'siswa') {
    header("Location: ../student/dashboard.php");
} else {
    header("Location: dashboard.php");
}
exit();
?>
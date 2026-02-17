<?php
session_start();
if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    header("Location: ../admin/dashboard.php");
} else {
    header("Location: dashboard.php");
}
exit();
?>
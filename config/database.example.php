<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // Default XAMPP/Laragon password is empty
$db = 'db_prestasi_beladiri';

// Define Base URL (Adjust this if your folder name is different)
define('BASE_URL', 'http://localhost/prestasi/');

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
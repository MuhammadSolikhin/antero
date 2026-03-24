<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Data Prestasi Bela Diri</title>
    <link rel="icon" type="image/jpeg" href="<?= BASE_URL ?>assets/img/logo.jpeg">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">

    <?php 
    // Fetch background image
    $bg_query = $conn->query("SELECT background_image FROM club_info WHERE id = 1");
    if ($bg_query) {
        $bg_data = $bg_query->fetch_assoc();
        if (!empty($bg_data['background_image'])) {
            $bg_url = BASE_URL . 'assets/uploads/system/' . $bg_data['background_image'];
            echo "<style>
            body {
                background: url('{$bg_url}') no-repeat center center fixed !important;
                background-size: cover !important;
            }
            </style>";
        }
    }
    ?>


    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>
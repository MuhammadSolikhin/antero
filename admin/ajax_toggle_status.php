<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get raw POST data
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['id']) && isset($input['status'])) {
        $id = intval($input['id']);
        $current_status = $input['status'];

        // Toggle status
        $new_status = ($current_status == 'aktif') ? 'tidak aktif' : 'aktif';

        $stmt = $conn->prepare("UPDATE students SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'new_status' => $new_status,
                'message' => 'Status berhasil diperbarui menjadi ' . ucfirst($new_status)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
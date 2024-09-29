<?php
include '../../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $userId = intval($_POST['id']);

    // Database connection
    $conn = new mysqli("localhost", "u450897284_root", "Lfisgemsdb1234", "u450897284_lfis_db");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Delete user query
    $sql = "DELETE FROM user_staff WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);

    if ($stmt->execute()) {
        echo '1'; // Success response
    } else {
        echo '0'; // Failure response
    }

    $stmt->close();
    $conn->close();
} else {
    echo '0'; // Invalid request
}
?>

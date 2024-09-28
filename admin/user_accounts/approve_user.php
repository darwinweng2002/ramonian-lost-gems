<?php
include '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];

    // Database connection
    $conn = new mysqli("localhost", "u450897284_root", "Lfisgemsdb1234", "u450897284_lfis_db");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Approve the user
    $stmt = $conn->prepare("UPDATE user_staff SET status = 'approved' WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo '1'; // Success
    } else {
        echo '0'; // Failure
    }

    $stmt->close();
    $conn->close();
}

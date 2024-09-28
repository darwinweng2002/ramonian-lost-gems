<?php
include '../../config.php';

// Database connection
$conn = new mysqli("localhost", "u450897284_root", "Lfisgemsdb1234", "u450897284_lfis_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    // Delete the user from the database
    $stmt = $conn->prepare("DELETE FROM user_staff WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo '1'; // Success
    } else {
        echo '0'; // Error
    }

    $stmt->close();
}

$conn->close();
?>

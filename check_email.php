<?php
include 'config.php';

if (isset($_POST['email'])) {
    $email = $_POST['email'];
    // Check for the email in the database
    $stmt = $conn->prepare("SELECT id FROM user_member WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Email exists
        echo json_encode(['exists' => true]);
    } else {
        // Email does not exist
        echo json_encode(['exists' => false]);
    }

    $stmt->close();
    $conn->close();
}

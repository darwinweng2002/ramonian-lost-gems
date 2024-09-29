<?php
include '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];

    // Update user status to "active" (since the approved status is actually 'active' in the table)
    $stmt = $conn->prepare("UPDATE user_staff SET status = 'active' WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo '1';  // Return success response
    } else {
        echo '0';  // Return failure response
    }

    $stmt->close();
    $conn->close();
}
?>

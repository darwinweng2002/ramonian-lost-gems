<?php
include '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

    if ($user_id > 0) {
        $stmt = $conn->prepare("UPDATE user_member SET status = 'approved' WHERE id = ?");
        $stmt->bind_param('i', $user_id);

        if ($stmt->execute()) {
            echo '1'; // Success
        } else {
            echo '0'; // Failure (query failed)
        }
        $stmt->close();
    } else {
        echo '0'; // Failure (invalid user ID)
    }

    $conn->close();
}
?>

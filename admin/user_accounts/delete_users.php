<?php
// Include database connection
include '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the user ID
    $id = $_POST['id'];

    // Ensure you have a valid user ID
    if (!empty($id)) {
        // Prepare and execute delete query
        $stmt = $conn->prepare("DELETE FROM user_staff WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            // Return success response (1)
            echo "1";
        } else {
            // If something went wrong with the query, return error response
            echo "0";
        }

        $stmt->close();
    } else {
        // Missing id
        echo "0";
    }

    $conn->close();
}

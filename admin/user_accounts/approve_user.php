<?php
// Include database configuration
include '../../config.php';

// Initialize the response
$response = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure the user ID is sent via POST
    if (isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);
        
        // Connect to the database
        $conn = new mysqli("localhost", "u450897284_root", "Lfisgemsdb1234", "u450897284_lfis_db");

        // Check the connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Update the status of the user to 'active'
        $sql = "UPDATE user_staff SET status = 'active' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            // If the query is successful, return '1'
            $response = 1;
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close();
    }
}

// Return the response
echo $response;

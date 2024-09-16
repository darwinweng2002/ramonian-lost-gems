<?php
include '../../config.php';

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db'); // Replace with your actual DB connection details

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize response
$response = ['success' => false, 'error' => ''];

// Check if id is set
if (isset($_POST['id'])) {
    $itemId = intval($_POST['id']); // Ensure id is an integer

    // Prepare the SQL statement
    $stmt = $conn->prepare("DELETE FROM missing_items WHERE id = ?");
    $stmt->bind_param('i', $itemId);

    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['error'] = $stmt->error;
    }

    $stmt->close();
}

$conn->close();

echo json_encode($response);
?>

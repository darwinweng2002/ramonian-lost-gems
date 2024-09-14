<?php
include '../config.php'; // Include your database configuration

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect if user is not logged in
    exit();
}

// Retrieve user ID from session
$userId = $_SESSION['user_id'];

// Retrieve form data
$itemId = $_POST['item_id'] ?? '';
$message = $_POST['message'] ?? '';
$course = $_POST['course'] ?? '';
$year = $_POST['year'] ?? '';
$section = $_POST['section'] ?? '';
$status = 'pending'; // Default status

// Handle file upload
$proofFile = '';
if (isset($_FILES['proof_file']) && $_FILES['proof_file']['error'] === UPLOAD_ERR_OK) {
    $proofFile = $_FILES['proof_file']['name'];
    $uploadDir = '../uploads/proofs/';
    $uploadFile = $uploadDir . basename($proofFile);

    // Move the uploaded file
    if (move_uploaded_file($_FILES['proof_file']['tmp_name'], $uploadFile)) {
        $proofFile = basename($proofFile); // File upload success
    } else {
        die('Error uploading proof of ownership.');
    }
} else {
    die('No proof of ownership uploaded or file upload error.');
}

// Database connection
$conn = new mysqli('localhost', 'root', '1234', 'lost_db'); // Replace with actual DB connection details

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if item_id exists in message_history table
$itemCheckQuery = "SELECT id FROM message_history WHERE id = ?";
$itemCheckStmt = $conn->prepare($itemCheckQuery);
$itemCheckStmt->bind_param('i', $itemId);
$itemCheckStmt->execute();
$itemCheckStmt->store_result();

if ($itemCheckStmt->num_rows === 0) {
    die("Error: The item with ID $itemId does not exist.");
}
$itemCheckStmt->close();

// Prepare and execute the SQL statement
$sql = "INSERT INTO claims (item_id, user_id, message, proof_file, status, course, year, section) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iissssss', $itemId, $userId, $message, $proofFile, $status, $course, $year, $section);

if ($stmt->execute()) {
    echo "Claim submitted successfully.";
} else {
    die("Error: " . $stmt->error);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>

<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include config or manually create the connection
include '../config.php';

// OR manually create the connection if not in config.php
$servername = "localhost";
$username = "u450897284_root";
$password = "Lfisgemsdb1234";
$dbname = "u450897284_lfis_db";

// Create the connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        die("User not logged in.");
    }

    // Get the data from the form
    $itemId = intval($_POST['item_id']);
    $itemDescription = htmlspecialchars(trim($_POST['item_description']));
    $dateLost = htmlspecialchars(trim($_POST['date_lost']));
    $locationLost = htmlspecialchars(trim($_POST['location_lost']));
    $securityQuestion = htmlspecialchars(trim($_POST['security_question']));
    $claimantId = $_SESSION['user_id'];

    // Directory for uploading files
    $uploadDir = '../uploads/claims/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
    }

    // Initialize variables for file uploads
    $proofOfOwnershipPath = null;
    $personalIdPath = null;

    // Allowed file types
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];

    // Handle proof of ownership file upload
    if (isset($_FILES['proof_of_ownership']) && $_FILES['proof_of_ownership']['error'] == UPLOAD_ERR_OK) {
        $fileName = basename($_FILES['proof_of_ownership']['name']);
        $fileType = $_FILES['proof_of_ownership']['type'];

        if (in_array($fileType, $allowedTypes)) {
            $targetFilePath = $uploadDir . uniqid() . '_' . $fileName; // Use a unique file name to avoid conflicts
            if (move_uploaded_file($_FILES['proof_of_ownership']['tmp_name'], $targetFilePath)) {
                $proofOfOwnershipPath = $targetFilePath; // Store full file path for better management
            } else {
                die("Error uploading proof of ownership file.");
            }
        } else {
            die("Invalid proof of ownership file type.");
        }
    }

    // Handle personal ID file upload
    if (isset($_FILES['personal_id']) && $_FILES['personal_id']['error'] == UPLOAD_ERR_OK) {
        $fileName = basename($_FILES['personal_id']['name']);
        $fileType = $_FILES['personal_id']['type'];

        if (in_array($fileType, $allowedTypes)) {
            $targetFilePath = $uploadDir . uniqid() . '_' . $fileName; // Use a unique file name to avoid conflicts
            if (move_uploaded_file($_FILES['personal_id']['tmp_name'], $targetFilePath)) {
                $personalIdPath = $targetFilePath; // Store full file path for better management
            } else {
                die("Error uploading personal ID file.");
            }
        } else {
            die("Invalid personal ID file type.");
        }
    }

    // Insert the claim into the database
    $sql = "INSERT INTO claimer (item_id, user_id, item_description, date_lost, location_lost, proof_of_ownership, security_question, personal_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Database error: " . $conn->error);
    }
    
    $stmt->bind_param('iissssss', $itemId, $claimantId, $itemDescription, $dateLost, $locationLost, $proofOfOwnershipPath, $securityQuestion, $personalIdPath);
    
    // Execute the query and check for errors
    if ($stmt->execute()) {
        // Success
        header('Location: success.php'); // Redirect to a success page
        exit();
    } else {
        // Error in query execution
        die("Error submitting claim: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
}
?>

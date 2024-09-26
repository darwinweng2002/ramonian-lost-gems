<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config.php';

// Check if the form is submitted
if (!isset($_SESSION['user_id']) && !isset($_SESSION['staff_id'])) {
    die("User not logged in");
}

    // Determine if the user is a regular user or staff user
    if (isset($_SESSION['user_id'])) {
        $claimantId = $_SESSION['user_id'];
        $claimantType = 'user_member'; // Table for regular users
    } elseif (isset($_SESSION['staff_id'])) {
        $claimantId = $_SESSION['staff_id'];
        $claimantType = 'staff_user'; // Table for staff users
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

    // Handle proof of ownership file upload
    $proofOfOwnershipPath = null;
    if (isset($_FILES['proof_of_ownership']) && $_FILES['proof_of_ownership']['error'] == UPLOAD_ERR_OK) {
        $fileName = basename($_FILES['proof_of_ownership']['name']);
        $fileType = $_FILES['proof_of_ownership']['type'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];

        if (in_array($fileType, $allowedTypes)) {
            $targetFilePath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['proof_of_ownership']['tmp_name'], $targetFilePath)) {
                $proofOfOwnershipPath = $fileName; // Store just the file name in the database
            }
        }
    }

    // Handle personal ID file upload
    $personalIdPath = null;
    if (isset($_FILES['personal_id']) && $_FILES['personal_id']['error'] == UPLOAD_ERR_OK) {
        $fileName = basename($_FILES['personal_id']['name']);
        $fileType = $_FILES['personal_id']['type'];
        if (in_array($fileType, $allowedTypes)) {
            $targetFilePath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['personal_id']['tmp_name'], $targetFilePath)) {
                $personalIdPath = $fileName; // Store just the file name in the database
            }
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
        header('Location: claim.php'); // Redirect to a success page
        exit();
    } else {
        // Error in query execution
        die("Error submitting claim: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
?>

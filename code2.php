<?php
include 'config.php'; 
// staff_register.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database configuration file
include 'config.php'; // Your DB config file should include $conn connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $user_type = trim($_POST['user_type']);

    // Verify passwords match
    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Handle file upload
    if (!isset($_FILES['id_file']) || $_FILES['id_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Please upload your school ID.']);
        exit;
    }

    // File upload details
    $fileTmpPath = $_FILES['id_file']['tmp_name'];
    $fileName = $_FILES['id_file']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Allowed file extensions
    $allowedFileExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
    if (!in_array($fileExtension, $allowedFileExtensions)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file format for ID.']);
        exit;
    }

    // Upload the file
    $uploadDir = 'uploads/ids/';
    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
    $uploadFilePath = $uploadDir . $newFileName;

    if (!move_uploaded_file($fileTmpPath, $uploadFilePath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload ID.']);
        exit;
    }

    // Check if the email is already registered
    $stmt = $conn->prepare("SELECT id FROM user_staff WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'This email is already registered.']);
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Insert the staff data into the database
    $stmt = $conn->prepare("INSERT INTO user_staff (first_name, last_name, email, password, user_type, id_file, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $status = 'pending'; // Pending approval
    $stmt->bind_param("sssssss", $first_name, $last_name, $email, $hashed_password, $user_type, $newFileName, $status);

    if ($stmt->execute()) {
        // Send success response
        echo json_encode(['success' => true, 'message' => 'Registration successful! Your account is pending approval.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to register staff.']);
    }

    $stmt->close();
    $conn->close();
}
?>

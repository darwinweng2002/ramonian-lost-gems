<?php
// Include the database configuration file
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'config.php';

// Include PHPMailer for email notifications (optional)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $user_type = trim($_POST['user_type']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $department = $user_type === 'teaching' ? trim($_POST['department']) : null;
    $position = $user_type !== 'teaching' ? trim($_POST['position']) : null;

    // Check if passwords match
    if ($password !== $confirm_password) {
        $response = ['success' => false, 'message' => 'Passwords do not match.'];
        echo json_encode($response);
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Handle file upload (School ID)
    $target_dir = "uploads/ids/";
    $school_id_file = $target_dir . basename($_FILES["id_file"]["name"]);
    $imageFileType = strtolower(pathinfo($school_id_file, PATHINFO_EXTENSION));

    // Check if file is a valid image type
    $valid_file_types = ['jpg', 'jpeg', 'png', 'pdf'];
    if (!in_array($imageFileType, $valid_file_types)) {
        $response = ['success' => false, 'message' => 'Invalid file format for school ID. Only JPG, JPEG, PNG, and PDF are allowed.'];
        echo json_encode($response);
        exit;
    }

    // Ensure target directory exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Generate a unique file name
    $newFileName = md5(time() . basename($_FILES["id_file"]["name"])) . '.' . $imageFileType;
    $target_file = $target_dir . $newFileName;

    // Attempt to move the uploaded file
    if (!move_uploaded_file($_FILES["id_file"]["tmp_name"], $target_file)) {
        $response = ['success' => false, 'message' => 'Error uploading school ID.'];
        echo json_encode($response);
        exit;
    }

    // Check if the email is already registered
    $stmt = $conn->prepare("SELECT id FROM user_staff WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $response = ['success' => false, 'message' => 'This email is already registered.'];
        echo json_encode($response);
        $stmt->close();
        $conn->close();
        exit;
    }

    $stmt->close();

    // Set account status as "pending"
    $status = 'pending';

    // Prepare the SQL statement to insert new staff member
    $stmt = $conn->prepare("INSERT INTO user_staff (first_name, last_name, email, password, department, position, user_type, id_file, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Debugging: Check if statement preparation fails
    if ($stmt === false) {
        error_log('Error in SQL prepare statement: ' . $conn->error);
        $response = ['success' => false, 'message' => 'Failed to prepare the database statement.'];
        echo json_encode($response);
        exit;
    }

    // Bind parameters
    $stmt->bind_param("sssssssss", $first_name, $last_name, $email, $hashed_password, $department, $position, $user_type, $newFileName, $status);

    // Execute the query and check for success
    if (!$stmt->execute()) {
        // Log error if query execution fails
        error_log('Error executing SQL query: ' . $stmt->error);
        $response = ['success' => false, 'message' => 'Failed to register staff member.'];
    } else {
        $response = ['success' => true, 'message' => 'Registration successful! Your account is pending approval.'];
    }

    $stmt->close();
    $conn->close();

    // Return a JSON response
    echo json_encode($response);
}
?>

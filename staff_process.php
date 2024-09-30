<?php
include 'config.php'; // Include the database configuration file

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data and validate
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $user_type = trim($_POST['user_type']);
    $username = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Handle department and position based on user type
    $department = null;
    $position = null;

    if ($user_type === 'teaching') {
        $department = trim($_POST['department']);
        if (empty($department)) {
            echo json_encode(['success' => false, 'message' => 'Department is required for teaching staff.']);
            exit;
        }
    } else {
        $position = trim($_POST['position']);
        if (empty($position)) {
            echo json_encode(['success' => false, 'message' => 'Position is required for non-teaching staff.']);
            exit;
        }
    }

    // Handle file upload (profile picture)
    $profile_image = '';
    $target_dir = "uploads/profiles/";

    // Ensure the directory exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true); // Create directory if not exists
    }

    if (!empty($_FILES['profile_image']['name'])) {
        // Ensure unique file name to avoid conflicts
        $profile_image = uniqid() . '_' . basename($_FILES['profile_image']['name']);
        $target_file = $target_dir . $profile_image;

        // Validate the image file
        $file_type = mime_content_type($_FILES['profile_image']['tmp_name']);
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

        if (!in_array($file_type, $allowed_types)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.']);
            exit;
        }

        // Move the uploaded file to the server
        if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            echo json_encode(['success' => false, 'message' => 'Failed to upload profile picture.']);
            exit;
        }
    }

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO user_staff (first_name, last_name, email, password, department, position, user_type, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt === false) {
        error_log("SQL prepare error: " . $conn->error); // Log error
        echo json_encode(['success' => false, 'message' => 'Failed to prepare the database statement.']);
        exit;
    }

    $stmt->bind_param("ssssssss", $first_name, $last_name, $username, $hashed_password, $department, $position, $user_type, $profile_image);

    // Execute the query and check for success
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registration successful!']);
    } else {
        error_log("SQL execute error: " . $stmt->error); // Log error
        echo json_encode(['success' => false, 'message' => 'Failed to register user.']);
    }

    $stmt->close();
    $conn->close();
}

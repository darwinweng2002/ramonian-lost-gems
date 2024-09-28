<?php
include 'config.php'; // Include the database configuration file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize form data
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $college = trim($_POST['college']);
    $course = trim($_POST['course']);
    $year = trim($_POST['year']);
    $section = trim($_POST['section']);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL); // Ensure valid email
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Check if all required fields are provided
    if (empty($first_name) || empty($last_name) || empty($college) || empty($course) || empty($year) || empty($section) || empty($email) || empty($password)) {
        $response = ['success' => false, 'message' => 'Please fill in all the required fields.'];
        echo json_encode($response);
        exit;
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        $response = ['success' => false, 'message' => 'Passwords do not match.'];
        echo json_encode($response);
        exit;
    }

    // Validate password length (8-16 characters)
    if (strlen($password) < 8 || strlen($password) > 16) {
        $response = ['success' => false, 'message' => 'Password must be between 8 and 16 characters long.'];
        echo json_encode($response);
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Check if the email already exists in the database
    $stmt = $conn->prepare("SELECT id FROM user_member WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Email already exists
        $response = ['success' => false, 'message' => 'This email is already registered.'];
        echo json_encode($response);
        $stmt->close();
        $conn->close();
        exit;
    }

    $stmt->close();

    // Handle school ID upload
    $target_dir = "uploads/school_ids/";
    $target_file = $target_dir . basename($_FILES["school_id"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check file type (only allow jpg, jpeg, png)
    $valid_file_types = ['jpg', 'jpeg', 'png'];
    if (!in_array($imageFileType, $valid_file_types)) {
        $response = ['success' => false, 'message' => 'Invalid file format for school ID. Only JPG, JPEG, and PNG are allowed.'];
        echo json_encode($response);
        exit;
    }

    // Attempt to move the uploaded file
    if (!move_uploaded_file($_FILES["school_id"]["tmp_name"], $target_file)) {
        $response = ['success' => false, 'message' => 'Error uploading school ID.'];
        echo json_encode($response);
        exit;
    }

    // Set user status as 'pending'
    $status = 'pending';

    // Prepare the SQL statement to insert new user with school ID attachment
    $stmt = $conn->prepare("INSERT INTO user_member (first_name, last_name, college, course, year, section, email, password, status, avatar) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        $response = ['success' => false, 'message' => 'Failed to prepare the database statement.'];
        echo json_encode($response);
        exit;
    }

    // Bind parameters (school ID is stored in avatar field)
    $stmt->bind_param("ssssssssss", $first_name, $last_name, $college, $course, $year, $section, $email, $hashed_password, $status, $target_file);

    // Execute the query and check for success
    if ($stmt->execute()) {
        // Optionally, you could send an email to the admin or the user notifying that the registration is pending approval
        $response = ['success' => true, 'message' => 'Registration successful! Your account is pending approval.'];
    } else {
        $response = ['success' => false, 'message' => 'Failed to register user.'];
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();

    // Return a JSON response
    echo json_encode($response);
}

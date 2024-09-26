<?php
include 'config.php'; // Include the database configuration file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $user_type = $_POST['user_type'];

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

    // Prepare additional fields based on user type
    $college = null;
    $course = null;
    $year = null;
    $section = null;
    $position = null;

    if ($user_type === 'student') {
        $college = trim($_POST['college']);
        $course = trim($_POST['course']);
        $year = trim($_POST['year']);
        $section = trim($_POST['section']);
    } else if ($user_type === 'faculty') {
        $college = trim($_POST['college']); // College/Department for faculty
    } else if ($user_type === 'staff') {
        $position = trim($_POST['position']); // Position for staff
    }

    // Check if the username already exists
    $stmt = $conn->prepare("SELECT id FROM user_member WHERE email = ?");
    if (!$stmt) {
        $response = ['success' => false, 'message' => 'Database error: Failed to prepare statement.'];
        echo json_encode($response);
        exit;
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $response = ['success' => false, 'message' => 'This username is already taken.'];
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    // Insert the user data into the database
    $stmt = $conn->prepare("INSERT INTO user_member (first_name, last_name, user_type, college, course, year, section, position, email, password) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        $response = ['success' => false, 'message' => 'Failed to prepare the database statement.'];
        echo json_encode($response);
        exit;
    }
    
    $stmt->bind_param("ssssssssss", $first_name, $last_name, $user_type, $college, $course, $year, $section, $position, $username, $hashed_password);

    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Registration successful!'];
    } else {
        $response = ['success' => false, 'message' => 'Failed to register user.'];
    }

    $stmt->close();
    $conn->close();

    echo json_encode($response);
}
?>

<?php
include 'config.php'; // Include the database configuration file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $user_type = $_POST['user_type']; // Get the user type
    $college = trim($_POST['college'] ?? null);
    $course = trim($_POST['course'] ?? null);
    $year = trim($_POST['year'] ?? null);
    $section = trim($_POST['section'] ?? null);
    $position = trim($_POST['position'] ?? null); // Position for staff
    $username = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Check if required fields are provided based on user type
    if (empty($first_name) || empty($last_name) || empty($username) || empty($password)) {
        $response = ['success' => false, 'message' => 'Please fill in all the required fields.'];
        echo json_encode($response);
        exit;
    }

    if ($user_type === 'student') {
        if (empty($college) || empty($course) || empty($year) || empty($section)) {
            $response = ['success' => false, 'message' => 'Please fill in all the required fields for students.'];
            echo json_encode($response);
            exit;
        }
    } elseif ($user_type === 'faculty' && empty($college)) {
        $response = ['success' => false, 'message' => 'Please select a department for faculty.'];
        echo json_encode($response);
        exit;
    } elseif ($user_type === 'staff' && empty($position)) {
        $response = ['success' => false, 'message' => 'Please enter your position for staff.'];
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

    // Check if the username (email) already exists in the database
    $stmt = $conn->prepare("SELECT id FROM user_member WHERE email = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Username already exists
        $response = ['success' => false, 'message' => 'This username is already taken.'];
        echo json_encode($response);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    // Prepare the SQL statement to insert new user
    $stmt = $conn->prepare("INSERT INTO user_member (first_name, last_name, user_type, college, course, year, section, position, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssss", $first_name, $last_name, $user_type, $college, $course, $year, $section, $position, $username, $hashed_password);

    // Execute the query and check for success
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Registration successful!'];
    } else {
        error_log("Database error: " . $stmt->error);
        $response = ['success' => false, 'message' => 'Failed to register user due to a database error.'];
    }

    $stmt->close();
    $conn->close();

    // Return a JSON response
    echo json_encode($response);
}
?>

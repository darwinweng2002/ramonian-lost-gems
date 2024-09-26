<?php
include 'config.php'; // Include the database configuration file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data and validate
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $user_type = $_POST['user_type']; // Added field for user type

    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($username) || empty($password)) {
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

    // Handle fields based on user type
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
        $college = trim($_POST['college']); // Department for faculty
    } else if ($user_type === 'staff') {
        $position = trim($_POST['position']); // Position for staff
    }

    // Check if the username already exists
    $stmt = $conn->prepare("SELECT id FROM user_member WHERE email = ?");
    if (!$stmt) {
        // Debugging SQL error
        echo "SQL Error: " . $conn->error;
        exit;
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $response = ['success' => false, 'message' => 'This username is already taken.'];
        echo json_encode($response);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    // Prepare the SQL statement to insert a new user
    $stmt = $conn->prepare("INSERT INTO user_member (first_name, last_name, user_type, college, course, year, section, position, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        // Debugging SQL error
        echo "SQL Error: " . $conn->error;
        exit;
    }

    $stmt->bind_param("ssssssssss", $first_name, $last_name, $user_type, $college, $course, $year, $section, $position, $username, $hashed_password);

    // Execute the query and check for success
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Registration successful!'];
    } else {
        // Output SQL error for debugging
        echo "SQL Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    // Return a JSON response
    echo json_encode($response);
}
?>

<?php
include 'config.php'; // Include the database configuration file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data and validate
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $college = trim($_POST['college']);
    $course = trim($_POST['course']);
    $year = trim($_POST['year']);
    $section = trim($_POST['section']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check if all required fields are provided
    if (empty($first_name) || empty($last_name) || empty($college) || empty($course) || empty($year) || empty($section) || empty($email) || empty($password)) {
        $response = ['success' => false, 'message' => 'Please fill in all the required fields.'];
        echo json_encode($response);
        exit;
    }

    // Check if the username (email) already exists in the database
    $stmt = $conn->prepare("SELECT id FROM user_member WHERE email = ?");
    $stmt->bind_param("s", $email);
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

    // Hash the password (limiting to 8 characters before hashing)
    $hashed_password = password_hash(substr($password, 0, 8), PASSWORD_BCRYPT);

    // Prepare the SQL statement for inserting a new user
    $stmt = $conn->prepare("INSERT INTO user_member (first_name, last_name, college, course, year, section, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        $response = ['success' => false, 'message' => 'Failed to prepare the database statement.'];
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param("ssssssss", $first_name, $last_name, $college, $course, $year, $section, $email, $hashed_password);

    // Execute the query and check for success
    if ($stmt->execute()) {
        $response = ['success' => true];
    } else {
        // Provide more detailed error information if query execution fails
        $response = ['success' => false, 'message' => 'Failed to register user. Error: ' . $conn->error];
    }

    $stmt->close();
    $conn->close();

    // Return a JSON response
    echo json_encode($response);
}
?>

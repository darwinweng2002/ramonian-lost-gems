<?php
include 'config.php'; // Include the database configuration file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data and sanitize inputs
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $department = trim($_POST['department']);
    $position = trim($_POST['position']);
    $username = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Check if all required fields are filled
    if (empty($first_name) || empty($last_name) || empty($department) || empty($position) || empty($username) || empty($password)) {
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

    // Hash the password before inserting into the database
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Check if the username already exists in the database
    if ($stmt = $conn->prepare("SELECT id FROM user_staff WHERE email = ?")) {
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
    } else {
        $response = ['success' => false, 'message' => 'Failed to prepare the database statement (check email check query).'];
        echo json_encode($response);
        exit;
    }

    // Prepare the SQL statement to insert new faculty user into user_staff table
    if ($stmt = $conn->prepare("INSERT INTO user_staff (first_name, last_name, department, position, email, password) VALUES (?, ?, ?, ?, ?, ?)")) {
        $stmt->bind_param("ssssss", $first_name, $last_name, $department, $position, $username, $hashed_password);

        // Execute the query and check for success
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Faculty registration successful!'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to register faculty member.'];
        }

        $stmt->close();
    } else {
        $response = ['success' => false, 'message' => 'Failed to prepare the database statement (check insert query).'];
        echo json_encode($response);
        exit;
    }

    $conn->close();

    // Return a JSON response
    echo json_encode($response);
}
?>

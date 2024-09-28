<?php
include 'config.php'; // Include the database configuration file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data and validate
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $user_type = trim($_POST['user_type']);
    $username = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($user_type === 'teaching') {
        $department = trim($_POST['department']);
        $position = null;
        if (empty($department)) {
            $response = ['success' => false, 'message' => 'Please enter the department for teaching staff.'];
            echo json_encode($response);
            exit;
        }
    } else {
        $position = trim($_POST['position']);
        $department = null;
        if (empty($position)) {
            $response = ['success' => false, 'message' => 'Please enter the role/position for non-teaching staff.'];
            echo json_encode($response);
            exit;
        }
    }

    if (empty($first_name) || empty($last_name) || empty($username) || empty($password)) {
        $response = ['success' => false, 'message' => 'Please fill in all the required fields.'];
        echo json_encode($response);
        exit;
    }

    if ($password !== $confirm_password) {
        $response = ['success' => false, 'message' => 'Passwords do not match.'];
        echo json_encode($response);
        exit;
    }

    if (strlen($password) < 8 || strlen($password) > 16) {
        $response = ['success' => false, 'message' => 'Password must be between 8 and 16 characters long.'];
        echo json_encode($response);
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("SELECT id FROM user_staff WHERE email = ?");
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

    // Insert into the database with 'pending' status
    $status = 'pending';
    $stmt = $conn->prepare("INSERT INTO user_staff (first_name, last_name, email, password, department, position, user_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $first_name, $last_name, $username, $hashed_password, $department, $position, $user_type, $status);

    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Registration successful! Your account is pending approval.'];
    } else {
        $response = ['success' => false, 'message' => 'Failed to register user.'];
    }

    $stmt->close();
    $conn->close();
    echo json_encode($response);
}

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $user_type = $_POST['user_type'];
    $username = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $response = ['success' => false, 'message' => 'Passwords do not match.'];
        echo json_encode($response);
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Check user type and set fields accordingly
    if ($user_type === 'staff') {
        $department = $_POST['department'];
        $role = null;
    } else {
        $department = null;
        $role = $_POST['role']; // Non-teaching staff have a role/position instead of a department
    }

    // Prepare the SQL query to insert into the database
    $stmt = $conn->prepare("INSERT INTO user_staff (first_name, last_name, email, password, department, position, user_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $first_name, $last_name, $username, $hashed_password, $department, $role, $user_type);

    // Execute the query
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'User registered successfully!'];
    } else {
        $response = ['success' => false, 'message' => 'Registration failed.'];
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();

    // Return a JSON response
    echo json_encode($response);
}

?>

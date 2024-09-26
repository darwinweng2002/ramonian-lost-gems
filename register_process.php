<?php
include 'config.php'; // Include the database configuration file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data and validate
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $college = trim($_POST['college']);
    $username = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $user_type = $_POST['user_type']; // student, faculty, or staff

    // Initialize variables for conditional fields
    $course = $year = $section = $faculty_department = $staff_position = null;

    // Based on user type, adjust required fields
    if ($user_type === 'student') {
        $course = trim($_POST['course']);
        $year = trim($_POST['year']);
        $section = trim($_POST['section']);
        if (empty($course) || empty($year) || empty($section)) {
            $response = ['success' => false, 'message' => 'Please fill in all the required fields for students.'];
            echo json_encode($response);
            exit;
        }
    } elseif ($user_type === 'faculty') {
        $faculty_department = trim($_POST['faculty_department']);
        if (empty($faculty_department)) {
            $response = ['success' => false, 'message' => 'Please provide the faculty department.'];
            echo json_encode($response);
            exit;
        }
    } elseif ($user_type === 'staff') {
        $staff_position = trim($_POST['staff_position']);
        if (empty($staff_position)) {
            $response = ['success' => false, 'message' => 'Please provide the staff position.'];
            echo json_encode($response);
            exit;
        }
    }

    // Validate common fields
    if (empty($first_name) || empty($last_name) || empty($college) || empty($username) || empty($password)) {
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

    // Prepare the SQL statement to insert new user based on the user_type
    $stmt = $conn->prepare("INSERT INTO user_member 
        (first_name, last_name, college, course, year, section, faculty_department, staff_position, email, password, user_type) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt === false) {
        $response = ['success' => false, 'message' => 'Failed to prepare the database statement.'];
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param("sssssssssss", 
        $first_name, $last_name, $college, 
        $course, $year, $section, 
        $faculty_department, $staff_position, 
        $username, $hashed_password, $user_type);

    // Execute the query and check for success
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Registration successful!'];
    } else {
        $response = ['success' => false, 'message' => 'Failed to register user.'];
    }

    $stmt->close();
    $conn->close();

    // Return a JSON response
    echo json_encode($response);
}
?>

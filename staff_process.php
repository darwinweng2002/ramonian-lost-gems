<?php
include 'config.php'; // Include the database configuration file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $user_type = trim($_POST['user_type']);
    $username = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // File Upload Handling
    $uploadDirectory = 'uploads/ids/';
    $fileName = basename($_FILES['id_file']['name']);
    $targetFilePath = $uploadDirectory . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    // Validate file upload
    if (!empty($fileName)) {
        $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];
        if (in_array(strtolower($fileType), $allowedTypes)) {
            if (move_uploaded_file($_FILES['id_file']['tmp_name'], $targetFilePath)) {
                $id_file = $targetFilePath; // Store the file path for later use
            } else {
                $response = ['success' => false, 'message' => 'Failed to upload the ID file.'];
                echo json_encode($response);
                exit;
            }
        } else {
            $response = ['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, PNG, and PDF are allowed.'];
            echo json_encode($response);
            exit;
        }
    } else {
        $response = ['success' => false, 'message' => 'Please upload your ID.'];
        echo json_encode($response);
        exit;
    }

    // For teaching staff, department is required
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

    // Check if all required fields are provided
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

    // Hash the password before inserting into the database
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Check if the username (email) already exists in the database
    $stmt = $conn->prepare("SELECT id FROM user_staff WHERE email = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $response = ['success' => false, 'message' => 'This email is already registered.'];
        echo json_encode($response);
        $stmt->close();
        $conn->close();
        exit;
    }

    $stmt->close();

    // Prepare the SQL statement to insert new user including the id_file
    $stmt = $conn->prepare("INSERT INTO user_staff (first_name, last_name, email, password, department, position, user_type, id_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        $response = ['success' => false, 'message' => 'Failed to prepare the database statement.'];
        echo json_encode($response);
        exit;
    }

    // Bind parameters including the ID file path
    $stmt->bind_param("ssssssss", $first_name, $last_name, $username, $hashed_password, $department, $position, $user_type, $id_file);

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

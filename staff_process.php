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

  // Handle file upload (profile picture)
$profile_image = '';
$target_dir = "uploads/profiles/"; // Directory to store uploaded images

// Check if the directory exists and create it if not
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true); // Create the directory if it doesn't exist
}

// Check if a file was uploaded
if (!empty($_FILES['profile_image']['name'])) {
    // Create a unique name for the uploaded file to avoid overwriting existing files
    $profile_image = uniqid() . '_' . basename($_FILES['profile_image']['name']);
    $target_file = $target_dir . $profile_image;

    // Move the uploaded file to the server and check if successful
    if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
        echo 'File upload failed!'; // Debugging output
        exit;
    } else {
        echo 'File uploaded successfully to ' . $target_file; // Debugging output
    }
} else {
    echo 'No file uploaded'; // Debugging output
    exit;
}


    // For teaching staff, department is required
    if ($user_type === 'teaching') {
        $department = trim($_POST['department']);
        $position = null; // No need for position in teaching
        if (empty($department)) {
            $response = ['success' => false, 'message' => 'Please enter the department for teaching staff.'];
            echo json_encode($response);
            exit;
        }
    } else {
        // For non-teaching staff, position is required and department is disabled
        $position = trim($_POST['position']);
        $department = null; // No need for department in non-teaching
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

    // Check if the email already exists in the database
    $stmt = $conn->prepare("SELECT id FROM user_staff WHERE email = ?");
    $stmt->bind_param("s", $username);
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

   // Prepare the SQL statement to insert new user (including profile_image)
$stmt = $conn->prepare("INSERT INTO user_staff (first_name, last_name, email, password, department, position, user_type, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

if ($stmt === false) {
    echo 'Failed to prepare the database statement'; // Debugging output
    exit;
}

// Bind parameters including the user_type and profile_image fields
echo 'Profile image to insert: ' . $profile_image; // Debugging output

$stmt->bind_param("ssssssss", $first_name, $last_name, $username, $hashed_password, $department, $position, $user_type, $profile_image);

// Execute the query and check for success
if ($stmt->execute()) {
    echo 'Registration successful!'; // Debugging output
} else {
    echo 'Database insert failed: ' . $stmt->error; // Debugging output
}


    $stmt->close();
    $conn->close();

    // Return a JSON response
    echo json_encode($response);
}
?>

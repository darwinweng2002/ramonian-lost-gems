<?php  
// Include the database configuration file
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the form data is complete
    if (!isset($_POST['first_name'], $_POST['last_name'], $_POST['college'], $_POST['course'], $_POST['year'], $_POST['section'], $_POST['email'], $_POST['password'], $_POST['confirm_password'])) {
        $response = ['success' => false, 'message' => 'Please fill in all the required fields.'];
        echo json_encode($response);
        exit;
    }

    // Retrieve form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $college = $_POST['college'];
    $course = $_POST['course'];
    $year = $_POST['year'];
    $section = $_POST['section'];
    $email = $_POST['email']; // Use email

    // Check if passwords match
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $response = ['success' => false, 'message' => 'Passwords do not match.'];
        echo json_encode($response);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response = ['success' => false, 'message' => 'Invalid email format.'];
        echo json_encode($response);
        exit;
    }

    // Hash the password
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); 

    // Handle file upload (School ID)
    $target_dir = "uploads/school_ids/";
    $school_id_file = $target_dir . basename($_FILES["school_id"]["name"]);
    $imageFileType = strtolower(pathinfo($school_id_file, PATHINFO_EXTENSION));

    // Check if file is a valid image type
    $valid_file_types = ['jpg', 'jpeg', 'png'];
    if (!in_array($imageFileType, $valid_file_types)) {
        $response = ['success' => false, 'message' => 'Invalid file format for school ID. Only JPG, JPEG, and PNG are allowed.'];
        echo json_encode($response);
        exit;
    }

    // Attempt to move the uploaded file
    if (!move_uploaded_file($_FILES["school_id"]["tmp_name"], $school_id_file)) {
        $response = ['success' => false, 'message' => 'Error uploading school ID.'];
        echo json_encode($response);
        exit;
    }

    // Set user status as "pending"
    $status = 'pending';

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO user_member (first_name, last_name, college, course, year, section, email, password, avatar, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssss", $first_name, $last_name, $college, $course, $year, $section, $email, $password, $school_id_file, $status);

    // Execute the query and check for success
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Registration successful! Your account is pending approval.'];
    } else {
        $response = ['success' => false, 'message' => 'Failed to register user.'];
    }

    $stmt->close();
    $conn->close();

    // Return a JSON response
    echo json_encode($response);
}

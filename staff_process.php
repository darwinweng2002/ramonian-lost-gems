<?php
// Enable detailed error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php'; // Include the database configuration file

// Check if the form is submitted via POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Step 1: Ensure that all required form data is set and valid
    if (!isset($_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['password'], $_POST['confirm_password'])) {
        die(json_encode(['success' => false, 'message' => 'Required form fields are missing']));
    }

    // Step 2: Retrieve and sanitize the form data
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $user_type = trim($_POST['user_type']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $department = ($user_type === 'teaching') ? trim($_POST['department']) : null;
    $position = ($user_type !== 'teaching') ? trim($_POST['position']) : null;

    // Step 3: Validate the password
    if ($password !== $confirm_password) {
        die(json_encode(['success' => false, 'message' => 'Passwords do not match.']));
    }

    // Step 4: Hash the password for secure storage
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Step 5: Handle the ID file upload
    $target_dir = "uploads/ids/";
    $school_id_file = basename($_FILES["id_file"]["name"]);
    $imageFileType = strtolower(pathinfo($school_id_file, PATHINFO_EXTENSION));
    $valid_file_types = ['jpg', 'jpeg', 'png', 'pdf'];

    // Step 6: Validate the file type and handle file upload
    if (!in_array($imageFileType, $valid_file_types)) {
        die(json_encode(['success' => false, 'message' => 'Invalid file format for school ID. Only JPG, JPEG, PNG, and PDF are allowed.']));
    }

    // Ensure the target directory exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Generate a unique file name to prevent overwriting files
    $newFileName = md5(time() . $school_id_file) . '.' . $imageFileType;
    $target_file = $target_dir . $newFileName;

    // Attempt to move the uploaded file to the target directory
    if (!move_uploaded_file($_FILES["id_file"]["tmp_name"], $target_file)) {
        die(json_encode(['success' => false, 'message' => 'Error uploading school ID.']));
    }

    // Step 7: Check the database connection
    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
    }

    // Step 8: Check if the email is already registered
    $stmt = $conn->prepare("SELECT id FROM user_staff WHERE email = ?");
    if ($stmt === false) {
        error_log('SQL error during email check: ' . $conn->error);
        die(json_encode(['success' => false, 'message' => 'Failed to prepare statement for checking email.']));
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // If email already exists, return an error
    if ($stmt->num_rows > 0) {
        $stmt->close();
        die(json_encode(['success' => false, 'message' => 'This email is already registered.']));
    }
    $stmt->close();

    // Step 9: Set the status for the new account
    $status = 'pending';

    // Step 10: Prepare the SQL statement to insert a new user
    $stmt = $conn->prepare("INSERT INTO user_staff (first_name, last_name, email, password, department, position, user_type, id_file, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Debugging: Check if SQL preparation is successful
    if ($stmt === false) {
        error_log('SQL error during insert: ' . $conn->error);
        die(json_encode(['success' => false, 'message' => 'Failed to prepare the database statement.']));
    }

    // Step 11: Bind parameters to the SQL query
    $stmt->bind_param("sssssssss", $first_name, $last_name, $email, $hashed_password, $department, $position, $user_type, $newFileName, $status);

    // Step 12: Execute the query and check for success
    if (!$stmt->execute()) {
        error_log('SQL error during execution: ' . $stmt->error);
        die(json_encode(['success' => false, 'message' => 'Failed to register staff member. SQL Error: ' . $stmt->error]));
    }

    // Step 13: Log the successful registration
    error_log('Registration successful for email: ' . $email);

    // Step 14: Close the statement and connection
    $stmt->close();
    $conn->close();

    // Return a JSON response to the client
    echo json_encode(['success' => true, 'message' => 'Registration successful! Your account is pending approval.']);
}
?>

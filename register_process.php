<?php  
// Include the database configuration file
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'config.php';

// Include PHPMailer for email notifications
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Correct path to PHPMailer files
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Log start of registration process
    error_log("Registration process started.");

    // Retrieve common form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $school_type = $_POST['school_type'];  // 1 for College, 0 for High School, 2 for Employee
    $email = $_POST['email'];

    // Handle password validation
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $response = ['success' => false, 'message' => 'Passwords do not match.'];
        echo json_encode($response);
        exit;
    }

    // Hash the password
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); 

    // Validate email or username
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if (strlen($email) < 8 || strlen($email) > 16) {
            $response = ['success' => false, 'message' => 'Username must be between 8 and 16 characters long.'];
            echo json_encode($response);
            exit;
        }
    }

    // Check if email/username is already registered
    $stmt = $conn->prepare("SELECT * FROM user_member WHERE email = ?");
    if (!$stmt) {
        error_log("Database error: Failed to prepare SQL query for user lookup: " . $conn->error);
        $response = ['success' => false, 'message' => 'Database error while checking email.'];
        echo json_encode($response);
        exit;
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($user['status'] === 'approved') {
            $response = ['success' => false, 'message' => 'This email or username is already registered.'];
            echo json_encode($response);
            exit;
        }
    }
    $stmt->close(); // Close the previous statement

    // Handle file upload (School ID)
    $target_dir = "uploads/school_ids/";
    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            error_log("Failed to create upload directory: " . $target_dir);
            $response = ['success' => false, 'message' => 'Error creating upload directory.'];
            echo json_encode($response);
            exit;
        }
    }

    $school_id_file = $target_dir . basename($_FILES["school_id"]["name"]);
    $imageFileType = strtolower(pathinfo($school_id_file, PATHINFO_EXTENSION));
    $valid_file_types = ['jpg', 'jpeg', 'png'];
    if (!in_array($imageFileType, $valid_file_types)) {
        $response = ['success' => false, 'message' => 'Invalid file format for school ID. Only JPG, JPEG, and PNG are allowed.'];
        echo json_encode($response);
        exit;
    }

    if (!move_uploaded_file($_FILES["school_id"]["tmp_name"], $school_id_file)) {
        error_log('File upload error: ' . print_r($_FILES, true));  
        $response = ['success' => false, 'message' => 'Error uploading school ID.'];
        echo json_encode($response);
        exit;
    }

    // Set default values for the following fields to N/A for employees or high school students
    $grade = 'N/A';
    $college = 'N/A';
    $course = 'N/A';
    $year = 'N/A';
    $teaching_status = NULL;
    $department_or_position = NULL;

    // Handling user role-specific fields
    if ($school_type == '1') {  // College selected
        $college = $_POST['college'];
        $course = $_POST['course'];
        $year = $_POST['year'];
        $grade = 'N/A'; // College students do not have grades
    } else if ($school_type == '0') {  // High School selected
        $grade = $_POST['grade'];
    } else if ($school_type == '2') {  // Employee selected
        // Employee-specific fields
        $teaching_status = $_POST['teaching_status'];
        if ($teaching_status == 'Teaching') {
            $department_or_position = $_POST['department']; // Department field for Teaching
        } else if ($teaching_status == 'Non-Teaching') {
            $department_or_position = $_POST['position']; // Position field for Non-Teaching
        }
    }

    // Generate a unique verification token and set expiration (24 hours)
    $verification_token = bin2hex(random_bytes(50));  
    $token_expiration = date("Y-m-d H:i:s", strtotime('+24 hours')); 

    // Set user status as "pending" until verification is complete
    $status = 'pending';

    $stmt = $conn->prepare("INSERT INTO user_member (first_name, last_name, college, course, year, school_type, grade, email, password, school_id_file, status, verification_token, token_expiration, teaching_status, department_or_position) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        error_log("Database error: Failed to prepare SQL query for registration: " . $conn->error);
        $response = ['success' => false, 'message' => 'Database error while registering user.'];
        echo json_encode($response);
        exit;
    }

    // Bind the parameters for the prepared statement
    $stmt->bind_param(
        "sssssssssssssss", 
        $first_name, 
        $last_name, 
        $college, 
        $course, 
        $year, 
        $school_type, 
        $grade, 
        $email, 
        $password, 
        $school_id_file, 
        $status, 
        $verification_token, 
        $token_expiration, 
        $teaching_status, 
        $department_or_position
    );

    // Execute the query and catch any SQL errors
    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        error_log("SQL error: " . $e->getMessage());
        $response = ['success' => false, 'message' => 'Failed to register user.'];
        echo json_encode($response);
        exit;
    }

    // Close the statement after execution
    $stmt->close();

    // Send email to the user with the verification link
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your_gmail_account@gmail.com'; 
        $mail->Password = 'your_gmail_password'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('your_gmail_account@gmail.com', 'Your App Name');
        $mail->addAddress($email);  

        $verification_link = "https://ramonianlostgems.com/verify.php?token=$verification_token";

        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email';
        $mail->Body = "Hello $first_name, <br>Click <a href='$verification_link'>here</a> to verify your email and activate your account.";

        $mail->send();
    } catch (Exception $e) {
        error_log("Mailer error: " . $e->getMessage());
        // Optionally handle failure of email notification here
    }

    // Final response after successful registration
    $response = ['success' => true, 'message' => 'Your registration was successful! Please wait for the admin to review and approve your account.'];
    echo json_encode($response);
}
?>

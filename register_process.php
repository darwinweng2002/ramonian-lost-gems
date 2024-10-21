<?php  
// Include the database configuration file
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'config.php';

// Include PHPMailer for email notifications (add PHPMailer to your project)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Correct path to PHPMailer files
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Log start of registration process
    error_log("Registration process started.");

    // Retrieve form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $school_type = $_POST['school_type'];  // 1 for College, 0 for High School
    $grade = $_POST['grade'];
    $email = $_POST['email'];

    // For College Students
    if ($school_type == '1') {  // College selected
        $college = $_POST['college'];
        $course = $_POST['course'];
        $year = $_POST['year'];
    } else {  // High School selected, set College-related fields to N/A
        $college = 'N/A';
        $course = 'N/A';
        $year = 'N/A';
    }

    // Validate if passwords match
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $response = ['success' => false, 'message' => 'Passwords do not match.'];
        echo json_encode($response);
        exit;
    }

    // Hash the password
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); 

    // Check if the input is either an email or a valid username (8-16 characters)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // If it's not an email, check if it's a valid username
        if (strlen($email) < 8 || strlen($email) > 16) {
            $response = ['success' => false, 'message' => 'Username must be between 8 and 16 characters long.'];
            echo json_encode($response);
            exit;
        }
    }

    // Check if the email/username is already registered
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

    // Check if file is a valid image type
    $valid_file_types = ['jpg', 'jpeg', 'png'];
    if (!in_array($imageFileType, $valid_file_types)) {
        $response = ['success' => false, 'message' => 'Invalid file format for school ID. Only JPG, JPEG, and PNG are allowed.'];
        echo json_encode($response);
        exit;
    }

    // Attempt to move the uploaded file
    if (!move_uploaded_file($_FILES["school_id"]["tmp_name"], $school_id_file)) {
        error_log('File upload error: ' . print_r($_FILES, true));  // Logs detailed error in PHP error logs
        $response = ['success' => false, 'message' => 'Error uploading school ID.'];
        echo json_encode($response);
        exit;
    }

    // Generate a unique verification token and set expiration (24 hours)
    $verification_token = bin2hex(random_bytes(50));  // Generate token
    $token_expiration = date("Y-m-d H:i:s", strtotime('+24 hours')); // Set expiration time

    // Set user status as "pending" until verification is complete
    $status = 'pending';

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO user_member (first_name, last_name, college, course, year, school_type, grade, email, password, school_id_file, status, verification_token, token_expiration) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        error_log("Database error: Failed to prepare SQL query for registration: " . $conn->error);
        $response = ['success' => false, 'message' => 'Database error while registering user.'];
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param("sssssssssssss", $first_name, $last_name, $college, $course, $year, $school_type, $grade, $email, $password, $school_id_file, $status, $verification_token, $token_expiration);

    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        error_log("SQL error: " . $e->getMessage());
        $response = ['success' => false, 'message' => 'Failed to register user.'];
        echo json_encode($response);
        exit;
    }

    // Close statement
    $stmt->close();

    // Send email to the user with the verification link
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your_gmail_account@gmail.com'; // Add your Gmail account
        $mail->Password = 'your_gmail_password'; // Add your Gmail password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('your_gmail_account@gmail.com', 'Your App Name');
        $mail->addAddress($email);  // Add user email address

        $verification_link = "https://ramonianlostgems.com/verify.php?token=$verification_token";

        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email';
        $mail->Body = "Hello $first_name, <br>Click <a href='$verification_link'>here</a> to verify your email and activate your account.";

        $mail->send();
    } catch (Exception $e) {
        error_log("Mailer error: " . $e->getMessage());
        // You can choose to continue even if email fails to send
    }

    // Final response after successful registration
    $response = ['success' => true, 'message' => 'Your registration was successful! Please wait for the admin to review and approve your account.'];
    echo json_encode($response);
}
?>

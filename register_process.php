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
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
   // Retrieve form data
$username = $_POST['email'];  // Now used for username (can be traditional or email)
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$college = $_POST['college'];
$course = $_POST['course'];
$year = $_POST['year'];
$school_type = $_POST['school_type'];
$grade = $_POST['grade'];

// Validate username (either email or traditional username)
$emailPattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
$usernamePattern = '/^[a-zA-Z0-9]{8,16}$/';  // Traditional username: 8-16 characters

if (!preg_match($emailPattern, $username) && !preg_match($usernamePattern, $username)) {
    $response = ['success' => false, 'message' => 'Invalid username. Must be a valid email or a traditional username (8-16 alphanumeric characters).'];
    echo json_encode($response);
    exit;
}

// Hash the password
$password = password_hash($_POST['password'], PASSWORD_BCRYPT);

// Check if the username is already registered
$stmt = $conn->prepare("SELECT * FROM user_member WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if ($user['status'] === 'approved') {
        $response = ['success' => false, 'message' => 'This account is already registered.'];
        echo json_encode($response);
        exit;
    }
}
$stmt->close();


    // Attempt to move the uploaded file
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);  // Ensure the target directory exists
    }

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

    try {
        // Prepare the SQL statement
        $stmt = $conn->prepare("INSERT INTO user_member (first_name, last_name, college, course, year, school_type, grade, email, password, school_id_file, status, verification_token, token_expiration) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssssss", $first_name, $last_name, $college, $course, $year, $school_type, $grade, $email, $password, $school_id_file, $status, $verification_token, $token_expiration);

        // Execute the query
        $stmt->execute();

        // Send email to the user with the verification link
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'vdarwin860@gmail.com'; // Add your Gmail account
            $mail->Password = ''; // Add your Gmail password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('vdarwin860@gmail.com', 'Your App Name');
            $mail->addAddress($email);  // Add user email address

            $verification_link = "https://ramonianlostgems.com/verify.php?token=$verification_token";

            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Email';
            $mail->Body    = "Hello $first_name, <br>Click <a href='$verification_link'>here</a> to verify your email and activate your account.";

            $mail->send();
        } catch (Exception $e) {
            // Handle email sending error
        }

        $response = ['success' => true, 'message' => 'Your registration was successful! Please wait for the admin to review and approve your account. Once your account is approved, you will be able to log in. Thank you for your patience.'];
    } catch (mysqli_sql_exception $e) {
        // Handle duplicate email error
        if ($e->getCode() == 1062) {  // Duplicate entry error code in MySQL
            $response = ['success' => false, 'message' => 'This email is already registered. Please use a different email.'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to register user. Please try again later.'];
        }
    }

    // Close statement without closing connection
    $stmt->close();

    // Return a JSON response
    echo json_encode($response);
}
?>
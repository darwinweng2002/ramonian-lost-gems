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
    $username = $_POST['email']; // email field now serves as the username
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $college = $_POST['college'];
    $course = $_POST['course'];
    $year = $_POST['year'];
    $school_type = $_POST['school_type'];
    $grade = $_POST['grade'];

    // Validate username: 8-16 characters, no full numeric usernames
    if (strlen($username) < 8 || strlen($username) > 16) {
        $response = ['success' => false, 'message' => 'Username must be between 8 and 16 characters.'];
        echo json_encode($response);
        exit;
    }

    // Disallow fully numeric usernames
    if (preg_match('/^\d+$/', $username)) {
        $response = ['success' => false, 'message' => 'Username cannot consist entirely of numbers. Please include letters.'];
        echo json_encode($response);
        exit;
    }

    // Validate password: must be 8-16 characters, include uppercase, lowercase, number, and special character
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{8,16}$/', $password)) {
        $response = ['success' => false, 'message' => 'Password must be 8-16 characters long, include at least one uppercase letter, one lowercase letter, one number, and one special character.'];
        echo json_encode($response);
        exit;
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        $response = ['success' => false, 'message' => 'Passwords do not match.'];
        echo json_encode($response);
        exit;
    }

    // Hash the password
    $password_hashed = password_hash($password, PASSWORD_BCRYPT); 

    // Check if the username (email) is already registered and approved
    $stmt = $conn->prepare("SELECT * FROM user_member WHERE email = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($user['status'] === 'approved') {
            // If username is already registered and account is approved
            $response = ['success' => false, 'message' => 'This account is already registered.'];
            echo json_encode($response);
            exit;
        }
    }
    $stmt->close(); // Close the previous statement

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
        $stmt->bind_param("sssssssssssss", $first_name, $last_name, $college, $course, $year, $school_type, $grade, $username, $password_hashed, $school_id_file, $status, $verification_token, $token_expiration);

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
            $mail->addAddress($username);  // Add user email address

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

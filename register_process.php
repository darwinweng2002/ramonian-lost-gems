<?php
// Include the database configuration file
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'config.php';

// Include PHPMailer for email notifications
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $user_type = $_POST['user_type'];  // Capture the user type (student, junior_high, senior_high)
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];

    // Check if passwords match
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $response = ['success' => false, 'message' => 'Passwords do not match.'];
        echo json_encode($response);
        exit;
    }
    
    // Hash the password
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Handle file upload for School ID
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

    // Ensure the target directory exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Attempt to move the uploaded file
    if (!move_uploaded_file($_FILES["school_id"]["tmp_name"], $school_id_file)) {
        error_log('File upload error: ' . print_r($_FILES, true));  // Log detailed error in PHP logs
        $response = ['success' => false, 'message' => 'Error uploading school ID.'];
        echo json_encode($response);
        exit;
    }

    // Set user status as "pending"
    $status = 'pending';

    // Generate a unique verification token and set expiration (24 hours)
    $verification_token = bin2hex(random_bytes(50));
    $token_expiration = date("Y-m-d H:i:s", strtotime('+24 hours'));

    try {
        // Prepare SQL query based on user type
        if ($user_type === 'student') {
            // For college students, handle college, course, year, section
            $college = $_POST['college'];
            $course = $_POST['course'];
            $year = $_POST['year'];
            $section = $_POST['section'];
            $stmt = $conn->prepare("INSERT INTO user_member 
                (first_name, last_name, college, course, year, section, email, password, school_id_file, user_type, status, verification_token, token_expiration) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssssssss", $first_name, $last_name, $college, $course, $year, $section, $email, $password, $school_id_file, $user_type, $status, $verification_token, $token_expiration);
        } elseif ($user_type === 'junior_high' || $user_type === 'senior_high') {
            // For junior or senior high, store grade (and track/strand for senior high)
            $grade = $_POST['grade'];
            $track_or_strand = ($user_type === 'senior_high') ? $_POST['track_or_strand'] : null;
            $stmt = $conn->prepare("INSERT INTO user_member 
                (first_name, last_name, grade, track_or_strand, email, password, school_id_file, user_type, status, verification_token, token_expiration) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssssss", $first_name, $last_name, $grade, $track_or_strand, $email, $password, $school_id_file, $user_type, $status, $verification_token, $token_expiration);
        }

        // Execute the query
        if ($stmt->execute()) {
            // Send email to the user with the verification link
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = '';  // Add your Gmail account
                $mail->Password = '';  // Add your Gmail password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('your_gmail_account@gmail.com', 'Your App Name');
                $mail->addAddress($email);  // Add user email address

                $verification_link = "https://yourdomain.com/verify.php?token=$verification_token";

                $mail->isHTML(true);
                $mail->Subject = 'Verify Your Email';
                $mail->Body = "Hello $first_name, <br>Click <a href='$verification_link'>here</a> to verify your email and activate your account.";

                $mail->send();
            } catch (Exception $e) {
                // Handle email sending error
                error_log("Email sending failed: " . $mail->ErrorInfo);
            }

            $response = ['success' => true, 'message' => 'Successfully registered! Please check your email to verify your account.'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to register user.'];
        }
    } catch (mysqli_sql_exception $e) {
        // Handle duplicate email error
        if ($e->getCode() == 1062) {
            $response = ['success' => false, 'message' => 'This email is already registered. Please use a different email.'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to register user. Please try again later.'];
        }
    }

    // Close statement
    $stmt->close();
    $conn->close();

    // Return a JSON response
    echo json_encode($response);
}
?>

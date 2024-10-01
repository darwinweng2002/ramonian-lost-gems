<?php 
// Include the database configuration file
include 'config.php'; 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include PHPMailer for email notifications
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize form data
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $college = isset($_POST['college']) ? $conn->real_escape_string($_POST['college']) : NULL;
    $course = isset($_POST['course']) ? $conn->real_escape_string($_POST['course']) : NULL;
    $year = isset($_POST['year']) ? $conn->real_escape_string($_POST['year']) : NULL;
    $section = isset($_POST['section']) ? $conn->real_escape_string($_POST['section']) : NULL;
    $grade = isset($_POST['grade']) ? $conn->real_escape_string($_POST['grade']) : NULL;
    $school_type = $conn->real_escape_string($_POST['school_type']);
    $email = $conn->real_escape_string($_POST['email']);
  
    // Check if passwords match
    if ($_POST['password'] !== $_POST['confirm_password']) {
        echo "Passwords do not match.";
        error_log('Passwords do not match.');
        $response = ['success' => false, 'message' => 'Passwords do not match.'];
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
        echo "Invalid file format for school ID.";
        error_log('Invalid file format for school ID.');
        $response = ['success' => false, 'message' => 'Invalid file format for school ID. Only JPG, JPEG, and PNG are allowed.'];
        echo json_encode($response);
        exit;
    }

    // Attempt to move the uploaded file
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);  // Ensure the target directory exists
    }

    if (!move_uploaded_file($_FILES["school_id"]["tmp_name"], $school_id_file)) {
        echo "Error uploading school ID.";
        error_log('File upload error: ' . print_r($_FILES, true));
        $response = ['success' => false, 'message' => 'Error uploading school ID.'];
        echo json_encode($response);
        exit;
    }

    // Generate a unique verification token and set expiration (24 hours)
    $verification_token = bin2hex(random_bytes(50));  // Generate token
    $token_expiration = date("Y-m-d H:i:s", strtotime('+24 hours')); // Set expiration time

    // Set user status as "pending" until verification is complete
    $status = 'pending';

    // Debug: Print out variables before SQL
    echo "<pre>";
    echo "First Name: $first_name\n";
    echo "Last Name: $last_name\n";
    echo "Email: $email\n";
    echo "School Type: $school_type\n";
    echo "</pre>";

    try {
        // Prepare the SQL statement
        $stmt = $conn->prepare("INSERT INTO user_member 
            (first_name, last_name, college, course, year, section, grade, school_type, email, password, school_id_file, status, verification_token, token_expiration) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            // If the statement preparation fails
            die('Prepare failed: (' . $conn->errno . ') ' . $conn->error);
        }

        // Debug: Check if parameters are being bound correctly
        echo "SQL Query prepared successfully!";

        $stmt->bind_param("sssssssssssss", $first_name, $last_name, $college, $course, $year, $section, $grade, $school_type, $email, $password, $school_id_file, $status, $verification_token, $token_expiration);

        // Execute the query
        if ($stmt->execute()) {
            echo "SQL Query executed successfully!";
            // Send email to the user with the verification link
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'your_email@gmail.com'; // Add your Gmail account
                $mail->Password = 'your_password'; // Add your Gmail password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('your_email@gmail.com', 'Your App Name');
                $mail->addAddress($email);  // Add user email address

                $verification_link = "https://yourdomain.com/verify.php?token=$verification_token";

                $mail->isHTML(true);
                $mail->Subject = 'Verify Your Email';
                $mail->Body    = "Hello $first_name, <br>Click <a href='$verification_link'>here</a> to verify your email and activate your account.";

                $mail->send();
            } catch (Exception $e) {
                error_log('Mail Error: ' . $mail->ErrorInfo);
            }

            $response = ['success' => true, 'message' => 'Your registration was successful! Please wait for the admin to review and approve your account. Once your account is approved, you will be able to log in.'];
        } else {
            echo "SQL Query failed!";
            $response = ['success' => false, 'message' => 'Failed to register user.'];
            error_log('Execute failed: (' . $stmt->errno . ') ' . $stmt->error);
        }
    } catch (mysqli_sql_exception $e) {
        // Handle duplicate email error
        if ($e->getCode() == 1062) {  // Duplicate entry error code in MySQL
            $response = ['success' => false, 'message' => 'This email is already registered. Please use a different email.'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to register user. Please try again later.'];
        }
        error_log('MySQL Error: ' . $e->getMessage());
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();

    // Return a JSON response
    echo json_encode($response);
}
?>

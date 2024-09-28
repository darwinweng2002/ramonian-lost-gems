<?php  
// Include the database configuration file
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
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $college = $_POST['college'];
    $course = $_POST['course'];
    $year = $_POST['year'];
    $section = $_POST['section'];
    $email = $_POST['email']; // Use email as username for now

    // Check if passwords match
    if ($_POST['password'] !== $_POST['confirm_password']) {
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

    // Check if file is a valid image type by MIME type
    $valid_mime_types = ['image/jpeg', 'image/png'];
    $file_mime_type = mime_content_type($_FILES["school_id"]["tmp_name"]);

    if (!in_array($file_mime_type, $valid_mime_types)) {
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
        // Send email to the user notifying them that the registration is successful and awaiting approval
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
            $mail->addAddress($email);  // Add user email address

            $mail->isHTML(true);
            $mail->Subject = 'Account Registration Pending Approval';
            $mail->Body    = "Hello $first_name, <br>Your registration was successful! Please wait for the admin to approve your account. You will receive an email once it's approved and ready for login.";

            $mail->send();
        } catch (Exception $e) {
            // Handle email sending error
        }

        $response = ['success' => true, 'message' => 'Successfully registered! Please wait for admin approval. You will receive an email once your account is approved.'];
    } else {
        $response = ['success' => false, 'message' => 'Failed to register user.'];
    }

    $stmt->close();
    $conn->close();

    // Return a JSON response
    echo json_encode($response);
}
?>

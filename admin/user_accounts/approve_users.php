<?php
include '../../config.php';  // Ensure config.php sets up $conn correctly

// Include PHPMailer for email notifications
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Correct path to PHPMailer files
require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

    if ($user_id > 0) {
        // Get the user's details before approval
        $stmt = $conn->prepare("SELECT first_name, email FROM user_member WHERE id = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error); // Log prepare error
            echo '0';
            exit;
        }
        
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            error_log("User not found or no email found for user_id: " . $user_id);
            echo '0'; // User not found
            exit;
        }

        // Proceed with updating the user's status to 'approved'
        $stmt = $conn->prepare("UPDATE user_member SET status = 'approved' WHERE id = ?");
        if (!$stmt) {
            error_log("Prepare failed for update query: " . $conn->error); // Log prepare error
            echo '0';
            exit;
        }

        $stmt->bind_param('i', $user_id);

        if ($stmt->execute()) {
            $stmt->close();

            // Prepare to send the approval email
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'mail.smtp2go.com'; // Replace with your SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'ran_ramonian'; // Replace with your email
                $mail->Password = 'test123456'; // Replace with your email password or app-specific password
                $mail->SMTPSecure = 'tls';
                $mail->Port = 2525;

                // Recipients
                $mail->setFrom('admin@ramonianlostgems.com', 'Admin of Ramonian Lost Gems'); // Replace with your sender email
                $mail->addAddress($user['email'], $user['first_name']);  // Add user's email

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Account Approved';
                $mail->Body = "Dear " . htmlspecialchars($user['first_name']) . ",<br>Your account has been approved by the admin. You can now log in to your account.<br><br>Regards,<br>Admin";

                // Send the email
                $mail->send();

                echo '1'; // Success
            } catch (Exception $e) {
                // Log the error and return a failure response
                error_log("PHPMailer Error: " . $mail->ErrorInfo);
                echo '0'; // Failure (email sending failed)
            }
        } else {
            error_log("Database update error: " . $stmt->error); // Log update query error
            echo '0'; // Failure (query failed)
            $stmt->close();
        }
    } else {
        error_log("Invalid user ID: " . $user_id); // Log invalid user ID
        echo '0'; // Failure (invalid user ID)
    }

    $conn->close();
}
?>

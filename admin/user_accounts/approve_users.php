<?php
include '../../config.php';

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
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        // Proceed with updating the user's status to 'approved'
        $stmt = $conn->prepare("UPDATE user_member SET status = 'approved' WHERE id = ?");
        $stmt->bind_param('i', $user_id);

        if ($stmt->execute()) {
            // Prepare to send the approval email
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'vdarwin860@gmail.com'; // Replace with your email
                $mail->Password = 'ybve xumi zutn nmro'; // Replace with your email password or app-specific password
                $mail->SMTPSecure =  'ssl';
                $mail->Port = 465;

                // Recipients
                $mail->setFrom('vdarwin860@gmail.com', 'Admin of Ramonian Lost Gems'); // Replace with your sender email
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
            echo '0'; // Failure (query failed)
        }
        $stmt->close();
    } else {
        echo '0'; // Failure (invalid user ID)
    }

    $conn->close();
}
?>
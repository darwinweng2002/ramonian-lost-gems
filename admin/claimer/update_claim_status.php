<?php
// Include database configuration and PHPMailer
include '../../config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $claimId = intval($_POST['claim_id']);
    $new_status = $_POST['status'];

    // Fetch claimant's email and item name to send the email
    $fetch_sql = "
        SELECT c.id, c.item_id, mh.title AS item_name, COALESCE(um.email, us.email) AS email, 
               COALESCE(um.first_name, us.first_name) AS first_name, 
               COALESCE(um.last_name, us.last_name) AS last_name
        FROM claimer c
        LEFT JOIN message_history mh ON c.item_id = mh.id
        LEFT JOIN user_member um ON c.user_id = um.id
        LEFT JOIN user_staff us ON c.user_id = us.id
        WHERE c.id = ?
    ";

    $stmt_fetch = $conn->prepare($fetch_sql);
    $stmt_fetch->bind_param('i', $claimId);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $claimantEmail = $row['email'];
        $itemName = $row['item_name'];
        $claimantName = $row['first_name'] . ' ' . $row['last_name'];

        // Update claim status in the database
        $update_sql = "UPDATE claimer SET status = ? WHERE id = ?";
        $stmt_update = $conn->prepare($update_sql);
        $stmt_update->bind_param('si', $new_status, $claimId);

        if ($stmt_update->execute()) {
            // Prepare the email content based on the new status
            $status_message = '';
            $subject = '';

            if ($new_status === 'approved') {
                $subject = 'Your Claim Request has been Approved';
                $status_message = "
                    Hello {$claimantName},<br><br>
                    Your claim request for the item <strong>{$itemName}</strong> has been approved.<br>
                    Please proceed to the OSA Building 3rd floor Student Organization Office for actual verification and please bring your ID.<br><br>
                    Thank you.
                ";
            } elseif ($new_status === 'rejected') {
                $subject = 'Your Claim Request has been Rejected';
                $status_message = "
                    Hello {$claimantName},<br><br>
                    Unfortunately, your claim request for the item <strong>{$itemName}</strong> has been rejected.<br><br>
                    Thank you for your understanding.
                ";
            } elseif ($new_status === 'claimed') {
                $subject = 'Your Claim Request Status Updated';
                $status_message = "
                    Hello {$claimantName},<br><br>
                    Your claim request status for the item <strong>{$itemName}</strong> has been updated to <strong>claimed</strong>.<br><br>
                    Thank you.
                ";
            }

            // Initialize PHPMailer and send the email
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'mail.smtp2go.com';  // Replace with your SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'ran_ramonian'; // Your SMTP username
                $mail->Password = 'test123456';   // Your SMTP password
                $mail->SMTPSecure = 'tls';
                $mail->Port = 2525;  // SMTP port

                // Recipients
                $mail->setFrom('admin@ramonianlostgems.com', 'Ramonian Lost Gems');
                $mail->addAddress($claimantEmail); // Add claimant's email

                // Content
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $status_message;

                // Send the email
                $mail->send();

                // Return success response
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                // Log the error
                error_log("Mailer Error: " . $mail->ErrorInfo);
                echo json_encode(['success' => false, 'error' => 'Failed to send email notification.']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update status.']);
        }

        $stmt_update->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Claim not found.']);
    }

    $stmt_fetch->close();
}

$conn->close();
?>

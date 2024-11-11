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
            // Initialize PHPMailer
            $mail = new PHPMailer(true);

            // Prepare the email content based on the new status
            if ($new_status === 'approved') {
                $subject = "Claim Request Approved";
                $status_message = "
                    Hi {$claimantName},<br><br>
                    Great news! Your request to claim the item '<strong>{$itemName}</strong>' has been <strong>approved</strong>.<br>
                    Please visit the Student Organization Office on the 3rd floor of the OSA Building for verification. The address is PRMSU Iba Campus, Zone 6, Iba, Zambales. Remember to bring your ID for identification.<br><br>
                    Thank you for using Ramonian Lost Gems to reunite with your lost items!<br><br>
                    Best regards,<br>
                    Ramonian Lost Gems Admin
                ";
            } elseif ($new_status === 'rejected') {
                $subject = "Claim Request Rejected";
                $status_message = "
                    Hi {$claimantName},<br><br>
                    We wanted to let you know that your request to claim the item '<strong>{$itemName}</strong>' has been <strong>rejected</strong>.<br><br>
                    Thank you for your understanding, and feel free to reach out if you have any questions.<br><br>
                    Best,<br>
                    Ramonian Lost Gems Admin
                ";
            } elseif ($new_status === 'claimed') {
                $subject = "Item Claimed";
                $status_message = "
                    Hi {$claimantName},<br><br>
                    We’re happy to inform you that the status of your requested item, '<strong>{$itemName}</strong>,' has been updated to <strong>claimed</strong>.<br><br>
                    Thank you for helping us reconnect items with their owners!<br><br>
                    Best,<br>
                    Ramonian Lost Gems Admin
                ";
            }

            try {
                // PHPMailer configuration
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'vdarwin860@gmail.com';
                $mail->Password = 'ybve xumi zutn nmro';
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;
                $mail->SMTPDebug = 2;  // Set to 0 to disable debug output

                // Email content
                $mail->setFrom('admin@ramonianlostgems.com', 'Ramonian Lost Gems');
                $mail->addAddress($claimantEmail);

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $status_message;
                $mail->AltBody = strip_tags($status_message);

                $mail->send();

                echo json_encode(['success' => true]);
            } catch (Exception $e) {
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

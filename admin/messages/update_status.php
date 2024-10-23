<?php
include '../../config.php';

// Include PHPMailer for email notifications
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the posted data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = intval($_POST['id']);
    $newStatus = intval($_POST['status']);
    
    // Check that the status is valid (between 0 and 4)
    if ($newStatus >= 0 && $newStatus <= 4) {
        // Fetch the reporter's email and item title for the email notification
        $stmtFetch = $conn->prepare("SELECT mh.title, mh.status, user_info.email, user_info.first_name
                                     FROM message_history mh
                                     LEFT JOIN (
                                         SELECT id AS user_id, email, first_name FROM user_member
                                         UNION
                                         SELECT id AS user_id, email, first_name FROM user_staff
                                     ) AS user_info ON mh.user_id = user_info.user_id
                                     WHERE mh.id = ?");
        $stmtFetch->bind_param('i', $itemId);
        $stmtFetch->execute();
        $stmtFetch->bind_result($itemTitle, $currentStatus, $reporterEmail, $reporterName);
        $stmtFetch->fetch();
        $stmtFetch->close();

        // Update the status in the database
        $stmt = $conn->prepare("UPDATE message_history SET status = ? WHERE id = ?");
        $stmt->bind_param('ii', $newStatus, $itemId);
        
        if ($stmt->execute()) {
            // If the item is published, also update the category status
            if ($newStatus == 1) { // 1 = Published
                // Set category status to 'published' when the item is published
                $stmtCategory = $conn->prepare("UPDATE categories SET status = 1 
                                                WHERE id = (SELECT category_id FROM message_history WHERE id = ? AND user_id IS NOT NULL)");
                $stmtCategory->bind_param("i", $itemId);
                $stmtCategory->execute();
                $stmtCategory->close();
            }

            // Determine the status text
            $statusText = '';
            switch ($newStatus) {
                case 1:
                    $statusText = 'Published';
                    break;
                case 2:
                    $statusText = 'Claimed';
                    break;
                case 3:
                    $statusText = 'Surrendered';
                    break;
                case 4:
                    $statusText = 'Denied';
                    break;
                default:
                    $statusText = 'Pending';
                    break;
            }

            // Send email notification to the reporter
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'mail.smtp2go.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'ran_ramonian'; // Your email
                $mail->Password = 'test123456'; // Your email password
                $mail->SMTPSecure = 'tls';
                $mail->Port = 2525;

                // Sender and recipient settings
                $mail->setFrom('admin@ramonianlostgems.com', 'Ramonian Lost Gems'); // Your app name
                $mail->addAddress($reporterEmail, $reporterName); // Send to the reporter

                // Email content
                $mail->isHTML(true);
                $mail->Subject = 'Item Status Update: ' . $itemTitle;
                $mail->Body    = "Hello $reporterName, <br><br>Your reported item titled '<strong>$itemTitle</strong>' has been updated to the status of '<strong>$statusText</strong>'.<br><br>Thank you for your contribution.";

                $mail->send();
            } catch (Exception $e) {
                error_log("Mailer Error: " . $mail->ErrorInfo);
            }

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid status value']);
    }
}

$conn->close();
?>

<?php
include '../../config.php';

// Include PHPMailer for sending email notifications
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['id']) && isset($_POST['status'])) {
    $itemId = intval($_POST['id']);
    $status = intval($_POST['status']);

    // Get reporter's email and name before updating the status
    $reporterSql = "
        SELECT mi.title, mi.status, user_info.email, user_info.first_name
        FROM missing_items mi
        LEFT JOIN (
            SELECT id AS user_id, email, first_name FROM user_member
            UNION
            SELECT id AS user_id, email, first_name FROM user_staff
        ) AS user_info ON mi.user_id = user_info.user_id
        WHERE mi.id = ? LIMIT 1
    ";
    $stmt = $conn->prepare($reporterSql);
    $stmt->bind_param('i', $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    $reporter = $result->fetch_assoc();
    
    if ($reporter && $reporter['email']) {
        $reporterEmail = $reporter['email'];
        $reporterName = $reporter['first_name'];
        $itemTitle = $reporter['title'];

        // Update status in the database
        $stmt = $conn->prepare("UPDATE missing_items SET status = ? WHERE id = ?");
        $stmt->bind_param('ii', $status, $itemId);
        
        if ($stmt->execute()) {
            // Handle category status updates
            if ($status == 1) { // Published
                $stmtCategory = $conn->prepare("UPDATE categories SET status = 1 
                                                WHERE id = (SELECT category_id FROM missing_items WHERE id = ? AND user_id IS NOT NULL)");
                $stmtCategory->bind_param("i", $itemId);
                $stmtCategory->execute();
                $stmtCategory->close();
            } else {
                // For pending, claimed, surrendered, and denied statuses
                $stmtCategory = $conn->prepare("UPDATE categories SET status = 0 
                                                WHERE id = (SELECT category_id FROM missing_items WHERE id = ? AND user_id IS NOT NULL)");
                $stmtCategory->bind_param("i", $itemId);
                $stmtCategory->execute();
                $stmtCategory->close();
            }

            // Determine the status message for the email
            $statusMessage = '';
            switch ($status) {
                case 1: $statusMessage = "Published"; break;
                case 2: $statusMessage = "Claimed"; break;
                case 3: $statusMessage = "Surrendered"; break;
                case 4: $statusMessage = "Denied"; break;
                default: $statusMessage = "Pending"; break;
            }

            // Prepare email notification
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'mail.smtp2go.com'; // Set your SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'ran_ramonian'; // Your email
                $mail->Password = 'test123456'; // Your email password
                $mail->SMTPSecure = 'tls';
                $mail->Port = 2525;
            
                // Recipients
                $mail->setFrom('admin@ramonianlostgems.com', 'Ramonian Lost Gems'); // Change this to your "From" email
                $mail->addAddress($reporterEmail); // The reporter's email
            
                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Status Update for Your Reported Item: ' . $itemTitle;
            
                // Ensure we use $status (the correct variable) instead of $newStatus
                if ($status == 1) { // Published
                    $mail->Body = "
                        Dear $reporterName, <br><br>
                        We are pleased to inform you that your reported found item titled '<strong>$itemTitle</strong>' has been <strong>published</strong> by the admins. 
                        Your report is now visible to other users on our mobile application. Thank you for helping us reconnect lost items with their owners.<br><br>
                        Best regards,<br>
                        Admins of Ramonian Lost Gems
                    ";
                } elseif ($status == 2) { // Claimed
                    $mail->Body = "
                        Dear $reporterName, <br><br>
                        We are happy to inform you that your reported found item titled '<strong>$itemTitle</strong>' has been successfully <strong>claimed</strong> by its rightful owner. 
                        Thank you for your valuable contribution in helping us return lost items.<br><br>
                        Best regards,<br>
                        Admins of Ramonian Lost Gems
                    ";
                } elseif ($status == 3) { // Surrendered
                    $mail->Body = "
                        Dear $reporterName, <br><br>
                        We would like to inform you that your reported found item titled '<strong>$itemTitle</strong>' has been <strong>surrendered</strong> to the SSG officers for safekeeping. 
                        Thank you for your efforts in reporting and submitting this item.<br><br>
                        Best regards,<br>
                        Admins of Ramonian Lost Gems
                    ";
                } elseif ($status == 4) { // Denied
                    $mail->Body = "
                        Dear $reporterName, <br><br>
                        We regret to inform you that your reported found item titled '<strong>$itemTitle</strong>' has been <strong>denied</strong> by our review team. 
                        This could be due to various reasons, such as insufficient information or the item not fitting our criteria. 
                        We sincerely apologize for any inconvenience this may have caused, and we encourage you to review the guidelines and submit a new report if applicable.<br><br>
                        Best regards,<br>
                        Admins of Ramonian Lost Gems
                    ";
                } else { // Pending (Fallback in case)
                    $mail->Body = "
                        Dear $reporterName, <br><br>
                        The status of your reported found item titled '<strong>$itemTitle</strong>' is currently <strong>pending</strong>. 
                        The admins of Ramonian Lost Gems are reviewing your report and will update you once a decision has been made.<br><br>
                        Best regards,<br>
                        Admins of Ramonian Lost Gems
                    ";
                }
                

                $mail->send();
            } catch (Exception $e) {
                error_log('Mail error: ' . $mail->ErrorInfo);
            }

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Reporter not found or email is missing']);
    }
}

$conn->close();
?>

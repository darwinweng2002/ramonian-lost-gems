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
                $stmtCategory = $conn->prepare("UPDATE categories SET status = 1 
                                                WHERE id = (SELECT category_id FROM message_history WHERE id = ? AND user_id IS NOT NULL)");
                $stmtCategory->bind_param("i", $itemId);
                $stmtCategory->execute();
                $stmtCategory->close();
            }

            // Determine the status text for the email notification and JSON response
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
                $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'vdarwin860@gmail.com'; // Replace with your email
                $mail->Password = 'ybve xumi zutn nmro'; // Replace with your email password or app-specific password
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;

                // Sender and recipient settings
                $mail->setFrom('admin@ramonianlostgems.com', 'Ramonian Lost Gems'); // Your app name
                $mail->addAddress($reporterEmail, $reporterName); // Send to the reporter

                // Email content based on status update
                $mail->isHTML(true);
                $mail->Subject = 'Update on Your Reported Item: ' . $itemTitle;
                
                // Compose email body based on status
                switch ($newStatus) {
                    case 1: // Published
                        $mail->Body = "
                            Hello $reporterName,<br><br>
                            The item you reported, '<strong>$itemTitle</strong>,' has now been <strong>published</strong> on our app. Thank you for helping reconnect lost items with their owners.<br><br>
                            Best regards,<br>
                            Ramonian Lost Gems Admin
                        ";
                        break;
                    case 2: // Claimed
                        $mail->Body = "
                            Hello $reporterName,<br><br>
                            We're happy to let you know that the item you reported, '<strong>$itemTitle</strong>,' has been <strong>claimed</strong> by its rightful owner. Thank you for your contribution!<br><br>
                            Regards,<br>
                            Ramonian Lost Gems Admin
                        ";
                        break;
                    case 3: // Surrendered
                        $mail->Body = "
                            Hi $reporterName,<br><br>
                            The item titled '<strong>$itemTitle</strong>' has been <strong>surrendered</strong> to the SSG officers for safekeeping. Thank you for reporting this item.<br><br>
                            Kind regards,<br>
                            Ramonian Lost Gems Admin
                        ";
                        break;
                    case 4: // Denied
                        $mail->Body = "
                            Hi $reporterName,<br><br>
                            We regret to inform you that the item '<strong>$itemTitle</strong>' did not meet our criteria and has been <strong>denied</strong>. Feel free to review our guidelines and submit a new report if needed.<br><br>
                            Regards,<br>
                            Ramonian Lost Gems Admin
                        ";
                        break;
                    default: // Pending
                        $mail->Body = "
                            Dear $reporterName,<br><br>
                            The status of the item '<strong>$itemTitle</strong>' is currently <strong>pending</strong>. We will notify you of any updates.<br><br>
                            Regards,<br>
                            Ramonian Lost Gems Admin
                        ";
                        break;
                }

                // Alternate plain text body
                $mail->AltBody = strip_tags($mail->Body);
                
                $mail->send();
            } catch (Exception $e) {
                error_log("Mailer Error: " . $mail->ErrorInfo);
            }

            // Send success response with updated status
            echo json_encode(['success' => true, 'status' => $newStatus, 'statusText' => $statusText]);
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

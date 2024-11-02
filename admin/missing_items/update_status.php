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
                $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'vdarwin860@gmail.com'; // Replace with your email
                $mail->Password = 'ybve xumi zutn nmro'; // Replace with your email password or app-specific password
                $mail->SMTPSecure =  'ssl';
                $mail->Port = 465;
            
                // Recipients
                $mail->setFrom('admin@ramonianlostgems.com', 'Ramonian Lost Gems'); // Change this to your "From" email
                $mail->addAddress($reporterEmail); // The reporter's email
            
                // Content
              
                $mail->isHTML(true);
                $mail->Subject = 'Update on Your Missing Item Report: ' . $itemTitle;
                
                if ($status == 1) { // Published
                    $mail->Body = "
                        Hello $reporterName,<br><br>
                        Your reported item, '<strong>$itemTitle</strong>,' has been <strong>published</strong> on our app. It's now visible to other users. Thank you for helping reconnect lost items.<br><br>
                        Regards,<br>
                        Ramonian Lost Gems Team
                    ";
                } elseif ($status == 2) { // Claimed
                    $mail->Body = "
                        Hi $reporterName,<br><br>
                        Great news! The item you reported, '<strong>$itemTitle</strong>,' has been <strong>claimed</strong> by its owner. Thank you for your help!<br><br>
                        Regards,<br>
                        Ramonian Lost Gems Team
                    ";
                } elseif ($status == 3) { // Surrendered
                    $mail->Body = "
                        Hello $reporterName,<br><br>
                        The item you reported, '<strong>$itemTitle</strong>,' has been <strong>surrendered</strong> for safekeeping. Visit the SSG office with a valid ID to claim it.<br><br>
                        Thank you for your assistance.<br><br>
                        Regards,<br>
                        Ramonian Lost Gems Team
                    ";
                } elseif ($status == 4) { // Denied
                    $mail->Body = "
                        Dear $reporterName,<br><br>
                        Unfortunately, your report for '<strong>$itemTitle</strong>' was <strong>denied</strong>. This may be due to incomplete information. Please review our guidelines and submit a new report if needed.<br><br>
                        Thank you for your understanding.<br><br>
                        Regards,<br>
                        Ramonian Lost Gems Team
                    ";
                } else { // Pending (Fallback)
                    $mail->Body = "
                        Hello $reporterName,<br><br>
                        The status of your reported item, '<strong>$itemTitle</strong>,' is currently <strong>pending</strong>. We will notify you once there is an update.<br><br>
                        Regards,<br>
                        Ramonian Lost Gems Team
                    ";
                }
                
                $mail->AltBody = strip_tags($mail->Body);
                
                

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

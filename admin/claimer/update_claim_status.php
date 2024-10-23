<?php
// Include database configuration
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $claimId = intval($_POST['claim_id']);
    $action = $_POST['action'];
    $status = '';

    // Update claim status based on the admin's action
    if ($action === 'approve') {
        $status = 'approved';
    } elseif ($action === 'reject') {
        $status = 'rejected';
    } elseif ($action === 'claimed') {
        $status = 'claimed';
    }

    // Fetch the claim details, including claimant's email and item name
    $sql = "
        SELECT c.id, c.status, c.item_id, mh.title AS item_name, COALESCE(um.email, us.email) AS email, COALESCE(um.first_name, us.first_name) AS claimant_name
        FROM claims c
        LEFT JOIN message_history mh ON c.item_id = mh.id
        LEFT JOIN user_member um ON c.user_id = um.id
        LEFT JOIN user_staff us ON c.user_id = us.id
        WHERE c.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $claimId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $claim = $result->fetch_assoc();
        $claimantEmail = $claim['email'];
        $claimantName = $claim['claimant_name'];
        $itemName = $claim['item_name'];

        // Update claim status in the database
        $updateSql = "UPDATE claims SET status = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param('si', $status, $claimId);

        if ($updateStmt->execute()) {
            // Send email notification to claimant
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
                $mail->addAddress($claimantEmail, $claimantName); // Send to the claimant

                // Email content
                $mail->isHTML(true);
                
                // Set email subject and body based on claim status
                if ($status === 'approved') {
                    $mail->Subject = 'Your Claim Request has been Approved';
                    $mail->Body = "Hello $claimantName, <br><br>Your claim request for '<strong>$itemName</strong>' has been approved. You can now proceed to the OSA Building 3rd floor Student Organization Office to collect the item.<br><br>Thank you.";
                } elseif ($status === 'rejected') {
                    $mail->Subject = 'Your Claim Request has been Rejected';
                    $mail->Body = "Hello $claimantName, <br><br>We regret to inform you that your claim request for '<strong>$itemName</strong>' has been rejected.<br><br>Thank you.";
                } elseif ($status === 'claimed') {
                    $mail->Subject = 'Your Claim Request has been Claimed';
                    $mail->Body = "Hello $claimantName, <br><br>Your claim request status has been updated to '<strong>claimed</strong>' for the item '<strong>$itemName</strong>'.<br><br>Thank you.";
                }

                // Send the email
                if ($mail->send()) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Mailer Error: ' . $mail->ErrorInfo]);
                }
            } catch (Exception $e) {
                // Log error if email sending fails
                error_log("Mailer Error: " . $mail->ErrorInfo);
                echo json_encode(['success' => false, 'error' => "Mailer Error: {$mail->ErrorInfo}"]);
            }
        } else {
            // Redirect with error status if update fails
            echo json_encode(['success' => false, 'error' => $updateStmt->error]);
        }

        $updateStmt->close();
    } else {
        // Return error if claim is not found
        echo json_encode(['success' => false, 'error' => 'Claim not found']);
    }

    $stmt->close();
}

$conn->close();
?>

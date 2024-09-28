<?php
require '../../vendor/autoload.php';  // Correct the path to autoload.php

// Include the database configuration file
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../../config.php';

// PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if admin is logged in (you should already have admin session logic)

// Handle approval
if (isset($_POST['approve'])) {
    $user_id = $_POST['user_id'];

    // Update user status to 'active'
    $stmt = $conn->prepare("UPDATE user_member SET status = 'active' WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        // Fetch the email of the user to send the approval email
        $stmt = $conn->prepare("SELECT email FROM user_member WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($email);
        $stmt->fetch();

        // Send approval email using PHPMailer
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
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Account Approved';
            $mail->Body    = "Hello,<br>Your account has been approved! You can now log in.";

            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}

// Fetch users with 'pending' status
$result = $conn->query("SELECT * FROM user_member WHERE status = 'pending'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Approval</title>
</head>
<body>
<h1>Pending User Approvals</h1>
<?php if ($result->num_rows > 0): ?>
<table border="1">
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Action</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></td>
        <td>
            <form method="POST" action="">
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" name="approve">Approve</button>
            </form>
        </td>
    </tr>
    <?php } ?>
</table>
<?php else: ?>
    <p>No users pending approval.</p>
<?php endif; ?>
</body>
</html>

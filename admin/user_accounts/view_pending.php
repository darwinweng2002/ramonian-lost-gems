<?php
session_start();
include 'config.php';

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
            // Handle email sending error
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
<table border="1">
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Action</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?= $row['first_name'] . ' ' . $row['last_name'] ?></td>
        <td><?= $row['email'] ?></td>
        <td>
            <form method="POST" action="">
                <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                <button type="submit" name="approve">Approve</button>
            </form>
        </td>
    </tr>
    <?php } ?>
</table>
</body>
</html>

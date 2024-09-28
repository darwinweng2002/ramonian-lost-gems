<?php
include '../../config.php'; // Include database configuration

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];

    // Update the user status to approved
    $stmt = $conn->prepare("UPDATE user_member SET status = 'approved' WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        // Fetch the user's email to send the approval notification
        $stmt = $conn->prepare("SELECT email, first_name FROM user_member WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($email, $first_name);
        $stmt->fetch();
        
        // Send email notification
        $to = $email;
        $subject = "Account Approved";
        $message = "Hi $first_name,\n\nYour account has been approved. You can now log in and access the system.\n\nBest regards,\nAdmin Team";
        $headers = "From: no-reply@yourdomain.com";

        mail($to, $subject, $message, $headers);

        // Redirect back to the admin page with a success message
        echo "<script>
                Swal.fire('Success!', 'User has been approved and notified via email.', 'success')
                    .then(() => window.location.href = 'admin_pending_approvals.php');
              </script>";
    } else {
        echo "<script>
                Swal.fire('Error!', 'Failed to approve the user.', 'error')
                    .then(() => window.location.href = 'admin_pending_approvals.php');
              </script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<?php
// Include the necessary files
include '../config.php';

// Start the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Destroy the session
session_unset();
session_destroy();

// Buffer output to avoid "headers already sent" warning
ob_start();
?>

<script>
    // Use JavaScript for the confirmation dialog
    if (confirm('Are you sure you want to logout?')) {
        alert('You have successfully logged out.');
        window.location.href = 'register.php';
    } else {
        window.location.href = 'index.php'; // or wherever you want to redirect if canceled
    }
</script>

<?php
// End output buffering and flush the buffer
ob_end_flush();
?>

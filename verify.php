<?php
// Include the database configuration file
include 'config.php';

// Get the token from the URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Prepare the SQL statement to find the token
    $stmt = $conn->prepare("SELECT * FROM user_member WHERE verification_token = ? AND token_expiration > NOW() AND status = 'pending'");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Token is valid, activate the account
        $stmt = $conn->prepare("UPDATE user_member SET status = 'active', verification_token = NULL, token_expiration = NULL WHERE verification_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();

        echo "Your email has been successfully verified! You can now log in.";
    } else {
        // Token is invalid or expired
        echo "Invalid or expired token.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "No token provided.";
}
?>

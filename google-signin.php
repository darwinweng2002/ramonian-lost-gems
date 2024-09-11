<?php
require_once 'config.php';
require_once 'vendor/autoload.php'; // Include Composer's autoloader

// Initialize Google Client
$client = new Google_Client(['client_id' => '462546722729-vflluo934lv9qei2jbeaqcib5sllh9t6.apps.googleusercontent.com']);
$token = $_POST['id_token'];

// Verify the token
$payload = $client->verifyIdToken($token);
if ($payload) {
    // Get user details
    $email = $payload['email'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];

    // Check if user already exists
    $stmt = $conn->prepare("SELECT id FROM user_member WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // User exists, log them in
        $stmt->bind_result($user_id);
        $stmt->fetch();
        session_start();
        $_SESSION['user_id'] = $user_id;
        echo json_encode(['success' => true]);
    } else {
        // Register new user
        $stmt = $conn->prepare("INSERT INTO user_member (first_name, last_name, email, verified) VALUES (?, ?, ?, 1)");
        $stmt->bind_param("sss", $first_name, $last_name, $email);
        if ($stmt->execute()) {
            session_start();
            $_SESSION['user_id'] = $conn->insert_id;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Registration failed.']);
        }
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid token.']);
}
?>

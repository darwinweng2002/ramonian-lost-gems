<?php
include('config.php');
session_start(); // Start the session to access session variables

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if user is logged in and user_id is set in session
    if (!isset($_SESSION['user_id'])) {
        die("User not logged in");
    }

    $message = $_POST['message'];
    $userId = $_SESSION['user_id']; // Use user ID from session

    // Directory for uploading files
    $uploadDir = 'uploads/items/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
    }

    $uploadedFiles = [];

    // Handle message saving
    $stmt = $conn->prepare("INSERT INTO message_history (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $userId, $message);
    $stmt->execute();
    $messageId = $stmt->insert_id;
    $stmt->close();

    // Handle file uploads
    foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
        $fileName = basename($_FILES['images']['name'][$key]);
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($tmpName, $targetFilePath)) {
            $stmt = $conn->prepare("INSERT INTO message_images (message_id, image_path) VALUES (?, ?)");
            $stmt->bind_param("is", $messageId, $fileName); // Store just the filename in DB
            $stmt->execute();
            $stmt->close();
            $uploadedFiles[] = $targetFilePath;
        } else {
            echo "Failed to upload file: " . $fileName;
        }
    }

    echo "Message and images uploaded successfully!";
}

// Retrieve user information
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($username, $email);
    $stmt->fetch();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send Message</title>
    <?php require_once('inc/header.php'); ?>
</head>
<body>
    <br>
    <br>
    <br>
    <br>
    <br>
    <?php require_once('inc/topBarNav.php') ?>
    
    <!-- Display user information -->
    <?php if (isset($username) && isset($email)): ?>
        <div>
            <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
            <p>Email: <?php echo htmlspecialchars($email); ?></p>
        </div>
    <?php endif; ?>

    <form action="send_message.php" method="post" enctype="multipart/form-data">
        <label for="message">Message:</label>
        <textarea name="message" id="message" required></textarea><br>
        <label for="images">Upload Images:</label>
        <input type="file" name="images[]" id="images" multiple><br>
        <input type="submit" value="Send Message">
    </form>
</body>
</html>

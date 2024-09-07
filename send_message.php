<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Include configuration and database connection
    include('config.php'); // Ensure this path is correct

    $message = $_POST['message'];
    $userId = $_POST['user_id']; // Ensure this comes from a secure source, e.g., session

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send Message</title>
</head>
<body>
    <form action="send_message.php" method="post" enctype="multipart/form-data">
        <label for="message">Message:</label>
        <textarea name="message" id="message" required></textarea><br>
        <label for="user_id">User ID:</label>
        <input type="text" name="user_id" id="user_id" required><br>
        <label for="images">Upload Images:</label>
        <input type="file" name="images[]" id="images" multiple><br>
        <input type="submit" value="Send Message">
    </form>
</body>
</html>

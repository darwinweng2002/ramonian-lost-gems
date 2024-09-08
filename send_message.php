<?php
include('config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if user is logged in and user_id is set in session
    if (!isset($_SESSION['user_id'])) {
        die("User not logged in");
    }

    $message = $_POST['message'];
    $landmark = $_POST['landmark']; // Add this line to get the landmark value
    $userId = $_SESSION['user_id']; // Use user ID from session

    // Directory for uploading files
    $uploadDir = 'uploads/items/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
    }

    $uploadedFiles = [];

    // Handle message saving
    $stmt = $conn->prepare("INSERT INTO message_history (user_id, message, landmark) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $message, $landmark); // Updated to "iss"
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
            $error = "Failed to upload file: " . $fileName;
        }
    }

    // Success or error message for SweetAlert
    $alertMessage = isset($error) ? $error : "Message and images uploaded successfully!";
}

// Retrieve user information
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT first_name, college, email FROM user_member WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($first_name, $college, $email);
    $stmt->fetch();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Message</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your custom stylesheet -->
    <?php require_once('inc/header.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* styles.css */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .user-info {
            margin-bottom: 20px;
        }

        .user-info h2 {
            color: #333;
        }

        .message-form {
            display: flex;
            flex-direction: column;
        }

        .message-form label {
            margin: 10px 0 5px;
            color: #333;
        }

        .message-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .message-form input[type="file"] {
            margin-bottom: 15px;
        }

        .submit-btn {
            padding: 10px 15px;
            background-color: #2C3E50;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .submit-btn:hover {
            background-color: #34495E;
        }
    </style>
</head>
<body>
    <?php require_once('inc/topBarNav.php') ?>
    <br>
    <br>
    <br>
    <div class="container">
        <!-- Display user information -->
        <?php if (isset($first_name) && isset($email) && isset($college)): ?>
        <div class="user-info">
            <h2>What you want to report?, <?php echo htmlspecialchars($first_name); ?>!</h2>
            <p>College: <?php echo htmlspecialchars($college); ?></p>
            <p>Email: <?php echo htmlspecialchars($email); ?></p>
        </div>
        <?php endif; ?>

        <form action="send_message.php" method="post" enctype="multipart/form-data" class="message-form">
            <label for="message">Message:</label>
            <textarea name="message" id="message" required></textarea>

            <label for="landmark">Landmark:</label>
            <textarea name="landmark" id="landmark" required></textarea>
            
            <label for="images">Upload Images:</label>
            <input type="file" name="images[]" id="images" multiple>
            
            <input type="submit" value="Send Message" class="submit-btn">
        </form>
    </div>

    <?php if (isset($alertMessage)): ?>
    <script>
        Swal.fire({
            icon: '<?php echo isset($error) ? 'error' : 'success'; ?>',
            title: '<?php echo isset($error) ? 'Oops!' : 'Success!'; ?>',
            text: '<?php echo htmlspecialchars($alertMessage); ?>'
        });
    </script>
    <?php endif; ?>
    <?php require_once('inc/footer.php') ?>
</body>
</html>

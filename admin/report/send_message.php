<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../../config.php';

// Database connection
$conn = new mysqli("localhost", "u450897284_root", "Lfisgemsdb1234", "u450897284_lfis_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = $_POST['message'];
    $landmark = $_POST['landmark'];
    $title = $_POST['title'];
    $timeFound = $_POST['time_found'];
    $contact = $_POST['contact'];
    $category_id = $_POST['category_id'];
    $new_category = $_POST['new_category'];
    $founder = 'Admin';  // Automatically set founder as Admin since this is an admin submission
    $status = 1; // Automatically set status to Published

    // Handle category addition
    if ($category_id == 'add_new' && !empty($new_category)) {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $new_category);
        $stmt->execute();
        $category_id = $stmt->insert_id;
        $stmt->close();
    }

    // Directory for uploading files
    $uploadDir = 'uploads/items/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
    }

    $uploadedFiles = [];
    
    // Insert the message into the database with status 'Published'
    $stmt = $conn->prepare("INSERT INTO message_history (user_id, message, landmark, title, time_found, contact, founder, category_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $userId = null; // Admin isn't associated with a user ID, so this can be null or 0
    $stmt->bind_param("issssssis", $userId, $message, $landmark, $title, $timeFound, $contact, $founder, $category_id, $status);
    $stmt->execute();
    $messageId = $stmt->insert_id;
    $stmt->close();

    // Handle file uploads
    $maxFileSize = 50 * 1024 * 1024; // 50MB
    foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
        $fileName = basename($_FILES['images']['name'][$key]);
        $fileSize = $_FILES['images']['size'][$key];
        $fileType = $_FILES['images']['type'][$key];
        $targetFilePath = $uploadDir . $fileName;
        if ($fileSize > $maxFileSize) {
            $error = "File " . $fileName . " exceeds the maximum file size of 50MB.";
            break;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($fileType, $allowedTypes)) {
            $error = "File type not allowed for file " . $fileName;
            break;
        }
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
    $alertMessage = isset($error) ? $error : "The item has been reported and published successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<?php require_once('../inc/header.php') ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Found Item - Admin</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your custom stylesheet -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php require_once('../inc/topBarNav.php') ?>
<?php require_once('../inc/navigation.php') ?> 
    <div class="container">
        <h2>Report and Publish Found Item</h2>

        <form action="" method="post" enctype="multipart/form-data">
            <label for="title">Item Name:</label>
            <input type="text" name="title" id="title" placeholder="Enter item name" required>

            <label for="category">Category:</label>
            <select name="category_id" id="category_id" required>
                <option value="">Select a category</option>
                <?php
                $stmt = $conn->prepare("SELECT id, name FROM categories");
                $stmt->execute();
                $stmt->bind_result($categoryId, $categoryName);
                while ($stmt->fetch()) {
                    echo "<option value=\"$categoryId\">$categoryName</option>";
                }
                $stmt->close();
                ?>
                <option value="add_new">Add New Category</option>
            </select>

            <div id="newCategoryDiv" style="display: none;">
                <label for="new_category">New Category:</label>
                <input type="text" name="new_category" id="new_category" placeholder="Enter new category name">
            </div>

            <label for="landmark">Location where the item was found:</label>
            <input type="text" name="landmark" id="landmark" placeholder="Enter location" required>

            <label for="time_found">Time Found:</label>
            <input type="datetime-local" name="time_found" id="time_found" required>

            <label for="contact">Contact Information:</label>
            <input type="text" id="contact" name="contact" pattern="[0-9]{10,11}" required>

            <label for="message">Description of the found item:</label>
            <textarea name="message" id="message" rows="4" placeholder="Enter item description" required></textarea>

            <label for="images">Upload Images:</label>
            <input type="file" name="images[]" id="images" multiple>

            <button type="submit">Publish</button>
        </form>
    </div>

    <script>
        document.getElementById('category_id').addEventListener('change', function() {
            document.getElementById('newCategoryDiv').style.display = this.value === 'add_new' ? 'block' : 'none';
        });
    </script>
</body>
</html>

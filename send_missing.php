<?php
include('config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if user is logged in and user_id is set in session
    if (!isset($_SESSION['user_id'])) {
        die("User not logged in");
    }

    // Retrieve user inputs
    $title = $_POST['title'];
    $description = $_POST['description'];
    $lastSeenLocation = $_POST['last_seen_location']; // Location where the item was last seen
    $timeMissing = $_POST['time_missing']; // Time the item went missing
    $userId = $_SESSION['user_id']; // Use user ID from session
    $status = 'Pending'; // Default status

    // Directory for uploading files
    $uploadDir = 'uploads/missing_items/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
    }

    $uploadedFiles = [];

    // Insert missing item details into the database
    $stmt = $conn->prepare("INSERT INTO missing_items (user_id, title, description, last_seen_location, time_missing, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $userId, $title, $description, $lastSeenLocation, $timeMissing, $status);
    $stmt->execute();
    $missingItemId = $stmt->insert_id;
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
            $stmt = $conn->prepare("INSERT INTO missing_item_images (missing_item_id, image_path) VALUES (?, ?)");
            $stmt->bind_param("is", $missingItemId, $fileName); // Store just the filename in the DB
            $stmt->execute();
            $stmt->close();
            $uploadedFiles[] = $targetFilePath;
        } else {
            $error = "Failed to upload file: " . $fileName;
        }
    }

    // Success or error message for SweetAlert
    $alertMessage = isset($error) ? $error : "Your missing item report has been successfully submitted!";
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
    <title>Post Missing Item</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your custom stylesheet -->
    <?php require_once('inc/header.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* styles.css */
        body {
            font-family: 'Open Sans', sans-serif;
            font: 16px;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
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
            color: #2C3E50;
            font-size: 24px;
            font-weight: 600;
        }

        .user-info p {
            color: #555;
            font-size: 16px;
        }

        .message-form {
            display: flex;
            flex-direction: column;
        }

        .message-form label {
            margin: 10px 0 5px;
            color: #2C3E50;
            font-size: 16px;
            font-weight: 500;
        }

        .message-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 16px;
            font-family: 'Helvetica Neue', Arial, sans-serif;
        }

        .message-form input[type="file"] {
            margin-bottom: 15px;
            font-size: 16px;
        }

        .message-form input[type="datetime-local"] {
            margin-bottom: 15px;
            font-size: 16px;
        }

        .submit-btn {
            padding: 10px 15px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            font-family: 'Helvetica Neue', Arial, sans-serif;
        }

        .submit-btn:hover {
            background-color: #0056b3;
        }
        .image-preview-container {
            margin-bottom: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .image-preview-container img {
            max-width: 100px;
            max-height: 100px;
            border-radius: 4px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
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
            <h2>Report Missing Item</h2>
            <p>College: <?php echo htmlspecialchars($college); ?></p>
            <p>Email: <?php echo htmlspecialchars($email); ?></p>
        </div>
        <?php endif; ?>

        <form action="send_missing.php" method="post" enctype="multipart/form-data" class="message-form">
            <label for="title">Title</label>
            <input type="text" name="title" id="title" placeholder="Enter item title" required>

            <label for="description">Description:</label>
            <textarea name="description" id="description" rows="4" placeholder="Describe the missing item" required></textarea>

            <label for="last_seen_location">Last Seen Location:</label>
            <input type="text" name="last_seen_location" id="last_seen_location" placeholder="Location where the item was last seen" required>

            <label for="time_missing">Time Missing:</label>
            <input type="datetime-local" name="time_missing" id="time_missing" required>

            <label for="images">Upload Images:</label>
            <input type="file" name="images[]" id="images" multiple onchange="previewImages()">
            <div class="image-preview-container" id="imagePreviewContainer"></div>
            <p id="fileValidationMessage" style="color: red; display: none;">Supported file types: jpg, jpeg, png, gif.</p>
            <button type="submit" class="submit-btn">Submit Report</button>
        </form>
    </div>
    <script>
      function previewImages() {
    const previewContainer = document.getElementById('imagePreviewContainer');
    const validationMessage = document.getElementById('fileValidationMessage');
    const files = document.getElementById('images').files;
    const maxSize = 50 * 1024 * 1024; // 50MB in bytes

    previewContainer.innerHTML = ''; // Clear previous previews
    validationMessage.style.display = 'none'; // Hide validation message

    for (let i = 0; i < files.length; i++) {
        const file = files[i];

        // Check file size
        if (file.size > maxSize) {
            validationMessage.textContent = `File ${file.name} is too large. Maximum size is 50MB.`;
            validationMessage.style.display = 'block';
            return; // Stop further processing if file is too large
        }

        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const img = document.createElement('img');
                img.src = event.target.result;
                previewContainer.appendChild(img);
            };
            reader.readAsDataURL(file);
        } else {
            validationMessage.style.display = 'block'; // Show validation message if file type is not supported
        }
    }
}

        <?php if (isset($alertMessage)): ?>
            Swal.fire({
                icon: '<?php echo isset($error) ? 'error' : 'success'; ?>',
                title: '<?php echo isset($error) ? 'Oops!' : 'Success!'; ?>',
                text: '<?php echo htmlspecialchars($alertMessage); ?>',
                confirmButtonText: 'OK'
            });
        <?php endif; ?>
    </script>
    <?php require_once('inc/footer.php') ?>
</body>
</html>

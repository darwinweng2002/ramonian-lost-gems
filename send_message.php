<?php
include('config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if user is logged in and user_id is set in session
    if (!isset($_SESSION['user_id'])) {
        die("User not logged in");
    }

    $message = $_POST['message'];
    $landmark = $_POST['landmark']; // Existing field
    $title = $_POST['title']; // New field
    $timeFound = $_POST['time_found']; // New field
    $userId = $_SESSION['user_id']; // Use user ID from session
    $contact = $_POST['contact'];
    $category_id = $_POST['category_id'];
    $new_category = $_POST['new_category'];
    

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

    // Handle message saving
   // Handle message saving
   $stmt = $conn->prepare("INSERT INTO message_history (user_id, message, landmark, title, time_found, contact, category_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
   $stmt->bind_param("isssssis", $userId, $message, $landmark, $title, $timeFound, $contact, $category_id, $status);
   $status = 'Pending'; // Set default status
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
    $alertMessage = isset($error) ? $error : "Your report has been submitted successfully. It will be reviewed by the admins before being published for public viewing.";
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
            <h2>Report Found Item</h2>
            <p><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-graduation-cap"><path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/><path d="M22 10v6"/><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/></svg> College: <?php echo htmlspecialchars($college); ?></p>
            <p><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-paperclip"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg> Username: <?php echo htmlspecialchars($email); ?></p>
        </div>
        <?php endif; ?>

        <form action="send_message.php" method="post" enctype="multipart/form-data" class="message-form">
        <label for="title">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pin"><path d="M12 17v5"/><path d="M9 10.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 5 15.24V16a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 15 10.76V7a1 1 0 0 1 1-1 2 2 0 0 0 0-4H8a2 2 0 0 0 0 4 1 1 0 0 1 1 1z"/></svg>
                </svg> Item Name:
            </label>
            <input type="text" name="title" id="title" placeholder="Enter item title" required>
            <label for="category">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-tag">
        <path d="M6 9v6a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V9a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1z"/>
        <path d="M14 2a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8z"/>
        <path d="M4 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
    </svg> Category:
</label>
<select name="category_id" id="category_id" required>
    <option value="">Select a category</option>
    <?php
    // Fetch categories from the database
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
    <label for="new_category">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-tag-add">
            <path d="M6 9v6a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V9a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1z"/>
            <path d="M4 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
            <path d="M18 6h-5v5h-2V6H6v2h7v7h2V8h5V6z"/>
        </svg> New Category:
    </label>
    <input type="text" name="new_category" id="new_category" placeholder="Enter new category name">
</div>
            <label for="landmark"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-location"><path d="M21 10a9 9 0 0 0-18 0c0 5.6 9 12 9 12s9-6.4 9-12z"/><path d="M12 10a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></svg> Location where the item was found:</label>
            <input type="text" name="landmark" id="landmark" placeholder="Location details" required>

            <label for="contact">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone">
                    <path d="M22 16.77a7.41 7.41 0 0 0-1.63-1.92c-.54-.54-1.18-1.09-1.92-1.63-1.12-.77-2.37-1.27-3.65-1.27s-2.53.5-3.65 1.27a14.1 14.1 0 0 1-3.25-3.25C3.58 11.59 3.09 10.34 2.33 9.22a8.06 8.06 0 0 1 .3-6.64c.51-.81 1.06-1.59 1.74-2.27.28-.27.5-.59.69-.95.18-.36.24-.76.2-1.17-.05-.42-.21-.83-.46-1.16-2.54-2.54-6.23-2.54-8.77 0-.49.49-.68 1.24-.5 1.87s.82 1.22 1.37 1.66a14.04 14.04 0 0 1 1.72 1.68c.48.46 1.02.78 1.6.99a1.14 1.14 0 0 1 1.1-.3 6.76 6.76 0 0 1 2.06-2.07c.53-.44 1.09-.76 1.72-.98s1.31-.25 1.95-.06a1.14 1.14 0 0 1 .59 1.14c.03.41.09.83.2 1.24.09.26.22.49.4.71.48.48.68 1.12.54 1.78s-.68 1.22-1.23 1.56c-1.41.79-2.87 1.19-4.35 1.28-.54.03-1.09.1-1.62.23-.16.02-.32.06-.48.08a1.04 1.04 0 0 1-.44.05c-.29.03-.58.09-.85.17s-.54.14-.79.27c-.27.13-.55.31-.76.53s-.36.45-.46.71c-.09.29-.14.6-.14.92 0 .52.1 1.04.29 1.54.39 1.05.97 2.05 1.71 2.91a8.18 8.18 0 0 1 .68 1.13c.44.83.9 1.63 1.41 2.38s1.03 1.42 1.62 2.1c.52.62 1.08 1.21 1.7 1.76s1.23 1.18 1.8 1.77c.67.59 1.31 1.21 2.02 1.81.6.53 1.23 1.02 1.83 1.52s1.21.96 1.79 1.46a2.32 2.32 0 0 0 3.28-.62 2.32 2.32 0 0 0-.62-3.28c-.5-.58-1.02-1.15-1.5-1.69a13.84 13.84 0 0 0-1.61-1.74c-.73-.61-1.48-1.14-2.24-1.67-.8-.56-1.56-1.17-2.27-1.82a1.14 1.14 0 0 1-.38-.9c.04-.46.2-.93.45-1.3s.59-.72 1-.99a10.13 10.13 0 0 1 1.68-.7c.55-.09 1.11-.17 1.66-.17a7.41 7.41 0 0 0 5.32-2.05z"/>
                </svg> Contact Information:
            </label>
<input type="text" id="contact" name="contact" pattern="[0-9]{10,11}" required>
<span id="contactError"></span>

            <input type="text" name="contact" id="contact" placeholder="Enter contact information" required>
            <label for="time_found">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-history"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg>
                </svg> Time Found:
            </label>
            <input type="datetime-local" name="time_found" id="time_found" required>
            <form action="send_message.php" method="post" enctype="multipart/form-data" class="message-form">
            <label for="message"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-notebook-pen"><path d="M13.4 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7.4"/><path d="M2 6h4"/><path d="M2 10h4"/><path d="M2 14h4"/><path d="M2 18h4"/><path d="M21.378 5.626a1 1 0 1 0-3.004-3.004l-5.01 5.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z"/></svg> Description of the found item:</label>
            <textarea name="message" id="message" rows="4" placeholder="Enter your description" required></textarea>
            <label for="images">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-image-up">
        <path d="M10.3 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10l-3.1-3.1a2 2 0 0 0-2.814.014L6 21"/>
        <path d="m14 19.5 3-3 3 3"/>
        <path d="M17 22v-5.5"/>
        <circle cx="9" cy="9" r="2"/>
    </svg> Please upload an image file of the item:
</label>
<input type="file" name="images[]" id="images" multiple onchange="previewImages()">
<div class="image-preview-container" id="imagePreviewContainer"></div>
<p id="fileValidationMessage" style="color: red; display: none;">Supported file types: jpg, jpeg, png, gif.</p>
<button type="submit" class="submit-btn">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send">
        <line x1="22" x2="11" y1="2" y2="13"/>
        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
    </svg> Send Report
</button>
        </form>
    </div>

    <?php require_once('inc/footer.php') ?>

    <script>
       // Add this JavaScript function to validate the file size before upload
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
        document.getElementById('category_id').addEventListener('change', function() {
    document.getElementById('newCategoryDiv').style.display = this.value === 'add_new' ? 'block' : 'none';
});
    </script>
</body>
</html>
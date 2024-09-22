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
    $lastSeenLocation = $_POST['last_seen_location'];
    $timeMissing = $_POST['time_missing'];
    $userId = $_SESSION['user_id'];
    $status = 0; // Set to 0 for 'Pending' (assuming 0 is for 'Pending')
    $contact = isset($_POST['contact']) ? $_POST['contact'] : '';
    $category_id = $_POST['category_id'];
    $new_category = $_POST['new_category'];

    // Check if category_id is set to add new category
    if ($category_id == 'add_new' && !empty($new_category)) {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $new_category);
        $stmt->execute();
        $category_id = $stmt->insert_id;
        $stmt->close();
    }

    // Directory for uploading files
    $uploadDir = 'uploads/missing_items/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
    }

    $uploadedFiles = [];

    // Prepare and execute the SQL statement
    $sql = "INSERT INTO missing_items (user_id, title, description, last_seen_location, time_missing, contact, category_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssis", $userId, $title, $description, $lastSeenLocation, $timeMissing, $contact, $category_id, $status);
    $stmt->execute();
    $missingItemId = $stmt->insert_id;
    $stmt->close();

    // Handle file uploads
    foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
        $fileName = basename($_FILES['images']['name'][$key]);
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($tmpName, $targetFilePath)) {
            $stmt = $conn->prepare("INSERT INTO missing_item_images (missing_item_id, image_path) VALUES (?, ?)");
            $stmt->bind_param("is", $missingItemId, $fileName);
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
            <label for="title">Item Name:</label>
            <input type="text" name="title" id="title" placeholder="Enter item name" required>
            <label for="category">Category:</label>
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
        New Category:
    </label>
    <input type="text" name="new_category" id="new_category" placeholder="Enter new category name">
</div>
            <label for="description">Description of the missing item:</label>
            <textarea name="description" id="description" rows="4" placeholder="Describe the missing item" required></textarea>

            <label for="last_seen_location">Last Seen Location:</label>
            <input type="text" name="last_seen_location" id="last_seen_location" placeholder="Location where the item was last seen" required>
            <label for="contact">
                </svg> Contact Information:
            </label>
            <input type="text" name="contact" id="contact" placeholder="Enter contact information" required>
            <label for="time_missing">Time Missing:</label>
            <input type="datetime-local" name="time_missing" id="time_missing" required>
           
            <label for="images">Upload Images:</label> <!-- Added the text label here -->
<input type="file" name="images[]" id="images" multiple onchange="previewImages()">
<div class="image-preview-container" id="imagePreviewContainer"></div>
<p id="fileValidationMessage" style="color: red; display: none;">Supported file types: jpg, jpeg, png, gif.</p>
<p>Upload multiple images if necessary.</p>
            <button type="submit" class="submit-btn">Submit Report</button>
        </form>
    </div>
    <script>
       function previewImages() {
        const previewContainer = document.getElementById('imagePreviewContainer');
        const validationMessage = document.getElementById('fileValidationMessage');
        const files = document.getElementById('images').files;
        
        previewContainer.innerHTML = ''; // Clear previous previews
        validationMessage.style.display = 'none'; // Hide validation message

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
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
    <?php require_once('inc/footer.php') ?>
</body>
</html>

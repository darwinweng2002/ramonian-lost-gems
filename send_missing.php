<?php
include('config.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session if not already started
}

// Check if the user is logged in as either a regular user or staff
if (!isset($_SESSION['user_id']) && !isset($_SESSION['staff_id'])) {
    die("User not logged in");
}

// Get the user ID and user type
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $userType = 'user_member'; // Table for regular users
} elseif (isset($_SESSION['staff_id'])) {
    $userId = $_SESSION['staff_id'];
    $userType = 'user_staff'; // Table for staff users
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize user inputs
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $lastSeenLocation = isset($_POST['last_seen_location']) ? trim($_POST['last_seen_location']) : '';
    $timeMissing = isset($_POST['time_missing']) ? trim($_POST['time_missing']) : '';
    $contact = isset($_POST['contact']) ? trim($_POST['contact']) : '';
    $category_id = isset($_POST['category_id']) ? $_POST['category_id'] : '';
    $new_category = isset($_POST['new_category']) ? trim($_POST['new_category']) : '';
    $owner = isset($_POST['owner']) ? trim($_POST['owner']) : '';

    // Validate required fields
    if (empty($title) || empty($description) || empty($lastSeenLocation) || empty($timeMissing) || empty($contact) || empty($category_id) || empty($owner)) {
        die('Please fill in all required fields.');
    }

    $status = 0; // Set to 0 for 'Pending'

    // Check if a new category needs to be created
    if ($category_id === 'add_new' && !empty($new_category)) {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        if ($stmt) {
            $stmt->bind_param("s", $new_category);
            $stmt->execute();
            $category_id = $stmt->insert_id; // Get the new category ID
            $stmt->close();
        } else {
            die('Error creating new category.');
        }
    }

    // Directory for uploading files
    $uploadDir = 'uploads/missing_items/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
    }

    $uploadedFiles = [];

    // Insert the missing item details into the database
    $sql = "INSERT INTO missing_items (user_id, title, description, last_seen_location, time_missing, contact, category_id, status, owner) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die('Error preparing statement: ' . $conn->error);
    }
    $stmt->bind_param("isssssiss", $userId, $title, $description, $lastSeenLocation, $timeMissing, $contact, $category_id, $status, $owner);
    if ($stmt->execute()) {
        $missingItemId = $stmt->insert_id; // Get the last inserted ID
    } else {
        die('Error executing statement: ' . $stmt->error);
    }
    $stmt->close();

    // Handle image file uploads
    if (!empty($_FILES['images']['tmp_name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            $fileName = basename($_FILES['images']['name'][$key]);
            $targetFilePath = $uploadDir . $fileName;

            // Validate file type and size
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
            if (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']) && $_FILES['images']['size'][$key] <= 5000000) { // Limit to 5MB
                if (move_uploaded_file($tmpName, $targetFilePath)) {
                    $stmt = $conn->prepare("INSERT INTO missing_item_images (missing_item_id, image_path) VALUES (?, ?)");
                    if ($stmt) {
                        $stmt->bind_param("is", $missingItemId, $fileName);
                        $stmt->execute();
                        $stmt->close();
                    }
                } else {
                    $error = "Failed to upload file: " . $fileName;
                }
            } else {
                $error = "Invalid file type or size for: " . $fileName;
            }
        }
    }

    // Display success or error message using SweetAlert
    $alertMessage = isset($error) ? $error : "Your report has been submitted successfully. It will be reviewed by the admins before being published.";
}

// Retrieve user information (both regular user and staff)
if (isset($userId)) {
    if ($userType === 'user_member') {
        $stmt = $conn->prepare("SELECT first_name, last_name, college, email FROM user_member WHERE id = ?");
    } else {
        $stmt = $conn->prepare("SELECT first_name, last_name, department AS college, email FROM user_staff WHERE id = ?");
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($first_name, $last_name, $college, $email);
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
    <link rel="stylesheet" href="styles.css">
    <?php require_once('inc/header.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* styles.css */
        body {
            font-family: 'Open Sans', sans-serif;
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
        .message-form textarea, .message-form input[type="text"], .message-form input[type="datetime-local"], .message-form input[type="file"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
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
    <div class="container">
        <h2>Report Missing Item</h2>
        <?php if (isset($first_name) && isset($email) && isset($college)): ?>
        <div class="user-info">
            <p>College: <?php echo htmlspecialchars($college); ?></p>
            <p>Email: <?php echo htmlspecialchars($email); ?></p>
        </div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data" class="message-form">
            <label for="owner">Owner's Name:</label>
            <input type="text" name="owner" id="owner" placeholder="Enter the owner's name" required>

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
                <label for="new_category">New Category:</label>
                <input type="text" name="new_category" id="new_category" placeholder="Enter new category name">
            </div>

            <label for="description">Description of the missing item:</label>
            <textarea name="description" id="description" rows="4" placeholder="Describe the missing item" required></textarea>

            <label for="last_seen_location">Last Seen Location:</label>
            <input type="text" name="last_seen_location" id="last_seen_location" placeholder="Location where the item was last seen" required>

            <label for="contact">Contact Information:</label>
            <input type="text" id="contact" name="contact" pattern="[0-9]{10,11}" placeholder="Enter contact information" required>

            <label for="time_missing">Time Missing:</label>
            <input type="datetime-local" name="time_missing" id="time_missing" required>

            <label for="images">Upload Images:</label>
            <input type="file" name="images[]" id="images" multiple onchange="previewImages()">
            <div class="image-preview-container" id="imagePreviewContainer"></div>

            <button type="submit" class="submit-btn">Submit Report</button>
        </form>

        <?php if (isset($alertMessage)): ?>
        <script>
            Swal.fire({
                icon: '<?php echo isset($error) ? 'error' : 'success'; ?>',
                title: '<?php echo isset($error) ? 'Oops!' : 'Success!'; ?>',
                text: '<?php echo htmlspecialchars($alertMessage); ?>',
                confirmButtonText: 'OK'
            });
        </script>
                <?php endif; ?>
    </div>

    <script>
        // Preview Images before upload
        function previewImages() {
            const previewContainer = document.getElementById('imagePreviewContainer');
            const files = document.getElementById('images').files;

            // Clear previous image previews
            previewContainer.innerHTML = '';

            // Loop through files and create image previews
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
                }
            }
        }

        // Show/Hide the new category input field
        document.getElementById('category_id').addEventListener('change', function() {
            const newCategoryDiv = document.getElementById('newCategoryDiv');
            if (this.value === 'add_new') {
                newCategoryDiv.style.display = 'block';
            } else {
                newCategoryDiv.style.display = 'none';
            }
        });
    </script>

    <?php require_once('inc/footer.php'); ?>
</body>
</html>


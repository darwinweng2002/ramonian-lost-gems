<?php
include('config.php');

session_start(); // Start session if not already started

if (!isset($_SESSION['user_id']) && !isset($_SESSION['staff_id'])) {
    die("User not logged in");
}

// Get the user ID and user type
if (isset($_SESSION['user_id'])) {
    // Regular user
    $userId = $_SESSION['user_id'];
    $userType = 'user_member'; // Table for regular users
} elseif (isset($_SESSION['staff_id'])) {
    // Staff user
    $userId = $_SESSION['staff_id'];
    $userType = 'user_staff'; // Table for staff users
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve user inputs
    $title = $_POST['title'];
    $description = $_POST['description'];
    $lastSeenLocation = $_POST['last_seen_location'];
    $timeMissing = $_POST['time_missing'];
    $status = 0; // Set to 0 for 'Pending'
    $contact = isset($_POST['contact']) ? $_POST['contact'] : '';
    $category_id = $_POST['category_id'];
    $new_category = $_POST['new_category'];
    $owner = $_POST['owner'];

    // Directory for uploading files
    $uploadDir = 'uploads/missing_items/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
    }

    // Prepare and execute the SQL statement
    $sql = "INSERT INTO missing_items (user_id, title, description, last_seen_location, time_missing, contact, category_id, status, owner) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssiss", $userId, $title, $description, $lastSeenLocation, $timeMissing, $contact, $category_id, $status, $owner);
    $stmt->execute();
    $missingItemId = $stmt->insert_id; // Get the last inserted missing item ID
    $stmt->close();

    // Validate image upload limit (between 1 and 6 files)
    if (count($_FILES['images']['tmp_name']) < 1 || count($_FILES['images']['tmp_name']) > 6) {
        $error = "You must only upload between 1 to 6 images.";
    } else {
        // Handle file uploads
        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            $fileName = basename($_FILES['images']['name'][$key]);
            $targetFilePath = $uploadDir . $fileName;

            if (move_uploaded_file($tmpName, $targetFilePath)) {
                $stmt = $conn->prepare("INSERT INTO missing_item_images (missing_item_id, image_path) VALUES (?, ?)");
                $stmt->bind_param("is", $missingItemId, $fileName);
                $stmt->execute();
                $stmt->close();
            } else {
                $error = "Failed to upload file: " . $fileName;
            }
        }
    }

    // Success or error message for SweetAlert
    $alertMessage = isset($error) ? $error : "Your report has been submitted successfully. It will be reviewed by the admins before being published for public viewing.";
}
// Retrieve categories (only user's or admin's categories)
$categories = [];
$stmt = $conn->prepare("SELECT id, name FROM categories WHERE user_id = ? OR user_id IS NULL");
$stmt->bind_param("i", $userId); // Fetch both user-specific categories and admin-added ones
$stmt->execute();
$stmt->bind_result($categoryId, $categoryName);
while ($stmt->fetch()) {
    $categories[] = ['id' => $categoryId, 'name' => $categoryName];
}
$stmt->close();
// Retrieve user information based on user type
if (isset($userId)) {
    if ($userType === 'user_member') {
        // Query for regular user
        $stmt = $conn->prepare("SELECT first_name, last_name, college,  school_type, teaching_status, department_or_position, grade, email FROM user_member WHERE id = ?");
    } else {
        // Query for staff user
        $stmt = $conn->prepare("SELECT first_name, last_name, department AS college,  email FROM user_staff WHERE id = ?");
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($first_name, $last_name, $college,  $school_type, $teaching_status, $department_or_position, $grade, $email);
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
        .back-btn-container {
            margin: 20px 0;
            display: flex;
            justify-content: flex-start;
        }

        .back-btn {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            font-family: 'Helvetica Neue', Arial, sans-serif;
            transition: background-color 0.3s ease;
        }

        .back-btn svg {
            margin-right: 8px;
        }

        .back-btn:hover {
            background-color: #0056b3;
        }

        .back-btn:focus {
            outline: none;
            box-shadow: 0 0 4px rgba(0, 123, 255, 0.5);
        }
        .loader-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: rgba(255, 255, 255, 0.8);
            z-index: 9999;
        }

        .loader {
            border: 8px solid #f3f3f3;
            border-top: 8px solid #3498db;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
<div id="loader" class="loader-wrapper" style="display:none;">
        <div class="loader"></div>
    </div>
    <?php require_once('inc/topBarNav.php') ?>
    <br>
    <br>
    <br>
    <div class="container">
    <h2 class="user-info">Report Missing Item</h2>
<?php if (isset($first_name) && isset($last_name) && isset($email)): ?>
    <div class="user-info">
        
        <?php if ($school_type == 3): // Guest ?>
            <!-- For Guest Users: Only show Username and User Role -->
            <p>Username: <?= htmlspecialchars($first_name . ' ' . $last_name); ?> (<?= htmlspecialchars($email); ?>)</p>
            <p>User Role: Guest</p>

        <?php elseif ($school_type == 2): // Employee ?>
            <!-- For Employees: Show Username, User Role, Teaching Status, and Department/Position -->
            <p>Username: <?= htmlspecialchars($first_name . ' ' . $last_name); ?> (<?= htmlspecialchars($email); ?>)</p>
            <p>User Role: Employee</p>
            <p>Employee Type: <?= htmlspecialchars($teaching_status ?? 'N/A'); ?></p>
            <p>Department/Position: <?= htmlspecialchars($department_or_position ?? 'N/A'); ?></p>

        <?php elseif ($school_type == 0): // High School ?>
            <!-- For High School Students: Show Grade and Username -->
            <p>User Role: Student - High School</p>
            <p>Grade: <?= htmlspecialchars($grade ?? 'N/A'); ?></p>
            <p>Username: <?= htmlspecialchars($first_name . ' ' . $last_name); ?> (<?= htmlspecialchars($email); ?>)</p>

        <?php else: // College ?>
            <!-- For College Students: Show College and Username -->
            <p>User Role: Student - College</p>
            <p>College: <?= htmlspecialchars($college); ?></p>
            <p>Username: <?= htmlspecialchars($first_name . ' ' . $last_name); ?> (<?= htmlspecialchars($email); ?>)</p>

        <?php endif; ?>

    </div>
<?php endif; ?>


        <form action="send_missing.php" method="post" enctype="multipart/form-data" class="message-form">
        <label for="owner">Owner's Name:</label>
        <?php if (isset($first_name) && isset($last_name) && isset($email)) { ?>
    <!-- Logged-in User: Autofill Finder's Name with both first and last names and Disable Field -->
    <input type="text" name="owner" id="owner" value="<?php echo htmlspecialchars($first_name . ' ' . $last_name); ?>" readonly>
    <p>Your name is automatically set as the Owner.</p>
<?php } else { ?>
    <!-- Guest User: Show Finder's Name Input -->
    <input type="text" name="owner" id="owner" placeholder="Enter owner's name" required>
<?php } ?>
            <label for="title">Item Name:</label>
            <input type="text" name="title" id="title" placeholder="Enter item name" required>
            <label for="category">Category:</label>
            <select name="category_id" id="category_id" required>
    <option value="">Select a category</option>
    <?php
    // Display available categories (user's and admin's)
    foreach ($categories as $category) {
        echo "<option value=\"{$category['id']}\">{$category['name']}</option>";
    }
    ?>
</select>
            <label for="description">Description of the missing item:</label>
            <textarea name="description" id="description" rows="4" placeholder="Describe the missing item" required></textarea>

            <label for="last_seen_location">Last Seen Location:</label>
            <input type="text" name="last_seen_location" id="last_seen_location" placeholder="Location where the item was last seen" required>
            <label for="contact">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-contact">
        <path d="M16 2v2"/>
        <path d="M7 22v-2a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2"/>
        <path d="M8 2v2"/>
        <circle cx="12" cy="11" r="3"/>
        <rect x="3" y="4" width="18" height="18" rx="2"/>
    </svg> 
    Contact Information:
</label>
<input type="text" id="contact" name="contact" pattern="^09[0-9]{9}$" required>
<span id="contactError"></span>
            <label for="time_missing">Time Missing:</label>
            <input type="datetime-local" name="time_missing" id="time_missing" required>
           
            <label for="images">Please upload an image file of the item:</label> <!-- Added the text label here -->
            <input type="file" name="images[]" id="images" accept=".jpg,.jpeg,.png,.gif" multiple onchange="previewImages()" required>
            <p>Supported image file formats: <strong>jpg, jpeg, png, gif</strong>.</p>
            <div class="image-preview-container" id="imagePreviewContainer"></div>
            <p id="fileValidationMessage" style="color: red; display: none;">Supported file types: jpg, jpeg, png, gif.</p>
            <p id="imageUploadError" style="color: red; display: none;">The maximum number of images to be uploaded is 6</p>
            <p>Upload multiple images if necessary.</p>
            <button type="submit" class="submit-btn"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send">
        <line x1="22" x2="11" y1="2" y2="13"/>
        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
    </svg>Submit Report</button>
        </form>
        <div class="back-btn-container">
    <button class="back-btn" onclick="history.back()">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left">
            <line x1="19" y1="12" x2="5" y2="12"/>
            <polyline points="12 19 5 12 12 5"/>
        </svg>
        Back
    </button>
</div>
    </div>
    <script>
      function previewImages() {
    const previewContainer = document.getElementById('imagePreviewContainer');
    const validationMessage = document.getElementById('fileValidationMessage');
    const files = document.getElementById('images').files;
    const submitButton = document.querySelector('.submit-btn'); // Grab the submit button
    const imageUploadError = document.getElementById('imageUploadError'); // Grab the error message element

    // Reset previous messages and previews
    previewContainer.innerHTML = '';
    validationMessage.style.display = 'none';
    imageUploadError.style.display = 'none'; // Hide the error message by default
    submitButton.disabled = false; // Reset the button to enabled state by default

    if (files.length > 6) {
        // If more than 6 images are uploaded, disable the submit button and show an error message
        imageUploadError.style.display = 'block'; // Show the error message in red
        submitButton.disabled = true; // Disable the submit button
        return; // Stop further execution if limit exceeded
    }

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

document.addEventListener('DOMContentLoaded', function() {
        const dateTimeInput = document.getElementById('time_missing');

        // Get the current date and time in the format required for the datetime-local input
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0'); // Months are zero-indexed
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');

        // Format: YYYY-MM-DDTHH:MM (this is the format datetime-local expects)
        const maxDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;

        // Set the max attribute to restrict future dates
        dateTimeInput.max = maxDateTime;
    });
    const contactInput = document.getElementById('contact');
    const contactError = document.getElementById('contactError');

    // Add input event listener for real-time validation
    contactInput.addEventListener('input', function () {
        if (this.validity.patternMismatch) {
            contactError.textContent = "Contact number must start with 09 and be exactly 11 digits.";
            contactError.style.color = 'red';
        } else {
            contactError.textContent = ""; // Clear the error message if valid
        }
    });
    document.addEventListener('DOMContentLoaded', function () {
            // Show loader on form submission
            const form = document.querySelector('.message-form');
            form.addEventListener('submit', function () {
                document.getElementById('loader').style.display = 'flex';
            });

            <?php if (isset($alertMessage)): ?>
                Swal.fire({
                    icon: '<?php echo isset($error) ? 'error' : 'success'; ?>',
                    title: '<?php echo isset($error) ? 'Oops!' : 'Success!'; ?>',
                    text: '<?php echo htmlspecialchars($alertMessage); ?>',
                    confirmButtonText: 'OK',
                    didClose: () => {
                        document.getElementById('loader').style.display = 'none'; // Hide loader after SweetAlert
                    }
                });
            <?php endif; ?>
        });
    </script>
    <?php require_once('inc/footer.php') ?>
</body>
</html>

<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('config.php'); // Include your DB connection

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in as either regular user or staff
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
    $message = $_POST['message'];
    $landmark = $_POST['landmark']; // Existing field
    $title = $_POST['title']; // New field
    $timeFound = $_POST['time_found']; // New field
    $contact = $_POST['contact'];
    $category_id = $_POST['category_id'];
    $new_category = $_POST['new_category'];
    $founder = $_POST['founder'];

    if ($category_id == 'add_new' && !empty($new_category)) {
        // Determine if the user is a guest (no user_id means guest)
        $is_guest = !isset($_SESSION['user_id']) ? 1 : 0;
    
        // Insert new category with user_id to make it private or set as guest category
        $stmt = $conn->prepare("INSERT INTO categories (name, user_id, is_guest, status) VALUES (?, ?, ?, 0)");
        $stmt->bind_param("sii", $new_category, $userId, $is_guest); // Add 'is_guest' flag
        $stmt->execute();
        $category_id = $stmt->insert_id; // Use the new category ID
        $stmt->close();
    }
    
    

    // Proceed to save the rest of the information
    $status = 0; // Default to 'Pending'

    // Directory for uploading files
    $uploadDir = 'uploads/items/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
    }

// Insert the report message into the database
$stmt = $conn->prepare("INSERT INTO message_history (user_id, message, landmark, title, time_found, contact, founder, category_id, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssssis", $userId, $message, $landmark, $title, $timeFound, $contact, $founder, $category_id, $status);

if ($stmt->execute()) {
    $messageId = $stmt->insert_id; // Get the ID of the newly inserted message

    if ($is_guest == 1 && isset($category_id)) {
        // Mark the category as hidden from other guest users after submission
        $stmt = $conn->prepare("UPDATE categories SET status = 0 WHERE id = ? AND is_guest = 1");
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $stmt->close();
    }
    
    

} else {
    // Handle insertion error
    $error = "Failed to submit the report: " . $stmt->error;
}
$stmt->close();


    if (count($_FILES['images']['tmp_name']) < 1 || count($_FILES['images']['tmp_name']) > 6) {
        $error = "You must upload between 1 to 6 images.";
    } else {
        // Handle file uploads
        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            $fileName = basename($_FILES['images']['name'][$key]);
            $targetFilePath = $uploadDir . $fileName;
    
            if (move_uploaded_file($tmpName, $targetFilePath)) {
                $stmt = $conn->prepare("INSERT INTO message_images (message_id, image_path) VALUES (?, ?)");
                $stmt->bind_param("is", $messageId, $fileName); // Store just the filename in the database
                $stmt->execute();
                $stmt->close();
            } else {
                $error = "Failed to upload file: " . $fileName;
            }
        }
    }
    

    // Success or error message for SweetAlert
    $alertMessage = isset($error) ? $error : "Your report has been submitted successfully. It will be reviewed by the admins, and you must surrender the item to the SSG office located at OSA Building 3rd floor before it is published for public viewing.";
}
$categories = [];

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] === 'guest') {
    // Show only admin categories or categories added by this guest user during the session
    $stmt = $conn->prepare("SELECT id, name FROM categories WHERE (user_id IS NULL OR (user_id = ? AND is_guest = 1)) AND status = 1");
    $stmt->bind_param("i", $userId);
} else {
    // For logged-in users, show their own categories, admin categories, and published guest categories
    $stmt = $conn->prepare("SELECT id, name FROM categories WHERE (user_id IS NULL OR user_id = ? OR (is_guest = 1 AND status = 1))");
    $stmt->bind_param("i", $userId);
}

$stmt->execute();
$stmt->bind_result($categoryId, $categoryName);
while ($stmt->fetch()) {
    $categories[] = ['id' => $categoryId, 'name' => $categoryName];
}
$stmt->close();



// Retrieve user information based on user type
if (isset($userId)) {
    if ($userType === 'user_member') {
        $stmt = $conn->prepare("SELECT first_name, last_name, college, school_type, email FROM user_member WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($first_name, $last_name, $college, $school_type, $email);
    } else {
        $stmt = $conn->prepare("SELECT first_name, last_name, department AS college, email FROM user_staff WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($first_name, $last_name, $college, $email);
    }

    // Fetch the result and close the statement
    $stmt->fetch();
    $stmt->close();

    // Map the numeric school_type to a string value, only if $userType is 'user_member'
    $schoolTypeString = '';
    if ($userType === 'user_member') {
        if ($school_type == 0) {
            $schoolTypeString = 'High School';
        } elseif ($school_type == 1) {
            $schoolTypeString = 'College';
        } else {
            $schoolTypeString = 'N/A';
        }
    }
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
        <!-- Display user information -->
        <h2 class="user-info">Report Found Item</h2>
        <?php if (isset($first_name) && isset($last_name) && isset($email) && isset($school_type) && isset($college)): ?>
            <div class="user-info">
    <p>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-graduation-cap">
            <path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/>
            <path d="M22 10v6"/>
            <path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/>
        </svg>
        Level: <?php echo htmlspecialchars($schoolTypeString); ?>
    </p>
    <p>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-graduation-cap">
            <path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/>
            <path d="M22 10v6"/>
            <path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/>
        </svg>
        College: <?php echo htmlspecialchars($college); ?>
    </p>
    <p>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-paperclip">
            <path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
        </svg>
        Username: <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?> (<?php echo htmlspecialchars($email); ?>)
    </p>
</div>

<?php endif; ?>
         
        <form action="send_message.php" method="post" enctype="multipart/form-data" class="message-form">
        <label for="founder">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user">
        <path d="M12 14c-4.28 0-8 3.58-8 8h16c0-4.42-3.72-8-8-8z"/>
        <circle cx="12" cy="6" r="4"/>
    </svg> Finder's Name:
</label>

<?php if (isset($first_name) && isset($last_name) && isset($email)) { ?>
    <!-- Logged-in User: Autofill Finder's Name with both first and last names and Disable Field -->
    <input type="text" name="founder" id="founder" value="<?php echo htmlspecialchars($first_name . ' ' . $last_name); ?>" readonly>
    <p>Your name is automatically set as the Finder.</p>
<?php } else { ?>
    <!-- Guest User: Show Finder's Name Input -->
    <input type="text" name="founder" id="founder" placeholder="Enter finder's name" required>
<?php } ?>
<label for="title">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pin"><path d="M12 17v5"/><path d="M9 10.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 5 15.24V16a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 15 10.76V7a1 1 0 0 1 1-1 2 2 0 0 0 0-4H8a2 2 0 0 0 0 4 1 1 0 0 1 1 1z"/></svg>
                </svg> Item Name:
            </label>
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
    <option value="add_new">Add other category</option>
</select>

<div id="newCategoryDiv" style="display: none;">
    <label for="new_category">Others:</label>
    <input type="text" name="new_category" id="new_category" placeholder="Enter new category name">
</div>


            <label for="landmark"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-location"><path d="M21 10a9 9 0 0 0-18 0c0 5.6 9 12 9 12s9-6.4 9-12z"/><path d="M12 10a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></svg> Location where the item was found:</label>
            <input type="text" name="landmark" id="landmark" placeholder="Location details" required>

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
<label for="time_found">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-history">
        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
        <path d="M3 3v5h5"/>
        <path d="M12 7v5l4 2"/>
    </svg>
    Time Found:
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
<input type="file" name="images[]" id="images" accept=".jpg,.jpeg,.png,.gif" multiple onchange="previewImages()">
<p>Supported image file formats: <strong>jpg, jpeg, png, gif</strong>.</p>
<div class="image-preview-container" id="imagePreviewContainer"></div>
<p id="fileValidationMessage" style="color: red; display: none;">Supported file types: jpg, jpeg, png, gif.</p>
<p id="imageUploadError" style="color: red; display: none;">You must only upload between 1 to 6 images.</p>
<button type="submit" class="submit-btn">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send">
        <line x1="22" x2="11" y1="2" y2="13"/>
        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
    </svg> Send Report
</button>
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

    <?php require_once('inc/footer.php') ?>

    <script>
       // Add this JavaScript function to validate the file size before upload
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
       // Show the new category input field when 'Add New Category' is selected
      // Show the new category input field when 'Add New Category' is selected
document.getElementById('category_id').addEventListener('change', function() {
    document.getElementById('newCategoryDiv').style.display = this.value === 'add_new' ? 'block' : 'none';
});



document.addEventListener('DOMContentLoaded', function() {
        const dateTimeInput = document.getElementById('time_found');

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
    </script>
</body>
</html>
<?php
include '../../config.php';

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db'); // Replace with your actual DB connection details

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize $result as null
$result = null;

if (isset($_GET['id'])) {
    $itemId = $_GET['id'];

    // Prepare the SQL statement
    $stmt = $conn->prepare("SELECT 
                mi.id, 
                mi.title, 
                mi.description, 
                mi.last_seen_location, 
                mi.time_missing, 
                mi.status, 
                mi.created_at, 
                um.email, 
                um.college,
                um.avatar, 
                GROUP_CONCAT(mii.image_path) AS images 
            FROM missing_items mi
            LEFT JOIN user_member um ON mi.user_id = um.id
            LEFT JOIN missing_item_images mii ON mi.id = mii.missing_item_id
            WHERE mi.id = ?
            GROUP BY mi.id, um.email, um.college, um.avatar"); // Group by all non-aggregated columns
    
    $stmt->bind_param('i', $itemId); // Bind the integer value
    $stmt->execute();
    $result = $stmt->get_result();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Missing Items - Admin View</title>
    <?php require_once('../inc/header.php'); ?>
    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            padding-top: 70px; /* Adjust this according to the height of your navbar */
            background-color: #f4f4f4;
        }
        .container {
            margin: 30px auto;
            width: 90%;
            max-width: 1200px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .message-box {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
        }
        .message-box p {
            margin: 10px 0;
        }
        .message-box img {
            max-width: 100%;
            border-radius: 5px;
            transition: transform 0.3s ease;
        }
        .message-box img:hover {
            transform: scale(1.1);
        }
        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
        }
        .delete-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            position: absolute;
            bottom: 20px;
            right: 20px;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
        .publish-btn {
            background-color: #28a745; /* Green background color */
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            position: absolute;
            bottom: 20px;
            right: 80px; /* Position it to the left of the delete button */
        }
        .publish-btn:hover {
            background-color: #218838; /* Darker green on hover */
        }
        .container .avatar {
            width: 100px; /* Set the width of the avatar */
            height: 100px; /* Set the height of the avatar to the same value as width for a circle */
            border-radius: 100%; /* Makes the image circular */
            object-fit: cover; /* Ensures the image covers the circle without distortion */
            display: block; /* Ensures the image is displayed as a block element */
            margin-bottom: 10px; /* Adds space below the image if needed */
        }
    </style>
</head>
<body>
    <?php require_once('../inc/topBarNav.php'); ?>
    <?php require_once('../inc/navigation.php'); ?>

    <div class="container">
        <h1>View Missing Item Details</h1>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $images = explode(',', $row['images']); // Convert image paths to an array

                echo "<div class='message-box'>";
                $firstName = htmlspecialchars($row['first_name'] ?? '');
                $email = htmlspecialchars($row['email'] ?? '');
                $college = htmlspecialchars($row['college'] ?? '');
                $title = htmlspecialchars($row['title'] ?? '');
                $lastSeenLocation = htmlspecialchars($row['last_seen_location'] ?? '');
                $description = htmlspecialchars($row['description'] ?? '');
                $avatar = htmlspecialchars($row['avatar'] ?? '');
                $timeMissing = htmlspecialchars($row['time_missing'] ?? '');
                if ($row['image_path']) {
                    $fullImagePath = base_url . 'uploads/items/' . $row['image_path'];
                    $messages[$row['id']]['images'][] = $fullImagePath;
                }
            }
            
                if ($avatar) {
                    $fullAvatar = base_url . 'uploads/avatars/' . $avatar;
                    echo "<img src='" . htmlspecialchars($fullAvatar) . "' alt='Avatar' class='avatar'>";
                } else {
                    echo "<img src='uploads/avatars/default-avatar.png' alt='Default Avatar' class='avatar'>";
                }
                
                echo "<p><strong>User:</strong> " . $firstName . " (" . $email . ")</p>";
                echo "<p><strong>College:</strong> " . $college . "</p>";
                echo "<p><strong>Last Seen Location:</strong> " . $lastSeenLocation . "</p>";
                echo "<p><strong>Title:</strong> " . $title . "</p>";
                echo "<p><strong>Description:</strong> " . $description . "</p>";
                echo "<p><strong>Time Missing:</strong> " . $timeMissing . "</p>";
                
                if (!empty($images)) {
                    echo "<p><strong>Images:</strong></p>";
                    echo "<div class='image-grid'>";
                    foreach ($images as $imagePath) {
                        $fullImagePath = '/uploads/items/' . htmlspecialchars($imagePath);  // Directly construct the image path
                        echo "<a href='" . htmlspecialchars($fullImagePath) . "' data-lightbox='message-" . htmlspecialchars($msgId) . "' data-title='Image'>
                                <img src='" . htmlspecialchars($fullImagePath) . "' alt='Image'>
                              </a>";
                    }
                    echo "</div>";
                }
                
                
                
                // Add both buttons
                echo "<button class='publish-btn' data-id='" . htmlspecialchars($row['id']) . "'>Publish</button>";
                echo "<button class='delete-btn' data-id='" . htmlspecialchars($row['id']) . "'>Delete</button>";
                echo "</div>";
            }
        }
        ?>
    </div>

    <!-- Include JavaScript files -->
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/custom.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox-plus-jquery.min.js"></script>

    <script>
      $(document).ready(function() {
        $('.delete-btn').on('click', function() {
            var messageId = $(this).data('id');
            if (confirm('Are you sure you want to delete this message?')) {
                $.ajax({
                    url: 'delete_message.php',
                    type: 'POST',
                    data: { id: messageId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Message deleted successfully.');
                            location.reload();
                        } else {
                            alert('Failed to delete the message: ' + response.error);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error:", status, error);
                        alert('An error occurred: ' + error);
                    }
                });
            }
        });

        $('.publish-btn').on('click', function() {
            var messageId = $(this).data('id');
            if (confirm('Are you sure you want to publish this message?')) {
                $.ajax({
                    url: 'publish_message.php',
                    type: 'POST',
                    data: { id: messageId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Message published successfully.');
                            location.reload();
                        } else {
                            alert('Failed to publish the message: ' + response.error);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error:", status, error);
                        alert('An error occurred: ' + error);
                    }
                });
            }
        });

      });
    </script>
</body>
</html>
<?php require_once('../inc/footer.php') ?>
<?php
$conn->close();
?>

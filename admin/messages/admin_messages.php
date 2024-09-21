<?php
include '../../config.php';

// Database connection
$conn = new mysqli('localhost','u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Updated SQL query to include category and contact fields
$sql = "SELECT mh.id, mh.message, mi.image_path, mh.title, mh.landmark, um.first_name, um.college, um.email, um.avatar, mh.time_found, 
        c.name as category_name, mh.contact
        FROM message_history mh
        LEFT JOIN message_images mi ON mh.id = mi.message_id
        LEFT JOIN user_member um ON mh.user_id = um.id
        LEFT JOIN categories c ON mh.category_id = c.id
        ORDER BY mh.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Admin View</title>
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

        /* Container to center content */
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

        /* Flexbox design for grid layout */
        .messages-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: space-between;
        }

        /* Styling each message-box */
        .message-box {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            flex-basis: 48%; /* Two items per row */
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* Message details */
        .message-details {
            flex: 1;
        }

        .message-box img {
            max-width: 100%;
            border-radius: 5px;
            transition: transform 0.3s ease;
        }

        .message-box img:hover {
            transform: scale(1.1);
        }

        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
        }

        /* Flex container for avatar and text details */
        .user-info {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-bottom: 15px;
        }

        .user-info p {
            margin: 0;
        }

        .delete-btn, .publish-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 15px;
            transition: background-color 0.3s ease;
        }

        .publish-btn {
            background-color: #28a745;
            margin-right: 10px;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        .publish-btn:hover {
            background-color: #218838;
        }

        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
        }

        /* Responsive adjustments */
        @media(max-width: 768px) {
            .message-box {
                flex-basis: 100%; /* Full width on smaller screens */
            }
        }
    </style>
</head>
<body>
    <?php require_once('../inc/topBarNav.php'); ?>
    <?php require_once('../inc/navigation.php'); ?>

    <div class="container">
        <h1>Reported Items</h1>
        <div class="messages-grid">
            <?php
            if ($result->num_rows > 0) {
                $messages = [];
                while ($row = $result->fetch_assoc()) {
                    if (!isset($messages[$row['id']])) {
                        $messages[$row['id']] = [
                            'message' => $row['message'], 
                            'images' => [],
                            'first_name' => $row['first_name'],
                            'landmark' => $row['landmark'],
                            'title' => $row['title'],
                            'college' => $row['college'],
                            'email' => $row['email'],
                            'avatar' => $row['avatar'],
                            'time_found' => $row['time_found'],
                            'category_name' => $row['category_name'],
                            'contact' => $row['contact'] // Add contact information
                        ];
                    }
                    if ($row['image_path']) {
                        // Construct the correct URL to the image
                        $fullImagePath = base_url . 'uploads/items/' . $row['image_path'];
                        $messages[$row['id']]['images'][] = $fullImagePath;
                    }
                }

                foreach ($messages as $msgId => $msgData) {
                    echo "<div class='message-box'>";
                    echo "<div class='message-details'>";
                    // Ensure values are strings or use default empty strings
                    $firstName = htmlspecialchars($msgData['first_name'] ?? '');
                    $email = htmlspecialchars($msgData['email'] ?? '');
                    $college = htmlspecialchars($msgData['college'] ?? '');
                    $title = htmlspecialchars($msgData['title'] ?? '');
                    $landmark = htmlspecialchars($msgData['landmark'] ?? '');
                    $message = htmlspecialchars($msgData['message'] ?? '');
                    $avatar = htmlspecialchars($msgData['avatar'] ?? '');
                    $timeFound = htmlspecialchars($msgData['time_found'] ?? '');
                    $categoryName = htmlspecialchars($msgData['category_name'] ?? ''); // Add this line
                    $contact = htmlspecialchars($msgData['contact'] ?? ''); // Add this line

                    // Avatar and user info
                    echo "<div class='user-info'>";
                    if ($avatar) {
                        $fullAvatar = base_url . 'uploads/avatars/' . $avatar;
                        echo "<img src='" . htmlspecialchars($fullAvatar) . "' alt='Avatar' class='avatar'>";
                    } else {
                        echo "<img src='uploads/avatars/default-avatar.png' alt='Default Avatar' class='avatar'>";
                    }
                    echo "<div>";
                    echo "<p><strong>User:</strong> " . $firstName . " (" . $email . ")</p>";
                    echo "<p><strong>College:</strong> " . $college . "</p>";
                    echo "</div></div>";

                    echo "<p><strong>Landmark:</strong> " . $landmark . "</p>";
                    echo "<p><strong>Title:</strong> " . $title . "</p>";
                    echo "<p><strong>Description:</strong> " . $message . "</p>";
                    echo "<p><strong>Time Found:</strong> " . $timeFound . "</p>";
                    echo "<p><strong>Category:</strong> " . $categoryName . "</p>";
                    echo "<p><strong>Contact:</strong> " . $contact . "</p>";

                    if (!empty($msgData['images'])) {
                        echo "<p><strong>Images:</strong></p>";
                        echo "<div class='image-grid'>";
                        foreach ($msgData['images'] as $imagePath) {
                            // Add Lightbox attributes
                            echo "<a href='" . htmlspecialchars($imagePath) . "' data-lightbox='message-" . htmlspecialchars($msgId) . "' data-title='Image'><img src='" . htmlspecialchars($imagePath) . "' alt='Image'></a>";
                        }
                        echo "</div>";
                    }
                    echo "</div>";
                    
                    // Buttons for publish and delete
                    echo "<div class='action-buttons'>";
                    echo "<button class='publish-btn' data-id='" . htmlspecialchars($msgId) . "'>Publish</button>";
                    echo "<button class='delete-btn' data-id='" . htmlspecialchars($msgId) . "'>Delete</button>";
                    echo "</div>";
                    echo "</div>";
                }
            }
            ?>
        </div>
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
                    url: '../delete_message.php',
                    type: 'POST',
                    data: { id: messageId },
                    success: function(response) {
                        console.log('Delete response:', response); 
                        location.reload();
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Delete AJAX error:', textStatus, errorThrown); 
                    }
                });
            }
        });

        $('.publish-btn').on('click', function() {
            var messageId = $(this).data('id');
            if (confirm('Are you sure you want to publish this message?')) {
                $.ajax({
                    url: '../publish_message.php',
                    type: 'POST',
                    data: { id: messageId },
                    success: function(response) {
                        console.log('Publish response:', response); 
                        location.reload();
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Publish AJAX error:', textStatus, errorThrown); 
                    }
                });
            }
        });
      });
    </script>

</body>
</html>

<?php
$conn->close();
?>

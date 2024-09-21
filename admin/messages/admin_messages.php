<?php
include '../../config.php';

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts for Modern Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            padding-top: 70px;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #343a40;
        }
        .message-box {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
        }
        .message-box p {
            margin: 10px 0;
        }
        .message-box img {
            width: 100%;
            border-radius: 8px;
            transition: transform 0.3s ease;
        }
        .message-box img:hover {
            transform: scale(1.05);
        }
        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }
        .action-btns {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 15px;
        }
        .btn-custom {
            border-radius: 5px;
            padding: 10px 20px;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .btn-publish {
            background-color: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Reported Items</h1>
        <div class="row">
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
                        'contact' => $row['contact']
                    ];
                }
                if ($row['image_path']) {
                    $fullImagePath = base_url . 'uploads/items/' . $row['image_path'];
                    $messages[$row['id']]['images'][] = $fullImagePath;
                }
            }

            foreach ($messages as $msgId => $msgData) {
                echo "<div class='col-md-6 col-lg-4'>";
                echo "<div class='message-box'>";
                
                $firstName = htmlspecialchars($msgData['first_name'] ?? '');
                $email = htmlspecialchars($msgData['email'] ?? '');
                $college = htmlspecialchars($msgData['college'] ?? '');
                $title = htmlspecialchars($msgData['title'] ?? '');
                $landmark = htmlspecialchars($msgData['landmark'] ?? '');
                $message = htmlspecialchars($msgData['message'] ?? '');
                $avatar = htmlspecialchars($msgData['avatar'] ?? '');
                $timeFound = htmlspecialchars($msgData['time_found'] ?? '');
                $categoryName = htmlspecialchars($msgData['category_name'] ?? '');
                $contact = htmlspecialchars($msgData['contact'] ?? '');

                echo "<img src='" . ($avatar ? base_url . 'uploads/avatars/' . $avatar : 'uploads/avatars/default-avatar.png') . "' alt='Avatar' class='avatar mb-3'>";
                echo "<p><strong>User:</strong> " . $firstName . " (" . $email . ")</p>";
                echo "<p><strong>College:</strong> " . $college . "</p>";
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
                        echo "<a href='" . htmlspecialchars($imagePath) . "' data-lightbox='message-" . htmlspecialchars($msgId) . "'><img src='" . htmlspecialchars($imagePath) . "' alt='Image'></a>";
                    }
                    echo "</div>";
                }
                echo "<div class='action-btns'>";
                echo "<button class='btn btn-custom btn-publish' data-id='" . htmlspecialchars($msgId) . "'>Publish</button>";
                echo "<button class='btn btn-custom btn-delete' data-id='" . htmlspecialchars($msgId) . "'>Delete</button>";
                echo "</div></div></div>";
            }
        }
        ?>
        </div>
    </div>

    <!-- Bootstrap 5 and JS dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox-plus-jquery.min.js"></script>

    <script>
        // Delete message AJAX
        $('.btn-delete').on('click', function() {
            var messageId = $(this).data('id');
            if (confirm('Are you sure you want to delete this message?')) {
                $.ajax({
                    url: '../delete_message.php',
                    type: 'POST',
                    data: { id: messageId },
                    success: function(response) {
                        location.reload();
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Error:', textStatus, errorThrown);
                    }
                });
            }
        });

        // Publish message AJAX
        $('.btn-publish').on('click', function() {
            var messageId = $(this).data('id');
            if (confirm('Are you sure you want to publish this message?')) {
                $.ajax({
                    url: '../publish_message.php',
                    type: 'POST',
                    data: { id: messageId },
                    success: function(response) {
                        location.reload();
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Error:', textStatus, errorThrown);
                    }
                });
            }
        });
    </script>

</body>
</html>

<?php
$conn->close();
?>

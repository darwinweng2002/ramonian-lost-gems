<?php
include '../config.php';
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: login.php'); // Adjust this path if necessary
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get item ID from URL
$itemId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// SQL query to get published item details
$sql = "SELECT mh.id, mh.message, mi.image_path, mh.title, mh.status, mh.landmark, mh.time_found, um.first_name, um.college, um.email, um.avatar, 
        mh.contact, c.name as category_name
        FROM message_history mh
        LEFT JOIN message_images mi ON mh.id = mi.message_id
        LEFT JOIN user_member um ON mh.user_id = um.id
        LEFT JOIN categories c ON mh.category_id = c.id
        WHERE mh.is_published = 1 AND mh.id = ?
        ORDER BY mh.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $itemId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php require_once('../inc/header.php') ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Published Item Details</title>
    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            padding-top: 70px;
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
        .container .avatar {
            width: 100px; /* Set the width of the avatar */
            height: 100px; /* Set the height of the avatar to the same value as width for a circle */
            border-radius: 100%; /* Makes the image circular */
            object-fit: cover; /* Ensures the image covers the circle without distortion */
            display: block; /* Ensures the image is displayed as a block element */
            margin-bottom: 10px; /* Adds space below the image if needed */
        }
        .claim-button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #28a745; /* Green color */
            border: none;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }

        .claim-button:hover {
            background-color: #218838; /* Darker green */
            color: #fff;
        }

        .claim-button-container {
            display: flex;
            justify-content: center; /* Center the button */
            margin-top: 20px;
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

    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php') ?>
    <div class="container">
        <h1>Found Items</h1>
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
                        'status' => $row['status'], // Fetch the status
                        'college' => $row['college'],
                        'email' => $row['email'],
                        'avatar' => $row['avatar'],
                        'time_found' => $row['time_found'],
                        'contact' => $row['contact'],
                        'category_name' => $row['category_name']
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
                $firstName = htmlspecialchars($msgData['first_name'] ?? '');
                $email = htmlspecialchars($msgData['email'] ?? '');
                $college = htmlspecialchars($msgData['college'] ?? '');
                $title = htmlspecialchars($msgData['title'] ?? '');
                $landmark = htmlspecialchars($msgData['landmark'] ?? '');
                $message = htmlspecialchars($msgData['message'] ?? '');
                $avatar = htmlspecialchars($msgData['avatar'] ?? '');
                $timeFound = htmlspecialchars($msgData['time_found'] ?? '');
                $contact = htmlspecialchars($msgData['contact'] ?? '');
                $categoryName = htmlspecialchars($msgData['category_name'] ?? '');
                $status = intval($msgData['status']); // Get the correct status


                if ($avatar) {
                    $fullAvatar = base_url . 'uploads/avatars/' . $avatar;
                    echo "<img src='" . htmlspecialchars($fullAvatar) . "' alt='Avatar' class='avatar'>";
                } else {
                    echo "<img src='uploads/avatars/default-avatar.png' alt='Default Avatar' class='avatar'>";
                }
                echo "<p><strong>Item Name:</strong> " . $title . "</p>";
                echo "<p><strong>Category:</strong> " . $categoryName . "</p>";
                echo "<p><strong>Founder Name:</strong> " . $firstName . " (" . $email . ")</p>";
                echo "<p><strong>College:</strong> " . $college . "</p>";
                echo "<p><strong>Location where the item was found:</strong> " . $landmark . "</p>";
                echo "<p><strong>Date and Time Found:</strong> " . $timeFound . "</p>";
                echo "<p><strong>Description:</strong> " . $message . "</p>";
                echo "<p><strong>Contact:</strong> " . $contact . "</p>";

                echo "<dt class='text-muted'>Status</dt>";
                echo "<dd class='ps-4'>";
                if ($status == 1) {
                    echo "<span class='badge bg-primary px-3 rounded-pill'>Published</span>";
                } elseif ($status == 2) {
                    echo "<span class='badge bg-success px-3 rounded-pill'>Claimed</span>";
                } elseif ($status == 3) {
                    echo "<span class='badge bg-secondary px-3 rounded-pill'>Surrendered</span>";
                } else {
                    echo "<span class='badge bg-secondary px-3 rounded-pill'>Pending</span>";   
                }
                echo "</dd>";


                if (!empty($msgData['images'])) {
                    echo "<p><strong>Images:</strong></p>";
                    echo "<div class='image-grid'>";
                    foreach ($msgData['images'] as $imagePath) {
                        echo "<a href='" . htmlspecialchars($imagePath) . "' data-lightbox='message-" . htmlspecialchars($msgId) . "' data-title='Image'><img src='" . htmlspecialchars($imagePath) . "' alt='Image'></a>";
                    }
                    echo "</div>";
                }

                // Add Claim Request Button
                echo '<div class="claim-button-container">';
                echo '<a href="https://ramonianlostgems.com/itemss/claim.php?id=' . htmlspecialchars($msgId) . '" class="claim-button">Send claim request.</a>';
                echo '</div>';


                echo "</div>";
            }
        } else {
            echo "<p>No details available for this item.</p>";
        }
        ?>
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
    <?php require_once('../inc/footer.php') ?>
    <script src="../js/jquery.min.js"></script> <!-- Ensure this path is correct -->
    <script src="../js/bootstrap.min.js"></script> <!-- Ensure this path is correct -->
    <script src="../js/custom.js"></script> <!-- Ensure this path is correct -->
    <script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox-plus-jquery.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
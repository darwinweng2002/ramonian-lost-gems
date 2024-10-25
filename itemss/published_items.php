<?php
include '../config.php';

// Check if user is logged in
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

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get item ID from URL
$itemId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// SQL query to get published item details with user info from both user_member and user_staff
$sql = "SELECT mh.id, mh.message, mi.image_path, mh.title, mh.founder, mh.status, mh.landmark, mh.time_found, 
        user_info.first_name, user_info.last_name, user_info.college, user_info.email, user_info.avatar, user_info.user_type, user_info.position, mh.contact, c.name as category_name
        FROM message_history mh
        LEFT JOIN message_images mi ON mh.id = mi.message_id
        LEFT JOIN (
            -- Fetch data from user_member with a flag
            SELECT id AS user_id, first_name, last_name, college, school_type, email, avatar, 'member' AS user_type, NULL AS position FROM user_member
            UNION
            -- Fetch data from user_staff with a flag
            SELECT id AS user_id, first_name, last_name, department AS college, NULL AS school_type, email, avatar, 'staff' AS user_type, position FROM user_staff
        ) AS user_info ON mh.user_id = user_info.user_id
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
            width: 100px; 
            height: 100px; 
            border-radius: 100%; 
            object-fit: cover;
            display: block;
            margin-bottom: 10px;
        }
        .claim-button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #28a745; 
            border: none;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }

        .claim-button:hover {
            background-color: #218838;
            color: #fff;
        }

        .claim-button-container {
            display: flex;
            justify-content: center;
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
                        'last_name' => $row['last_name'],
                        'landmark' => $row['landmark'],
                        'founder' => $row['founder'],
                        'title' => $row['title'],
                        'status' => $row['status'], 
                        'college' => $row['college'],
                        'school_type' => $row['school_type'],
                        'email' => $row['email'],
                        'avatar' => $row['avatar'],
                        'time_found' => $row['time_found'],
                        'contact' => $row['contact'],
                        'category_name' => $row['category_name']
                    ];
                }
                if ($row['image_path']) {
                    $fullImagePath = base_url . 'uploads/items/' . $row['image_path'];
                    $messages[$row['id']]['images'][] = $fullImagePath;
                }
            }

            foreach ($messages as $msgId => $msgData) {
                echo "<div class='message-box'>";
                $firstName = htmlspecialchars($msgData['first_name'] ?? '');
                $lastName = htmlspecialchars($msgData['last_name'] ?? '');
                $email = htmlspecialchars($msgData['email'] ?? '');
                $college = htmlspecialchars($msgData['college'] ?? '');
                $school_type = htmlspecialchars($msgData['school_type'] ?? '');

// Map numeric school_type to string
$schoolTypeString = '';
if ($school_type === '0') {
    $schoolTypeString = 'High School';
} elseif ($school_type === '1') {
    $schoolTypeString = 'College';
} else {
    $schoolTypeString = 'N/A';
}

                $title = htmlspecialchars($msgData['title'] ?? '');
                $landmark = htmlspecialchars($msgData['landmark'] ?? '');
                $founder = htmlspecialchars($msgData['founder'] ?? '');
                $message = htmlspecialchars($msgData['message'] ?? '');
                $avatar = htmlspecialchars($msgData['avatar'] ?? '');
                $timeFound = htmlspecialchars($msgData['time_found'] ?? '');
                $contact = htmlspecialchars($msgData['contact'] ?? '');
                $categoryName = htmlspecialchars($msgData['category_name'] ?? '');
                $status = intval($msgData['status']);

                // Only display avatar if the post is not from a guest user
                if ($firstName || $email || $college) {
                    if ($avatar) {
                        $fullAvatar = base_url . 'uploads/avatars/' . $avatar;
                        echo "<img src='" . htmlspecialchars($fullAvatar) . "' alt='Avatar' class='avatar'>";
                    } else {
                        // Updated decent default avatar
                        echo "<img src='uploads/avatars/2.png' alt='Default Avatar' class='avatar'>";
                    }
                } else {
                    echo "<p><strong>User Info:</strong>No Info</p>";
                }

                echo "<p><strong>Item Name:</strong> " . $title . "</p>";
                echo "<p><strong>Category:</strong> " . $categoryName . "</p>";
                echo "<p><strong>Finder's Name:</strong> " . $founder . "</p>";
                echo "<p><strong>Location where the item was found:</strong> " . $landmark . "</p>";
                echo "<p><strong>Date and Time Found:</strong> " . $timeFound . "</p>";
                echo "<p><strong>Description:</strong> " . $message . "</p>";
                echo "<p><strong>Contact:</strong> " . $contact . "</p>";

                // Display user information only if available
                if ($firstName || $email || $college) {
                    echo "<p><strong>User Info:</strong> " . ($firstName ? $firstName : 'N/A') . " " . ($lastName ? $lastName : '') . " (" . ($email ? $email : 'N/A') . ")</p>";

                    if ($userType === 'staff') {
                        echo "<p><strong>Department:</strong> " . ($college ? htmlspecialchars($college) : 'N/A') . "</p>";
                        echo "<p><strong>Position:</strong> " . ($msgData['position'] ? htmlspecialchars($msgData['position']) : 'N/A') . "</p>";
                    } elseif ($userType === 'member') {
                        echo "<p><strong>Level:</strong> " . $schoolTypeString . "</p>";
                        if ($schoolTypeString === 'College') {
                            echo "<p><strong>College:</strong> " . ($college ? htmlspecialchars($college) : 'N/A') . "</p>";
                        }
                    }
                }

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

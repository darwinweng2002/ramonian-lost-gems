<?php
include '../config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db'); // Replace with your actual DB connection details

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get item ID from URL
$itemId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// SQL query to get missing item details and associated images
$sql = "SELECT mi.id, mi.description, mi.last_seen_location, mi.time_missing, mi.title, mi.status, mi.owner, 
        COALESCE(um.first_name, us.first_name) AS first_name, 
        COALESCE(um.last_name, us.last_name) AS last_name, 
        COALESCE(um.college, us.department) AS college, 
        COALESCE(um.email, us.email) AS email, 
        COALESCE(um.avatar, us.avatar) AS avatar,
        us.position, -- Fetch position for staff members
        um.school_type, -- Fetch level for regular users
        mi.contact, c.name as category_name, imi.image_path
        FROM missing_items mi
        LEFT JOIN user_member um ON mi.user_id = um.id
        LEFT JOIN user_staff us ON mi.user_id = us.id
        LEFT JOIN missing_item_images imi ON mi.id = imi.missing_item_id
        LEFT JOIN categories c ON mi.category_id = c.id
        WHERE mi.id = ?";

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
    <title>Missing Item Details</title>
    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet">
    <style>
        /* Styles */
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
        .container .avatar {
            width: 100px;
            height: 100px;
            border-radius: 100%;
            object-fit: cover;
            display: block;
            margin-bottom: 10px;
        }
        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
        }
        .claim-button-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .claim-button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #E63946;
            border: none;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .claim-button:hover {
            background-color: #E63940;
            color: #fff;
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
        <br><br><br>
        <h1>Missing Item Details</h1>
        <?php
        if ($result->num_rows > 0) {
            $items = [];
            while ($row = $result->fetch_assoc()) {
                if (!isset($items[$row['id']])) {
                    $items[$row['id']] = [
                        'description' => $row['description'],
                        'owner' => $row['owner'],
                        'last_seen_location' => $row['last_seen_location'],
                        'time_missing' => $row['time_missing'],
                        'title' => $row['title'],
                        'status' => $row['status'], 
                        'first_name' => $row['first_name'],
                        'last_name' => $row['last_name'],
                        'college' => $row['college'],
                        'school_type' => $row['school_type'], // Fetch level for regular users
                        'email' => $row['email'],
                        'avatar' => $row['avatar'],
                        'position' => $row['position'], // Store position for staff
                        'images' => [],
                        'contact' => $row['contact'],
                        'category_name' => $row['category_name']
                    ];
                }
                if ($row['image_path']) {
                    // Construct the correct URL to the image
                    $fullImagePath = base_url . 'uploads/missing_items/' . $row['image_path'];
                    $items[$row['id']]['images'][] = $fullImagePath;
                }
            }

            foreach ($items as $itemId => $itemData) {
                $firstName = htmlspecialchars($itemData['first_name'] ?? '');
                $lastName = htmlspecialchars($itemData['last_name'] ?? '');
                $email = htmlspecialchars($itemData['email'] ?? '');
                $college = htmlspecialchars($itemData['college'] ?? '');
                $school_type = htmlspecialchars($itemData['school_type'] ?? ''); // Handle level for user_member
                $title = htmlspecialchars($itemData['title'] ?? '');
                $lastSeenLocation = htmlspecialchars($itemData['last_seen_location'] ?? '');
                $description = htmlspecialchars($itemData['description'] ?? '');
                $owner = htmlspecialchars($itemData['owner'] ?? '');
                $avatar = htmlspecialchars($itemData['avatar'] ?? '');
                $timeMissing = htmlspecialchars($itemData['time_missing'] ?? '');
                $contact = htmlspecialchars($itemData['contact'] ?? '');
                $categoryName = htmlspecialchars($itemData['category_name'] ?? '');
                $status = intval($itemData['status']);
                $position = htmlspecialchars($itemData['position'] ?? ''); // Handle position

                echo "<div class='message-box'>";

                // Check if there is any user information (staff or member)
                if ($firstName || $lastName || $email) {
                    if ($avatar) {
                        $fullAvatar = base_url . 'uploads/avatars/' . $avatar;
                        echo "<img src='" . htmlspecialchars($fullAvatar) . "' alt='Avatar' class='avatar'>";
                    } else {
                        echo "<img src='uploads/avatars/default-avatar.png' alt='Default Avatar' class='avatar'>";
                    }

                    echo "<p><strong>User Info:</strong> " . ($firstName ? $firstName . " " . $lastName : 'N/A') . " (" . ($email ? $email : 'N/A') . ")</p>";

                    // Only show position for staff and college/level for members
                    if (!empty($position)) {
                        echo "<p><strong>Position:</strong> " . $position . "</p>";
                    }
                    if ($college === 'N/A' && !empty($school_type)) {
                        echo "<p><strong>Level:</strong> " . $school_type . "</p>";
                    } elseif (!empty($college)) {
                        echo "<p><strong>College:</strong> " . $college . "</p>";
                    }
                } else {
                    echo "<p><strong>User Info:</strong> Guest User</p>";
                }

                echo "<p><strong>Item Name:</strong> " . $title . "</p>";
                echo "<p><strong>Owner's Name:</strong> " . $owner . "</p>";
                echo "<p><strong>Last Seen Location:</strong> " . $lastSeenLocation . "</p>";
                echo "<p><strong>Date and time the item was lost:</strong> " . $timeMissing . "</p>";
                echo "<p><strong>Description:</strong> " . $description . "</p>";
                echo "<p><strong>Category:</strong> " . $categoryName . "</p>";
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

                if (!empty($itemData['images'])) {
                    echo "<p><strong>Images:</strong></p>";
                    echo "<div class='image-grid'>";
                    foreach ($itemData['images'] as $imagePath) {
                        echo "<a href='" . htmlspecialchars($imagePath) . "' data-lightbox='item-" . htmlspecialchars($itemId) . "' data-title='Image'><img src='" . htmlspecialchars($imagePath) . "' alt='Image'></a>";
                    }
                    echo "</div>";
                }

                echo '<div class="claim-button-container">';
                echo '<a href="https://ramonianlostgems.com/send_message.php" class="claim-button">Report if you found this item</a>';
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
    <script src="../js/jquery.min.js"></script> 
    <script src="../js/bootstrap.min.js"></script> 
    <script src="../js/custom.js"></script> 
    <script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox-plus-jquery.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

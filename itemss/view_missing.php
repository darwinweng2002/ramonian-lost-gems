<?php
include '../config.php';
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: login.php'); // Adjust this path if necessary
    exit();
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
$sql = "SELECT mi.id, mi.description, mi.last_seen_location, mi.time_missing, mi.title, um.first_name, um.college, um.email, um.avatar, imi.image_path
        FROM missing_items mi
        LEFT JOIN user_member um ON mi.user_id = um.id
        LEFT JOIN missing_item_images imi ON mi.id = imi.missing_item_id
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
            width: 100px; /* Set the width of the avatar */
            height: 100px; /* Set the height of the avatar to the same value as width for a circle */
            border-radius: 100%; /* Makes the image circular */
            object-fit: cover; /* Ensures the image covers the circle without distortion */
            display: block; /* Ensures the image is displayed as a block element */
            margin-bottom: 10px; /* Adds space below the image if needed */
        }
        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
        }
        .claim-button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #3498db; /* Blue color */
            border: none;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }
        .claim-button:hover {
            background-color: #2980b9; /* Darker blue */
            color: #fff;
        }
    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php') ?>
    <div class="container">
        <h1>Missing Items</h1>
        <?php
        if ($result->num_rows > 0) {
            $items = [];
            while ($row = $result->fetch_assoc()) {
                if (!isset($items[$row['id']])) {
                    $items[$row['id']] = [
                        'description' => $row['description'],
                        'last_seen_location' => $row['last_seen_location'],
                        'time_missing' => $row['time_missing'],
                        'title' => $row['title'],
                        'first_name' => $row['first_name'],
                        'college' => $row['college'],
                        'email' => $row['email'],
                        'avatar' => $row['avatar'],
                        'images' => []
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
                $email = htmlspecialchars($itemData['email'] ?? '');
                $college = htmlspecialchars($itemData['college'] ?? '');
                $title = htmlspecialchars($itemData['title'] ?? '');
                $lastSeenLocation = htmlspecialchars($itemData['last_seen_location'] ?? '');
                $description = htmlspecialchars($itemData['description'] ?? '');
                $avatar = htmlspecialchars($itemData['avatar'] ?? '');
                $timeMissing = htmlspecialchars($itemData['time_missing'] ?? ''); // Fetch date and time
                
                echo "<div class='message-box'>";
                
                if ($avatar) {
                    $fullAvatar = base_url . 'uploads/avatars/' . $avatar;
                    echo "<img src='" . htmlspecialchars($fullAvatar) . "' alt='Avatar' class='avatar'>";
                } else {
                    echo "<img src='uploads/avatars/default-avatar.png' alt='Default Avatar' class='avatar'>";
                }
                
                echo "<p><strong>Founder Name:</strong> " . $firstName . " (" . $email . ")</p>";
                echo "<p><strong>College:</strong> " . $college . "</p>";
                echo "<p><strong>Last Seen Location:</strong> " . $lastSeenLocation . "</p>";
                echo "<p><strong>Date and Time Missing:</strong> " . $timeMissing . "</p>"; // Display date and time
                echo "<p><strong>Title:</strong> " . $title . "</p>";
                echo "<p><strong>Description:</strong> " . $description . "</p>";
                
                if (!empty($itemData['images'])) {
                    echo "<p><strong>Images:</strong></p>";
                    echo "<div class='image-grid'>";
                    foreach ($itemData['images'] as $imagePath) {
                        echo "<a href='" . htmlspecialchars($imagePath) . "' data-lightbox='item-" . htmlspecialchars($itemId) . "' data-title='Image'><img src='" . htmlspecialchars($imagePath) . "' alt='Image'></a>";
                    }
                    echo "</div>";
                }
                
                // Add Claim Request Button
                echo "<a href='claim_request.php?id=" . urlencode($itemId) . "' class='claim-button'>Claim Request</a>";
                
                echo "</div>";
            }
        } else {
            echo "<p>No details available for this item.</p>";
        }
        ?>
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

<?php
include '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
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

// Query for item data including images
$sql = "SELECT mh.id, mh.title, mh.message, mh.landmark, mh.time_found, mh.contact, 
        mh.user_id AS finder_id, um.first_name, um.last_name, um.email, um.college, c.name AS category_name,
        GROUP_CONCAT(mi.image_path) AS image_paths
        FROM message_history mh
        LEFT JOIN user_member um ON mh.user_id = um.id
        LEFT JOIN categories c ON mh.category_id = c.id
        LEFT JOIN message_images mi ON mh.id = mi.message_id
        WHERE mh.id = ? AND mh.is_published = 1
        GROUP BY mh.id";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $itemId);
$stmt->execute();
$itemResult = $stmt->get_result();
$itemData = $itemResult->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once('../inc/header.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim This Item</title>

    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet">
    <style>
        /* Add your styles */
        .item-images img {
            max-width: 150px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php') ?>
<div class="container">
    <h1>Claim This Item</h1>

    <!-- Display Item Information -->
    <?php if ($itemData) : ?>
        <div class="info-section">
            <p>Item Name: <?= htmlspecialchars($itemData['title']); ?></p>
            <p>Category: <?= htmlspecialchars($itemData['category_name']); ?></p>
            <p>Found by: <?= htmlspecialchars($itemData['first_name'] . ' ' . $itemData['last_name']); ?></p>
            <p>Time Found: <?= htmlspecialchars($itemData['time_found']); ?></p>
            <p>Location Found: <?= htmlspecialchars($itemData['landmark']); ?></p>
            <p>Description: <?= htmlspecialchars($itemData['message']); ?></p>
            <p>Contact: <?= htmlspecialchars($itemData['contact']); ?></p>

            <!-- Display Item Images -->
            <div class="item-images">
                <h3>Item Images:</h3>
                <?php 
                if (!empty($itemData['image_paths'])) {
                    $images = explode(',', $itemData['image_paths']);
                    foreach ($images as $image) {
                        $imagePath = 'uploads/items/' . htmlspecialchars($image); // Adjust the path as needed
                        echo "<a href='$imagePath' data-lightbox='item-images'><img src='$imagePath' alt='Item Image'></a>";
                    }
                } else {
                    echo "<p>No images available for this item.</p>";
                }
                ?>
            </div>
        </div>
    <?php else : ?>
        <p>Item not found or not published.</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox-plus-jquery.min.js"></script>
<?php require_once('../inc/footer.php') ?>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

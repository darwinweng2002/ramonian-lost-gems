<?php
include '../../config.php';

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if 'id' and 'item_type' are present in the URL (item_type can be 'found' or 'missing')
if (isset($_GET['id']) && isset($_GET['item_type'])) {
    $itemId = intval($_GET['id']);
    $itemType = $_GET['item_type'];  // 'found' or 'missing'

    if ($itemType === 'missing') {
        // Fetch missing item details
        $stmt = $conn->prepare("SELECT 
                                    mi.id, 
                                    mi.title, 
                                    mi.description, 
                                    mi.last_seen_location, 
                                    mi.time_missing, 
                                    mi.created_at, 
                                    um.email, 
                                    um.college,
                                    um.avatar,
                                    mi.contact,
                                    c.name AS category_name,
                                    GROUP_CONCAT(mii.image_path) AS images 
                                FROM missing_items mi
                                LEFT JOIN user_member um ON mi.user_id = um.id
                                LEFT JOIN categories c ON mi.category_id = c.id
                                LEFT JOIN missing_item_images mii ON mi.id = mii.missing_item_id
                                WHERE mi.id = ?");
    } elseif ($itemType === 'found') {
        // Fetch found item details
        $stmt = $conn->prepare("SELECT 
                                    mh.id, 
                                    mh.title, 
                                    mh.message AS description, 
                                    mh.landmark AS last_seen_location, 
                                    mh.time_found AS time_missing, 
                                    mh.created_at, 
                                    um.email, 
                                    um.college,
                                    um.avatar,
                                    mh.contact,
                                    c.name AS category_name,
                                    GROUP_CONCAT(mi.image_path) AS images 
                                FROM message_history mh
                                LEFT JOIN user_member um ON mh.user_id = um.id
                                LEFT JOIN categories c ON mh.category_id = c.id
                                LEFT JOIN message_images mi ON mh.id = mi.message_id
                                WHERE mh.id = ?");
    } else {
        echo "<p>Invalid item type provided.</p>";
        exit();
    }

    $stmt->bind_param('i', $itemId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the item exists
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Extract necessary fields
        $title = htmlspecialchars($row['title']);
        $description = htmlspecialchars($row['description']);
        $lastSeenLocation = htmlspecialchars($row['last_seen_location']);
        $timeMissing = htmlspecialchars($row['time_missing']);
        $contact = htmlspecialchars($row['contact']);
        $email = htmlspecialchars($row['email']);
        $college = htmlspecialchars($row['college']);
        $categoryName = htmlspecialchars($row['category_name']);
        $avatar = htmlspecialchars($row['avatar']);
        $images = !empty($row['images']) ? explode(',', $row['images']) : [];
        $createdAt = date("F j, Y, g:i a", strtotime($row['created_at']));
    }
} else {
    echo "<p>No ID or item type provided in the URL.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Claimed Item</title>
    <?php require_once('../inc/header.php'); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 20px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 30px;
            color: #333;
            font-weight: 700;
        }
        .item-details {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 40px;
        }
        .item-info, .user-info {
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .item-info h2, .user-info h2 {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #444;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
        }
        .item-info p, .user-info p {
            margin-bottom: 10px;
            font-size: 1rem;
            color: #555;
        }
        .item-info p strong {
            color: #111;
        }
        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .image-grid img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .image-grid img:hover {
            transform: scale(1.05);
        }
        .user-info img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin-bottom: 15px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }
        .user-info p {
            font-size: 1rem;
            color: #666;
        }
        .user-info p strong {
            color: #333;
        }
    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php'); ?>
<?php require_once('../inc/navigation.php'); ?>

<div class="container">
    <h1>Claimed Item: <?php echo $title; ?></h1>

    <div class="item-details">
        <!-- Item Information -->
        <div class="item-info">
            <h2>Item Information</h2>
            <p><strong>Description:</strong> <?php echo $description; ?></p>
            <p><strong>Last Seen Location:</strong> <?php echo $lastSeenLocation; ?></p>
            <p><strong>Time Missing:</strong> <?php echo $timeMissing; ?></p>
            <p><strong>Contact:</strong> <?php echo $contact; ?></p>
            <p><strong>Category:</strong> <?php echo $categoryName; ?></p>
            <p><strong>Date Created:</strong> <?php echo $createdAt; ?></p>

            <!-- Images Grid -->
            <h3>Item Images</h3>
            <?php if (!empty($images)) : ?>
            <div class="image-grid">
                <?php foreach ($images as $imagePath): 
                    $fullImagePath = base_url . 'uploads/items/' . htmlspecialchars($imagePath);
                ?>
                    <a href="<?php echo $fullImagePath; ?>" data-lightbox="item-images" data-title="Item Image">
                        <img src="<?php echo $fullImagePath; ?>" alt="Image">
                    </a>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
                <p>No images available.</p>
            <?php endif; ?>
        </div>

        <!-- User Information -->
        <div class="user-info text-center">
            <h2>User Information</h2>
            <!-- Avatar -->
            <?php if ($avatar): ?>
                <img src="<?php echo base_url . 'uploads/avatars/' . $avatar; ?>" alt="User Avatar">
            <?php else: ?>
                <img src="uploads/avatars/default-avatar.png" alt="Default Avatar">
            <?php endif; ?>

            <p><strong>Email:</strong> <?php echo $email; ?></p>
            <p><strong>College:</strong> <?php echo $college; ?></p>
        </div>
    </div>
</div>

<!-- Include Lightbox JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox-plus-jquery.min.js"></script>
<?php require_once('../inc/footer.php'); ?>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

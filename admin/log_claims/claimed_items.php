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


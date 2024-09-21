<?php
include '../../config.php';

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if 'id' is present in the URL
if (isset($_GET['id'])) {
    $itemId = intval($_GET['id']);  // Make sure the ID is sanitized as an integer

    // Fetch the claimed item by ID (Only this specific item)
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
                            WHERE mi.id = ?"); // Only get the item with the specific ID
    $stmt->bind_param('i', $itemId);  // Bind the ID as an integer
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the item exists
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Your display logic here, for example:
        $title = htmlspecialchars($row['title']);
        $description = htmlspecialchars($row['description']);
        $lastSeenLocation = htmlspecialchars($row['last_seen_location']);
        $timeMissing = htmlspecialchars($row['time_missing']);
        $contact = htmlspecialchars($row['contact']);
        $email = htmlspecialchars($row['email']);
        $college = htmlspecialchars($row['college']);
        $categoryName = htmlspecialchars($row['category_name']);
        $avatar = htmlspecialchars($row['avatar']);
        
        // Output the information for this specific item
        echo "<h1>Viewing: " . $title . "</h1>";
        echo "<p><strong>Description:</strong> " . $description . "</p>";
        echo "<p><strong>Last Seen Location:</strong> " . $lastSeenLocation . "</p>";
        echo "<p><strong>Time Missing:</strong> " . $timeMissing . "</p>";
        echo "<p><strong>Contact:</strong> " . $contact . "</p>";
        echo "<p><strong>User Email:</strong> " . $email . "</p>";
        echo "<p><strong>College:</strong> " . $college . "</p>";
        echo "<p><strong>Category:</strong> " . $categoryName . "</p>";

        // Handle avatar image
        if ($avatar) {
            $fullAvatar = base_url . 'uploads/avatars/' . $avatar;
            echo "<img src='$fullAvatar' alt='Avatar' class='avatar'>";
        } else {
            echo "<img src='uploads/avatars/default-avatar.png' alt='Default Avatar' class='avatar'>";
        }

        // Handle images
        if (!empty($row['images'])) {
            $images = explode(',', $row['images']);
            echo "<div class='image-grid'>";
            foreach ($images as $imagePath) {
                $fullImagePath = base_url . 'uploads/items/' . htmlspecialchars($imagePath);
                echo "<a href='$fullImagePath' data-lightbox='claimed-images' data-title='Image'><img src='$fullImagePath' alt='Image'></a>";
            }
            echo "</div>";
        }
    } else {
        // If no item is found with this ID
        echo "<p>No claimed item found with this ID.</p>";
    }
} else {
    echo "<p>No ID provided in the URL.</p>";
}

$stmt->close();
$conn->close();
?>
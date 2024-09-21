<?php
include '../../config.php';

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch claimed items (status = 2)
$sql = "SELECT 
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
        WHERE mi.status = 2
        GROUP BY mi.id, um.email, um.college, um.avatar, mi.contact, c.name";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claimed Items History</title>
    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
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
        .claimed-box {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .claimed-box img {
            max-width: 100px;
            border-radius: 5px;
        }
        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Claimed Items History</h1>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='claimed-box'>";
                $title = htmlspecialchars($row['title']);
                $description = htmlspecialchars($row['description']);
                $lastSeenLocation = htmlspecialchars($row['last_seen_location']);
                $timeMissing = htmlspecialchars($row['time_missing']);
                $contact = htmlspecialchars($row['contact']);
                $email = htmlspecialchars($row['email']);
                $college = htmlspecialchars($row['college']);
                $categoryName = htmlspecialchars($row['category_name']);
                $avatar = htmlspecialchars($row['avatar']);
                
                // Displaying avatar or default image
                if ($avatar) {
                    $fullAvatar = base_url . 'uploads/avatars/' . $avatar;
                    echo "<img src='$fullAvatar' alt='Avatar' class='avatar'>";
                } else {
                    echo "<img src='uploads/avatars/default-avatar.png' alt='Default Avatar' class='avatar'>";
                }

                // Display item details
                echo "<p><strong>Title:</strong> " . $title . "</p>";
                echo "<p><strong>Description:</strong> " . $description . "</p>";
                echo "<p><strong>Last Seen Location:</strong> " . $lastSeenLocation . "</p>";
                echo "<p><strong>Time Missing:</strong> " . $timeMissing . "</p>";
                echo "<p><strong>Contact:</strong> " . $contact . "</p>";
                echo "<p><strong>Email:</strong> " . $email . "</p>";
                echo "<p><strong>College:</strong> " . $college . "</p>";
                echo "<p><strong>Category:</strong> " . $categoryName . "</p>";

                // Display images
                if (!empty($row['images'])) {
                    $images = explode(',', $row['images']);
                    echo "<div class='image-grid'>";
                    foreach ($images as $imagePath) {
                        $fullImagePath = base_url . 'uploads/items/' . htmlspecialchars($imagePath);
                        echo "<a href='$fullImagePath' data-lightbox='claimed-images' data-title='Image'><img src='$fullImagePath' alt='Image'></a>";
                    }
                    echo "</div>";
                }

                echo "</div>";
            }
        } else {
            echo "<p>No claimed items found.</p>";
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox-plus-jquery.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>

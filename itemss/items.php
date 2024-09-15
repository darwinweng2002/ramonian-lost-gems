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

// SQL queries to get published items
$sqlFound = "SELECT mh.id, mh.title, GROUP_CONCAT(mi.image_path) AS image_paths
             FROM message_history mh
             LEFT JOIN message_images mi ON mh.id = mi.message_id
             WHERE mh.is_published = 1
             GROUP BY mh.id
             ORDER BY mh.id DESC";

$sqlMissing = "SELECT mi.id, mi.title, GROUP_CONCAT(mii.image_path) AS image_paths
               FROM missing_items mi
               LEFT JOIN missing_item_images mii ON mi.id = mii.id
               WHERE mi.status = 'Published'
               GROUP BY mi.id
               ORDER BY mi.id DESC";

// Execute queries
$resultFound = $conn->query($sqlFound);
$resultMissing = $conn->query($sqlMissing);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php require_once('../inc/header.php') ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Items Gallery</title>
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
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .gallery-item {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 10px;
            text-align: center;
            position: relative;
        }
        .gallery-item img {
            max-width: 100%;
            border-radius: 5px;
        }
        .gallery-item h3 {
            margin: 10px 0 0;
        }
        .gallery-item a {
            text-decoration: none;
            color: #333;
        }
    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php') ?>
    <div class="container">
        <h1>Published Items Gallery</h1>
        <h2>Found Items</h2>
        <div class="gallery-grid">
            <?php
            if ($resultFound->num_rows > 0) {
                while ($row = $resultFound->fetch_assoc()) {
                    $itemId = htmlspecialchars($row['id']);
                    $title = htmlspecialchars($row['title']);
                    $imagePaths = htmlspecialchars($row['image_paths']);
                    $images = explode(',', $imagePaths); // Split concatenated images

                    echo "<div class='gallery-item'>";
                    echo "<a href='published_items.php?id=" . $itemId . "'>";
                    if (!empty($images)) {
                        echo "<img src='" . base_url . 'uploads/items/' . $images[0] . "' alt='" . $title . "'>";
                    } else {
                        echo "<img src='uploads/items/default-image.png' alt='No Image'>";
                    }
                    echo "<h3>" . $title . "</h3>";
                    echo "</a>";
                    echo "</div>";
                }
            } else {
                echo "<p>No published found items available.</p>";
            }
            ?>
        </div>
        
        <h2>Missing Items</h2>
        <div class="gallery-grid">
            <?php
            if ($resultMissing->num_rows > 0) {
                while ($row = $resultMissing->fetch_assoc()) {
                    $itemId = htmlspecialchars($row['id']);
                    $title = htmlspecialchars($row['title']);
                    $imagePaths = htmlspecialchars($row['image_paths']);
                    $images = explode(',', $imagePaths); // Split concatenated images

                    echo "<div class='gallery-item'>";
                    echo "<a href='missing_items.php?id=" . $itemId . "'>";
                    if (!empty($images)) {
                        echo "<img src='" . base_url . 'uploads/items/' . $images[0] . "' alt='" . $title . "'>";
                    } else {
                        echo "<img src='uploads/items/default-image.png' alt='No Image'>";
                    }
                    echo "<h3>" . $title . "</h3>";
                    echo "</a>";
                    echo "</div>";
                }
            } else {
                echo "<p>No published missing items available.</p>";
            }
            ?>
        </div>
    </div>
    <?php require_once('../inc/footer.php') ?>
</body>
</html>

<?php
$conn->close();
?>

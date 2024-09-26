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

// Capture the search term and selected category
$searchTerm = '';
$selectedCategory = '';
if (isset($_GET['search'])) {
    $searchTerm = trim($conn->real_escape_string($_GET['search']));
}
if (isset($_GET['category_id'])) {
    $selectedCategory = intval($_GET['category_id']);
}

// Fetch categories for dropdown
$categoriesResult = $conn->query("SELECT id, name FROM categories");

// SQL query for found items, including status
$sqlFound = "SELECT mh.id, mh.title, mh.category_id, mh.time_found, mh.message, mh.status, GROUP_CONCAT(mi.image_path) AS image_paths
             FROM message_history mh
             LEFT JOIN message_images mi ON mh.id = mi.message_id
             WHERE mh.is_published = 1 AND mh.status = 1";

// Search and filter by category
if ($searchTerm) {
    $sqlFound .= " AND (mh.title LIKE '%$searchTerm%' 
                      OR mh.category_id LIKE '%$searchTerm%'
                      OR mh.time_found LIKE '%$searchTerm%'
                      OR mh.message LIKE '%$searchTerm%')";
}
if ($selectedCategory) {
    $sqlFound .= " AND mh.category_id = $selectedCategory";
}

$sqlFound .= " GROUP BY mh.id
               ORDER BY mh.id DESC";

// SQL query for missing items with extended search functionality
$sqlMissing = "SELECT mi.id, mi.title, mi.category_id, mi.time_missing, mi.description, mi.status, GROUP_CONCAT(mii.image_path) AS image_paths
               FROM missing_items mi
               LEFT JOIN missing_item_images mii ON mi.id = mii.missing_item_id
               WHERE mi.status = 1";  // Fetch only published items

// Search and filter by category
if ($searchTerm) {
    $sqlMissing .= " AND (mi.title LIKE '%$searchTerm%'
                         OR mi.category_id LIKE '%$searchTerm%'
                         OR mi.time_missing LIKE '%$searchTerm%'
                         OR mi.description LIKE '%$searchTerm%')";
}
if ($selectedCategory) {
    $sqlMissing .= " AND mi.category_id = $selectedCategory";
}

$sqlMissing .= " GROUP BY mi.id ORDER BY mi.id DESC";

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
            overflow: hidden;
            height: 350px;
        }
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 5px;
        }
        .status-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 12px;
            color: #fff;
        }
        .badge-published { background-color: #28a745; }
        .badge-claimed { background-color: #ffc107; }
        .badge-surrendered { background-color: #6c757d; }
        .badge-pending { background-color: #007bff; }
    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php') ?>

<div class="container">
    <h1>Published Items</h1>
    <div class="gallery-grid">
        <?php
        if ($resultFound->num_rows > 0) {
            while ($row = $resultFound->fetch_assoc()) {
                $itemId = htmlspecialchars($row['id']);
                $title = htmlspecialchars($row['title']);
                $imagePaths = htmlspecialchars($row['image_paths']);
                $status = htmlspecialchars($row['status']); // Get the status
                $images = explode(',', $imagePaths);
                
                // Status Badge
                echo "<div class='gallery-item'>";
                echo "<span class='status-badge ";
                if ($status == 1) {
                    echo "badge-published'>Published";
                } elseif ($status == 2) {
                    echo "badge-claimed'>Claimed";
                } elseif ($status == 3) {
                    echo "badge-surrendered'>Surrendered";
                } else {
                    echo "badge-pending'>Pending";
                }
                echo "</span>";

                // Display the item image
                $imagePath = "../uploads/items/" . $images[0]; // Correct path to the image directory
                if (!empty($images[0]) && file_exists($imagePath)) {
                    echo "<img src='" . $imagePath . "' alt='" . $title . "'>";
                } else {
                    // Fallback to default image if image does not exist
                    echo "<img src='../uploads/items/no-image.png' alt='No Image'>";
                }
                echo "<h3>" . $title . "</h3>";
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
                $status = isset($row['status']) && !is_null($row['status']) ? htmlspecialchars($row['status']) : 'Pending';  // Get status or default to 'Pending'
                $images = explode(',', $imagePaths);

                echo "<div class='gallery-item'>";
                echo "<a href='view_missing.php?id=" . $itemId . "'>";

                // Add status badge based on the status value
                echo "<span class='status-badge ";
                if ($status == 1) {
                    echo "badge-published";
                } elseif ($status == 2) {
                    echo "badge-claimed";
                } elseif ($status == 3) {
                    echo "badge-surrendered";
                } else {
                    echo "badge-pending";
                }
                echo "'>";
                echo ($status == 1 ? 'Published' : ($status == 2 ? 'Claimed' : ($status == 3 ? 'Surrendered' : 'Pending')));
                echo "</span>";

                // Display the item image
                $imagePath = "../uploads/items/" . $images[0]; // Correct path to the image directory
                if (!empty($images[0]) && file_exists($imagePath)) {
                    echo "<img src='" . $imagePath . "' alt='" . $title . "'>";
                } else {
                    // Fallback to default image if image does not exist
                    echo "<img src='../uploads/items/no-image.png' alt='No Image'>";
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

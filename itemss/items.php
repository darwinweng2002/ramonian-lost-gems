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

// SQL query for found items
$sqlFound = "SELECT mh.id, mh.title, mh.category_id, mh.time_found, mh.message, GROUP_CONCAT(mi.image_path) AS image_paths
             FROM message_history mh
             LEFT JOIN message_images mi ON mh.id = mi.message_id
             WHERE mh.is_published = 1";

// Search and filter by category for found items
if ($searchTerm) {
    $sqlFound .= " AND (mh.title LIKE '%$searchTerm%' 
                      OR mh.message LIKE '%$searchTerm%')";
}
if ($selectedCategory) {
    $sqlFound .= " AND mh.category_id = $selectedCategory";
}

$sqlFound .= " GROUP BY mh.id
               ORDER BY mh.id DESC";

// SQL query for missing items (using numerical status values)
$sqlMissing = "SELECT mi.id, mi.title, mi.category_id, mi.time_missing, mi.description, GROUP_CONCAT(mii.image_path) AS image_paths
               FROM missing_items mi
               LEFT JOIN missing_item_images mii ON mi.id = mii.missing_item_id
               WHERE mi.status = 1";  // '1' corresponds to 'Published'

// Search and filter by category for missing items
if ($searchTerm) {
    $sqlMissing .= " AND (mi.title LIKE '%$searchTerm%'
                         OR mi.description LIKE '%$searchTerm%')";
}
if ($selectedCategory) {
    $sqlMissing .= " AND mi.category_id = $selectedCategory";
}

$sqlMissing .= " GROUP BY mi.id
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
        .search-bar {
            text-align: center;
            margin-bottom: 20px;
        }
        .search-bar form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        .search-bar input[type="text"],
        .search-bar select {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
            width: 100%;
            max-width: 400px;
            font-size: 16px;
        }
        .search-bar button {
            padding: 12px 20px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .gallery-item {
            background-color: #fff;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            position: relative;
            overflow: hidden;
            height: 300px;
        }
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 5px;
        }
        .gallery-item h3 {
            position: absolute;
            bottom: 10px;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.6);
            color: #fff;
            padding: 5px;
            text-align: center;
            border-radius: 0 0 8px 8px;
        }
    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php') ?>

<div class="container">
    <h1>Published Items</h1>

    <!-- Search Bar -->
    <div class="search-bar">
        <form action="" method="get">
            <input type="text" name="search" placeholder="Search by title or description" value="<?php echo htmlspecialchars($searchTerm); ?>">
            <select name="category_id">
                <option value="" <?php echo $selectedCategory === '' ? 'selected' : ''; ?>>All Categories</option>
                <?php
                if ($categoriesResult->num_rows > 0) {
                    while ($row = $categoriesResult->fetch_assoc()) {
                        $categoryId = htmlspecialchars($row['id']);
                        $categoryName = htmlspecialchars($row['name']);
                        $selected = $selectedCategory == $categoryId ? 'selected' : '';
                        echo "<option value=\"$categoryId\" $selected>$categoryName</option>";
                    }
                }
                ?>
            </select>
            <button type="submit">Search</button>
        </form>
    </div>

    <h2>Found Items</h2>
    <div class="gallery-grid">
        <?php
        if ($resultFound->num_rows > 0) {
            while ($row = $resultFound->fetch_assoc()) {
                $itemId = htmlspecialchars($row['id']);
                $title = htmlspecialchars($row['title']);
                $imagePaths = htmlspecialchars($row['image_paths']);
                $images = explode(',', $imagePaths);

                echo "<div class='gallery-item'>";
                echo "<a href='published_items.php?id=" . $itemId . "'>";
                if (!empty($images) && $images[0] !== '') {
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
                $images = explode(',', $imagePaths);

                echo "<div class='gallery-item'>";
                echo "<a href='view_missing.php?id=" . $itemId . "'>";
                if (!empty($images) && $images[0] !== '') {
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

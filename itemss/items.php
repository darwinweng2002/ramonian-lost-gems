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

// SQL query for found items, including status
// SQL query for found items, including status
$sqlFound = "SELECT mh.id, mh.title, mh.category_id, mh.time_found, mh.message, mh.status, GROUP_CONCAT(mi.image_path) AS image_paths
             FROM message_history mh
             LEFT JOIN message_images mi ON mh.id = mi.message_id
             WHERE mh.is_published = 1 AND mh.status = 1"; // Only fetch items where status is Published

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
// SQL query for missing items with extended search functionality
$sqlMissing = "SELECT mi.id, mi.title, mi.category_id, mi.time_missing, mi.description, mi.status, GROUP_CONCAT(mii.image_path) AS image_paths
               FROM missing_items mi
               LEFT JOIN missing_item_images mii ON mi.id = mii.missing_item_id
               WHERE mi.status = 1";  // Fetch only published items
 // Only fetch published items

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
        ..search-bar {
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
    box-sizing: border-box;
    font-size: 16px;
    transition: border-color 0.3s ease;
}

.search-bar input[type="text"]:focus,
.search-bar select:focus {
    border-color: #333;
    outline: none;
}

.search-bar button {
    padding: 12px 20px;
    background-color: #007BFF;
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s ease;
}

.search-bar button:hover {
    background-color: #008BFF;
}

/* Gallery Grid Styling */
/* Gallery Grid Styling */
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
    position: relative; /* Ensure positioning context for title */
    overflow: hidden; /* Ensure content doesn't overflow */
    height: 300px; /* Fixed height for consistency */
}

.gallery-item img {
    width: 100%; /* Make the image cover the width of the container */
    height: 100%; /* Make the image cover the height of the container */
    object-fit: cover; /* Ensure the image covers the container while preserving aspect ratio */
    border-radius: 5px;
}

.gallery-item h3 {
    position: absolute; /* Position title absolutely within the container */
    bottom: 10px; /* Distance from the bottom */
    left: 0; /* Align to the left */
    right: 0; /* Align to the right */
    background: rgba(0, 0, 0, 0.6); /* Semi-transparent background for readability */
    color: #fff; /* White text color */
    padding: 5px; /* Space around text */
    text-align: center; /* Center-align text */
    border-radius: 0 0 8px 8px; /* Rounded corners at the bottom */
}

.gallery-item a {
    text-decoration: none;
    color: #333;
}
.back-btn-container {
    margin: 20px 0;
    display: flex;
    justify-content: center; /* Center the button */
}

.back-btn {
    display: flex;
    align-items: center;
    padding: 10px 20px;
    background-color: #007BFF;
    color: #fff;
    text-decoration: none;
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
                $status = htmlspecialchars($row['status']); // Get the status
                $images = explode(',', $imagePaths);

                echo "<div class='gallery-item'>";
                // Status Badge
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

                // Image and title
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

    <div class="back-btn-container">
    <a href="https://ramonianlostgems.com/main.php" class="back-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left">
            <line x1="19" y1="12" x2="5" y2="12"/>
            <polyline points="12 19 5 12 12 5"/>
        </svg>
        Home
    </a>
</div>

</div>

<?php require_once('../inc/footer.php') ?>
</body>
</html>

<?php
$conn->close();
?>

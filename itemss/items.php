<?php
    include '../config.php';

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    // Database connection
    $conn = new mysqli('localhost', 'root', '1234', 'lost_db'); 

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Capture the search term
    $searchTerm = '';
    if (isset($_GET['search'])) {
        $searchTerm = trim($conn->real_escape_string($_GET['search']));
    }

    // SQL query for found items with extended search functionality
    $sqlFound = "SELECT mh.id, mh.title, mh.category_id, mh.time_found, mh.message, GROUP_CONCAT(mi.image_path) AS image_paths
                 FROM message_history mh
                 LEFT JOIN message_images mi ON mh.id = mi.message_id
                 WHERE mh.is_published = 1";

    // Search across title, category, time_found, and description
    if ($searchTerm) {
        $sqlFound .= " AND (mh.title LIKE '%$searchTerm%' 
                          OR mh.category_id LIKE '%$searchTerm%'
                          OR mh.time_found LIKE '%$searchTerm%'
                          OR mh.message LIKE '%$searchTerm%')";
    }

    $sqlFound .= " GROUP BY mh.id
                   ORDER BY mh.id DESC";

    // SQL query for missing items with extended search functionality
    $sqlMissing = "SELECT mi.id, mi.title, mi.category_id, mi.time_missing, mi.description, GROUP_CONCAT(mii.image_path) AS image_paths
                   FROM missing_items mi
                   LEFT JOIN missing_item_images mii ON mi.id = mii.missing_item_id
                   WHERE mi.status = 'Published'";

    // Search across title, category, time_last_seen, and description
    if ($searchTerm) {
        $sqlMissing .= " AND (mi.title LIKE '%$searchTerm%'
                           OR mi.category_id LIKE '%$searchTerm%'
                           OR mi.time_missing LIKE '%$searchTerm%'
                           OR mi.description LIKE '%$searchTerm%')";
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
            .search-bar {
                text-align: center;
                margin-bottom: 20px;
            }
            .search-bar input[type="text"] {
                padding: 10px;
                width: 80%;
                max-width: 400px;
                border-radius: 5px;
                border: 1px solid #ccc;
            }
            .search-bar button {
                padding: 10px;
                background-color: #333;
                color: #fff;
                border: none;
                border-radius: 5px;
                cursor: pointer;
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
                    $images = explode(',', $imagePaths);

                    echo "<div class='gallery-item'>";
                    echo "<a href='view_missing.php?id=" . $itemId . "'>";
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

<?php
include '../../config.php';

// Define the base path where the images are stored
$base_image_url = base_url . 'uploads/items/';  // Adjust this to your actual image directory

// Initialize search term
$searchTerm = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// SQL query to fetch denied found items with their first image and add search functionality
$sql_found = "
    SELECT mh.id, mh.title, mh.landmark, mh.contact, mh.founder, mh.time_found, c.name as category_name, 'Found' as item_type, mi.image_path
    FROM message_history mh
    LEFT JOIN categories c ON mh.category_id = c.id
    LEFT JOIN (
        SELECT message_id, MIN(image_path) AS image_path
        FROM message_images
        GROUP BY message_id
    ) mi ON mh.id = mi.message_id
    WHERE mh.is_denied = 1
    AND (mh.title LIKE '%$searchTerm%'
        OR mh.landmark LIKE '%$searchTerm%'
        OR mh.founder LIKE '%$searchTerm%'
        OR c.name LIKE '%$searchTerm%')
    ORDER BY mh.id DESC";

// Execute the query
$result_found = $conn->query($sql_found);
?>

<!DOCTYPE html>
<html lang="en">
<?php require_once('../inc/header.php') ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <title>Denied Found Items</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            padding: 20px;
        }
        .container {
            margin: 30px auto;
            max-width: 1200px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .table-responsive {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            min-width: 1000px;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: center;
        }
        thead th {
            background-color: #f2f2f2;
            color: #444;
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tbody tr:hover {
            background-color: #f1f1f1;
        }
        .no-data {
            text-align: center;
            font-size: 1.2rem;
            color: #333;
            padding: 30px 0;
        }
        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }
        .input-group {
            display: flex;
            align-items: center;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .search-input {
            border: 1px solid #ddd;
            border-right: none;
            border-radius: 0;
            padding: 10px;
            outline: none;
            box-shadow: none;
            width: 200px;
            flex-grow: 1;
        }
        .search-button {
            border-radius: 0;
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 10px 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-left: -5px;
        }
        .search-button:hover {
            background-color: #218838;
        }
        .input-group-text {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 10px;
            border-right: none;
            color: #333;
        }
        .input-group-text i {
            font-size: 14px;
        }
        .item-image {
            width: 50px; /* Fixed width */
            height: 50px; /* Fixed height */
            object-fit: cover; /* Ensures no stretching */
            border-radius: 5px; /* Optional for rounded corners */
        }
    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php') ?>
<?php require_once('../inc/navigation.php') ?> 
<br>
<br>
<section class="section">
    <div class="container">
        <h2>Denied Found Items</h2>
        
        <!-- Search Form -->
        <form class="search-form" method="GET" action="denied_items.php">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="search-input form-control" placeholder="Search items..." value="<?= htmlspecialchars($searchTerm) ?>">
                <button type="submit" class="search-button">Search</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Item Image</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Finder</th>
                        <th>Location Found</th>
                        <th>Date Found</th>
                        <th>Contact</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Display denied found items
                    if ($result_found->num_rows > 0) {
                        while ($row = $result_found->fetch_assoc()) {
                            echo "<tr>";
                            // Display the item image, with a fallback in case no image is available
                        if (!empty($row['image_path'])) {
                            $imageSrc = $base_image_url . htmlspecialchars($row['image_path']);
                            echo "<td><img src='" . $imageSrc . "' alt='Item Image' class='item-image'></td>";
                        } else {
                            echo "<td><img src='/path/to/placeholder.jpg' alt='No Image' class='item-image'></td>";  // Provide a default image path
                        }
                            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['founder']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['landmark']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['time_found']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['contact']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        // If no denied found items are found
                        echo "<tr><td colspan='7'>No denied found items found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php
// Close connections
$result_found->free();
$conn->close();
?>
<?php require_once('../inc/footer.php') ?>
</body>
</html>

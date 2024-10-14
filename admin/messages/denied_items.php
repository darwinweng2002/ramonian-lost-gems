<?php
include '../../config.php';

// Define the base path where the images are stored
$base_image_url = base_url . 'uploads/items/';  // Adjust this to your actual image directory

// Fetch denied found items with their first image
$sql_found = "
    SELECT mh.id, mh.title, mh.landmark, mh.contact, mh.founder, mh.time_found, c.name as category_name, 'Found' as item_type, mi.image_path
    FROM message_history mh
    LEFT JOIN categories c ON mh.category_id = c.id
    LEFT JOIN (
        SELECT message_id, MIN(image_path) AS image_path
        FROM message_images
        GROUP BY message_id
    ) mi ON mh.id = mi.message_id
    WHERE mh.is_denied = 1"; // Fetch denied found items

// Execute the query
$result_found = $conn->query($sql_found);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Denied Found Items</title>
    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet">
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
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        table th {
            background-color: #f4f4f4;
        }
        .no-items {
            text-align: center;
            margin-top: 20px;
        }
        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Denied Found Items</h1>
        <div class="table-responsive">
            <table>
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
                                echo "<td><img src='default-image.jpg' alt='No Image' class='item-image'></td>";  // Provide a default image path
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
</body>
</html>

<?php
// Close connections
$result_found->free();
$conn->close();
?>

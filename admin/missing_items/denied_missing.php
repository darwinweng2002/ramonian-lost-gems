<?php
include '../../config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch denied missing items with their first image
$sql = "
    SELECT mi.id, mi.title, mi.description, mi.last_seen_location, mi.time_missing, mi.contact, mi.owner, c.name AS category_name, imi.image_path
    FROM missing_items mi
    LEFT JOIN categories c ON mi.category_id = c.id
    LEFT JOIN (
        SELECT missing_item_id, MIN(image_path) AS image_path
        FROM missing_item_images
        GROUP BY missing_item_id
    ) imi ON mi.id = imi.missing_item_id
    WHERE mi.is_denied = 1";  // Fetch only denied items

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Denied Missing Items</title>
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
        <h1>Denied Missing Items</h1>

        <?php if ($result->num_rows > 0) { ?>
            <table>
                <thead>
                    <tr>
                        <th>Item Image</th>
                        <th>Item Title</th>
                        <th>Category</th>
                        <th>Owner</th>
                        <th>Last Seen Location</th>
                        <th>Time Missing</th>
                        <th>Contact</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        // Display the item image, with a fallback in case no image is available
                        if (!empty($row['image_path'])) {
                            $imageSrc = htmlspecialchars($row['image_path']);
                            echo "<td><img src='" . $imageSrc . "' alt='Item Image' class='item-image'></td>";
                        } else {
                            echo "<td><img src='default-image.jpg' alt='No Image' class='item-image'></td>";  // Provide a default image path
                        }
                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['owner']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['last_seen_location']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['time_missing']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['contact']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p class="no-items">No denied missing items found.</p>
        <?php } ?>

    </div>
</body>
</html>

<?php
$conn->close();
?>

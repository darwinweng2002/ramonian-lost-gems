<?php
include '../../config.php';

// Fetch denied items
$sql = "
    SELECT mh.id, mh.message, mh.title, mh.landmark, mh.contact, mh.founder, mh.time_found, c.name as category_name
    FROM message_history mh
    LEFT JOIN categories c ON mh.category_id = c.id
    WHERE mh.is_denied = 1"; // Fetch only denied items

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Denied Items</title>
    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet">
</head>
<body>
    <h1>Denied Items</h1>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
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
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['founder']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['landmark']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['time_found']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['contact']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No denied items found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

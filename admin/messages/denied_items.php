<?php
include '../../config.php';

// Fetch denied found items
$sql_found = "
    SELECT mh.id, mh.title, mh.landmark, mh.contact, mh.founder, mh.time_found, c.name as category_name, 'Found' as item_type
    FROM message_history mh
    LEFT JOIN categories c ON mh.category_id = c.id
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Denied Found Items</h1>
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
                    // Display denied found items
                    if ($result_found->num_rows > 0) {
                        while ($row = $result_found->fetch_assoc()) {
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
                        // If no denied found items are found
                        echo "<tr><td colspan='6'>No denied found items found.</td></tr>";
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

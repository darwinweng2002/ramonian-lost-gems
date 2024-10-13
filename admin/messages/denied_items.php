<?php
include '../../config.php';

// Fetch denied found items and denied missing items
$sql_found = "
    SELECT mh.id, mh.title, mh.landmark, mh.contact, mh.founder, mh.time_found, c.name as category_name, 'Found' as item_type
    FROM message_history mh
    LEFT JOIN categories c ON mh.category_id = c.id
    WHERE mh.is_denied = 1"; // Fetch denied found items

$sql_missing = "
    SELECT mi.id, mi.title, mi.last_seen_location AS landmark, mi.contact, mi.owner AS founder, mi.time_missing AS time_found, c.name AS category_name, 'Missing' as item_type
    FROM missing_items mi
    LEFT JOIN categories c ON mi.category_id = c.id
    WHERE mi.is_denied = 1"; // Fetch denied missing items

// Execute both queries
$result_found = $conn->query($sql_found);
$result_missing = $conn->query($sql_missing);
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
                    <th>Type</th> <!-- Type column to indicate Found or Missing -->
                    <th>Finder/Owner</th>
                    <th>Location</th>
                    <th>Date Found/Missing</th>
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
                        echo "<td>" . htmlspecialchars($row['item_type']) . "</td>"; // Shows "Found"
                        echo "<td>" . htmlspecialchars($row['founder']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['landmark']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['time_found']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['contact']) . "</td>";
                        echo "</tr>";
                    }
                }

                // Display denied missing items
                if ($result_missing->num_rows > 0) {
                    while ($row = $result_missing->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['item_type']) . "</td>"; // Shows "Missing"
                        echo "<td>" . htmlspecialchars($row['founder']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['landmark']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['time_found']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['contact']) . "</td>";
                        echo "</tr>";
                    }
                }

                // If no denied items found
                if ($result_found->num_rows == 0 && $result_missing->num_rows == 0) {
                    echo "<tr><td colspan='7'>No denied items found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
// Close connections
$result_found->free();
$result_missing->free();
$conn->close();
?>

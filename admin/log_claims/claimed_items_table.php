<?php
include '../../config.php';

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch claimed items (status = 2 for claimed items) from both missing_items and found_items
$sql = "
    SELECT mi.id, mi.title, mi.description, mi.time_missing, um.email, c.name AS category_name, 'Missing' AS item_type
    FROM missing_items mi
    LEFT JOIN user_member um ON mi.user_id = um.id
    LEFT JOIN categories c ON mi.category_id = c.id
    WHERE mi.status = 2
    UNION ALL
    SELECT mh.id, mh.title, mh.message AS description, mh.time_found AS time_missing, um.email, c.name AS category_name, 'Found' AS item_type
    FROM message_history mh
    LEFT JOIN user_member um ON mh.user_id = um.id
    LEFT JOIN categories c ON mh.category_id = c.id
    WHERE mh.status = 2
";

// Execute the query
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php require_once('../inc/header.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claimed Items Table View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Your CSS */
    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php'); ?>
<?php require_once('../inc/navigation.php'); ?>

<div class="container">
    <h1>Claimed Items History</h1>
    
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Description</th>
                <th>Time Missing/Found</th> <!-- Updated -->
                <th>User Email</th>
                <th>Category</th>
                <th>Item Type</th> <!-- Added this column to distinguish Missing/Found -->
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['time_missing']) . "</td>";  // Works for both missing and found
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['item_type']) . "</td>"; // Displays either 'Missing' or 'Found'
                    echo "<td>";
                    echo "<a href='https://ramonianlostgems.com/admin/log_claims/claimed_items.php?id=" . urlencode($row['id']) . "' class='btn btn-view'>View</a>";
                    echo "<button class='btn btn-delete' data-id='" . htmlspecialchars($row['id']) . "'>Delete</button>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7' class='text-center'>No claimed items found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- JavaScript for Delete Functionality -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Delete button functionality (now just changes the status to "archived")
    $('.btn-delete').on('click', function() {
        var itemId = $(this).data('id');
        if (confirm('Are you sure you want to remove this item from the Claimed Items History?')) {
            $.ajax({
                url: '../delete_claimed_item.php', // Path to update status
                type: 'POST',
                data: { id: itemId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Item removed from Claim History successfully.');
                        location.reload(); // Reload the page to reflect changes
                    } else {
                        alert('Failed to remove the item: ' + response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error);
                }
            });
        }
    });
});
</script>
<?php require_once('../inc/footer.php'); ?>
</body>
</html>

<?php
$conn->close();
?>

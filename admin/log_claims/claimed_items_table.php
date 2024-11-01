<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../../config.php';

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch items that are set to claimed (status = 2)
$sql = "
    -- Claimed missing items
    SELECT mi.id, mi.title, mi.owner, mi.description, mi.time_missing AS time_recorded, um.email, c.name AS category_name, 'Missing' AS item_type
    FROM missing_items mi
    LEFT JOIN user_member um ON mi.user_id = um.id
    LEFT JOIN categories c ON mi.category_id = c.id
    WHERE mi.status = 2  -- Only claimed items
    
    UNION
    
    -- Claimed found items (message_history)
    SELECT mh.id, mh.title, CONCAT(um.first_name, ' ', um.last_name) AS owner, mh.message AS description, mh.time_found AS time_recorded, um.email, c.name AS category_name, 'Found' AS item_type
    FROM message_history mh
    LEFT JOIN user_member um ON mh.user_id = um.id
    LEFT JOIN categories c ON mh.category_id = c.id
    WHERE mh.status = 2  -- Only claimed items
";


$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php require_once('../inc/header.php'); ?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Claimed Items</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        padding: 20px;
    }
    .container {
        margin: 30px auto;
        max-width: 1200px;
        background-color: #fff;
        padding: 30px; /* Increased padding for better spacing */
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    h2 {
        text-align: center;
        color: #333;
        margin-bottom: 20px;
    }
    .table-wrapper {
        overflow-x: auto; /* Enable horizontal scrolling for smaller screens */
    }
    .table {
        width: 100%;
        min-width: 800px; /* Set a reasonable minimum width */
        table-layout: auto; /* Prevents table cells from squeezing */
    }
    .table thead {
        background-color: #f2f2f2;
        color: #444444;
    }
    .table th, .table td {
        vertical-align: middle;
        white-space: nowrap; /* Prevent text wrapping */
    }
    .btn-view {
        background-color: #007bff;
        color: #fff;
        border: none;
        padding: 5px 10px;
        border-radius: 5px;
        cursor: pointer;
    }
    .btn-view:hover {
        background-color: #0056b3;
    }
    .btn-delete {
        background-color: #dc3545;
        color: #fff;
        border: none;
        padding: 5px 10px;
        border-radius: 5px;
        cursor: pointer;
    }
    .btn-delete:hover {
        background-color: #c82333;
    }
</style>
</head>
<body>
<?php require_once('../inc/topBarNav.php'); ?>
<?php require_once('../inc/navigation.php'); ?>

<div class="container">
    <h2>Claimed Items</h2>
    
    <!-- Table wrapper for horizontal scrolling -->
    <div class="table-wrapper">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Description</th>
                    <th>Time Recorded</th>
                    <th>Owners Name</th>
                    <th>User Email</th>
                    <th>Category</th>
                    <th>Item Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['title'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['description'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['time_recorded'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['owner'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['email'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['category_name'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['item_type'] ?? '') . "</td>";
                echo "<td><button class='btn btn-delete' data-id='" . htmlspecialchars($row['id'] ?? '') . "'>Delete</button></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='8' class='text-center'>No claimed items found.</td></tr>";
        }
        ?>
        </tbody>
        </table>
    </div>
</div>

<!-- JavaScript for Delete Functionality -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Delete button functionality
    $('.btn-delete').on('click', function() {
        var itemId = $(this).data('id');
        if (confirm('Are you sure you want to remove this item from the Claimed Items?')) {
            $.ajax({
                url: '../delete_claimed_item.php', // Path to update status or delete
                type: 'POST',
                data: { id: itemId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Item removed successfully.');
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

<?php
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
    
    -- Claimed found items
    SELECT mh.id, mh.title, NULL AS owner, mh.message AS description, mh.time_found AS time_recorded, um.email, c.name AS category_name, 'Found' AS item_type
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
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .table thead {
            background-color: #f2f2f2;
            color: #444444;
        }
        .table th, .table td {
            vertical-align: middle;
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
    
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Description</th>
                <th>Time Recorded</th>
                <th>User Email</th>
                <th>Category</th>
                <th>Item Type</th> <!-- Show whether it’s Found or Missing -->
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
                    echo "<td>" . htmlspecialchars($row['time_recorded']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['item_type']) . "</td>"; // Show whether it's Found or Missing
                    echo "<td>";
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

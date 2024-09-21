<?php
include '../../config.php';

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch claimed items (status = 2)
$sql = "SELECT 
            mi.id, 
            mi.title, 
            mi.description, 
            mi.time_missing, 
            um.email, 
            c.name AS category_name
        FROM missing_items mi
        LEFT JOIN user_member um ON mi.user_id = um.id
        LEFT JOIN categories c ON mi.category_id = c.id
        WHERE mi.status = 2";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claimed Items Table View</title>
    <?php require_once('../inc/header.php'); ?>
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
        h1 {
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
    <h1>Claimed Items History</h1>
    
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Item Name</th>
                <th>Description</th>
                <th>Time Missing</th>
                <th>User Email</th>
                <th>Category</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['time_missing']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
                    echo "<td>";
                    echo "<a href='claimed_items.php?id=" . htmlspecialchars($row['id']) . "' class='btn btn-view'>View</a> ";
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
        if (confirm('Are you sure you want to delete this item?')) {
            $.ajax({
                url: 'delete_claimed_item.php', // Change this path if necessary
                type: 'POST',
                data: { id: itemId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Item deleted successfully.');
                        location.reload(); // Reload the page to reflect changes
                    } else {
                        alert('Failed to delete the item: ' + response.error);
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

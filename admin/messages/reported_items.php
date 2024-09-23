<?php
include '../../config.php';

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize search term
$searchTerm = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// SQL query to fetch found items with search functionality
$sql = "SELECT mh.id, mh.title, um.first_name, um.college, mh.time_found, c.name AS category_name
        FROM message_history mh
        LEFT JOIN user_member um ON mh.user_id = um.id
        LEFT JOIN categories c ON mh.category_id = c.id
        WHERE CONCAT_WS(' ', mh.title, um.first_name, um.college, c.name) LIKE '%$searchTerm%'
        ORDER BY mh.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<?php require_once('../inc/header.php') ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <title>Reported Found Items</title>
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
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        /* Search bar design */
        .input-group {
            display: flex;
            align-items: center;
            border-radius: 8px;
            overflow: hidden;
        }

        .search-input {
            border: 1px solid #ddd;
            border-right: none;
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

        .table thead {
            background-color: #f2f2f2;
            color: #444;
        }

        .table th, .table td {
            vertical-align: middle;
            padding: 12px;
        }

        tbody tr:hover {
            background-color: #f1f1f1;
        }

        .btn-view {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-view:hover {
            background-color: #007bff;
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

        .no-data {
            text-align: center;
            font-size: 1.2rem;
            color: #333;
            padding: 30px 0;
        }
    </style>
</head>
<body>
    <?php require_once('../inc/topBarNav.php'); ?>
    <?php require_once('../inc/navigation.php'); ?>
    <div class="container">
        <h2>Reported Found Items</h2>

        <!-- Search Form -->
        <form class="search-form" method="GET" action="reported_items.php">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="search-input form-control" placeholder="Search items..." value="<?= htmlspecialchars($searchTerm) ?>">
                <button type="submit" class="search-button">Search</button>
            </div>
        </form>

        <table class="table table-striped table-bordered mt-3">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>User</th>
                    <th>College</th>
                    <th>Category</th> <!-- Display Category -->
                    <th>Time Found</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['college']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['category_name']) . "</td>"; // Display category name
                        echo "<td>" . htmlspecialchars($row['time_found']) . "</td>";
                        // Use the ID for the 'View' button link
                        echo "<td><a href='https://ramonianlostgems.com/admin/messages/view_reported_item.php?id=" . urlencode($row['id']) . "' class='btn-view'>
                        <i class='fas fa-eye'></i> View
                        </a></td>";

                            echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='no-data'>No reported items found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <?php require_once('../inc/footer.php'); ?>
</body>
</html>

<?php
$conn->close();
?>

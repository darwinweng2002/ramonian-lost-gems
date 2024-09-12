<?php
include '../../config.php';

// Database connection
$conn = new mysqli('localhost', 'root', '1234', 'lfis_db'); // Update with your DB credentials

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to fetch reported items
$sql = "SELECT mh.id, mh.title, um.first_name, um.college, mh.time_found 
        FROM message_history mh
        LEFT JOIN user_member um ON mh.user_id = um.id
        ORDER BY mh.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reported Items List</title>
    <?php require_once('../inc/header.php'); ?>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .action-btn {
            padding: 8px 12px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .action-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php require_once('../inc/topBarNav.php'); ?>
    <?php require_once('../inc/navigation.php'); ?>

    <div class="container">
        <h1>Reported Items List</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>User</th>
                    <th>College</th>
                    <th>Time Found</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['college']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['time_found']) . "</td>";
                        echo "<td><a href='../view_reported_item.php?id=" . htmlspecialchars($row['id']) . "' class='action-btn'>View</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No reported items found.</td></tr>";
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

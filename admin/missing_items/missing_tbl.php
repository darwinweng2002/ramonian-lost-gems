<?php
include '../../config.php';

// Define the base path where the images are stored
$base_image_url = base_url . 'uploads/missing_items/';  // Adjust this to your actual image directory

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize search term
$searchTerm = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// SQL query to fetch reported missing items, including status, email, and image
$sql = "
SELECT mi.id, mi.title, mi.owner, user_info.email, user_info.college, mi.time_missing, mi.status, c.name AS category,
       img.image_path AS image_path  -- Fetch first image path for each item
FROM missing_items mi
LEFT JOIN (
    -- Fetch data from user_member
    SELECT id AS user_id, email, college FROM user_member
    UNION
    -- Fetch data from user_staff
    SELECT id AS user_id, email, department AS college FROM user_staff
) AS user_info ON mi.user_id = user_info.user_id
LEFT JOIN categories c ON mi.category_id = c.id
LEFT JOIN (
    SELECT missing_item_id, MIN(image_path) AS image_path
    FROM missing_item_images
    GROUP BY missing_item_id
) img ON mi.id = img.missing_item_id  -- Join to get the first image of each item
WHERE mi.is_denied = 0  -- Exclude denied items
AND CONCAT_WS(' ', mi.title, user_info.email, user_info.college, c.name) LIKE '%$searchTerm%'
ORDER BY mi.id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<?php require_once('../inc/header.php') ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <title>Reported Missing Items</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
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
        /* Ensure horizontal scrolling for tables */
        .table-responsive {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow-x: auto; /* Enables horizontal scrolling */
        }
        table {
            width: 100%;
            min-width: 1000px; /* Force table to stretch for scrolling if needed */
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: center;
        }
        th {
            white-space: nowrap; /* Prevents text from wrapping */
        }
        thead th {
            background-color: #f2f2f2;
            color: #444;
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tbody tr:hover {
            background-color: #f1f1f1;
        }
        .btn {
            border-radius: 5px;
            padding: 8px 12px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s;
            color: #fff;
        }
        .btn-view {
            background-color: #007bff;
            border: none;
        }
        .btn-view:hover {
            background-color: #007bff;
        }
        .no-data {
            text-align: center;
            font-size: 1.2rem;
            color: #333;
            padding: 30px 0;
        }
        .input-group {
            display: flex;
            align-items: center;
            border-radius: 8px;
            overflow: hidden;   /* Ensures the border-radius applies to all child elements */
        }
        .search-input {
            border: 1px solid #ddd;
            border-right: none;
            border-radius: 0;
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
        .badge-pending { background-color: #6c757d; }
        .badge-published { background-color: #007bff; }
        .badge-claimed { background-color: #28a745; }
        .badge-surrendered { background-color: #6c757d; }
        .badge-denied {
    background-color: #dc3545; /* Red color to indicate Denied status */
}

        /* Custom scrollbar styling */
        .table-responsive::-webkit-scrollbar {
            height: 10px;
        }
        .table-responsive::-webkit-scrollbar-thumb {
            background-color: #b0b0b0;
            border-radius: 10px;
        }
        .table-responsive::-webkit-scrollbar-track {
            background-color: #f4f4f4;
        }
        .item-image {
            width: 50px; /* Fixed width */
            height: 50px; /* Fixed height */
            object-fit: cover; /* Ensures no stretching */
            border-radius: 5px; /* Optional for rounded corners */
        }
    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php') ?>
<?php require_once('../inc/navigation.php') ?> 
<br>
<br>
<section class="section">
    <div class="container">
        <h2>Reported Missing Items</h2>
        <a href="https://ramonianlostgems.com/admin/report/send_missing.php/" class="btn btn-success">
                <i class="fas fa-plus-circle"></i> Create report missing item
            </a>
        <form class="search-form" method="GET" action="missing_tbl.php">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="search-input form-control" placeholder="Search items..." value="<?= htmlspecialchars($searchTerm) ?>">
                <button type="submit" class="search-button">Search</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Item Image</th>
                        <th>ID</th>
                        <th>Owner's Name</th>
                        <th>Item Name</th>
                        <th>User</th>
                        <th>College</th>
                        <th>Category</th>
                        <th>Last Seen</th>
                        <th>Status</th> <!-- New Status Column -->
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                            <td>
                                    <?php if (!empty($row['image_path'])): ?>
                                        <img src="<?= $base_image_url . htmlspecialchars($row['image_path']) ?>" alt="Item Image" class="item-image">
                                    <?php else: ?>
                                        <img src="/path/to/placeholder.jpg" alt="No Image" class="item-image">
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td><?= htmlspecialchars($row['owner']) ?></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['college']) ?></td>
                                <td><?= htmlspecialchars($row['category']) ?></td>
                                <td><?= htmlspecialchars($row['time_missing']) ?></td>
                                
                                <!-- Display status as a badge -->
                                <td>
                                <?php
                                switch ($row['status']) {
                                    case 1:
                                        echo "<span class='badge badge-published'>Published</span>";
                                        break;
                                    case 2:
                                        echo "<span class='badge badge-claimed'>Claimed</span>";
                                        break;
                                    case 3:
                                        echo "<span class='badge badge-surrendered'>Surrendered</span>";
                                        break;
                                    case 4:
                                        echo "<span class='badge badge-denied'>Denied</span>"; // New Denied Status
                                        break;
                                    default:
                                        echo "<span class='badge badge-pending'>Pending</span>";
                                        break;
                                }
                                ?>
                            </td>


                                <td>
                                    <div class="d-flex justify-content-center">
                                        <a href="https://ramonianlostgems.com/admin/missing_items/view_missing.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-view">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="no-data">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 25 25" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-x">
                                    <path d="M2 21a8 8 0 0 1 11.873-7"/><circle cx="10" cy="8" r="5"/><path d="m17 17 5 5"/><path d="m22 17-5 5"/>
                                </svg> 
                                No reported items found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php
$conn->close();
?>
<?php require_once('../inc/footer.php') ?>
</body>
</html>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../../config.php';

// Define the base path where the images are stored
$base_image_url = base_url . 'uploads/items/';

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize search term
$searchTerm = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// SQL query to fetch reported found items with their first image and admin who updated status
$sql = "
    SELECT mh.id, mh.message, mi.image_path, mh.title, mh.landmark, user_info.first_name, user_info.college, 
           user_info.email, user_info.avatar, user_info.school_type, mh.contact, mh.founder, mh.time_found, 
           mh.status, mh.updated_by_admin, mh.updated_at, c.name as category_name, 
           users.username AS admin_username  -- Get admin username from the `user` table
    FROM message_history mh
    LEFT JOIN message_images mi ON mh.id = mi.message_id
    LEFT JOIN (
        SELECT id AS user_id, first_name, college, email, avatar, school_type, 'member' AS user_type FROM user_member
        UNION
        SELECT id AS user_id, first_name, department AS college, email, avatar, NULL AS school_type, 'staff' AS user_type FROM user_staff
    ) AS user_info ON mh.user_id = user_info.user_id
    LEFT JOIN categories c ON mh.category_id = c.id
    LEFT JOIN users ON mh.updated_by_admin = users.id  -- Join with `user` for updated by info
    WHERE mh.is_denied = 0";

// Add search condition if a search term is present
if (!empty($searchTerm)) {
    $sql .= " AND (mh.title LIKE '%$searchTerm%' 
              OR user_info.email LIKE '%$searchTerm%'
              OR user_info.college LIKE '%$searchTerm%' 
              OR c.name LIKE '%$searchTerm%'
              OR mh.founder LIKE '%$searchTerm%')";
}

// Order the results by time found
$sql .= " ORDER BY mh.time_found DESC";

$result = $conn->query($sql);
?>




<!DOCTYPE html>
<html lang="en">
<?php require_once('../inc/header.php') ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <title>Reported Found Items List</title>
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
        /* Make table scroll horizontally */
        .table-responsive {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow-x: auto; /* Enable horizontal scrolling */
        }
        table {
            width: 100%;
            min-width: 900px; /* Force table to be wider than the container */
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: center;
        }
        th {
            white-space: nowrap;
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
            background-color: #0056b3;
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
            overflow: hidden;
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
        /* Custom scrollbar styling for horizontal scroll */
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
        /* Set consistent padding and width for both buttons */
/* Align the Report button on the right above the search bar */
.report-btn {
    padding: 8px 16px;
    float: right;
    margin-bottom: 10px;
    width: auto;
}

/* Ensure search button and search bar remain aligned */
.search-button {
    padding: 10px 16px;
}

.input-group {
    clear: both; /* Clears float from the button */
}
.badge-danger { background-color: #dc3545; } /* Denied status */
.item-image {
            width: 100px; /* Fixed width */
            height: 100px; /* Fixed height */
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
        <h2>Reported Found Items</h2>

        <a href="https://ramonianlostgems.com/admin/report/send_message.php/" class="btn btn-success">
            <i class="fas fa-plus-circle"></i> Create report found item
        </a>

        <form class="search-form" method="GET" action="">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="search-input form-control" placeholder="Search items..." value="<?= htmlspecialchars($searchTerm) ?>">
                <button type="submit" class="search-button btn btn-success">Search</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Item Image</th>
                        <th>Item Name</th>
                        <th>User</th>
                        <th>Department</th>
                        <th>Category</th>
                        <th>Finder's Name</th>
                        <th>Time Found</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        
                        if (!empty($row['image_path'])) {
                            $imageSrc = $base_image_url . htmlspecialchars($row['image_path']);
                            echo "<td><img src='" . $imageSrc . "' alt='Item Image' class='item-image'></td>";
                        } else {
                            echo "<td><img src='/path/to/placeholder.jpg' alt='No Image' class='item-image'></td>";  // Provide a default image path
                        }

                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['user_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['college']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['founder']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['time_found']) . "</td>";

                        // Display status as badge
                        echo "<td>";
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
                                echo "<span class='badge badge-danger'>Denied</span>";
                                break;
                            default:
                                echo "<span class='badge badge-pending'>Pending</span>";
                                break;
                        }
                        echo "</td>";

                        echo "<td><a href='https://ramonianlostgems.com/admin/messages/view_reported_item.php?id=" . htmlspecialchars($row['id']) . "' class='btn btn-primary btn-sm'>View</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='9'>No items found.</td></tr>";
                }
                ?>
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

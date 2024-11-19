<?php
include '../../config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// SQL query to fetch denied missing items with their first image, and add search functionality
$sql = "
    SELECT mi.id, mi.title, mi.description, mi.last_seen_location, mi.time_missing, mi.contact, mi.owner, c.name AS category_name, imi.image_path
    FROM missing_items mi
    LEFT JOIN categories c ON mi.category_id = c.id
    LEFT JOIN (
        SELECT missing_item_id, MIN(image_path) AS image_path
        FROM missing_item_images
        GROUP BY missing_item_id
    ) imi ON mi.id = imi.missing_item_id
    WHERE mi.is_denied = 1  -- Fetch only denied items
    AND (mi.title LIKE '%$searchTerm%'
        OR mi.description LIKE '%$searchTerm%'
        OR mi.owner LIKE '%$searchTerm%'
        OR c.name LIKE '%$searchTerm%'
        OR mi.last_seen_location LIKE '%$searchTerm%')
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
    <title>Denied Missing Items</title>
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
        .table-responsive {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            min-width: 1000px;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: center;
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
        .no-data {
            text-align: center;
            font-size: 1.2rem;
            color: #333;
            padding: 30px 0;
        }
        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }
        .input-group {
            display: flex;
            align-items: center;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
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
        .print-button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            margin-bottom: 20px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .print-button:hover {
            background-color: #0056b3;
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
        <h2>Denied Missing Item Reports</h2>
        <button class="print-button" onclick="printSection()">
            <i class="fas fa-print"></i> Print Denied Items
        </button>
        <!-- Search Form -->
        <form class="search-form" method="GET" action="denied_missing_items.php">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="search-input form-control" placeholder="Search items..." value="<?= htmlspecialchars($searchTerm) ?>">
                <button type="submit" class="search-button">Search</button>
            </div>
        </form>

        <?php if ($result->num_rows > 0) { ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Item Image</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Owner</th>
                            <th>Last Seen Location</th>
                            <th>Time Missing</th>
                            <th>Contact</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            // Display the item image, with a fallback in case no image is available
                            if (!empty($row['image_path'])) {
                                $imageSrc = $base_image_url . htmlspecialchars($row['image_path']);
                                echo "<td><img src='" . $imageSrc . "' alt='Item Image' class='item-image'></td>";
                            } else {
                                echo "<td><img src='default-image.jpg' alt='No Image' class='item-image'></td>";  // Provide a default image path
                            }
                            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['owner']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['last_seen_location']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['time_missing']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['contact']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <p class="no-data">No denied missing items found.</p>
        <?php } ?>
    </div>
</section>
<script>
    // JavaScript function to handle print
    function printSection() {
        // Define the logo URL (replace with the actual logo path)
        const logoSrc = '../../uploads/logo.png'; // Replace with actual logo path

        // Get only the table content
        const tableContent = document.querySelector('.table-responsive').innerHTML;

        // Open a new window and write HTML content to it
        const printWindow = window.open('', '', 'width=800,height=600');
        
        printWindow.document.write(`
            <html>
            <head>
                <title>Denied Missing Items</title>
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
                <style>
                    body { font-family: Arial, sans-serif; text-align: center; }
                    .item-image { width: 80px; height: 80px; object-fit: cover; border-radius: 5px; }
                    .generated-by { font-size: 1.1rem; margin-top: 20px; }
                    .logo { width: 100px; height: auto; margin: 0 auto 10px; }
                </style>
            </head>
            <body onload="window.print(); window.close();">
                <img src="${logoSrc}" alt="Logo" class="logo">
                <h4>Denied Missing Items</h4>
                ${tableContent}
                <div class="generated-by">Generated by: Admin of Ramonian Lost Gems</div>
            </body>
            </html>
        `);

        // Close the document to signal it is ready for printing
        printWindow.document.close();
    }
</script>
<?php
$conn->close();
?>
<?php require_once('../inc/footer.php') ?>
</body>
</html>

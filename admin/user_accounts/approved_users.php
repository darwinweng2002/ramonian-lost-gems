<?php
include '../../config.php';

// Database connection
$conn = new mysqli("localhost", "u450897284_root", "Lfisgemsdb1234", "u450897284_lfis_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize search term
$searchTerm = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Update SQL query to include search functionality based on relevant columns
$sql = "SELECT * FROM user_member 
        WHERE status = 'approved'
        AND (first_name LIKE '%$searchTerm%' 
        OR last_name LIKE '%$searchTerm%' 
        OR college LIKE '%$searchTerm%' 
        OR course LIKE '%$searchTerm%')";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once('../inc/header.php') ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h3 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .table-responsive {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: center;
        }

        th {
            white-space: nowrap;
            background-color: #f2f2f2;
            color: #444;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tbody tr:hover {
            background-color: #f1f1f1;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .search-input {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            flex-grow: 1;
        }

        .search-button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-button:hover {
            background-color: #218838;
        }

        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }

        .no-data {
            text-align: center;
            font-size: 1.2rem;
            color: #333;
            padding: 30px 0;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .input-group {
                flex-direction: column;
            }

            .search-input {
                margin-bottom: 10px;
            }

            .btn-info {
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php') ?>
<?php require_once('../inc/navigation.php') ?>
    <div class="container">
        <h3>Pending Student Account Approvals - PRMSU Iba</h3>

        <!-- Search Form -->
        <form class="search-form" method="GET" action="view_users.php">
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="search-input form-control" placeholder="Search users..." value="<?= htmlspecialchars($searchTerm) ?>">
                <button type="submit" class="search-button">Search</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>College</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Username</th> <!-- Replace email with username if needed -->
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['first_name']) ?></td>
                                <td><?= htmlspecialchars($row['last_name']) ?></td>
                                <td><?= htmlspecialchars($row['college']) ?></td>
                                <td><?= htmlspecialchars($row['course']) ?></td>
                                <td><?= htmlspecialchars($row['year']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td>
                                    <!-- Add View Button for actions -->
                                    <a href="../viewpage.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-info btn-sm">
                                        <i class="fa fa-eye"></i> View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="no-data">No registered users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php require_once('../inc/footer.php') ?>
</body>
</html>

<?php
$conn->close();
?>

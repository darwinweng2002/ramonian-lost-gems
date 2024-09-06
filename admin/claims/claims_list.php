<?php
include '../../config.php';

// Number of entries to show per page
$entries_per_page = 10;

// Get the current page number from the URL, default to 1 if not set
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the offset for the SQL query
$offset = ($page - 1) * $entries_per_page;

// Check if the database connection is established
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

// Handle Approve and Delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $claim_id = intval($_POST['claim_id']);
    
    if (isset($_POST['approve'])) {
        // Update claim status to 'approved'
        $update_query = "UPDATE claims SET status = 'approved' WHERE id = $claim_id";
        if ($conn->query($update_query)) {
            echo "<script>alert('Claim approved successfully!'); window.location.href = 'claim_list.php';</script>";
        } else {
            echo "<script>alert('Failed to approve claim: " . $conn->error . "'); window.location.href = 'claim_list.php';</script>";
        }
    }
    
    if (isset($_POST['delete'])) {
        // Delete claim from database
        $delete_query = "DELETE FROM claims WHERE id = $claim_id";
        if ($conn->query($delete_query)) {
            echo "<script>alert('Claim deleted successfully!'); window.location.href = 'claim_list.php';</script>";
        } else {
            echo "<script>alert('Failed to delete claim: " . $conn->error . "'); window.location.href = 'claim_list.php';</script>";
        }
    }
}

// Count total number of claims for pagination
$total_claims_qry = $conn->query("SELECT COUNT(*) AS total_claims FROM claims");
$total_claims = $total_claims_qry->fetch_assoc()['total_claims'];

// Calculate total pages required
$total_pages = ceil($total_claims / $entries_per_page);

// Query to fetch claims data with LIMIT and OFFSET for pagination
$qry = $conn->query("
    SELECT 
        c.id AS claim_id, 
        c.user_id, 
        c.item_id, 
        c.additional_info,
        i.title AS item_title, 
        m.email AS user_email,
        m.course AS user_course,
        m.year AS user_year,
        m.section AS user_section,
        c.status AS claim_status
    FROM 
        claims c
    INNER JOIN 
        item_list i ON c.item_id = i.id
    INNER JOIN 
        user_member m ON c.user_id = m.id
    LIMIT $entries_per_page OFFSET $offset
");

// Debug output
if (!$qry) {
    echo 'Error executing query: ' . $conn->error;
    exit;
}

if ($qry->num_rows > 0) {
    $claims = $qry->fetch_all(MYSQLI_ASSOC);
} else {
    $claims = [];
}

$qry = $conn->prepare("SELECT c.*, i.title, i.category_id FROM claims c INNER JOIN item_list i ON c.item_id = i.id WHERE c.id = ?");
$qry->bind_param("i", $claim_id);
$qry->execute();
$result = $qry->get_result();
$claim = $result->fetch_assoc();
$qry->close();
?>

<!DOCTYPE html>
<html lang="en">
<?php require_once('../inc/header.php') ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claims List</title>
    <style>
               body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        h1 {
            font-size: 24px;
            color: #2C3E50;
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        table {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            border-collapse: collapse;
            font-size: 16px;
            text-align: left;
        }
        table th, table td {
            padding: 8px 12px;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #2C3E50;
            color: #fff;
            font-size: 18px;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        table tr:hover {
            background-color: #f1f1f1;
        }
        .action-buttons {
            display: flex;
            gap: 8px; /* Space between buttons */
            justify-content: center; /* Center align buttons horizontally */
        }
        .action-buttons button {
            padding: 6px 12px;
            border: none;
            color: #fff;
            cursor: pointer;
            border-radius: 3px;
            font-size: 14px;
            display: inline-block;
            text-align: center;
        }
        .action-buttons button.view {
            background-color: #3498db;
        }
        .action-buttons button.view:hover {
            background-color: #2980b9;
        }
        .action-buttons button.approve {
            background-color: #2ecc71;
        }
        .action-buttons button.delete {
            background-color: #e74c3c;
        }
        .action-buttons button:disabled {
            background-color: #95a5a6;
            cursor: not-allowed;
        }

        .action-buttons {
    display: flex;
    gap: 8px; /* Space between buttons */
    justify-content: flex-start; /* Align buttons to the start of the container */
}

.action-buttons form {
    display: flex;
    gap: 8px; /* Space between buttons */
    margin: 0; /* Remove default form margins */
}

.action-buttons button {
    padding: 10px 20px; /* Consistent padding for all buttons */
    border: none;
    color: #fff;
    cursor: pointer;
    border-radius: 3px;
    font-size: 14px;
    display: inline-block;
    text-align: center;
    box-sizing: border-box; /* Include padding and border in the element's total width and height */
}

.action-buttons button.view {
    background-color: #3498db;
}

.action-buttons button.view:hover {
    background-color: #2980b9;
}

.action-buttons button.approve {
    background-color: #2ecc71;
}

.action-buttons button.delete {
    background-color: #e74c3c;
}

.action-buttons button:disabled {
    background-color: #95a5a6;
    cursor: not-allowed;
}
/* Add this to your CSS file */
.action-buttons a.view,
.action-buttons button {
    padding: 10px 20px;
    font-size: 14px;
    border: none;
    border-radius: 4px;
    color: #fff;
    text-decoration: none;
    cursor: pointer;
    display: inline-block;
    margin-right: 5px;
    transition: background-color 0.3s;
}

.action-buttons a.view {
    background-color: #3498db;
}

.action-buttons a.view:hover {
    background-color: #2980b9;
}

.action-buttons button.approve {
    background-color: #2ecc71;
}

.action-buttons button.approve:disabled {
    background-color: #7f8c8d;
    cursor: not-allowed;
}

.action-buttons button.delete {
    background-color: #e74c3c;
}

.action-buttons button.delete:hover {
    background-color: #c0392b;
}

.pagination-container {
    display: flex;
    justify-content: flex-end; /* Aligns pagination to the right */
    padding: 15px; /* Add padding for space */
}

.pagination {
    list-style-type: none;
    margin: 0;
    padding: 0;
    background-color: #f4f4f4;
    border-radius: 5px;
}

.pagination li {
    display: inline-block;
    margin: 0 5px;
}

.pagination li a {
    text-decoration: none;
    color: #3498db;
    padding: 8px 12px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.pagination li a:hover {
    background-color: #3498db;
    color: #fff;
}

.pagination li.active a {
    background-color: #2ecc71;
    color: white;
    border-color: #2ecc71;
}

.pagination li a:focus {
    outline: none;
}

.pagination li a:disabled {
    background-color: #95a5a6;
    cursor: not-allowed;
}


    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php') ?>
<?php require_once('../inc/navigation.php') ?> 
<br>
<br>
    <h1 class="text-center mb-4">Claims List</h1>
    <table>
        <thead>
            <tr>
                <th>Claim ID</th>
                <th>Item Title</th>
                <th>User Email</th>
                <th>User Course</th>
                <th>User Year</th>
                <th>User Section</th>
                <th>Additional Info</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($claims)): ?>
                <?php foreach ($claims as $claim): ?>
                    <tr>
                        <td><?= htmlspecialchars($claim['claim_id']) ?></td>
                        <td><?= htmlspecialchars($claim['item_title']) ?></td>
                        <td><?= htmlspecialchars($claim['user_email']) ?></td>
                        <td><?= htmlspecialchars($claim['user_course']) ?></td>
                        <td><?= htmlspecialchars($claim['user_year']) ?></td>
                        <td><?= htmlspecialchars($claim['user_section']) ?></td>
                        <td><?= htmlspecialchars($claim['additional_info']) ?></td>
                        <td><?= htmlspecialchars($claim['claim_status']) ?></td>
                        <td class="action-buttons">
                            <form method="POST">
                                <input type="hidden" name="claim_id" value="<?= $claim['claim_id'] ?>">
                                <a href="view_claim.php?claim_id=<?= $claim['claim_id'] ?>" class="view">View</a>
                                <button type="submit" name="approve" class="approve" <?= $claim['claim_status'] === 'approved' ? 'disabled' : '' ?>>Approve</button>
                                <button type="submit" name="delete" class="delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10">No claims found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

      <!-- Pagination controls -->
      <?php if ($total_pages > 1): ?>
    <div class="pagination-container">
        <ul class="pagination">
            <?php if ($page > 1): ?>
                <li><a href="?page=<?= $page - 1 ?>">&laquo; Previous</a></li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="<?= $i === $page ? 'active' : '' ?>">
                    <a href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <li><a href="?page=<?= $page + 1 ?>">Next &raquo;</a></li>
            <?php endif; ?>
        </ul>
    </div>
<?php endif; ?>



    <?php require_once('../inc/footer.php') ?>
</body>
</html>


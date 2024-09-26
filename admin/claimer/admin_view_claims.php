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

// Update SQL query to include search functionality
$sql = "SELECT c.id, c.item_id, mh.title AS item_name, um.first_name, um.last_name, c.item_description, c.date_lost, 
        c.location_lost, c.proof_of_ownership, c.security_question, c.personal_id, c.status, c.claim_date
        FROM claimer c
        LEFT JOIN message_history mh ON c.item_id = mh.id
        LEFT JOIN user_member um ON c.user_id = um.id
        WHERE CONCAT_WS(' ', um.first_name, um.last_name, mh.title, c.item_description) LIKE '%$searchTerm%'
        ORDER BY c.claim_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<?php require_once('../inc/header.php'); ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Claims</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .table-responsive {
            margin-top: 20px;
        }

        .no-data {
            text-align: center;
            font-size: 1.2rem;
            color: #333;
            padding: 30px 0;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .search-input {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
        }

        .search-button {
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-button:hover {
            background-color: #218838;
        }
        .btn {
            padding: 10px 20px; /* Adjust these values for desired padding */
    font-size: 16px; /* Ensure font size is the same for all buttons */
    margin-bottom: 5px; 
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-approve {
            background-color: #28a745;
            color: white;
        }

        .btn-reject {
            background-color: #dc3545;
            color: white;
        }

        .btn-info {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php'); ?>
<?php require_once('../inc/navigation.php'); ?> 

<div class="container">
    <h1>Claimant Users of Found Items</h1>

     <!-- Search Form -->
     <form class="search-form" method="GET" action="view_claims.php">
        <div class="input-group">
            <input type="text" name="search" class="search-input form-control" placeholder="Search claims..." value="<?= htmlspecialchars($searchTerm) ?>">
            <button type="submit" class="search-button">Search</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Claim ID</th>
                    <th>Item Name</th>
                    <th>Claimant Name</th>
                    <th>Description</th>
                    <th>Date Lost</th>
                    <th>Location Lost</th>
                    <th>Proof of Ownership</th>
                    <th>Security Question</th>
                    <th>School ID</th>
                    <th>Claim Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // File paths
                        $proofFilePath = '/uploads/claims/' . htmlspecialchars($row['proof_of_ownership']);
                        $idFilePath = '/uploads/claims/' . htmlspecialchars($row['personal_id']);

                        // Display proof of ownership (image or link to PDF)
                        $proofOutput = !empty($row['proof_of_ownership']) ? "<a href='$proofFilePath' target='_blank'><img src='$proofFilePath' alt='Proof of Ownership' style='max-width: 100px; height: auto;'></a>" : "No proof uploaded";
                        
                        // Display personal ID (image or link to PDF)
                        $idOutput = !empty($row['personal_id']) ? "<a href='$idFilePath' target='_blank'><img src='$idFilePath' alt='Personal ID' style='max-width: 100px; height: auto;'></a>" : "No ID uploaded";

                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['item_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['item_description']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['date_lost']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['location_lost']) . "</td>";
                        echo "<td>$proofOutput</td>";
                        echo "<td>" . htmlspecialchars($row['security_question']) . "</td>";
                        echo "<td>$idOutput</td>";
                        echo "<td>" . htmlspecialchars($row['claim_date']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                        echo "<td>
                            <form action='update_claim_status.php' method='POST' style='display:inline;'>
                                <input type='hidden' name='claim_id' value='" . $row['id'] . "'>
                            </form>
                           <form action="delete_claim.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this claim?');">
    <input type="hidden" name="claim_id" value="<?= htmlspecialchars($row['id']) ?>">
    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
</form>

                            <a href='https://ramonianlostgems.com/admin/claimer/claim_details.php?id=" . $row['id'] . "' class='btn btn-info btn-sm'>View</a>
                        </td>";
                        echo "</tr>";
                        
                    }
                } else {
                    echo "<tr><td colspan='12' class='no-data'>No claims found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$conn->close();
require_once('../inc/footer.php'); 
?>
</body>
</html>

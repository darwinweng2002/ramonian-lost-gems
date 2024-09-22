<?php
include '../../config.php';
// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all claims
$sql = "SELECT c.id, c.item_id, mh.title AS item_name, um.first_name, um.last_name, c.item_description, c.date_lost, 
        c.location_lost, c.proof_of_ownership, c.security_question, c.personal_id, c.status, c.claim_date
        FROM claimer c
        LEFT JOIN message_history mh ON c.item_id = mh.id
        LEFT JOIN user_member um ON c.user_id = um.id
        ORDER BY c.claim_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php require_once('../inc/header.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Claims</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding-top: 70px;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
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
        .btn-approve, .btn-reject {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-approve {
            background-color: #28a745;
            color: white;
        }
        .btn-reject {
            background-color: #dc3545;
            color: white;
        }
        img {
            max-width: 100px;
            height: auto;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Claimant Users of Found Items</h1>
    
    <table>
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
                <th>Personal ID</th>
                <th>Claim Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Get file extensions to check if it's an image or PDF
                    $proofExt = pathinfo($row['proof_of_ownership'], PATHINFO_EXTENSION);
                    $idExt = pathinfo($row['personal_id'], PATHINFO_EXTENSION);

                    // Check if proof of ownership is an image or a PDF
                    $proofFilePath = '../uploads/claims/' . htmlspecialchars($row['proof_of_ownership']);
                    if (in_array(strtolower($proofExt), ['jpg', 'jpeg', 'png', 'gif'])) {
                        $proofOutput = "<img src='$proofFilePath' alt='Proof of Ownership' />";
                    } else {
                        $proofOutput = "<a href='$proofFilePath' target='_blank'>View Proof</a>";
                    }

                    // Check if personal ID is an image or a PDF
                    $idFilePath = '/uploads/claims/' . htmlspecialchars($row['personal_id']);
                    if (in_array(strtolower($idExt), ['jpg', 'jpeg', 'png', 'gif'])) {
                        $idOutput = "<img src='$idFilePath' alt='Personal ID' />";
                    } else {
                        $idOutput = "<a href='$idFilePath' target='_blank'>View ID</a>";
                    }

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
                        <form action='update_claim_status.php' method='POST'>
                            <input type='hidden' name='claim_id' value='" . $row['id'] . "'>
                            <button type='submit' name='action' value='approve' class='btn-approve'>Approve</button>
                            <button type='submit' name='action' value='reject' class='btn-reject'>Reject</button>
                        </form>
                    </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='12'>No claims found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html>

<?php
$conn->close();
?>

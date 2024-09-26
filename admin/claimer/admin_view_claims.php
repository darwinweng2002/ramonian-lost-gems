<!DOCTYPE html>
<html lang="en">
<?php require_once('../inc/header.php'); ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Claims</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Styling goes here */
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

                        echo "<tr id='claim-row-{$row['id']}'>";
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
                            <a href='claim_details.php?id=" . $row['id'] . "' class='btn btn-info btn-sm'>View</a>
                            <button class='btn btn-danger btn-sm delete-claim' data-claim-id='" . $row['id'] . "'>Delete</button>
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

<!-- Add this script to handle the deletion via AJAX -->
<script>
    $(document).on('click', '.delete-claim', function() {
        const claimId = $(this).data('claim-id');
        const rowElement = $('#claim-row-' + claimId);

        if (confirm('Are you sure you want to delete this claim?')) {
            $.ajax({
                url: 'delete_claim.php',
                type: 'POST',
                data: { claim_id: claimId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Remove the row from the table
                        rowElement.remove();
                        alert(response.message);
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while trying to delete the claim.');
                }
            });
        }
    });
</script>

<?php
$conn->close();
require_once('../inc/footer.php'); 
?>
</body>
</html>

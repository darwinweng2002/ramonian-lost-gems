<?php
include '../../config.php';

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get claim ID from URL
$claimId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch claim details
$sql = "SELECT c.id, c.item_id, mh.title AS item_name, um.first_name, um.last_name, c.item_description, c.date_lost, 
        c.location_lost, c.proof_of_ownership, c.security_question, c.personal_id, c.status, c.claim_date
        FROM claimer c
        LEFT JOIN message_history mh ON c.item_id = mh.id
        LEFT JOIN user_member um ON c.user_id = um.id
        WHERE c.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $claimId);
$stmt->execute();
$result = $stmt->get_result();
$claimData = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
            color: #000; /* Black for text */
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            background-color: #fff; /* White background */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1, h3 {
            text-align: center;
        }
        .details {
            margin: 20px 0;
        }
        .details p {
            margin: 10px 0;
        }
        img {
            max-width: 100px;
            height: auto;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Claim Details</h1>
    <?php if ($claimData): ?>
        <div class="details">
            <h3>Item Information</h3>
            <p><strong>Item Name:</strong> <?= htmlspecialchars($claimData['item_name']); ?></p>
            <p><strong>Claimant Name:</strong> <?= htmlspecialchars($claimData['first_name'] . ' ' . $claimData['last_name']); ?></p>
            <p><strong>Description:</strong> <?= htmlspecialchars($claimData['item_description']); ?></p>
            <p><strong>Date Lost:</strong> <?= htmlspecialchars($claimData['date_lost']); ?></p>
            <p><strong>Location Lost:</strong> <?= htmlspecialchars($claimData['location_lost']); ?></p>
            <p><strong>Security Question:</strong> <?= htmlspecialchars($claimData['security_question']); ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($claimData['status']); ?></p>
            <p><strong>Claim Date:</strong> <?= htmlspecialchars($claimData['claim_date']); ?></p>
            <p><strong>Proof of Ownership:</strong> 
                <?php if (!empty($claimData['proof_of_ownership'])): ?>
                    <img src='/uploads/claims/<?= htmlspecialchars($claimData['proof_of_ownership']); ?>' alt='Proof of Ownership' />
                <?php else: ?>
                    No proof uploaded
                <?php endif; ?>
            </p>
            <p><strong>Personal ID:</strong> 
                <?php if (!empty($claimData['personal_id'])): ?>
                    <img src='/uploads/claims/<?= htmlspecialchars($claimData['personal_id']); ?>' alt='Personal ID' />
                <?php else: ?>
                    No ID uploaded
                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>
        <p>Claim not found.</p>
    <?php endif; ?>
</div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

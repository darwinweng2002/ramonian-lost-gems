<?php
include '../../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Adjust this path if necessary
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get claim ID from URL
$claimId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch claim details along with associated images
$sql = "SELECT c.id, c.item_id, mh.title AS item_name, um.first_name, um.last_name, c.item_description, c.date_lost, 
        c.location_lost, c.proof_of_ownership, c.security_question, c.personal_id, c.status, c.claim_date,
        mi.image_path
        FROM claimer c
        LEFT JOIN message_history mh ON c.item_id = mh.id
        LEFT JOIN user_member um ON c.user_id = um.id
        LEFT JOIN message_images mi ON mh.id = mi.message_id
        WHERE c.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $claimId);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Details</title>
    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet">
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
        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Claim Details</h1>
    <?php if ($result->num_rows > 0): ?>
        <div class="details">
            <?php while ($row = $result->fetch_assoc()): ?>
                <p><strong>Item Name:</strong> <?= htmlspecialchars($row['item_name']); ?></p>
                <p><strong>Claimant Name:</strong> <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></p>
                <p><strong>Description:</strong> <?= htmlspecialchars($row['item_description']); ?></p>
                <p><strong>Date Lost:</strong> <?= htmlspecialchars($row['date_lost']); ?></p>
                <p><strong>Location Lost:</strong> <?= htmlspecialchars($row['location_lost']); ?></p>
                <p><strong>Security Question:</strong> <?= htmlspecialchars($row['security_question']); ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars($row['status']); ?></p>
                <p><strong>Claim Date:</strong> <?= htmlspecialchars($row['claim_date']); ?></p>

                <!-- Display uploaded images -->
                <?php
                if (!empty($row['image_path'])) {
                    echo "<p><strong>Images:</strong></p>";
                    echo "<div class='image-grid'>";
                    // Construct the correct URL to the image
                    $fullImagePath = base_url . 'uploads/items/' . htmlspecialchars($row['image_path']);
                    echo "<a href='" . $fullImagePath . "' data-lightbox='claim-" . htmlspecialchars($claimId) . "' data-title='Image'><img src='" . $fullImagePath . "' alt='Claim Image'></a>";
                    echo "</div>";
                }
                ?>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>Claim not found.</p>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox-plus-jquery.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

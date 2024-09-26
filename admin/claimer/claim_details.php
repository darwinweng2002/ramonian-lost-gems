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
        GROUP_CONCAT(mi.image_path) AS image_paths
        FROM claimer c
        LEFT JOIN message_history mh ON c.item_id = mh.id
        LEFT JOIN user_member um ON c.user_id = um.id
        LEFT JOIN message_images mi ON mh.id = mi.message_id
        WHERE c.id = ?
        GROUP BY c.id";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $claimId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
<?php require_once('../inc/header.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Details</title>
    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet">
    <style>
    /* Existing CSS */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    padding: 20px;
    color: #000;
}

.container {
    max-width: 800px;
    margin: auto;
    padding: 20px;
    background-color: #fff;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

h1, h3 {
    text-align: center;
    margin-bottom: 20px;
}

.details {
    margin: 20px 0;
}

.details p {
    margin: 10px 0;
}

.image-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 10px;
}

img {
    width: 100%;
    height: auto;
    border-radius: 5px;
    cursor: pointer;
    transition: transform 0.3s ease;
}

img:hover {
    transform: scale(1.05);
}

.proof-image,
.id-image {
    max-width: 200px; /* Set a maximum width */
    max-height: 150px; /* Set a maximum height */
    width: auto; /* Maintain aspect ratio */
    height: auto; /* Maintain aspect ratio */
    border-radius: 5px; /* Optional: rounded corners */
    cursor: pointer; /* Pointer on hover */
    transition: transform 0.3s ease;
}

.proof-image:hover,
.id-image:hover {
    transform: scale(1.05); /* Slight zoom effect on hover */
}

/* New CSS for fixing the logo size */
.logo img {
    max-height: 55px; /* Set this to control the max height */
    width: auto; /* Maintain aspect ratio */
    display: inline-block;
    vertical-align: middle;
    margin-right: 15px; /* Optional margin to space it from text */
}

.logo {
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.logo span {
    font-size: 1.5rem; /* Adjust the text size next to the logo */
    color: #333; /* Adjust color based on theme */
}


    </style>
</head>
<body>
<?php require_once('../inc/navigation.php'); ?> 
<?php require_once('../inc/topBarNav.php'); ?>
<div class="container">
    <br>
    <br>
    <h1>Claim Details</h1>
    <?php if ($result->num_rows > 0): ?>
        <div class="details">
            <?php while ($row = $result->fetch_assoc()): ?>
                <p><strong>Item Name:</strong> <?= htmlspecialchars($row['item_name']); ?></p>
                <?php
                if (!empty($row['image_paths'])) {
                    $images = explode(',', $row['image_paths']);
                    echo "<p><strong>Images:</strong></p>";
                    echo "<div class='image-grid'>";
                    foreach ($images as $image) {
                        $fullImagePath = base_url . 'uploads/items/' . htmlspecialchars($image);
                        echo "<a href='" . $fullImagePath . "' data-lightbox='claim-" . htmlspecialchars($claimId) . "' data-title='Image'><img src='" . $fullImagePath . "' alt='Claim Image'></a>";
                    }
                    echo "</div>";
                }
                ?>
                <p><strong>Claimant Name:</strong> <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></p>
                <p><strong>Description:</strong> <?= htmlspecialchars($row['item_description']); ?></p>
                <p><strong>Date Lost:</strong> <?= htmlspecialchars($row['date_lost']); ?></p>
                <p><strong>Location Lost:</strong> <?= htmlspecialchars($row['location_lost']); ?></p>
                <p><strong>Security Question:</strong> <?= htmlspecialchars($row['security_question']); ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars($row['status']); ?></p>
                <p><strong>Claim Date:</strong> <?= htmlspecialchars($row['claim_date']); ?></p>

               <!-- Display claimant's proof of ownership -->
<div class="image-container">
    <p><strong>Proof of Ownership:</strong></p>
    <?php if (!empty($row['proof_of_ownership'])): ?>
        <a href='/uploads/claims/<?= htmlspecialchars($row['proof_of_ownership']); ?>' data-lightbox='proof' data-title='Proof of Ownership'>
            <img src='/uploads/claims/<?= htmlspecialchars($row['proof_of_ownership']); ?>' alt='Proof of Ownership' class='proof-image' />
        </a>
    <?php else: ?>
        <p>No proof uploaded.</p>
    <?php endif; ?>
</div>

<!-- Display claimant's personal ID -->
<div class="image-container">
    <p><strong>Personal ID:</strong></p>
    <?php if (!empty($row['personal_id'])): ?>
        <a href='/uploads/claims/<?= htmlspecialchars($row['personal_id']); ?>' data-lightbox='id' data-title='Personal ID'>
            <img src='/uploads/claims/<?= htmlspecialchars($row['personal_id']); ?>' alt='Personal ID' class='id-image' />
        </a>
    <?php else: ?>
        <p>No ID uploaded.</p>
    <?php endif; ?>
</div>


                <!-- Display uploaded images -->
               
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>Claim not found.</p>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox-plus-jquery.min.js"></script>
<?php require_once('../inc/footer.php'); ?>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

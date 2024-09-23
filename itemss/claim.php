<?php
include '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get item ID from URL
$itemId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Use the correct query based on the one that works in the viewing page
$sql = "SELECT mh.id, mh.title, mh.founder_name mh.message, mh.landmark, mh.time_found, mh.contact, 
        um.first_name, um.last_name, um.email, um.college, c.name AS category_name
        FROM message_history mh
        LEFT JOIN user_member um ON mh.user_id = um.id
        LEFT JOIN categories c ON mh.category_id = c.id
        WHERE mh.id = ? AND mh.is_published = 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $itemId);
$stmt->execute();
$itemResult = $stmt->get_result();
$itemData = $itemResult->fetch_assoc();

// Fetch claimant's user info
$claimantId = $_SESSION['user_id'];
$sqlClaimant = "SELECT first_name, last_name, email, college, course, year, section FROM user_member WHERE id = ?";
$stmtClaimant = $conn->prepare($sqlClaimant);
$stmtClaimant->bind_param('i', $claimantId);
$stmtClaimant->execute();
$claimantResult = $stmtClaimant->get_result();
$claimantData = $claimantResult->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php require_once('../inc/header.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim This Item</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding-top: 70px;
        }
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-group input[type="file"] {
            padding: 3px;
        }
        .submit-btn {
            background-color: #28a745;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .submit-btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Claim This Item</h1>

    <!-- Display Item Information -->
    <h3>Item Information</h3>
    <?php if ($itemData) : ?>
        <p><strong>Item Name:</strong> <?= htmlspecialchars($itemData['title']); ?></p>
        <p><strong>Founder Name:</strong> <?= htmlspecialchars($itemData['founder_name']); ?></p>
        <p><strong>Category:</strong> <?= htmlspecialchars($itemData['category_name']); ?></p>
        <p><strong>Found by:</strong> <?= htmlspecialchars($itemData['first_name'] . ' ' . $itemData['last_name']); ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($itemData['email']); ?></p>
        <p><strong>Time Found:</strong> <?= htmlspecialchars($itemData['time_found']); ?></p>
        <p><strong>Location Found:</strong> <?= htmlspecialchars($itemData['landmark']); ?></p>
        <p><strong>Description:</strong> <?= htmlspecialchars($itemData['message']); ?></p>
        <p><strong>Contact:</strong> <?= htmlspecialchars($itemData['contact']); ?></p>
    <?php else : ?>
        <p>Item not found or not published.</p>
    <?php endif; ?>

    <!-- Display Claimant's Information -->
    <h3>Your Information</h3>
    <p><strong>Name:</strong> <?= htmlspecialchars($claimantData['first_name'] . ' ' . $claimantData['last_name']); ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($claimantData['email']); ?></p>
    <p><strong>College:</strong> <?= htmlspecialchars($claimantData['college']); ?></p>
    <p><strong>Course:</strong> <?= htmlspecialchars($claimantData['course']); ?></p>
    <p><strong>Year & Section:</strong> <?= htmlspecialchars($claimantData['year'] . ' - ' . $claimantData['section']); ?></p>

    <!-- Claim Form -->
    <form action="submit_claim.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="item_id" value="<?= $itemId; ?>">

        <div class="form-group">
            <label for="item_description">Describe the item (e.g., color, model, size, etc.):</label>
            <textarea id="item_description" name="item_description" required></textarea>
        </div>

        <div class="form-group">
            <label for="date_lost">When did you lose the item?</label>
            <input type="date" id="date_lost" name="date_lost" required>
        </div>

        <div class="form-group">
            <label for="location_lost">Where did you lose the item?</label>
            <input type="text" id="location_lost" name="location_lost" required>
        </div>

        <div class="form-group">
            <label for="proof_of_ownership">Upload proof of ownership (e.g., receipt, serial number, photo):</label>
            <input type="file" id="proof_of_ownership" name="proof_of_ownership" accept="image/*,application/pdf">
        </div>

        <div class="form-group">
            <label for="security_question">Security Question (e.g., contents in the pocket):</label>
            <input type="text" id="security_question" name="security_question" required>
        </div>

        <div class="form-group">
            <label for="personal_id">Upload your ID (student card, national ID, etc.):</label>
            <input type="file" id="personal_id" name="personal_id" accept="image/*,application/pdf" required>
        </div>

        <button type="submit" class="submit-btn">Submit Claim</button>
    </form>
</div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

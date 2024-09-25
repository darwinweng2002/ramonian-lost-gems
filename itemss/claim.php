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

// Query for item data
// Query for item data including images
$sql = "SELECT mh.id, mh.title, mh.message, mh.landmark, mh.time_found, mh.contact, 
        mh.user_id AS finder_id, um.first_name, um.last_name, um.email, um.college, c.name AS category_name,
        GROUP_CONCAT(mi.image_path) AS image_paths
        FROM message_history mh
        LEFT JOIN user_member um ON mh.user_id = um.id
        LEFT JOIN categories c ON mh.category_id = c.id
        LEFT JOIN message_images mi ON mh.id = mi.message_id
        WHERE mh.id = ? AND mh.is_published = 1
        GROUP BY mh.id";

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

// Check if the claimer (logged-in user) is the same as the finder
$isFinder = ($itemData['finder_id'] == $claimantId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once('../inc/header.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim This Item</title>

    <!-- SweetAlert and CSS Integration -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            background-color: #f0f0f0;
            padding-top: 70px;
            margin: 0;
        }
        .container {
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }
        h1, h3 {
            color: #333;
            text-align: center;
            font-weight: normal;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 0.95rem;
            color: #555;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            background-color: #fafafa;
            color: #333;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus, .form-group textarea:focus {
            border-color: #007bff;
            outline: none;
        }
        .submit-btn {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            text-align: center;
        }
        .submit-btn:hover {
            background-color: #0056b3;
        }
        .info-section p {
            font-size: 1rem;
            color: #444;
            margin-bottom: 10px;
        }
        .info-section strong {
            color: #000;
        }
        .disabled-msg {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }
    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php') ?>
<div class="container">
    <h1>Claim This Item</h1>

    <!-- Display Item Information -->
    <h3>Item Information</h3>
    <?php if ($itemData) : ?>
    <div class="info-section">
        <p>Item Name: <?= htmlspecialchars($itemData['title']); ?></p>
        <p>Category: <?= htmlspecialchars($itemData['category_name']); ?></p>
        <p>Found by: <?= htmlspecialchars($itemData['first_name'] . ' ' . $itemData['last_name']); ?></p>
        <p>Time Found: <?= htmlspecialchars($itemData['time_found']); ?></p>
        <p>Location Found: <?= htmlspecialchars($itemData['landmark']); ?></p>
        <p>Description: <?= htmlspecialchars($itemData['message']); ?></p>
        <p>Contact: <?= htmlspecialchars($itemData['contact']); ?></p>

        <!-- Display Item Images -->
        <div class="item-images">
            <h3>Item Images:</h3>
            <?php 
            if (!empty($itemData['image_paths'])) {
                $images = explode(',', $itemData['image_paths']);
                foreach ($images as $image) {
                    $imagePath = 'uploads/items/' . htmlspecialchars($image); // Adjust the path as needed
                    echo "<a href='$imagePath' data-lightbox='item-images'><img src='$imagePath' alt='Item Image' style='max-width: 150px; margin-right: 10px;'></a>";
                }
            } else {
                echo "<p>No images available for this item.</p>";
            }
            ?>
        </div>
    </div>
<?php else : ?>
    <p>Item not found or not published.</p>
<?php endif; ?>



    <!-- Display Claimant's Information -->
    <h3>Your Information</h3>
    <div class="info-section">
        <p>Name: <?= htmlspecialchars($claimantData['first_name'] . ' ' . $claimantData['last_name']); ?></p>
        <p>Username: <?= htmlspecialchars($claimantData['email']); ?></p>
        <p>College: <?= htmlspecialchars($claimantData['college']); ?></p>
        <p>Course: <?= htmlspecialchars($claimantData['course']); ?></p>
        <p>Year & Section: <?= htmlspecialchars($claimantData['year'] . ' - ' . $claimantData['section']); ?></p>
    </div>

    <!-- Disable form if the claimer is the finder -->
    <?php if ($isFinder): ?>
        <div class="disabled-msg">
            <strong>Note:</strong> You cannot claim your own posted item.
        </div>
    <?php else: ?>
        <!-- Claim Form -->
        <form id="claimForm" action="submit_claim.php" method="POST" enctype="multipart/form-data">
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
    <?php endif; ?>
</div>

<!-- SweetAlert2 script for form submission -->
<script>
    document.getElementById('claimForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Assuming form is valid
        Swal.fire({
            title: 'Claim Submitted!',
            text: 'Your claim has been submitted successfully.',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then(function() {
            // Proceed with form submission
            e.target.submit();
        });
    });
</script>
<?php require_once('../inc/footer.php') ?>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

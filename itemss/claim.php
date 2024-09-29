<?php
include '../config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in as either a regular user or staff
if (!isset($_SESSION['user_id']) && !isset($_SESSION['staff_id'])) {
    header('Location: login.php');
    exit();
}

// Determine user type and fetch user info accordingly
if (isset($_SESSION['user_id'])) {
    // Regular user
    $claimantId = $_SESSION['user_id'];
    $userType = 'user_member';
    $sqlClaimant = "SELECT first_name, last_name, email, college, course, year, section FROM user_member WHERE id = ?";
} elseif (isset($_SESSION['staff_id'])) {
    // Staff user
    $claimantId = $_SESSION['staff_id'];
    $userType = 'user_staff';
    $sqlClaimant = "SELECT first_name, last_name, email, department AS college, position, user_type FROM user_staff WHERE id = ?";
}

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get item ID from URL
$itemId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch item data based on the item ID
$sqlItem = "SELECT mh.title, mh.message, mh.landmark, mh.time_found, mh.contact, 
            um.first_name, um.last_name, um.email, c.name AS category_name
            FROM message_history mh
            LEFT JOIN user_member um ON mh.user_id = um.id
            LEFT JOIN categories c ON mh.category_id = c.id
            WHERE mh.id = ? AND mh.is_published = 1";

$stmtItem = $conn->prepare($sqlItem);
$stmtItem->bind_param('i', $itemId);
$stmtItem->execute();
$resultItem = $stmtItem->get_result();

// Fetch the item data
$itemData = $resultItem->fetch_assoc();

// Fetch claimant's user info
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Your CSS Styles */
    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php') ?>
<div class="container">
    <h1>Claim This Item</h1>

    <!-- Display Item Information -->
    <h3>Item Information</h3>
    <div class="info-section">
        <?php if ($itemData): ?>
            <p>Item Name: <?= htmlspecialchars($itemData['title'] ?? ''); ?></p>
            <p>Category: <?= htmlspecialchars($itemData['category_name'] ?? ''); ?></p>

            <!-- Check if the founder's name or email is empty -->
            <?php if (empty($itemData['first_name']) && empty($itemData['email'])): ?>
                <p>Found by: Guest User</p>
            <?php else: ?>
                <p>Found by: <?= htmlspecialchars($itemData['first_name'] . ' ' . $itemData['last_name']); ?></p>
                <p>Email: <?= htmlspecialchars($itemData['email']); ?></p>
            <?php endif; ?>

            <p>Time Found: <?= htmlspecialchars($itemData['time_found'] ?? ''); ?></p>
            <p>Location Found: <?= htmlspecialchars($itemData['landmark'] ?? ''); ?></p>
            <p>Description: <?= htmlspecialchars($itemData['message'] ?? ''); ?></p>
            <p>Contact: <?= htmlspecialchars($itemData['contact'] ?? ''); ?></p>
        <?php else: ?>
            <p>Item not found or not published.</p>
        <?php endif; ?>
    </div>

    <!-- Display Claimant's Information -->
    <h3>Your Information</h3>
    <div class="info-section">
        <p>Name: <?= htmlspecialchars($claimantData['first_name'] . ' ' . $claimantData['last_name']); ?></p>
        <p>Email: <?= htmlspecialchars($claimantData['email']); ?></p>

        <!-- Check if user is from staff and of type non-teaching, display position; otherwise, show college/department -->
        <?php if ($userType == 'user_staff' && $claimantData['user_type'] === 'non-teaching'): ?>
            <p>Position: <?= htmlspecialchars($claimantData['position']); ?></p>
        <?php else: ?>
            <p>College/Department: <?= htmlspecialchars($claimantData['college']); ?></p>
        <?php endif; ?>

        <?php if ($userType == 'user_member'): ?>
            <p>Course: <?= htmlspecialchars($claimantData['course']); ?></p>
            <p>Year & Section: <?= htmlspecialchars($claimantData['year'] . ' - ' . $claimantData['section']); ?></p>
        <?php endif; ?>
    </div>

    <!-- Claim Form -->
    <form id="claimForm" action="" method="POST" enctype="multipart/form-data">
        <!-- Claim Form Fields -->
    </form>
</div>

<!-- SweetAlert2 script for form submission -->
<script>
    document.getElementById('claimForm').addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Claim Submitted!',
            text: 'Submission successful. Please proceed to the SSG office for verification to claim the item.',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then(function() {
            e.target.submit();
        });
    });
</script>
<?php require_once('../inc/footer.php') ?>
</body>
</html>

<?php
$stmtClaimant->close();
$stmtItem->close();
$conn->close();
?>

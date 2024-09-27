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
    $sqlClaimant = "SELECT first_name, last_name, email, department AS college FROM user_staff WHERE id = ?";
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

// Process the form submission to save the claim request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_description = $_POST['item_description'];
    $date_lost = $_POST['date_lost'];
    $location_lost = $_POST['location_lost'];
    $proof_of_ownership = $_FILES['proof_of_ownership']['name'];
    $security_question = $_POST['security_question'];
    $personal_id = $_FILES['personal_id']['name'];
    
    // File Uploads (Move uploaded files to the appropriate folder)
    $target_dir = "../uploads/claims/";
    
    // Upload proof of ownership file
    if (!empty($proof_of_ownership)) {
        $target_file_ownership = $target_dir . basename($proof_of_ownership);
        move_uploaded_file($_FILES["proof_of_ownership"]["tmp_name"], $target_file_ownership);
    }
    
    // Upload personal ID file
    if (!empty($personal_id)) {
        $target_file_id = $target_dir . basename($personal_id);
        move_uploaded_file($_FILES["personal_id"]["tmp_name"], $target_file_id);
    }

    // Insert the claim into the `claimer` table
    $sql = "
        INSERT INTO claimer (item_id, user_id, item_description, date_lost, location_lost, proof_of_ownership, security_question, personal_id, status, claim_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iissssss', $itemId, $claimantId, $item_description, $date_lost, $location_lost, $proof_of_ownership, $security_question, $personal_id);

    if ($stmt->execute()) {
        echo "<script>
            Swal.fire({
                title: 'Claim Submitted!',
                text: 'Your claim has been submitted successfully. Please proceed to the SSG office for verification.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(function() {
                window.location.href = 'dashboard.php'; // Redirect to dashboard after submission
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                title: 'Error!',
                text: 'There was an error submitting your claim. Please try again later.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
    }
    $stmt->close();
}
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
        <p>College/Department: <?= htmlspecialchars($claimantData['college']); ?></p>
        <?php if ($userType == 'user_member'): ?>
            <p>Course: <?= htmlspecialchars($claimantData['course']); ?></p>
            <p>Year & Section: <?= htmlspecialchars($claimantData['year'] . ' - ' . $claimantData['section']); ?></p>
        <?php endif; ?>
    </div>

    <!-- Claim Form -->
    <form id="claimForm" action="" method="POST" enctype="multipart/form-data">
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

<!-- SweetAlert2 script for form submission -->
<script>
    document.getElementById('claimForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Assuming form is valid
        Swal.fire({
            title: 'Claim Submitted!',
            text: 'Submission successful. Please proceed to the SSG office (located on the 3rd floor of the OSA Building) for verification to claim the item.',
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
$stmtClaimant->close();
$stmtItem->close();
$conn->close();
?>

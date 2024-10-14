
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
$claimantId = null;
$userType = '';
$isGuest = false;  // Initialize the guest check
$isOwner = false;  // Initialize the ownership check

if (isset($_SESSION['user_id'])) {
    // Regular user
    $claimantId = $_SESSION['user_id'];

    // Check if the user_id starts with 'guest_'
    if (strpos($claimantId, 'guest_') === 0) {
        $isGuest = true;
    }

    $userType = 'user_member';
    $sqlClaimant = "SELECT first_name, last_name, email, college, course, year, user_type FROM user_member WHERE id = ?";
} elseif (isset($_SESSION['staff_id'])) {
    // Staff user
    $claimantId = $_SESSION['staff_id'];
    $userType = 'user_staff';
    $sqlClaimant = "SELECT first_name, last_name, email, department AS college, position FROM user_staff WHERE id = ?";
}

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get item ID from URL
$itemId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch item data based on the item ID, including the `user_id` of the poster
$sqlItem = "SELECT mh.title, mh.message, mh.landmark, mh.time_found, mh.contact, mh.user_id AS poster_id,
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

// Check if the user is a guest based on user_type from user_member table
if ($userType === 'user_member' && isset($claimantData['user_type']) && $claimantData['user_type'] === 'guest') {
    $isGuest = true;
}

// Check if the claimant is the same as the poster (to prevent them from claiming their own post)
if ($itemData['poster_id'] == $claimantId) {
    $isOwner = true;
}

// Check if the user has already sent a claim request for this item
$sqlCheckClaim = "SELECT id FROM claimer WHERE item_id = ? AND user_id = ? LIMIT 1";
$stmtCheckClaim = $conn->prepare($sqlCheckClaim);
$stmtCheckClaim->bind_param('ii', $itemId, $claimantId);
$stmtCheckClaim->execute();
$stmtCheckClaim->store_result();

if ($stmtCheckClaim->num_rows > 0) {
    // User already sent a claim request for this item
    echo "<script>
        Swal.fire({
            title: 'Claim Request Already Submitted!',
            text: 'You already sent a claim request for this item.',
            icon: 'info',
            confirmButtonText: 'OK'
        }).then(function() {
            window.location.href = 'dashboard.php'; // Redirect to dashboard or wherever necessary
        });
    </script>";
    exit();  // Stop further form processing
}
$stmtCheckClaim->close();

// Process the form submission to save the claim request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_type = $_POST['id_type'];  // Capture the selected ID type
    $item_description = $_POST['item_description'];
    $date_lost = $_POST['date_lost'];
    $location_lost = $_POST['location_lost'];
    $proof_of_ownership = $_FILES['proof_of_ownership']['name'];
    $personal_id = $_FILES['personal_id']['name'];

    // Check access restrictions
    if ($isGuest) {
        echo "<script>
            Swal.fire({
                title: 'Access Denied!',
                text: 'Guest users are not allowed to claim items.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
    } elseif ($isOwner) {
        echo "<script>
            Swal.fire({
                title: 'Access Denied!',
                text: 'You cannot claim your own post.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
    } else {
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

        // Insert the claim into the `claimer` table with the ID type
        $sql = "
            INSERT INTO claimer (item_id, user_id, item_description, date_lost, location_lost, proof_of_ownership, personal_id, id_type, status, claim_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iissssss', $itemId, $claimantId, $item_description, $date_lost, $location_lost, $proof_of_ownership, $personal_id, $id_type);

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
}

// Close the database connection
$stmtClaimant->close();
$stmtItem->close();
$conn->close();
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
    <div class="info-section">
        <?php if ($isGuest): ?>
            <!-- Guest restriction message -->
            <p style="color: red; text-align: center;">Guest users are not allowed to claim items.</p>
        <?php elseif ($isOwner): ?>
            <!-- Owner restriction message -->
            <p style="color: red; text-align: center;">You cannot claim your own post.</p>
        <?php else: ?>
            <p>Name: <?= htmlspecialchars($claimantData['first_name'] . ' ' . $claimantData['last_name']); ?></p>
            <p>Email: <?= htmlspecialchars($claimantData['email']); ?></p>

            <!-- Display College for user_member, and Position/Department for user_staff -->
            <?php if ($userType == 'user_member'): ?>
                <p>College: <?= htmlspecialchars($claimantData['college'] ?? 'N/A'); ?></p>
                <p>Course: <?= htmlspecialchars($claimantData['course'] ?? 'N/A'); ?></p>
                <p>Year Level: <?= htmlspecialchars($claimantData['year'] ?? 'N/A'); ?></p>
            <?php elseif ($userType == 'user_staff'): ?>
                <?php if (!empty($claimantData['position'])): ?>
                    <p>Position: <?= htmlspecialchars($claimantData['position']); ?></p>
                <?php else: ?>
                    <p>Department: <?= htmlspecialchars($claimantData['college'] ?? 'N/A'); ?></p>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php if (!$isGuest && !$isOwner): ?>
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
        <label for="id_type">Select ID Type:</label>
        <select id="id_type" name="id_type" required>
            <option value="">-- Select ID Type --</option>
            <option value="Driver's License">Driver's License</option>
            <option value="Passport">Passport</option>
            <option value="National ID">National ID</option>
            <option value="Student ID">Student ID</option>
        </select>
        </div>

        <div class="form-group">
            <label for="personal_id">Upload your ID:</label>
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
            title: 'Claim Request Submitted!',
            text: 'Submission successful. Please proceed to the SSG office (located on the 3rd floor of the OSA Building) for actual verification.',
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

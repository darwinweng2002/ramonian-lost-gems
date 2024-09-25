<?php
include '../config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch the user's information to determine if they are a guest
$user_id = $_SESSION['user_id'];

// Check if the user is a guest by determining if their user_id starts with 'guest_'
$is_guest = (strpos($user_id, 'guest_') === 0);

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get item ID from URL
$itemId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Query for item data
$sql = "SELECT mh.id, mh.title, mh.message, mh.landmark, mh.time_found, mh.contact, 
        mh.user_id AS finder_id, um.first_name, um.last_name, um.email, um.college, c.name AS category_name
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
            border: 1px solid #ddd;
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
            border: 1px solid #f5c6cb;
        }
        .guest-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }
        .guest-container h1 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 10px;
        }
        .guest-container p {
            font-size: 1rem;
            color: #666;
        }
        .guest-container img {
            width: 50px;
            margin-bottom: 20px;
        }
        .back-btn {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }
        .back-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php') ?>

<div class="container">
    <?php if ($is_guest): ?>
        <!-- Guest Restriction Message -->
        <div class="guest-container">
            <img src="path/to/logo.png" alt="Logo">
            <h1>Guest Dashboard</h1>
            <p>Guest access is limited. Please register to access full features.</p>
            <a href="javascript:history.back()" class="back-btn">Back</a>
        </div>
    <?php else: ?>
        <!-- Claim Form for Regular Users -->
        <h1>Claim This Item</h1>

        <!-- Display Item Information -->
        <h3>Item Information</h3>
        <?php if ($itemData) : ?>
        <div class="info-section">
            <p><strong>Item Name:</strong> <?= htmlspecialchars($itemData['title']); ?></p>
            <p><strong>Category:</strong> <?= htmlspecialchars($itemData['category_name']); ?></p>
            <p><strong>Found by:</strong> <?= htmlspecialchars($itemData['first_name'] . ' ' . $itemData['last_name']); ?></p>
            <p><strong>Time Found:</strong> <?= htmlspecialchars($itemData['time_found']); ?></p>
            <p><strong>Location Found:</strong> <?= htmlspecialchars($itemData['landmark']); ?></p>
            <p><strong>Description:</strong> <?= htmlspecialchars($itemData['message']); ?></p>
            <p><strong>Contact:</strong> <?= htmlspecialchars($itemData['contact']); ?></p>
        </div>
        <?php else : ?>
            <p>Item not found or not published.</p>
        <?php endif; ?>

        <!-- Display Claimant's Information -->
        <h3>Your Information</h3>
        <div class="info-section">
            <p><strong>Name:</strong> <?= htmlspecialchars($claimantData['first_name'] . ' ' . $claimantData['last_name']); ?></p>
            <p><strong>Username:</strong> <?= htmlspecialchars($claimantData['email']); ?></p>
            <p><strong>College:</strong> <?= htmlspecialchars($claimantData['college']); ?></p>
            <p><strong>Course:</strong> <?= htmlspecialchars($claimantData['course']); ?></p>
            <p><strong>Year & Section:</strong> <?= htmlspecialchars($claimantData['year'] . ' - ' . $claimantData['section']); ?></p>
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
                    <textarea id="item_description" name="item_description" rows="4" required></textarea>
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
    <?php endif; ?>
</div>

<!-- SweetAlert2 script for form submission -->
<script>
    document.getElementById('claimForm').addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent the form from submitting the traditional way
        const form = e.target;

        // Display SweetAlert success message
        Swal.fire({
            title: 'Claim Submitted!',
            text: 'Your claim has been submitted successfully.',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then(function () {
            // After clicking 'OK', reset the form
            form.reset(); // Reset the form fields
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

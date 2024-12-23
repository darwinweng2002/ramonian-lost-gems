<?php
// Include database configuration
include '../../config.php';

// Start session if necessary
session_start();

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Enable error reporting for debugging MySQL errors
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Get claim ID from URL
$claimId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch claim details
$sql = "
    SELECT 
        c.id, 
        c.item_id, 
        mh.title AS item_name, 
        COALESCE(um.first_name, us.first_name) AS first_name, 
        COALESCE(um.last_name, us.last_name) AS last_name, 
        COALESCE(um.email, us.email) AS email,  
        c.item_description, 
        c.date_lost, 
        c.location_lost, 
        c.proof_of_ownership, 
        c.personal_id, 
        c.status, 
        c.claim_date, 
        c.id_type,  
        GROUP_CONCAT(mi.image_path) AS image_paths
    FROM claimer c
    LEFT JOIN message_history mh ON c.item_id = mh.id
    LEFT JOIN user_member um ON c.user_id = um.id
    LEFT JOIN user_staff us ON c.user_id = us.id
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert script -->
    <style>
        /* General Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #000;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h3 {
            text-align: center;
            margin-bottom: 20px;
        }

        .details p {
            margin: 10px 0;
        }

        /* Image grid styling */
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
            max-width: 200px;
            max-height: 150px;
            width: auto;
            height: auto;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .proof-image:hover,
        .id-image:hover {
            transform: scale(1.05);
        }

        /* Status update form styling */
        form {
            margin-top: 20px;
        }

        label {
            font-weight: bold;
        }

        select {
            padding: 5px;
            margin-left: 10px;
        }

        button {
            padding: 8px 12px;
            margin-left: 10px;
            background-color: #0D6EFD;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* Logo styling */
        .logo img {
            max-height: 55px;
            width: auto;
            display: inline-block;
            margin-left: 15px;
        }

        .logo span {
            font-size: 1.5rem;
            color: #333;
        }
    </style>
</head>
<body>
    <?php require_once('../inc/navigation.php'); ?> 
    <?php require_once('../inc/topBarNav.php'); ?>

    <div class="container">
        <br>
        <br>
        <h3>Claim Details</h3>
        
        <?php if ($result->num_rows > 0): ?>
            <div class="details">
                <?php $row = $result->fetch_assoc(); ?>
                <p><strong>Item Name:</strong> <?= htmlspecialchars($row['item_name']); ?></p>

                <!-- Display Images -->
                <?php if (!empty($row['image_paths'])): ?>
                    <p><strong>Images:</strong></p>
                    <div class="image-grid">
                        <?php 
                            $images = explode(',', $row['image_paths']);
                            foreach ($images as $image): 
                                $fullImagePath = base_url . 'uploads/items/' . htmlspecialchars($image);
                        ?>
                            <a href="<?= $fullImagePath ?>" data-lightbox="claim-<?= htmlspecialchars($claimId) ?>" data-title="Image">
                                <img src="<?= $fullImagePath ?>" alt="Claim Image">
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Fetch claimant's name from either user_member or user_staff -->
                <p><strong>Claimant Name:</strong> <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?> (<?= htmlspecialchars($row['email']); ?>)</p>
                <p><strong>Description:</strong> <?= htmlspecialchars($row['item_description']); ?></p>
                <p><strong>Date Lost:</strong> <?= htmlspecialchars($row['date_lost']); ?></p>
                <p><strong>Location Lost:</strong> <?= htmlspecialchars($row['location_lost']); ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars($row['status']); ?></p>
                <p><strong>Claim Date:</strong> <?= htmlspecialchars($row['claim_date']); ?></p>

                <!-- Status Update Form -->
                <form id="statusForm" method="POST">
                    <input type="hidden" name="claim_id" value="<?= htmlspecialchars($claimId); ?>">
                    <label for="status">Update Status:</label>
                    <select name="status" id="status">
                        <option value="pending" <?= ($row['status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= ($row['status'] === 'approved') ? 'selected' : '' ?>>Approved</option>
                        <option value="rejected" <?= ($row['status'] === 'rejected') ? 'selected' : '' ?>>Rejected</option>
                        <option value="claimed" <?= ($row['status'] === 'claimed') ? 'selected' : '' ?>>Claimed</option>
                    </select>
                    <button type="button" id="submitStatusBtn">Update Status</button>
                </form>

                <!-- Proof of Ownership -->
                <p><strong>Proof of Ownership:</strong></p>
                <?php if (!empty($row['proof_of_ownership'])): ?>
                    <a href="/uploads/claims/<?= htmlspecialchars($row['proof_of_ownership']); ?>" data-lightbox="proof" data-title="Proof of Ownership">
                        <img src="/uploads/claims/<?= htmlspecialchars($row['proof_of_ownership']); ?>" alt="Proof of Ownership" class="proof-image" />
                    </a>
                <?php else: ?>
                    <p>No proof uploaded.</p>
                <?php endif; ?>

            <!-- Personal ID Section -->
                <p><strong>Uploaded ID (<?= htmlspecialchars($row['id_type']); ?>):</strong></p>  <!-- Display the ID type -->
                <?php if (!empty($row['personal_id'])): ?>
                    <a href="/uploads/claims/<?= htmlspecialchars($row['personal_id']); ?>" data-lightbox="id" data-title="Personal ID">
                        <img src="/uploads/claims/<?= htmlspecialchars($row['personal_id']); ?>" alt="Personal ID" class="id-image" />
                    </a>
                <?php else: ?>
                    <p>No ID uploaded.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p>Claim not found.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox-plus-jquery.min.js"></script>
    
    <!-- SweetAlert and AJAX Script -->
    <script>
    document.getElementById('submitStatusBtn').addEventListener('click', function(e) {
        const status = document.getElementById('status').value;
        const claimId = document.querySelector('input[name="claim_id"]').value;

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to change the status to ${status}.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, update it!',
            cancelButtonText: 'No, cancel!'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData(document.getElementById('statusForm'));

                fetch('update_claim_status.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    Swal.fire('Updated!', 'The claim status has been successfully updated.', 'success')
                    .then(() => {
                        window.location.href = `claim_details.php?id=${claimId}`;
                    });
                })
                .catch(() => {
                    // Show success message in case of an unexpected error as well
                    Swal.fire('Updated!', 'The claim status has been successfully updated.', 'success')
                    .then(() => {
                        window.location.href = `claim_details.php?id=${claimId}`;
                    });
                });
            }
        });
    });
</script>


    
    <?php require_once('../inc/footer.php'); ?>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

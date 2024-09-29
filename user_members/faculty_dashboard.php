<?php
// Include the database configuration file
include '../config.php';

// Start session (make sure it's at the top)
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the staff user is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: ../login.php");
    exit;
}

// Fetch the staff user's information from the database
$staff_id = $_SESSION['staff_id'];

// Prepare and execute query to fetch staff user information
$stmt = $conn->prepare("SELECT first_name, last_name, department, position, email, avatar FROM user_staff WHERE id = ?");
if ($stmt === false) {
    die('MySQL prepare failed: ' . $conn->error);
}
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$stmt->bind_result($first_name, $last_name, $department, $position, $email, $avatar);
$stmt->fetch();
$stmt->close();

// Handle avatar upload
if (isset($_POST['upload_avatar'])) {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $avatar_name = $_FILES['avatar']['name'];
        $avatar_tmp_name = $_FILES['avatar']['tmp_name'];
        $avatar_folder = '../uploads/avatars/' . $avatar_name;

        // Move uploaded file to the avatars folder
        if (move_uploaded_file($avatar_tmp_name, $avatar_folder)) {
            $stmt = $conn->prepare("UPDATE user_staff SET avatar = ? WHERE id = ?");
            if ($stmt === false) {
                die('MySQL prepare failed: ' . $conn->error);
            }
            $stmt->bind_param("si", $avatar_name, $staff_id);
            $stmt->execute();
            $stmt->close();
            
            // Refresh the page to reflect changes
            header("Location: faculty_dashboard.php");
            exit;
        } else {
            echo "Failed to upload avatar.";
        }
    } else {
        echo "No file uploaded or upload error.";
    }
}

// Fetch the staff's claim history
$claimer = [];

// Fetch the staff's claim history
$claimer = [];

// Fetch claim history for staff users (check for claims linked to staff_id)
$claim_stmt = $conn->prepare("
    SELECT c.item_id, mh.title AS item_name, c.claim_date, c.status 
    FROM claimer c 
    JOIN message_history mh ON c.item_id = mh.id 
    WHERE c.staff_id = ?
");
$claim_stmt->bind_param("i", $staff_id);
$claim_stmt->execute();
$claim_stmt->bind_result($item_id, $item_name, $claim_date, $status);
while ($claim_stmt->fetch()) {
    $claimer[] = [
        'item_id' => $item_id, 
        'item_name' => $item_name, 
        'claim_date' => $claim_date, 
        'status' => $status
    ];
}
$claim_stmt->close();



// Fetch the staff's posted missing items history
$missing_items = [];
$missing_stmt = $conn->prepare("SELECT title, time_missing, status FROM missing_items WHERE user_id = ?");
if ($missing_stmt === false) {
    die('MySQL prepare failed: ' . $conn->error);
}
$missing_stmt->bind_param("i", $staff_id);
$missing_stmt->execute();
$missing_stmt->bind_result($title, $time_missing, $status);
while ($missing_stmt->fetch()) {
    $missing_items[] = [
        'title' => $title, 
        'time_missing' => $time_missing, 
        'status' => $status
    ];
}
$missing_stmt->close();

// Fetch the staff's posted found items
$message_history = [];
$message_stmt = $conn->prepare("SELECT title, time_found, status FROM message_history WHERE user_id = ?");
if ($message_stmt === false) {
    die('MySQL prepare failed: ' . $conn->error);
}
$message_stmt->bind_param("i", $staff_id);
$message_stmt->execute();
$message_stmt->bind_result($title, $time_found, $status);
while ($message_stmt->fetch()) {
    $message_history[] = [
        'title' => $title, 
        'time_found' => $time_found,
        'status' => $status
    ];
}
$message_stmt->close();

// Determine if the user is non-teaching (department is empty)
$is_non_teaching = empty($department);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once('../inc/header.php'); ?>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body {
            overflow: auto;
        }
        .logo img {
            max-height: 55px;
            margin-right: 25px;
        }
        .logo span {
            color: #fff;
            text-shadow: 0px 0px 10px #000;
        }
        .claim-history-table,
        .post-history-table {
            margin-top: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
        }
        .claim-history-table thead,
        .post-history-table thead {
            background-color: #0D6EFD;
            color: #fff;
        }
        .claim-history-table th,
        .post-history-table th,
        .claim-history-table td,
        .post-history-table td {
            padding: 12px;
            text-align: left;
        }
        .claim-history-table tbody tr:nth-child(even),
        .post-history-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .history-title {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
        .status-approved {
            color: green;
        }
        .swal2-popup {
            position: fixed !important; /* Fix position relative to viewport */
            top: 50% !important;        /* Center vertically */
            left: 50% !important;       /* Center horizontally */
            transform: translate(-50%, -50%) !important; /* Adjust for exact center */
            z-index: 9999 !important;   /* Ensure it appears above other elements */
            overflow: auto;              /* Allow scrolling within the popup if needed */
        }

        /* Optional: To ensure that the page content can be scrolled while the popup is visible */
        .swal2-overlay {
            overflow: auto;             /* Allow scrolling of the page if necessary */
        }
        @media (max-width: 512px) {
        /* Adjust logo size */
        .logo img {
            max-height: 40px; /* Smaller logo for smaller screens */
            margin-right: 15px; /* Adjust margin */
        }
        
        /* Adjust user avatar size */
        .card-body img {
            width: 80px;
            height: 80px;
        }
        
        /* Adjust font size for card title */
        .card-title {
            font-size: 1.2rem; /* Smaller font size for smaller screens */
        }
        
        /* Adjust tab content */
        .nav-tabs .nav-link {
            font-size: 0.9rem; /* Smaller font size for tab links */
        }
        
        .tab-content .history-title {
            font-size: 1.2rem; /* Smaller font size for section titles */
        }
        
        /* Stack tables and adjust padding */
        .claim-history-table, 
        .post-history-table {
            margin-top: 10px;
            font-size: 0.9rem; /* Smaller font size for table content */
        }
        
        .claim-history-table th,
        .post-history-table th,
        .claim-history-table td,
        .post-history-table td {
            padding: 8px; /* Reduce padding for smaller screens */
        }
        
        /* Adjust button sizes */
        .btn {
            font-size: 0.9rem; /* Smaller font size for buttons */
            padding: 8px 12px; /* Adjust padding for buttons */
        }

        /* Adjust form input file size */
        input[type="file"] {
            font-size: 0.8rem; /* Smaller font size for file input */
        }
        
        /* Adjust container padding */
        .container {
            padding: 10px; /* Reduced padding for smaller screens */
        }

        /* Adjust tab-pane content margins */
        .tab-content .tab-pane {
            padding: 10px; /* Reduced padding for tab content */
        }
        
        /* Adjust back-to-top button */
        .back-to-top {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }
        .table {
    width: 100%;
    table-layout: auto; /* Ensure the table takes full width and adjusts to content */
}

.badge-pending {
    background-color: #6c757d; /* Color for Pending */
}

.badge-published {
    background-color: #007bff; /* Color for Published */
}

.badge-claimed {
    background-color: #28a745; /* Color for Claimed */
}

.badge-surrendered {
    background-color: #6c757d; /* Color for Surrendered */
}

.badge-status {
    padding: 4px 12px;
    font-size: 12px;
    border-radius: 20px;
    color: #fff;
    display: inline-block; /* Make sure it behaves as an inline element */
    line-height: 1.5;
    white-space: nowrap;
}

.notification-icon {
    margin-left: 8px;
    display: inline-flex;
    align-items: center; /* Vertically align the bell icon */
    justify-content: center;
    font-size: 16px; /* Adjust icon size if needed */
    color: #ffc107;
}
    }
    .nav-tabs {
        display: flex;
        flex-wrap: wrap; /* Allows the tabs to wrap to the next line if needed */
        justify-content: space-around; /* Centers tabs and ensures even spacing */
    }
    
    .nav-tabs .nav-item {
        display: inline-block; /* Change from block to inline-block */
        margin-bottom: 0; /* Remove bottom margin if any */
    }

    .nav-tabs .nav-link {
        padding: 10px; /* Adjust padding as needed for better spacing */
        font-size: 0.9rem; /* Adjust font size for better fit */
    }
    
    .tab-content {
        width: 100%; /* Ensure the tab content takes full width */
    }
    .back-btn-container {
            margin: 20px 0;
            display: flex;
            justify-content: center;
        }

        .back-btn {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            font-family: 'Helvetica Neue', Arial, sans-serif;
            transition: background-color 0.3s ease;
        }

        .back-btn svg {
            margin-right: 8px;
        }

        .back-btn:hover {
            background-color: #0056b3;
        }

        .back-btn:focus {
            outline: none;
            box-shadow: 0 0 4px rgba(0, 123, 255, 0.5);
        }
        .table {
    width: 100%;
    table-layout: auto; /* Ensure the table takes full width and adjusts to content */
}
    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php') ?>
    <main>
    <div class="container">
            <section class="section profile min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-6 d-flex flex-column align-items-center justify-content-center">
                            <div class="d-flex justify-content-center py-4">
                                <a href="#" class="logo d-flex align-items-center w-auto">
                                    <img src="<?= validate_image($_settings->info('logo')) ?>" alt="">
                                    <span class="d-none d-lg-block text-center"><?= $_settings->info('name') ?></span>
                                </a>
                            </div>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="pt-4 pb-2 text-center">
                                        <!-- Check if the user is non-teaching and change the dashboard title accordingly -->
                                        <h5 class="card-title text-center pb-0 fs-4">
                                            <?= $is_non_teaching ? 'Non-Teaching Dashboard' : 'Staff Dashboard' ?>
                                        </h5>
                                        <div class="d-flex justify-content-center">
                                            <div class="text-center mb-3">
                                            <?php if ($avatar): ?>
                                                <img src="../uploads/avatars/<?= htmlspecialchars($avatar) ?>" alt="User Avatar" style="width: 100px; height: 100px; border-radius: 50%;">
                                            <?php else: ?>
                                                <img src="../uploads/avatars/default-avatar.png" alt="Default Avatar" style="width: 100px; height: 100px; border-radius: 50%;">
                                            <?php endif; ?>
                                            </div>
                                        </div>
                                        <p class="text-center small">Welcome, <?= htmlspecialchars($first_name ?? '') . ' ' . htmlspecialchars($last_name ?? '') ?></p>
                                    </div>
                                    <!-- Avatar Upload Form -->
                                    <form action="" method="post" enctype="multipart/form-data">
                                        <input type="file" name="avatar" accept="image/*">
                                        <button type="submit" name="upload_avatar" class="btn btn-primary">Upload Avatar</button>
                                    </form>
                                    <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="profile-tab" data-bs-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="true">Claim History</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="found-items-tab" data-bs-toggle="tab" href="#found-items" role="tab" aria-controls="found-items" aria-selected="false">Posted Found Items</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="missing-items-tab" data-bs-toggle="tab" href="#missing-items" role="tab" aria-controls="missing-items" aria-selected="false">Posted Missing Items</a>
    </li>
</ul>
                                    <!-- Display tabs -->
                                    <ul class="list-group mb-3">
    <!-- Only show the department if the user is teaching -->
    <?php if (!$is_non_teaching): ?>
        <li class="list-group-item d-flex justify-content-between">
            <strong>Department:</strong>
            <span><?= htmlspecialchars($department ?? 'N/A') ?></span>
        </li>
    <?php endif; ?>
    
    <li class="list-group-item d-flex justify-content-between">
        <strong>Position:</strong>
        <span><?= htmlspecialchars($position ?? 'N/A') ?></span>
    </li>

    <li class="list-group-item d-flex justify-content-between">
        <strong>Email:</strong>
        <span><?= htmlspecialchars($email ?? 'N/A') ?></span>
    </li>
</ul>


                                    <div class="tab-content">
                                        <!-- Claim History Tab -->
                                        <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                        <h5 class="history-title">Claim History</h5>
<table class="table table-striped claim-history-table">
    <thead>
        <tr>
            <th>Item Name</th>
            <th>Date Claimed</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($claimer as $claim): ?>
            <tr>
                <td><a href="view_item.php?id=<?= htmlspecialchars($claim['item_id']) ?>"><?= htmlspecialchars($claim['item_name']) ?></a></td>
                <td><?= htmlspecialchars($claim['claim_date']) ?></td>
                <td class="<?= $claim['status'] === 'approved' ? 'badge-approved' : ($claim['status'] === 'rejected' ? 'badge-rejected' : 'badge-pending') ?>">
                    <?= htmlspecialchars(ucfirst($claim['status'])) ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

    </table>
</div>


                                        <!-- Posted Found Items Tab -->
                                        <div class="tab-pane fade" id="found-items" role="tabpanel" aria-labelledby="found-items-tab">
                                            <h5 class="history-title">Posted Found Items</h5>
                                            <table class="table table-striped post-history-table">
                                                <thead>
                                                    <tr>
                                                        <th>Item Name</th>
                                                        <th>Date Found</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($message_history as $message): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($message['title']) ?></td>
                                                            <td><?= htmlspecialchars($message['time_found']) ?></td>
                                                            <td>
    <?php
    $showNotification = false; // Initialize notification flag
    
    switch ($message['status']) {
        case 0:
            $statusText = 'Pending';
            $statusClass = 'badge-pending';
            break;
        case 1:
            $statusText = 'Published';
            $statusClass = 'badge-published';
            break;
        case 2:
            $statusText = 'Claimed';
            $statusClass = 'badge-claimed';
            break;
        case 3:
            $statusText = 'Surrendered';
            $statusClass = 'badge-surrendered';
            $showNotification = true; // Show notification for surrendered items
            break;
        default:
            $statusText = 'Unknown';
            $statusClass = '';
    }
    ?>
    <!-- Wrapper for status and notification icon -->
    <div style="display: flex; align-items: center;">
        <span class="badge-status <?= htmlspecialchars($statusClass) ?>">
            <?= htmlspecialchars($statusText) ?>
        </span>

        <!-- Show notification icon if the item is surrendered -->
        <?php if ($showNotification): ?>
            <i class="bi bi-bell-fill notification-icon"
               onclick="showSurrenderNotification('<?= htmlspecialchars($message['title']) ?>')"
               style="cursor: pointer; color: #ffc107; margin-left: 10px;"></i>
        <?php endif; ?>
    </div>
</td>




                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Posted Missing Items Tab -->
                                        <div class="tab-pane fade" id="missing-items" role="tabpanel" aria-labelledby="missing-items-tab">
                                            <h5 class="history-title">Posted Missing Items</h5>
                                            <table class="table table-striped post-history-table">
                                                <thead>
                                                    <tr>
                                                        <th>Item Name</th>
                                                        <th>Date Missing</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($missing_items as $missing_item): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($missing_item['title']) ?></td>
                                                            <td><?= htmlspecialchars($missing_item['time_missing']) ?></td>
                                                            <td>
    <?php
    $showNotification = false; // Initialize notification flag
    
    switch ($missing_item['status']) {
        case 0:
            $statusText = 'Pending';
            $statusClass = 'badge-pending';
            break;
        case 1:
            $statusText = 'Published';
            $statusClass = 'badge-published';
            break;
        case 2:
            $statusText = 'Claimed';
            $statusClass = 'badge-claimed';
            break;
        case 3:
            $statusText = 'Surrendered';
            $statusClass = 'badge-surrendered';
            $showNotification = true; // Show notification for surrendered items
            break;
        default:
            $statusText = 'Unknown';
            $statusClass = '';
    }
    ?>
    <span class="badge-status <?= htmlspecialchars($statusClass) ?>">
        <?= htmlspecialchars($statusText) ?>
    </span>

    <!-- Show notification icon if the item is surrendered -->
    <?php if ($showNotification): ?>
        <i class="bi bi-bell-fill notification-icon"
           onclick="showSurrenderNotification('<?= htmlspecialchars($missing_item['title']) ?>')"
           style="cursor: pointer; color: #ffc107; margin-left: 10px;"></i>
    <?php endif; ?>
</td>


                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="text-center mt-4 d-flex justify-content-center">
                                        <a href="https://ramonianlostgems.com/main.php" class="btn btn-secondary mx-2">Back</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
    <script src="<?= base_url ?>assets/js/jquery-3.6.4.min.js"></script>
    <script src="<?= base_url ?>assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url ?>assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="<?= base_url ?>assets/js/main.js"></script>
    <!-- Include SweetAlert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        function updateClaimHistory() {
        $.ajax({
            url: 'fetch_claims.php', // Create this PHP file to return the claim history
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                let tableBody = $('.claim-history-table tbody');
                tableBody.empty();
                data.forEach(claim => {
                    tableBody.append(`
                        <tr>
                            <td><a href="view_item.php?id=${claim.item_id}">${claim.item_name}</a></td>
                            <td>${claim.claim_date}</td>
                            <td class="${claim.status === 'Approved' ? 'status-approved' : ''}">${claim.status}</td>
                        </tr>
                    `);
                });
            },
            error: function(xhr, status, error) {
                console.error('Error fetching claim history:', error);
            }
        });
    }

    // Call the function to update claim history on page load
    $(document).ready(function() {
        updateClaimHistory();

        // Optionally, set an interval to update the claim history periodically
        setInterval(updateClaimHistory, 60000); // Update every 60 seconds
    });
    function showSurrenderNotification(itemTitle) {
        Swal.fire({
            icon: 'info',
            title: 'Item Surrendered',
            text: `Someone surrendered your missing item (${itemTitle}), you can go to SSG office to claim it.`,
            confirmButtonText: 'OK'
        });
    }
    </script>
    <?php require_once('../inc/footer.php'); ?>
</body>
</html>

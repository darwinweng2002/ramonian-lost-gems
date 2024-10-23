<?php
// Include the database configuration file
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Fetch the user's information from the database
$user_id = $_SESSION['user_id'];

// Check if the user is a guest by determining if their user_id starts with 'guest_'
$is_guest = (strpos($user_id, 'guest_') === 0);

// Prepare and execute query to fetch user information for regular users
if (!$is_guest) {
    $stmt = $conn->prepare("SELECT first_name, last_name, course, year, email, school_type, grade, college, avatar, user_type, teaching_status, department_or_position FROM user_member WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($first_name, $last_name, $course, $year, $email, $school_type, $grade, $college, $avatar, $user_type, $teaching_status, $department_or_position);
    $stmt->fetch();
    $stmt->close();
}

// Handle avatar upload (disabled for guests)
if (isset($_POST['upload_avatar']) && !$is_guest) {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $avatar_name = $_FILES['avatar']['name'];
        $avatar_tmp_name = $_FILES['avatar']['tmp_name'];
        $avatar_folder = '../uploads/avatars/' . $avatar_name;

        // Move uploaded file to the avatars folder
        if (move_uploaded_file($avatar_tmp_name, $avatar_folder)) {
            $stmt = $conn->prepare("UPDATE user_member SET avatar = ? WHERE id = ?");
            $stmt->bind_param("si", $avatar_name, $user_id);
            $stmt->execute();
            $stmt->close();
            
            // Refresh the page to reflect changes
            header("Location: dashboard.php");
            exit;
        } else {
            echo "Failed to upload avatar.";
        }
    } else {
        echo "No file uploaded or upload error.";
    }
}

// Fetch the user's claim history with the image of the claimed item
$claimer = [];
if (!$is_guest) {
    // Only fetch claim history for regular users, including the first image of the claimed item
    $claim_stmt = $conn->prepare("
        SELECT c.item_id, i.title AS item_name, c.claim_date, c.status, img.image_path
        FROM claimer c 
        JOIN message_history i ON c.item_id = i.id 
        LEFT JOIN message_images img ON i.id = img.message_id
        WHERE c.user_id = ?
        GROUP BY c.item_id
    ");
    $claim_stmt->bind_param("i", $user_id);
    $claim_stmt->execute();
    $claim_stmt->bind_result($item_id, $item_name, $claim_date, $status, $image_path);
    while ($claim_stmt->fetch()) {
        $claimer[] = [
            'item_id' => $item_id, 
            'item_name' => $item_name, 
            'claim_date' => $claim_date, 
            'status' => $status,
            'image_path' => $image_path
        ];
    }
    $claim_stmt->close();
}


// Fetch the user's posted missing items history (including first image)
$missing_items = [];
if (!$is_guest) {
    // Only fetch missing items for regular users
    $missing_stmt = $conn->prepare("
        SELECT mi.title, mi.time_missing, mi.status, i.image_path 
        FROM missing_items mi
        LEFT JOIN missing_item_images i ON mi.id = i.missing_item_id
        WHERE mi.user_id = ?
        GROUP BY mi.id
    ");
    $missing_stmt->bind_param("i", $user_id);
    $missing_stmt->execute();
    $missing_stmt->bind_result($title, $time_missing, $status, $image_path);
    while ($missing_stmt->fetch()) {
        $missing_items[] = [
            'title' => $title, 
            'time_missing' => $time_missing, 
            'status' => $status,
            'image_path' => $image_path
        ];
    }
    $missing_stmt->close();
}

// Fetch the user's posted found items (including first image)
$message_history = [];
if (!$is_guest) {
    // Only fetch found items for regular users
    $message_stmt = $conn->prepare("
        SELECT mh.title, mh.time_found, mh.status, i.image_path 
        FROM message_history mh
        LEFT JOIN message_images i ON mh.id = i.message_id
        WHERE mh.user_id = ?
        GROUP BY mh.id
    ");
    $message_stmt->bind_param("i", $user_id);
    $message_stmt->execute();
    $message_stmt->bind_result($title, $time_found, $status, $image_path);
    while ($message_stmt->fetch()) {
        $message_history[] = [
            'title' => $title, 
            'time_found' => $time_found,
            'status' => $status,
            'image_path' => $image_path
        ];
    }
    $message_stmt->close();
}

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
    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox.min.js"></script>
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
.badge-danger {
    background-color: #dc3545; /* Red for Denied status */
    color: #fff;
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
.modal {
        display: none;
        position: fixed;
        z-index: 1000;
        padding-top: 60px;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.8);
    }
    .modal-content {
        background-color: #fff;
        margin: auto;
        padding: 20px;
        border-radius: 10px;
        width: 80%;
    }
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }
    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
    #galleryImages img {
        width: 100%;
        max-height: 500px;
        object-fit: contain;
        margin-bottom: 10px;
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
                                    <?php if ($is_guest): ?>
                                        <!-- Disable the entire dashboard for guest users -->
                                        <div class="text-center">
                                            <h5 class="card-title">Guest Dashboard</h5>
                                            <p class="text-muted">Guest access is limited. Please register to access full features.</p>
                                            <div class="back-btn-container">
                                            <button class="back-btn" onclick="history.back()">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left">
                                                    <line x1="19" y1="12" x2="5" y2="12"/>
                                                    <polyline points="12 19 5 12 12 5"/>
                                                </svg>
                                                Back
                                            </button>
                                        </div>
                                        </div>
                                    <?php else: ?>
                                        <!-- Show the dashboard only for regular users -->
                                        <div class="pt-4 pb-2 text-center">
                                            <h5 class="card-title text-center pb-0 fs-4">User Dashboard</h5>
                                            <div class="d-flex justify-content-center">
                                            <div class="text-center mb-3">
                                            <?php if ($avatar): ?>
                                            <img src="../uploads/avatars/<?= htmlspecialchars($avatar) ?>" alt="User Avatar" style="width: 100px; height: 100px; border-radius: 50%;">
                                            <?php else: ?>
                                                <div style="width: 100px; height: 100px; border-radius: 50%; background-color: #ddd; display: flex; align-items: center; justify-content: center; font-size: 14px; color: #555;">
                                                    No Avatar
                                                </div>
                                            <?php endif; ?>

                                        </div>
                                            </div>
                                            <p class="text-center small">Welcome, <?= htmlspecialchars($first_name ?? '') . ' ' . htmlspecialchars($last_name ?? '') ?></p>
                                        </div>

                                        <!-- Display User Avatar -->
                                        

                                        <!-- Avatar Upload Form -->
                                        <form action="" method="post" enctype="multipart/form-data">
                                            <input type="file" name="avatar" accept="image/*">
                                            <button type="submit" name="upload_avatar" class="btn btn-primary">Upload Avatar</button>
                                        </form>

                                        <?php if (!$is_guest): ?>
    <!-- Display tabs only for non-guest users -->
    <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="home-tab" data-bs-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Profile Information</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="profile-tab" data-bs-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Claim History</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="contact-tab" data-bs-toggle="tab" href="#contact" role="tab" aria-controls="contact" aria-selected="false">Posted Found Items</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="history-tab" data-bs-toggle="tab" href="#history" role="tab" aria-controls="history" aria-selected="false">Posted Missing Items</a>
        </li>
    </ul>

    <!-- Show tab content only for non-guest users -->
    <!-- Show tab content only for non-guest users -->
<div class="tab-content">
    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
    <ul class="list-group mb-3">
    <!-- Level Display: College, High School, Employee, or Guest -->
    <li class="list-group-item d-flex justify-content-between">
        <strong>User Type:</strong>
        <span>
            <?php 
            // Check the school_type column to determine the User Type
            if ($school_type == 1) {
                echo 'College';
            } elseif ($school_type == 0) {
                echo 'High School';
            } elseif ($school_type == 2) {
                echo 'Employee';
            } elseif ($school_type == 3) {
                echo 'Guest';  // Added case for Guest users
            } else {
                echo 'Unknown'; // Fallback for unknown types
            }
            ?>
        </span>
    </li>

    <?php if ($school_type == 0): // High School ?>
        <!-- Show Grade for High School -->
        <li class="list-group-item d-flex justify-content-between">
            <strong>Grade:</strong>
            <span><?= htmlspecialchars($grade ?? '') ?></span>
        </li>
    <?php endif; ?>

    <?php if ($school_type == 1): // College ?>
        <!-- Show College-specific fields for College students -->
        <li class="list-group-item d-flex justify-content-between">
            <strong>Course:</strong>
            <span><?= htmlspecialchars($course ?? '') ?></span>
        </li>
        <li class="list-group-item d-flex justify-content-between">
            <strong>Year:</strong>
            <span><?= htmlspecialchars($year ?? '') ?></span>
        </li>
        <li class="list-group-item d-flex justify-content-between">
            <strong>Department:</strong>
            <span><?= htmlspecialchars($college ?? '') ?></span>
        </li>
    <?php endif; ?>

    <?php if ($school_type == 2): // Employee ?>
        <!-- Show Employee-specific fields for Employee users -->
        <li class="list-group-item d-flex justify-content-between">
            <strong>Employee Type:</strong>
            <span><?= htmlspecialchars($teaching_status ?? 'N/A') ?></span>
        </li>
        <li class="list-group-item d-flex justify-content-between">
            <strong>Department/Position:</strong>
            <span><?= htmlspecialchars($department_or_position ?? 'N/A') ?></span>
        </li>
    <?php endif; ?>

    <li class="list-group-item d-flex justify-content-between">
        <strong>Email:</strong>
        <span><?= htmlspecialchars($email ?? '') ?></span>
    </li>
</ul>
    </div>
</div>
<div class="tab-content">
<div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
    <h5 class="history-title">Claim History</h5>
    <table class="table table-striped claim-history-table">
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Item Image</th>
                <th>Date Claimed</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($claimer as $claim): ?>
        <tr>
            <td><a href="https://ramonianlostgems.com/itemss/published_items.php?id=<?= htmlspecialchars($claim['item_id']) ?>">
                <?= htmlspecialchars($claim['item_name']) ?></a></td>
                <td>
                <a href="../uploads/items/<?= htmlspecialchars($claim['image_path']) ?>" data-lightbox="claimed-items" data-title="<?= htmlspecialchars($claim['item_name']) ?>">
                    <img src="../uploads/items/<?= htmlspecialchars($claim['image_path']) ?>" alt="Item Image" style="width: 50px; height: auto; border-radius: 4px; margin-right: 10px;">
                </a>
            </td>

            <td><?= htmlspecialchars($claim['claim_date']) ?></td>
            <td class="<?= $claim['status'] === 'approved' ? 'status-approved' : ($claim['status'] === 'rejected' ? 'status-declined' : ($claim['status'] === 'claimed' ? 'status-claimed' : 'status-pending')) ?>">
                <?= htmlspecialchars(ucfirst($claim['status'])) ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>




    <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
    <h5 class="history-title">Posted Found Items</h5>
    <table class="table table-striped post-history-table" style="width: 100%; table-layout: auto;">
        <thead>
            <tr>
                <th style="width: 40%;">Item Name</th>
                <th style="width: 40%;">Item Image</th>
                <th style="width: 30%;">Date Posted</th>
                <th style="width: 30%;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($message_history as $message): ?>
                <tr>
                    <td><?= htmlspecialchars($message['title']) ?></td>
                    <td>
                    <a href="../uploads/items/<?= htmlspecialchars($message['image_path']) ?>" data-lightbox="found-items" data-title="<?= htmlspecialchars($message['title']) ?>">
                        <img src="../uploads/items/<?= htmlspecialchars($message['image_path']) ?>" alt="Item Image" style="width: 50px; height: auto; border-radius: 4px; margin-right: 10px;">
                    </a>
                </td>


                    <td><?= htmlspecialchars($message['time_found']) ?></td>
                    <td>
                    <?php
                    $statusClass = '';
                    $statusText = '';
                    $showNotification = false;

                    // Define status classes and text, including the new "Denied" status
                    if ($message['status'] == 0) {
                        $statusClass = 'badge-pending';
                        $statusText = 'Pending';
                    } elseif ($message['status'] == 1) {
                        $statusClass = 'badge-published';
                        $statusText = 'Published';
                    } elseif ($message['status'] == 2) {
                        $statusClass = 'badge-claimed';
                        $statusText = 'Claimed';
                    } elseif ($message['status'] == 3) {
                        $statusClass = 'badge-surrendered';
                        $statusText = 'Surrendered';
                        $showNotification = true; // Show notification for surrendered items
                    } elseif ($message['status'] == 4) { // New Denied status
                        $statusClass = 'badge-danger';
                        $statusText = 'Denied';
                    }
                    ?>
                    <span class="badge-status <?= $statusClass ?>" style="display: inline-flex; align-items: center;">
                        <?= htmlspecialchars($statusText) ?>
                        <?php if ($showNotification): ?>
                            <i class="bi bi-bell-fill notification-icon"
                            onclick="showSurrenderNotification('<?= htmlspecialchars($message['title']) ?>')"
                            style="cursor: pointer; color: #ffc107; margin-left: 5px; display: inline-flex; align-items: center;"></i>
                        <?php endif; ?>
                    </span>
                </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
    <h5 class="history-title">Posted Missing Items</h5>
    <table class="table table-striped post-history-table">
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Item Image</th>
                <th>Date Missing</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($missing_items as $missing_item): ?>
                <tr>
                    <td><?= htmlspecialchars($missing_item['title']) ?></td>
                    <td>
                    <a href="../uploads/missing_items/<?= htmlspecialchars($missing_item['image_path']) ?>" data-lightbox="missing-items" data-title="<?= htmlspecialchars($missing_item['title']) ?>">
                        <img src="../uploads/missing_items/<?= htmlspecialchars($missing_item['image_path']) ?>" alt="Item Image" style="width: 50px; height: auto; cursor: pointer;">
                    </a>
                </td>



                    <td><?= htmlspecialchars($missing_item['time_missing']) ?></td>
                    <td>
    <?php
    // Define status classes and text, including the new "Denied" status
    $statusClass = '';
    $statusText = '';
    $showNotification = false;

    if ($missing_item['status'] == 0) {
        $statusClass = 'badge-pending';
        $statusText = 'Pending';
    } elseif ($missing_item['status'] == 1) {
        $statusClass = 'badge-published';
        $statusText = 'Published';
    } elseif ($missing_item['status'] == 2) {
        $statusClass = 'badge-claimed';
        $statusText = 'Claimed';
    } elseif ($missing_item['status'] == 3) {
        $statusClass = 'badge-surrendered';
        $statusText = 'Surrendered';
        $showNotification = true; // Show notification for surrendered items
    } elseif ($missing_item['status'] == 4) { // New Denied status
        $statusClass = 'badge-danger';
        $statusText = 'Denied';
    }
    ?>
    <span class="badge-status <?= $statusClass ?>">
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
<?php endif; ?>

                                            <div class="text-center mt-4 d-flex justify-content-center">
                                        <a href="https://ramonianlostgems.com/main.php" class="btn btn-secondary mx-2">Back</a>
                                    </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="imageGalleryModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <div id="galleryImages"></div>
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
        document.getElementById('logout-btn').addEventListener('click', function (event) {
            event.preventDefault(); // Prevent default link behavior
            Swal.fire({
                title: 'Are you sure you want to logout?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to logout URL
                    window.location.href = 'logout.php';
                }
            });
        });
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
    function openGallery(itemId, type) {
    // Fetch all images for the selected item
    $.ajax({
        url: `fetch_images.php?item_id=${itemId}&type=${type}`, // Create this PHP to return images
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            const gallery = document.getElementById('galleryImages');
            gallery.innerHTML = ''; // Clear previous images
            data.forEach(image => {
                const img = document.createElement('img');
                img.src = `../uploads/${type}/${image.image_path}`;
                gallery.appendChild(img);
            });
            document.getElementById('imageGalleryModal').style.display = 'block';
        },
        error: function(xhr, status, error) {
            console.error('Error fetching images:', error);
        }
    });
}

function closeModal() {
    document.getElementById('imageGalleryModal').style.display = 'none';
}
lightbox.option({
      'resizeDuration': 200,       // Duration for resizing the image
      'wrapAround': true,          // Allows infinite navigation in gallery
      'alwaysShowNavOnTouchDevices': true, // Keeps navigation visible on mobile
      'fadeDuration': 300,         // Time to fade images in and out
      'imageFadeDuration': 300     // Time to fade the actual image
    })
    </script>
    <?php require_once('../inc/footer.php'); ?>
</body>
</html>

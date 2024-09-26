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
$stmt = $conn->prepare("SELECT first_name, last_name, department, email, avatar FROM user_staff WHERE id = ?");
if ($stmt === false) {
    die('MySQL prepare failed: ' . $conn->error);
}
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$stmt->bind_result($first_name, $last_name, $department, $email, $avatar);
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
$claims = [];
$claim_stmt = $conn->prepare("
    SELECT c.item_id, i.title AS item_name, c.claim_date, c.status 
    FROM claims c 
    JOIN item_list i ON c.item_id = i.id 
    WHERE c.user_id = ?
");
if ($claim_stmt === false) {
    die('MySQL prepare failed: ' . $conn->error);
}
$claim_stmt->bind_param("i", $staff_id);
$claim_stmt->execute();
$claim_stmt->bind_result($item_id, $item_name, $claim_date, $status);
while ($claim_stmt->fetch()) {
    $claims[] = [
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
        /* Add your custom styles here */
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
                                        <h5 class="card-title text-center pb-0 fs-4">Staff Dashboard</h5>
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

                                    <!-- Display Staff User Information -->
                                    <ul class="list-group mb-3">
                                        <li class="list-group-item d-flex justify-content-between">
                                            <strong>Department:</strong>
                                            <span><?= htmlspecialchars($department ?? '') ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <strong>Email:</strong>
                                            <span><?= htmlspecialchars($email ?? '') ?></span>
                                        </li>
                                    </ul>

                                    <!-- Display tabs -->
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
                                                    <?php foreach ($claims as $claim): ?>
                                                        <tr>
                                                            <td><a href="<?= base_url ?>?page=items/view&id=<?= htmlspecialchars($claim['item_id']) ?>"><?= htmlspecialchars($claim['item_name']) ?></a></td>
                                                            <td><?= htmlspecialchars($claim['claim_date']) ?></td>
                                                            <td class="<?= $claim['status'] == 'Approved' ? 'status-approved' : '' ?>">
                                                                <?= htmlspecialchars($claim['status']) ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
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
    switch ($message['status']) {
        case 0:
            $statusText = 'Pending';
            break;
        case 1:
            $statusText = 'Published';
            break;
        case 2:
            $statusText = 'Claimed';
            break;
        case 3:
            $statusText = 'Surrendered';
            break;
        default:
            $statusText = 'Unknown'; // In case of an unexpected value
    }
    ?>
    <span class="badge-status"><?= htmlspecialchars($statusText) ?></span>
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
    switch ($missing_item['status']) {
        case 0:
            $statusText = 'Pending';
            break;
        case 1:
            $statusText = 'Published';
            break;
        case 2:
            $statusText = 'Claimed';
            break;
        case 3:
            $statusText = 'Surrendered';
            break;
        default:
            $statusText = 'Unknown';
    }
    ?>
    <span class="badge-status"><?= htmlspecialchars($statusText) ?></span>
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
    <?php require_once('../inc/footer.php'); ?>
</body>
</html>

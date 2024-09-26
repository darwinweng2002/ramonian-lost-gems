<?php
// Include the database configuration file
include '../config.php';

// Check if the user is logged in as either regular user or staff
if (!isset($_SESSION['user_id']) && !isset($_SESSION['staff_id'])) {
    header("Location: ../login.php");
    exit;
}

// Get the user ID and user type
if (isset($_SESSION['user_id'])) {
    // Regular user
    $user_id = $_SESSION['user_id'];
    $user_type = 'user_member'; // Table for regular users
} elseif (isset($_SESSION['staff_id'])) {
    // Staff user
    $user_id = $_SESSION['staff_id'];
    $user_type = 'user_staff'; // Table for staff users
}

// Fetch the user's information from the database based on user type
if ($user_type === 'user_member') {
    $stmt = $conn->prepare("SELECT first_name, last_name, course, year, section, email, college, avatar, user_type FROM user_member WHERE id = ?");
} else {
    $stmt = $conn->prepare("SELECT first_name, last_name, department AS college, email, avatar, 'staff' AS user_type FROM user_staff WHERE id = ?");
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($first_name, $last_name, $course_or_dept, $year, $section, $email, $college, $avatar, $user_type);
$stmt->fetch();
$stmt->close();

// Handle avatar upload (disabled for guests)
if (isset($_POST['upload_avatar'])) {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $avatar_name = $_FILES['avatar']['name'];
        $avatar_tmp_name = $_FILES['avatar']['tmp_name'];
        $avatar_folder = '../uploads/avatars/' . $avatar_name;

        // Move uploaded file to the avatars folder
        if (move_uploaded_file($avatar_tmp_name, $avatar_folder)) {
            if ($user_type === 'user_member') {
                $stmt = $conn->prepare("UPDATE user_member SET avatar = ? WHERE id = ?");
            } else {
                $stmt = $conn->prepare("UPDATE user_staff SET avatar = ? WHERE id = ?");
            }
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

// Fetch the user's claim history
$claims = [];
if ($user_type === 'user_member') {
    $claim_stmt = $conn->prepare("
        SELECT c.item_id, i.title AS item_name, c.claim_date, c.status 
        FROM claims c 
        JOIN item_list i ON c.item_id = i.id 
        WHERE c.user_id = ?
    ");
    $claim_stmt->bind_param("i", $user_id);
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
}

// Fetch the user's posted missing items history
$missing_items = [];
if ($user_type === 'user_member') {
    $missing_stmt = $conn->prepare("SELECT title, time_missing, status FROM missing_items WHERE user_id = ?");
    $missing_stmt->bind_param("i", $user_id);
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
}

// Fetch the user's posted found items
$message_history = [];
if ($user_type === 'user_member') {
    $message_stmt = $conn->prepare("SELECT title, time_found, status FROM message_history WHERE user_id = ?");
    $message_stmt->bind_param("i", $user_id);
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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once('../inc/header.php'); ?>
    <style>
        /* Add your styling */
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
                                        <h5 class="card-title text-center pb-0 fs-4">User Dashboard</h5>
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

                                    <!-- Display User Information -->
                                    <ul class="list-group mb-3">
                                        <?php if ($user_type === 'user_member'): ?>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <strong>Course:</strong>
                                                <span><?= htmlspecialchars($course_or_dept ?? '') ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <strong>Year:</strong>
                                                <span><?= htmlspecialchars($year ?? '') ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <strong>Section:</strong>
                                                <span><?= htmlspecialchars($section ?? '') ?></span>
                                            </li>
                                        <?php else: ?>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <strong>Department:</strong>
                                                <span><?= htmlspecialchars($course_or_dept ?? '') ?></span>
                                            </li>
                                        <?php endif; ?>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <strong>Email:</strong>
                                            <span><?= htmlspecialchars($email ?? '') ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <strong>College/Department:</strong>
                                            <span><?= htmlspecialchars($college ?? '') ?></span>
                                        </li>
                                    </ul>

                                    <!-- Tabbed content remains same -->
                                    <!-- Claim History, Found Items, and Missing Items can be loaded similarly as before for both user types -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>

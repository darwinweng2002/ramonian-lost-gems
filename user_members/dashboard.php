<?php
// Include the database configuration file
include '../config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Fetch the user's information from the database
$user_id = $_SESSION['user_id'];

// Prepare and execute query to fetch user information
$stmt = $conn->prepare("SELECT first_name, last_name, course, year, section, email, college, avatar FROM user_member WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($first_name, $last_name, $course, $year, $section, $email, $college, $avatar);
$stmt->fetch();
$stmt->close();

// Handle avatar upload
if(isset($_POST['upload_avatar'])) {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $avatar_name = $_FILES['avatar']['name'];
        $avatar_tmp_name = $_FILES['avatar']['tmp_name'];
        $avatar_folder = '../uploads/avatars/'.$avatar_name;

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

// Fetch the user's claim history with additional details
$claim_stmt = $conn->prepare("
    SELECT c.item_id, i.title AS item_name, c.claim_date, c.status 
    FROM claims c 
    JOIN item_list i ON c.item_id = i.id 
    WHERE c.user_id = ?
");
$claim_stmt->bind_param("i", $user_id);
$claim_stmt->execute();
$claim_stmt->bind_result($item_id, $item_name, $claim_date, $status);
$claims = [];
while ($claim_stmt->fetch()) {
    $claims[] = [
        'item_id' => $item_id, 
        'item_name' => $item_name, 
        'claim_date' => $claim_date, 
        'status' => $status
    ];
}
$claim_stmt->close();

// Fetch the user's posted items history
$post_stmt = $conn->prepare("SELECT title, time_found, status FROM message_history WHERE user_id = ?");
$post_stmt->bind_param("i", $user_id);
$post_stmt->execute();
$post_stmt->bind_result($title, $time_found, $status);
$posts = [];
while ($post_stmt->fetch()) {
    $posts[] = [
        'title' => $title, 
        'time_found' => $time_found,
        'status' => $status
    ];
}
$post_stmt->close();
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
     /* Your existing CSS */
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


/* Optional: If you have any styles that could impact the body overflow */
.swal2-overlay {
    overflow: auto;             /* Allow scrolling of the page if necessary */
}
    </style>
</head>
<body>
<?php require_once('../inc/side_bar.php') ?>
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
                                            <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-user">
                                                <circle cx="12" cy="12" r="10"/>
                                                <circle cx="12" cy="10" r="3"/>
                                                <path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662"/>
                                            </svg>
                                        </div>
                                        <p class="text-center small">Welcome, <?= htmlspecialchars($first_name . ' ' . $last_name) ?></p>
                                    </div>

                                    <!-- Display User Avatar -->
                                    <div class="text-center mb-3">
                                        <?php if($avatar): ?>
                                            <img src="../uploads/avatars/<?= htmlspecialchars($avatar) ?>" alt="User Avatar" style="width: 100px; height: 100px; border-radius: 50%;">
                                        <?php else: ?>
                                            <img src="../uploads/avatars/default-avatar.png" alt="Default Avatar" style="width: 100px; height: 100px; border-radius: 50%;">
                                        <?php endif; ?>
                                    </div>

                                    <!-- Avatar Upload Form -->
                                    <form action="dashboard.php" method="post" enctype="multipart/form-data">
                                        <label class="form-label">Upload Avatar:</label>
                                        <input type="file" name="avatar" id="avatar" required>
                                        <button type="submit" name="upload_avatar" class="btn btn-primary mt-2">Upload</button>
                                    </form>

                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <label class="form-label">Full Name</label>
                                            <p class="form-control"><?= htmlspecialchars($first_name . ' ' . $last_name) ?></p>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">College</label>
                                            <p class="form-control"><?= htmlspecialchars($college) ?></p>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Email</label>
                                            <p class="form-control"><?= htmlspecialchars($email) ?></p>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Course</label>
                                            <p class="form-control"><?= htmlspecialchars($course) ?></p>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Year</label>
                                            <p class="form-control"><?= htmlspecialchars($year) ?></p>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Section</label>
                                            <p class="form-control"><?= htmlspecialchars($section) ?></p>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Email</label>
                                            <p class="form-control"><?= htmlspecialchars($email) ?></p>
                                        </div>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                   

                                    <div class="history-title">Your Claim History</div>
                                    <table class="table claim-history-table">
                                        <thead>
                                            <tr>
                                                <th>Item Name</th>
                                                <th>Claim Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($claims)): ?>
                                                <tr>
                                                    <td colspan="3" class="text-center">No claim history available.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($claims as $claim): ?>
                                                    <tr>
                                                    <td><a href="<?= base_url ?>?page=items/view&id=<?= htmlspecialchars($claim['item_id']) ?>"><?= htmlspecialchars($claim['item_name']) ?></a></td>


                                                    <td><?= htmlspecialchars($claim['claim_date']) ?></td>
                                                    <td class="<?= $claim['status'] === 'approved' ? 'status-approved' : '' ?>">
                                                        <?= htmlspecialchars($claim['status']) ?>
                                                    </td>
                                                </tr>

                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>

                                    <div class="history-title">Your Posted Items</div>
                                    <table class="table post-history-table">
                                        <thead>
                                            <tr>
                                                <th>Item Name</th>
                                                <th>Post Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($posts)): ?>
                                                <tr>
                                                    <td colspan="2" class="text-center">No posted items available.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($posts as $post): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($post['title']) ?></td>
                                                        <td><?= htmlspecialchars($post['time_found']) ?></td>
                                                        <td><?= htmlspecialchars($post['status']) ?></td>

                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                    <div class="text-center mt-4 d-flex justify-content-center">
                                        <button id="logout-btn" class="btn btn-primary mx-2">Logout</button>
                                        <a href="http://localhost/lostgemramonian/" class="btn btn-secondary mx-2">Back</a>
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
                    window.location.href = 'http://localhost/lostgemramonian/logout.php';
                }
            });
        });
    </script>
    <?php require_once('../inc/footer.php'); ?>
</body>
</html>

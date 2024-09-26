<?php
include '../../config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in as either a regular user or staff
if (!isset($_SESSION['user_id']) && !isset($_SESSION['staff_id'])) {
    die("User not logged in");
}

// Get the user ID and user type
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_type = 'user_member';
} elseif (isset($_SESSION['staff_id'])) {
    $user_id = $_SESSION['staff_id'];
    $user_type = 'user_staff';
}

// Fetch the user's claim history with additional details
$claim_stmt = $conn->prepare("
    SELECT c.item_id, i.title AS item_name, c.claim_date, c.status, c.username, c.course, c.year, c.section 
    FROM claims c 
    JOIN item_list i ON c.item_id = i.id 
    WHERE c.user_id = ?
");
$claim_stmt->bind_param("i", $user_id);
$claim_stmt->execute();
$claim_stmt->bind_result($item_id, $item_name, $claim_date, $status, $username, $course, $year, $section);
$claims = [];
while ($claim_stmt->fetch()) {
    $claims[] = [
        'item_id' => $item_id, 
        'item_name' => $item_name, 
        'claim_date' => $claim_date, 
        'status' => $status,
        'username' => $username,
        'course' => $course,
        'year' => $year,
        'section' => $section
    ];
}
$claim_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once('../inc/header.php'); ?>
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
    <main>
        <div class="container">
            <section class="section profile min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-8 d-flex flex-column align-items-center justify-content-center">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="history-title">Your Claim History</div>
                                    <table class="table claim-history-table">
                                        <thead>
                                            <tr>
                                                <th>Item Name</th>
                                                <th>Claim Date</th>
                                                <th>Status</th>
                                                <th>Username</th>
                                                <th>Course</th>
                                                <th>Year</th>
                                                <th>Section</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($claims)): ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No claim history available.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($claims as $claim): ?>
                                                    <tr>
                                                        <td><a href="item_details.php?id=<?= htmlspecialchars($claim['item_id']) ?>"><?= htmlspecialchars($claim['item_name']) ?></a></td>
                                                        <td><?= htmlspecialchars($claim['claim_date']) ?></td>
                                                        <td><?= htmlspecialchars($claim['status']) ?></td>
                                                        <td><?= htmlspecialchars($claim['username']) ?></td>
                                                        <td><?= htmlspecialchars($claim['course']) ?></td>
                                                        <td><?= htmlspecialchars($claim['year']) ?></td>
                                                        <td><?= htmlspecialchars($claim['section']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                    <!-- Your existing footer and other elements -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
    <!-- Your existing scripts -->
</body>
</html>

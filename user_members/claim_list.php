<?php
include '../../config.php';

// Fetch the user's claim history with additional details
$user_id = $_SESSION['user_id'];
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
        /* Your existing styles */
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

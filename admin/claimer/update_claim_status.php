<?php
include '../../config.php';
// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $claimId = intval($_POST['claim_id']);
    $action = $_POST['action'];

    // Update claim status
    if ($action === 'approve') {
        $status = 'approved';
    } elseif ($action === 'reject') {
        $status = 'rejected';
    }

    $sql = "UPDATE claims SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $status, $claimId);

    if ($stmt->execute()) {
        header('Location: admin_view_claims.php?status=success');
    } else {
        header('Location: admin_view_claims.php?status=error');
    }

    $stmt->close();
}

$conn->close();
?>

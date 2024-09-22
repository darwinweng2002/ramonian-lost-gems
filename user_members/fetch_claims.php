<?php
include '../config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch the user's claim history
$claimer = [];
$stmt = $conn->prepare("
    SELECT c.item_id, mh.title AS item_name, c.claim_date, c.status 
    FROM claimer c 
    JOIN message_history mh ON c.item_id = mh.id 
    WHERE c.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($item_id, $item_name, $claim_date, $status);
while ($stmt->fetch()) {
    $claimer[] = [
        'item_id' => $item_id, 
        'item_name' => $item_name, 
        'claim_date' => $claim_date, 
        'status' => $status
    ];s
}
$stmt->close();

header('Content-Type: application/json');
echo json_encode($claimer);
?>

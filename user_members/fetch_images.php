<?php
include '../config.php';

$item_id = $_GET['item_id'];
$type = $_GET['type']; // Either "missing_items" or "found_items"

if ($type == 'missing_items') {
    $stmt = $conn->prepare("SELECT image_path FROM missing_item_images WHERE missing_item_id = ?");
} else {
    $stmt = $conn->prepare("SELECT image_path FROM found_item_images WHERE found_item_id = ?");
}

$stmt->bind_param("i", $item_id);
$stmt->execute();
$stmt->bind_result($image_path);

$images = [];
while ($stmt->fetch()) {
    $images[] = ['image_path' => $image_path];
}
$stmt->close();

header('Content-Type: application/json');
echo json_encode($images);
?>

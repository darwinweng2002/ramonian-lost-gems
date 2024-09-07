<?php
include '../../config.php'; // Ensure this path is correct

// Fetch messages and images
$sql = "SELECT mh.id, mh.message, mi.image_path FROM message_history mh
        LEFT JOIN message_images mi ON mh.id = mi.message_id
        ORDER BY mh.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Messages</title>
</head>
<body>
    <h1>Messages and Images</h1>
    <?php
    if ($result->num_rows > 0) {
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            if (!isset($messages[$row['id']])) {
                $messages[$row['id']] = ['message' => $row['message'], 'images' => []];
            }
            if ($row['image_path']) {
                // Construct the correct URL to the image
                $fullImagePath = base_url . 'uploads/items/' . $row['image_path'];
                $messages[$row['id']]['images'][] = $fullImagePath;
            }
        }
        
        foreach ($messages as $msgId => $msgData) {
            echo "<div>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($msgData['message']) . "</p>";
            if (!empty($msgData['images'])) {
                echo "<p><strong>Images:</strong></p>";
                foreach ($msgData['images'] as $imagePath) {
                    // Display the image
                    echo "<img src='" . htmlspecialchars($imagePath) . "' alt='Image' style='max-width: 200px;'><br>";
                }
            }
            echo "</div><hr>";
        }
    } else {
        echo "<p>No messages found.</p>";
    }
    ?>
</body>
</html>

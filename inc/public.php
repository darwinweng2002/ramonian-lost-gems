<?php
include '../../config.php';

// Database connection
$conn = new mysqli('localhost', 'root', '1234', 'u450897284_Ifis_db'); // Replace with your actual DB connection details

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch published messages
$sql = "SELECT mh.id, mh.message, mi.image_path, mh.landmark, um.first_name, um.college, um.email 
        FROM message_history mh
        LEFT JOIN message_images mi ON mh.id = mi.message_id
        LEFT JOIN user_member um ON mh.user_id = um.id
        WHERE mh.published = 1
        ORDER BY mh.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Published Messages</title>
    <?php require_once('../inc/header.php'); ?>
    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet">
    <style>
        /* Add necessary CSS styles here */
    </style>
</head>
<body>
    <?php require_once('../inc/topBarNav.php'); ?>
    <?php require_once('../inc/navigation.php'); ?>

    <div class="container">
        <h1>Published Items</h1>
        <?php
        if ($result->num_rows > 0) {
            $messages = [];
            while ($row = $result->fetch_assoc()) {
                if (!isset($messages[$row['id']])) {
                    $messages[$row['id']] = [
                        'message' => $row['message'], 
                        'images' => [],
                        'first_name' => $row['first_name'],
                        'landmark' => $row['landmark'],
                        'college' => $row['college'],
                        'email' => $row['email']
                    ];
                }
                if ($row['image_path']) {
                    // Construct the correct URL to the image
                    $fullImagePath = base_url . 'uploads/items/' . $row['image_path'];
                    $messages[$row['id']]['images'][] = $fullImagePath;
                }
            }
            
            foreach ($messages as $msgId => $msgData) {
                echo "<div class='message-box'>";
                // Ensure values are strings or use default empty strings
                $firstName = htmlspecialchars($msgData['first_name'] ?? '');
                $email = htmlspecialchars($msgData['email'] ?? '');
                $college = htmlspecialchars($msgData['college'] ?? '');
                $landmark = htmlspecialchars($msgData['landmark'] ?? '');
                $message = htmlspecialchars($msgData['message'] ?? '');
            
                echo "<p><strong>User:</strong> " . $firstName . " (" . $email . ")</p>";
                echo "<p><strong>College:</strong> " . $college . "</p>";
                echo "<p><strong>Landmark:</strong> " . $landmark . "</p>";
                echo "<p><strong>Description:</strong> " . $message . "</p>";
                
                if (!empty($msgData['images'])) {
                    echo "<p><strong>Images:</strong></p>";
                    echo "<div class='image-grid'>";
                    foreach ($msgData['images'] as $imagePath) {
                        echo "<a href='" . htmlspecialchars($imagePath) . "' data-lightbox='message-" . htmlspecialchars($msgId) . "' data-title='Image'><img src='" . htmlspecialchars($imagePath) . "' alt='Image'></a>";
                    }
                    echo "</div>";
                }
                echo "</div>";
            }
            
        } else {
            echo "<p>No published messages found.</p>";
        }
        ?>
    </div>

    <script src="../js/jquery.min.js"></script> <!-- Ensure this path is correct -->
    <script src="../js/bootstrap.min.js"></script> <!-- Ensure this path is correct -->
    <script src="../js/custom.js"></script> <!-- Ensure this path is correct -->
    <script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox-plus-jquery.min.js"></script>
</body>
<?php require_once('../inc/footer.php') ?>
</html>

<?php
$conn->close();
?>

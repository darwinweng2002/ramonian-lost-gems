<?php
include '../../config.php';

// Database connection
$conn = new mysqli('localhost', 'root', '1234', 'lfis_db'); // Replace with your actual DB connection details

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Correct SQL query
$sql = "SELECT mh.id, mh.message, mi.image_path, um.first_name, um.email 
        FROM message_history mh
        LEFT JOIN message_images mi ON mh.id = mi.message_id
        LEFT JOIN user_member um ON mh.user_id = um.id
        ORDER BY mh.id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Admin View</title>
    <?php require_once('../inc/header.php'); ?>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            padding-top: 70px; /* Adjust this according to the height of your navbar */
            background-color: #f4f4f4;
        }
        .container {
            margin: 30px auto;
            width: 90%;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .message-box {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .message-box img {
            max-width: 200px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php require_once('../inc/topBarNav.php'); ?>
    <?php require_once('../inc/navigation.php'); ?>

    <div class="container">
        <h1>Messages and Images</h1>
        <?php
        if ($result->num_rows > 0) {
            $messages = [];
            while ($row = $result->fetch_assoc()) {
                if (!isset($messages[$row['id']])) {
                    $messages[$row['id']] = [
                        'message' => $row['message'], 
                        'images' => [],
                        'first_name' => $row['first_name'],
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
                echo "<p><strong>User:</strong> " . htmlspecialchars($msgData['first_name']) . " (" . htmlspecialchars($msgData['email']) . ")</p>";
                echo "<p><strong>Message:</strong> " . htmlspecialchars($msgData['message']) . "</p>";
                if (!empty($msgData['images'])) {
                    echo "<p><strong>Images:</strong></p>";
                    foreach ($msgData['images'] as $imagePath) {
                        // Display the image
                        echo "<img src='" . htmlspecialchars($imagePath) . "' alt='Image'><br>";
                    }
                }
                echo "</div>";
            }
        } else {
            echo "<p>No messages found.</p>";
        }
        ?>
    </div>

    <!-- Include JavaScript files -->
    <script src="../js/jquery.min.js"></script> <!-- Ensure this path is correct -->
    <script src="../js/bootstrap.min.js"></script> <!-- Ensure this path is correct -->
    <script src="../js/custom.js"></script> <!-- Ensure this path is correct -->

    <script>
        $(document).ready(function() {
            // Example code for handling navigation button triggers
            $('.nav-button').on('click', function() {
                // Your logic for handling navigation button click
                alert('Navigation button clicked!');
            });

            // Example code for handling header settings
            $('#settings-button').on('click', function() {
                // Your logic for handling header settings
                alert('Settings button clicked!');
            });
        });
    </script>
</body>
<?php require_once('../inc/footer.php') ?>
</html>

<?php
$conn->close();
?>

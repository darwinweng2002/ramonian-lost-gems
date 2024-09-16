<?php
include '../config.php';

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to get published items details along with status
$sql = "SELECT mh.id, mh.message, mh.status, mi.image_path, mh.title, mh.landmark, mh.time_found, um.first_name, um.college, um.email, um.avatar 
        FROM message_history mh
        LEFT JOIN message_images mi ON mh.id = mi.message_id
        LEFT JOIN user_member um ON mh.user_id = um.id
        ORDER BY mh.id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once('../inc/header.php') ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Published Items</title>
    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet">
    <style>
        /* Existing styles... */
        .status-box {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .status-box p {
            margin: 0;
        }
    </style>
</head>
<body>
    <?php require_once('../inc/topBarNav.php') ?>
    <div class="container">
        <h1>Published Items</h1>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $status = htmlspecialchars($row['status']);
                $message = htmlspecialchars($row['message']);
                $title = htmlspecialchars($row['title']);
                $landmark = htmlspecialchars($row['landmark']);
                $timeFound = htmlspecialchars($row['time_found']);
                $firstName = htmlspecialchars($row['first_name']);
                $email = htmlspecialchars($row['email']);
                $college = htmlspecialchars($row['college']);
                $avatar = htmlspecialchars($row['avatar']);
                
                echo "<div class='message-box'>";
                
                if ($avatar) {
                    $fullAvatar = base_url . 'uploads/avatars/' . $avatar;
                    echo "<img src='" . htmlspecialchars($fullAvatar) . "' alt='Avatar' class='avatar'>";
                } else {
                    echo "<img src='uploads/avatars/default-avatar.png' alt='Default Avatar' class='avatar'>";
                }

                echo "<p><strong>Founder Name:</strong> " . $firstName . " (" . $email . ")</p>";
                echo "<p><strong>College:</strong> " . $college . "</p>";
                echo "<p><strong>Landmark:</strong> " . $landmark . "</p>";
                echo "<p><strong>Date and Time Found:</strong> " . $timeFound . "</p>";
                echo "<p><strong>Title:</strong> " . $title . "</p>";
                echo "<p><strong>Description:</strong> " . $message . "</p>";
                
                // Display Status
                echo "<div class='status-box'>";
                echo "<p><strong>Status:</strong> " . $status . "</p>";
                echo "</div>";
                
                echo "</div>";
            }
        } else {
            echo "<p>No items found.</p>";
        }
        ?>
    </div>
    <?php require_once('../inc/footer.php') ?>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/custom.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox-plus-jquery.min.js"></script>
</body>
</html>

<?php
$result->free();
$conn->close();
?>

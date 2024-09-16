<?php
include '../config.php';
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: login.php'); // Adjust this path if necessary
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db'); // Replace with your actual DB connection details

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get item ID from URL
$itemId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// SQL query to get published item details
$sql = "SELECT mh.id, mh.message, mh.status, mi.image_path, mh.title, mh.landmark, mh.time_found, um.first_name, um.college, um.email, um.avatar 
        FROM message_history mh
        LEFT JOIN message_images mi ON mh.id = mi.message_id
        LEFT JOIN user_member um ON mh.user_id = um.id
        WHERE mh.id = ?
        ORDER BY mh.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $itemId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php require_once('../inc/header.php') ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Published Item Details</title>
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
        .status-button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #e67e22; /* Orange color */
            border: none;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }
        .status-button:hover {
            background-color: #d35400; /* Darker orange */
        }
    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php') ?>
    <div class="container">
        <h1>Found Items</h1>
        <?php
        if ($result->num_rows > 0) {
            $messages = [];
            while ($row = $result->fetch_assoc()) {
                if (!isset($messages[$row['id']])) {
                    $messages[$row['id']] = [
                        'message' => $row['message'], 
                        'status' => $row['status'], // Include status
                        'images' => [],
                        'first_name' => $row['first_name'],
                        'landmark' => $row['landmark'],
                        'title' => $row['title'],
                        'college' => $row['college'],
                        'email' => $row['email'],
                        'avatar' => $row['avatar'],
                        'time_found' => $row['time_found']
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
                $firstName = htmlspecialchars($msgData['first_name'] ?? '');
                $email = htmlspecialchars($msgData['email'] ?? '');
                $college = htmlspecialchars($msgData['college'] ?? '');
                $title = htmlspecialchars($msgData['title'] ?? '');
                $landmark = htmlspecialchars($msgData['landmark'] ?? '');
                $message = htmlspecialchars($msgData['message'] ?? '');
                $avatar = htmlspecialchars($msgData['avatar'] ?? '');
                $timeFound = htmlspecialchars($msgData['time_found'] ?? ''); // Fetch date and time
                
                if ($avatar) {
                    $fullAvatar = base_url . 'uploads/avatars/' . $avatar;
                    echo "<img src='" . htmlspecialchars($fullAvatar) . "' alt='Avatar' class='avatar'>";
                } else {
                    echo "<img src='uploads/avatars/default-avatar.png' alt='Default Avatar' class='avatar'>";
                }
                
                echo "<p><strong>Founder Name:</strong> " . $firstName . " (" . $email . ")</p>";
                echo "<p><strong>College:</strong> " . $college . "</p>";
                echo "<p><strong>Landmark:</strong> " . $landmark . "</p>";
                echo "<p><strong>Date and Time Found:</strong> " . $timeFound . "</p>"; // Display date and time
                echo "<p><strong>Title:</strong> " . $title . "</p>";
                echo "<p><strong>Description:</strong> " . $message . "</p>";
                
                if (!empty($msgData['images'])) {
                    echo "<p><strong>Images:</strong></p>";
                    echo "<div class='image-grid'>";
                    foreach ($msgData['images'] as $imagePath) {
                        echo "<a href='" . htmlspecialchars($imagePath) . "' data-lightbox='message-" . htmlspecialchars($msgId) . "' data-title='Image'><img src='" . htmlspecialchars($imagePath) . "' alt='Image'></a>";
                    }
                    echo "</div>";
                }
                
                // Display Status
                echo "<div class='status-box'>";
                echo "<p><strong>Status:</strong> " . htmlspecialchars($msgData['status']) . "</p>";
                echo "<button class='status-button' data-id='" . htmlspecialchars($msgId) . "' data-status='claimed'>Mark as Claimed</button>";
                echo "<button class='status-button' data-id='" . htmlspecialchars($msgId) . "' data-status='surrendered'>Mark as Surrendered</button>";
                echo "</div>";
                
                echo "<a href='claim_request.php?id=" . urlencode($msgId) . "' class='claim-button'>Claim Request</a>";
                
                echo "</div>";
            }
        } else {
            echo "<p>No details available for this item.</p>";
        }
        ?>
    </div>
    <?php require_once('../inc/footer.php') ?>
    <script src="../js/jquery.min.js"></script> <!-- Ensure this path is correct -->
    <script src="../js/bootstrap.min.js"></script> <!-- Ensure this path is correct -->
    <script src="../js/custom.js"></script> <!-- Ensure this path is correct -->
    <script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox-plus-jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.status-button').on('click', function() {
                var itemId = $(this).data('id');
                var status = $(this).data('status');
                if (confirm('Are you sure you want to change the status to ' + status + '?')) {
                    $.ajax({
                        url: 'update_status.php',
                        type: 'POST',
                        data: { id: itemId, status: status },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                alert('Status updated successfully.');
                                location.reload();
                            } else {
                                alert('Failed to update status: ' + response.error);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX error:", status, error);
                            alert('An error occurred: ' + error);
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

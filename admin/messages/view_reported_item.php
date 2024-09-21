<?php
include '../../config.php';

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the specific message ID from the URL
$message_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($message_id > 0) {
    // SQL query to fetch the details of the selected message by its ID
    $sql = "SELECT mh.id, mh.message, mi.image_path, mh.title, mh.landmark, um.first_name, um.college, um.email, um.avatar, mh.contact, mh.time_found, mh.status, c.name as category_name
        FROM message_history mh
        LEFT JOIN message_images mi ON mh.id = mi.message_id
        LEFT JOIN user_member um ON mh.user_id = um.id
        LEFT JOIN categories c ON mh.category_id = c.id
        WHERE mh.id = $message_id";

    // Fetch only the selected message
    $result = $conn->query($sql);
} else {
    echo "Invalid message ID.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Admin View</title>
    <?php require_once('../inc/header.php'); ?>
    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet">
    <style>
        /* Same styling as before */
    </style>
</head>
<body>
    <?php require_once('../inc/topBarNav.php'); ?>
    <?php require_once('../inc/navigation.php'); ?>

    <div class="container">
        <h1>View Details</h1>
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
                        'title' => $row['title'],
                        'college' => $row['college'],
                        'email' => $row['email'],
                        'avatar' => $row['avatar'],
                        'contact' => $row['contact'],
                        'time_found' => $row['time_found'],
                        'category_name' => $row['category_name'],  
                        'status' => $row['status']  // Include status field
                    ];
                }
                if ($row['image_path']) {
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
                $contact = htmlspecialchars($msgData['contact'] ?? '');
                $timeFound = htmlspecialchars($msgData['time_found'] ?? '');
                $categoryName = htmlspecialchars($msgData['category_name'] ?? ''); 
                
                if ($avatar) {
                    $fullAvatar = base_url . 'uploads/avatars/' . $avatar;
                    echo "<img src='" . htmlspecialchars($fullAvatar) . "' alt='Avatar' class='avatar'>";
                } else {
                    echo "<img src='uploads/avatars/default-avatar.png' alt='Default Avatar' class='avatar'>";
                }
                
                echo "<p><strong>User:</strong> " . $firstName . " (" . $email . ")</p>";
                echo "<p><strong>College:</strong> " . $college . "</p>";
                echo "<p><strong>Landmark:</strong> " . $landmark . "</p>";
                echo "<p><strong>Title:</strong> " . $title . "</p>";
                echo "<p><strong>Category:</strong> " . $categoryName . "</p>"; 
                echo "<p><strong>Description:</strong> " . $message . "</p>";
                echo "<p><strong>Contact:</strong> " . $contact . "</p>"; 
                echo "<p><strong>Time Found:</strong> " . $timeFound . "</p>";

                // Status dropdown and status badge display
                echo "<div class='form-group'>";
                echo "<label for='status' class='control-label'>Status</label>";
                echo "<select name='status' id='status-".$msgId."' class='form-select form-select-sm rounded-0' required='required'>";
                echo "<option value='0' " . ($msgData['status'] == 0 ? 'selected' : '') . ">Pending</option>";
                echo "<option value='1' " . ($msgData['status'] == 1 ? 'selected' : '') . ">Published</option>";
                echo "<option value='2' " . ($msgData['status'] == 2 ? 'selected' : '') . ">Claimed</option>";
                echo "<option value='3' " . ($msgData['status'] == 3 ? 'selected' : '') . ">Surrendered</option>";
                echo "</select>";
                echo "<button class='btn btn-primary save-status-btn' data-id='" . $msgId . "'>Save Status</button>";
                echo "</div>";

                echo "<dt class='text-muted'>Status</dt>";
                if ($msgData['status'] == 1) {
                    echo "<span class='badge bg-primary px-3 rounded-pill'>Published</span>";
                } elseif ($msgData['status'] == 2) {
                    echo "<span class='badge bg-success px-3 rounded-pill'>Claimed</span>";
                } elseif ($msgData['status'] == 3) {
                    echo "<span class='badge bg-secondary px-3 rounded-pill'>Surrendered</span>";
                } else {
                    echo "<span class='badge bg-secondary px-3 rounded-pill'>Pending</span>";
                }

                if (!empty($msgData['images'])) {
                    echo "<p><strong>Images:</strong></p>";
                    echo "<div class='image-grid'>";
                    foreach ($msgData['images'] as $imagePath) {
                        echo "<a href='" . htmlspecialchars($imagePath) . "' data-lightbox='message-" . htmlspecialchars($msgId) . "' data-title='Image'><img src='" . htmlspecialchars($imagePath) . "' alt='Image'></a>";
                    }
                    echo "</div>";
                }
                echo "<button class='publish-btn' data-id='" . htmlspecialchars($msgId) . "'>Publish</button>";
                echo "<button class='delete-btn' data-id='" . htmlspecialchars($msgId) . "'>Delete</button>";
                echo "</div>";
            }
        }
        ?>
    </div>

    <!-- Include JavaScript files -->
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/custom.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox-plus-jquery.min.js"></script>

    <script>
      $(document).ready(function() {
        $('.delete-btn').on('click', function() {
            var messageId = $(this).data('id');
            if (confirm('Are you sure you want to delete this message?')) {
                $.ajax({
                    url: 'delete_message.php',
                    type: 'POST',
                    data: { id: messageId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Found item deleted successfully.');
                            location.reload();
                        } else {
                            alert('Failed to delete the message: ' + response.error);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error:", status, error);
                        alert('An error occurred: ' + error);
                    }
                });
            }
        });

        $('.publish-btn').on('click', function() {
            var messageId = $(this).data('id');
            if (confirm('Are you sure you want to publish this message?')) {
                $.ajax({
                    url: 'publish_message.php',
                    type: 'POST',
                    data: { id: messageId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Found item published successfully.');
                            location.reload();
                        } else {
                            alert('Failed to publish the message: ' + response.error);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error:", status, error);
                        alert('An error occurred: ' + error);
                    }
                });
            }
        });

        // Handle status update
        $('.save-status-btn').on('click', function() {
            var messageId = $(this).data('id');
            var selectedStatus = $('#status-' + messageId).val(); // Get the selected status

            // Send an AJAX request to update the status
            $.ajax({
                url: 'update_status.php', // Backend URL to handle status updates
                type: 'POST',
                data: {
                    id: messageId,
                    status: selectedStatus
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Status updated successfully.');
                        if (selectedStatus == 2) { 
                            // Redirect to the claim log page after setting to "Claimed"
                            window.location.href = 'claim_log_table.php'; 
                        } else {
                            location.reload();  // Reload the page to reflect status update
                        }
                    } else {
                        alert('Failed to update status: ' + response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX error:", status, error);
                    alert('An error occurred: ' + error);
                }
            });
        });

      });
    </script>
</body>
<?php require_once('../inc/footer.php') ?>
</html>

<?php
$conn->close();
?>

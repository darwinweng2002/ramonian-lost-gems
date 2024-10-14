<?php
include '../../config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize $result as null
$result = null;

if (isset($_GET['id'])) {
    $itemId = $_GET['id'];

// SQL query to get missing item details and associated images
$sql = "
    SELECT mi.id, mi.title, mi.description, mi.last_seen_location, mi.time_missing, mi.status, mi.contact, mi.owner, user_info.first_name, user_info.college, user_info.email, user_info.avatar, c.name AS category_name, imi.image_path
    FROM missing_items mi
    LEFT JOIN (
        -- Fetch from user_member
        SELECT id AS user_id, first_name, college, email, avatar FROM user_member
        UNION
        -- Fetch from user_staff
        SELECT id AS user_id, first_name, department AS college, email, avatar FROM user_staff
    ) user_info ON mi.user_id = user_info.user_id
    LEFT JOIN missing_item_images imi ON mi.id = imi.missing_item_id
    LEFT JOIN categories c ON mi.category_id = c.id
    WHERE mi.id = ? AND mi.is_denied = 0  -- Add this condition to exclude denied items
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $itemId);
$stmt->execute();
$result = $stmt->get_result();

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Missing Items - Admin View</title>
    <?php require_once('../inc/header.php'); ?>
    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet">
    <style>
       body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            padding-top: 70px;
            background-color: #f4f4f4;
        }
        .container {
            margin: 30px auto;
            width: 90%;
            max-width: 1200px;
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
            position: relative;
        }
        .message-box p {
            margin: 10px 0;
        }
        .message-box img {
            max-width: 100%;
            border-radius: 5px;
            transition: transform 0.3s ease;
        }
        .message-box img:hover {
            transform: scale(1.1);
        }
        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
        }
        .deny-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            position: absolute;
            bottom: 20px;
            right: 10px;
        }
        .deny-btn:hover {
            background-color: #c82333;
        }
        .publish-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            position: absolute;
            bottom: 20px;
            right: 80px;
        }
        .publish-btn:hover {
            background-color: #218838;
        }
        .container .avatar {
            width: 100px;
            height: 100px;
            border-radius: 100%;
            object-fit: cover;
            display: block;
            margin-bottom: 10px;
        }
        /* Added styles for table responsiveness */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch; /* Smooth scrolling on touch devices */
        }
        .table {
            width: 100%;
            max-width: 100%;
            margin-bottom: 1rem;
            background-color: transparent;
        }
        .table th, .table td {
            white-space: nowrap; /* Prevent text wrapping */
        }
    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php') ?>
<?php require_once('../inc/navigation.php') ?> 
    <div class="container">
         <h1>View Missing Item Details</h1>
    <?php
    if ($result->num_rows > 0) {
        $items = [];
        while ($row = $result->fetch_assoc()) {
            if (!isset($items[$row['id']])) {
                $items[$row['id']] = [
                    'description' => $row['description'],
                    'last_seen_location' => $row['last_seen_location'],
                    'owner' => $row['owner'],
                    'time_missing' => $row['time_missing'],
                    'title' => $row['title'],
                    'status' => $row['status'],
                    'first_name' => $row['first_name'],
                    'college' => $row['college'],
                    'email' => $row['email'],
                    'avatar' => $row['avatar'],
                    'images' => [],
                    'contact' => $row['contact'],
                    'category_name' => $row['category_name']
                ];
            }
            if ($row['image_path']) {
                $fullImagePath = base_url . 'uploads/missing_items/' . $row['image_path'];
                $items[$row['id']]['images'][] = $fullImagePath;
            }
        }

        foreach ($items as $itemId => $itemData) {
            $firstName = htmlspecialchars($itemData['first_name'] ?? '');
            $email = htmlspecialchars($itemData['email'] ?? '');
            $college = htmlspecialchars($itemData['college'] ?? '');
            $title = htmlspecialchars($itemData['title'] ?? '');
            $lastSeenLocation = htmlspecialchars($itemData['last_seen_location'] ?? '');
            $description = htmlspecialchars($itemData['description'] ?? '');
            $owner = htmlspecialchars($itemData['owner'] ?? '');
            $avatar = htmlspecialchars($itemData['avatar'] ?? '');
            $timeMissing = htmlspecialchars($itemData['time_missing'] ?? '');
            $contact = htmlspecialchars($itemData['contact'] ?? '');
            $categoryName = htmlspecialchars($itemData['category_name'] ?? '');
            $status = intval($itemData['status']);

            echo "<div class='message-box'>";
                
                if ($avatar) {
                    $fullAvatar = base_url . 'uploads/avatars/' . $avatar;
                    echo "<img src='" . htmlspecialchars($fullAvatar) . "' alt='Avatar' class='avatar'>";
                } else {
                    echo "<img src='uploads/avatars/default-avatar.png' alt='Default Avatar' class='avatar'>";
                }

                echo "<p><strong>Item Name:</strong> " . $title . "</p>";
                echo "<p><strong>Owner Name:</strong> " . $owner . "</p>";
                // Check if user is a guest
            if (empty($firstName) || empty($college)) {
                echo "<p><strong>User Info:</strong> No Info</p>";
            } else {
                echo "<p><strong>User Name:</strong> " . $firstName . " (" . $email . ")</p>";
                echo "<p><strong>College:</strong> " . $college . "</p>";
            }
                echo "<p><strong>Last Seen Location:</strong> " . $lastSeenLocation . "</p>";
                echo "<p><strong>Date and time the item was lost:</strong> " . $timeMissing . "</p>";
                echo "<p><strong>Description:</strong> " . $description . "</p>";
                echo "<p><strong>Category:</strong> " . $categoryName . "</p>";
                echo "<p><strong>Contact:</strong> " . $contact . "</p>";

                // Status dropdown
                echo "<div class='form-group col-lg-12 col-md-12 col-sm-12 col-xs-12'>";
                echo "<label for='status' class='control-label'>Status</label>";
                echo "<select name='status' id='status-".$itemId."' class='form-select form-select-sm rounded-0' required='required'>";
                echo "<option value='0' " . ($status == 0 ? 'selected' : '') . ">Pending</option>";
                echo "<option value='1' " . ($status == 1 ? 'selected' : '') . ">Published</option>";
                echo "<option value='2' " . ($status == 2 ? 'selected' : '') . ">Claimed</option>";
                echo "<option value='3' " . ($status == 3 ? 'selected' : '') . ">Surrendered</option>";
                echo "</select>";
                echo "<button class='btn btn-primary save-status-btn' data-id='" . $itemId . "'>Save Status</button>";
                echo "</div>";

                echo "<dt class='text-muted'>Status</dt>";
                if ($status == 1) {
                    echo "<span class='badge bg-primary px-3 rounded-pill'>Published</span>";
                } elseif ($status == 2) {
                    echo "<span class='badge bg-success px-3 rounded-pill'>Claimed</span>";
                } elseif ($status == 3) {
                    echo "<span class='badge bg-secondary px-3 rounded-pill'>Surrendered</span>";
                } else {
                    echo "<span class='badge bg-secondary px-3 rounded-pill'>Pending</span>";   
                }

                if (!empty($itemData['images'])) {
                    echo "<p><strong>Images:</strong></p>";
                    echo "<div class='image-grid'>";
                    foreach ($itemData['images'] as $imagePath) {
                        echo "<a href='" . htmlspecialchars($imagePath) . "' data-lightbox='item-" . htmlspecialchars($itemId) . "' data-title='Image'><img src='" . htmlspecialchars($imagePath) . "' alt='Image'></a>";
                    }
                    echo "</div>";
                } else {
                    echo "<p>No images available.</p>";
                }

                $denyDisabled = $msgData['status'] != 0 ? 'disabled' : '';
                $denyClass = $msgData['status'] != 0 ? 'disabled-btn' : 'deny-btn';
                
                echo "<button class='publish-btn' data-id='" . htmlspecialchars($msgId) . "' " . ($msgData['status'] != 1 ? 'disabled title=\"Status is not set to Published\"' : '') . ">Publish</button>";
                echo "<button class='" . $denyClass . "' data-id='" . htmlspecialchars($msgId) . "' " . $denyDisabled . ">Denied</button>";

                echo "</div>";
        } else {
            echo "<p>No details available for this item.</p>";
        }
        ?>
    </div>

    <!-- Include JavaScript files -->
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox-plus-jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    $(document).ready(function() {
        // Function to update status
        $('.save-status-btn').on('click', function() {
            var itemId = $(this).data('id');
            var selectedStatus = $('#status-' + itemId).val();

            $.ajax({
                url: 'update_status.php',
                type: 'POST',
                data: {
                    id: itemId,
                    status: selectedStatus
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success', 'The status has been updated successfully.', 'success')
                        .then(() => location.reload());
                    } else {
                        Swal.fire('Error', 'Failed to update status.', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire('Error', 'An error occurred while updating the status.', 'error');
                }
            });
        });

        // Publish button functionality
        $('.publish-btn').on('click', function() {
            var itemId = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to publish this missing item?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, publish it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'publish_message.php',
                        type: 'POST',
                        data: { id: itemId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Published!', 'The missing item has been successfully published.', 'success')
                                .then(() => location.reload());
                            } else {
                                Swal.fire('Error', 'Failed to publish the missing item.', 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire('Error', 'An error occurred while publishing the item.', 'error');
                        }
                    });
                }
            });
        });

        $(document).ready(function() {
    // Deny button functionality for missing items
    $('.deny-btn').on('click', function() {
        var itemId = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to deny this missing item?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, deny it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'deny_item.php', // Point to the deny endpoint
                    type: 'POST',
                    data: { id: itemId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Denied!', 'The missing item has been denied.', 'success')
                                .then(() => {
                                    // Remove the item from the page and reload the page
                                    location.reload(); // This ensures the denied item is no longer visible
                                });
                        } else {
                            Swal.fire('Error!', 'Failed to deny the missing item: ' + response.error, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire('Error!', 'An error occurred while denying the item: ' + error, 'error');
                    }
                });
            }
            
        });
    });
});
});
    </script>
    <?php require_once('../inc/footer.php'); ?>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

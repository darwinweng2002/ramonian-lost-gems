<?php
include '../../config.php';

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');// Replace with your actual DB connection details

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize $result as null
$result = null;

if (isset($_GET['id'])) {
    $itemId = $_GET['id'];

    // Prepare the SQL statement
    $stmt = $conn->prepare("SELECT 
                mi.id, 
                mi.title, 
                mi.description, 
                mi.last_seen_location, 
                mi.time_missing, 
                mi.status,  /* Status field is fetched */
                mi.created_at, 
                um.email, 
                um.college,
                um.avatar,
                mi.contact,
                c.name AS category_name,
                GROUP_CONCAT(mii.image_path) AS images 
            FROM missing_items mi
            LEFT JOIN user_member um ON mi.user_id = um.id
            LEFT JOIN categories c ON mi.category_id = c.id
            LEFT JOIN missing_item_images mii ON mi.id = mii.missing_item_id
            WHERE mi.id = ?
            GROUP BY mi.id, um.email, um.college, um.avatar, mi.contact, c.name"); // Group by all non-aggregated columns
    
    $stmt->bind_param('i', $itemId); // Bind the integer value
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
    <!-- SweetAlert2 CSS and JS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        .delete-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            position: absolute;
            bottom: 20px;
            right: 20px;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
        .publish-btn {
            background-color: #28a745; /* Green background color */
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            position: absolute;
            bottom: 20px;
            right: 80px; /* Position it to the left of the delete button */
        }
        .publish-btn:hover {
            background-color: #218838; /* Darker green on hover */
        }
        .container .avatar {
            width: 100px; /* Set the width of the avatar */
            height: 100px; /* Set the height of the avatar to the same value as width for a circle */
            border-radius: 100%; /* Makes the image circular */
            object-fit: cover; /* Ensures the image covers the circle without distortion */
            display: block; /* Ensures the image is displayed as a block element */
            margin-bottom: 10px; /* Adds space below the image if needed */
        }
    </style>
</head>
<body>
    <?php require_once('../inc/topBarNav.php'); ?>
    <?php require_once('../inc/navigation.php'); ?>

    <div class="container">
        <h1>View Missing Item Details</h1>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Assign the status field to the variable
                $status = $row['status'];  // Assign the status value from the result

                $images = explode(',', $row['images']); // Convert image paths to an array

                echo "<div class='message-box'>";
                $firstName = htmlspecialchars($row['first_name'] ?? '');
                $email = htmlspecialchars($row['email'] ?? '');
                $college = htmlspecialchars($row['college'] ?? '');
                $title = htmlspecialchars($row['title'] ?? '');
                $lastSeenLocation = htmlspecialchars($row['last_seen_location'] ?? '');
                $description = htmlspecialchars($row['description'] ?? '');
                $avatar = htmlspecialchars($row['avatar'] ?? '');
                $timeMissing = htmlspecialchars($row['time_missing'] ?? '');
                $contact = htmlspecialchars($row['contact'] ?? '');
                $categoryName = htmlspecialchars($row['category_name'] ?? '');
                
                if ($avatar) {
                    $fullAvatar = base_url . 'uploads/avatars/' . $avatar;
                    echo "<img src='" . htmlspecialchars($fullAvatar) . "' alt='Avatar' class='avatar'>";
                } else {
                    echo "<img src='uploads/avatars/default-avatar.png' alt='Default Avatar' class='avatar'>";
                }
                
                echo "<p><strong>User:</strong> " . $firstName . " (" . $email . ")</p>";
                echo "<p><strong>College:</strong> " . $college . "</p>";
                echo "<p><strong>Last Seen Location:</strong> " . $lastSeenLocation . "</p>";
                echo "<p><strong>Item Name:</strong> " . $title . "</p>";
                echo "<p><strong>Description:</strong> " . $description . "</p>";
                echo "<p><strong>Time Missing:</strong> " . $timeMissing . "</p>";
                echo "<p><strong>Contact:</strong> " . $contact . "</p>";
                echo "<p><strong>Category:</strong> " . $categoryName . "</p>";
                echo "<div class='form-group col-lg-12 col-md-12 col-sm-12 col-xs-12'>";
                echo "<label for='status' class='control-label'>Status</label>";
                echo "<select name='status' id='status-".$row['id']."' class='form-select form-select-sm rounded-0' required='required'>";
                echo "<option value='0' " . ($status == 0 ? 'selected' : '') . ">Pending</option>";
                echo "<option value='1' " . ($status == 1 ? 'selected' : '') . ">Published</option>";
                echo "<option value='2' " . ($status == 2 ? 'selected' : '') . ">Claimed</option>";
                echo "<option value='3' " . ($status == 3 ? 'selected' : '') . ">Surrendered</option>";
                echo "</select>";
                echo "<button class='btn btn-primary save-status-btn' data-id='" . $row['id'] . "'>Save Status</button>";

                // Close message box div
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
                

                if (!empty($images)) {
                    echo "<p><strong>Images:</strong></p>";
                    echo "<div class='image-grid'>";
                    foreach ($images as $imagePath) {
                        $fullImagePath = base_url . 'uploads/items/' . htmlspecialchars($imagePath);
                        // Add Lightbox attributes
                        echo "<a href='" . $fullImagePath . "' data-lightbox='message-" . htmlspecialchars($row['id']) . "' data-title='Image'><img src='" . $fullImagePath . "' alt='Image'></a>";
                    }
                    echo "</div>";
                }

                echo "<button class='publish-btn' data-id='" . htmlspecialchars($row['id']) . "'>Publish</button>";
                echo "<button class='delete-btn' data-id='" . htmlspecialchars($row['id']) . "'>Delete</button>";
                echo "</div>";
            }
        }
        ?>
    </div>

    <!-- Include JavaScript files -->
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox-plus-jquery.min.js"></script>

    <script>
    $(document).ready(function() {
    // Delete button functionality
    $('.delete-btn').on('click', function() {
        var itemId = $(this).data('id');
        // Use SweetAlert2 to show a confirmation dialog
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you really want to delete this missing item?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Perform the AJAX request to delete the item
                $.ajax({
                    url: 'delete_message.php', // Ensure this path is correct
                    type: 'POST',
                    data: { id: itemId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                'Deleted!',
                                'The missing item has been deleted.',
                                'success'
                            ).then(() => {
                                location.reload(); // Reload the page after deletion
                            });
                        } else {
                            Swal.fire('Error', 'Failed to delete the missing item: ' + response.error, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error:", status, error);
                        Swal.fire('Error', 'An error occurred while deleting the item.', 'error');
                    }
                });
            }
        });
    });

    // Publish button functionality
    $('.publish-btn').on('click', function() {
        var itemId = $(this).data('id');
        // Use SweetAlert2 to show the confirmation
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
                    url: 'publish_message.php', // Ensure this path is correct
                    type: 'POST',
                    data: { id: itemId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                'Published!',
                                'The missing item has been published.',
                                'success'
                            ).then(() => {
                                location.reload(); // Reload the page after publishing
                            });
                        } else {
                            Swal.fire('Error', 'Failed to publish the missing item: ' + response.error, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error:", status, error);
                        Swal.fire('Error', 'An error occurred while publishing the item.', 'error');
                    }
                });
            }
        });
    });

    // Save status button functionality
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
                    Swal.fire(
                        'Success',
                        'The status has been updated successfully.',
                        'success'
                    ).then(() => {
                        location.reload();  // Reflect the status update
                    });
                } else {
                    Swal.fire('Error', 'Failed to update status: ' + response.error, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX error:", status, error);
                Swal.fire('Error', 'An error occurred while updating the status.', 'error');
            }
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


<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Enable error reporting for debugging

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
    // Adjusted SQL query to fetch both user_member (students) and user_staff (staff)
    $sql = "SELECT mh.id, mh.message, mi.image_path, mh.title, mh.landmark, 
                   um.first_name AS student_first_name, um.last_name AS student_last_name, um.college, um.email AS student_email, 
                   us.first_name AS staff_first_name, us.last_name AS staff_last_name, us.department AS staff_department, us.email AS staff_email, 
                   mh.contact, mh.founder, mh.time_found, mh.status, 
                   c.name as category_name, mh.user_id, mh.staff_id
            FROM message_history mh
            LEFT JOIN message_images mi ON mh.id = mi.message_id
            LEFT JOIN user_member um ON mh.user_id = um.id
            LEFT JOIN user_staff us ON mh.staff_id = us.id
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
    <title>Found Item Details - Admin View</title>
    <?php require_once('../inc/header.php'); ?>
    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet">
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
            right: 10px;
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
        <h1>Found Item Details</h1>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='message-box'>";
                
                // Variables for user info (either from user_member or user_staff)
                $firstName = '';
                $lastName = '';
                $email = '';
                $departmentOrCollege = '';
                
                // If user is a student (user_member)
                if (!empty($row['student_first_name'])) {
                    $firstName = htmlspecialchars($row['student_first_name']);
                    $lastName = htmlspecialchars($row['student_last_name']);
                    $email = htmlspecialchars($row['student_email']);
                    $departmentOrCollege = htmlspecialchars($row['college']);
                } 
                // If user is a staff (user_staff)
                elseif (!empty($row['staff_first_name'])) {
                    $firstName = htmlspecialchars($row['staff_first_name']);
                    $lastName = htmlspecialchars($row['staff_last_name']);
                    $email = htmlspecialchars($row['staff_email']);
                    $departmentOrCollege = htmlspecialchars($row['staff_department']);
                }

                // Check if we have user info (either student or staff)
                if ($firstName || $email || $departmentOrCollege) {
                    echo "<p><strong>User Info:</strong> $firstName $lastName ($email)</p>";
                    echo "<p><strong>Department/College:</strong> $departmentOrCollege</p>";
                } else {
                    echo "<p><strong>User Info:</strong> Guest User</p>";
                }

                // Continue with item details display
                $title = htmlspecialchars($row['title']);
                $landmark = htmlspecialchars($row['landmark']);
                $timeFound = htmlspecialchars($row['time_found']);
                $message = htmlspecialchars($row['message']);
                $categoryName = htmlspecialchars($row['category_name']);
                $contact = htmlspecialchars($row['contact']);
                $founder = htmlspecialchars($row['founder']);
                
                echo "<p><strong>Item Name:</strong> $title</p>";
                echo "<p><strong>Category:</strong> $categoryName</p>";
                echo "<p><strong>Finder's Name:</strong> $founder</p>";
                echo "<p><strong>Location where the item was found:</strong> $landmark</p>";
                echo "<p><strong>Date and Time Found:</strong> $timeFound</p>";
                echo "<p><strong>Description:</strong> $message</p>";
                echo "<p><strong>Contact:</strong> $contact</p>";

                // Status dropdown and status badge display
                echo "<div class='form-group'>";
                echo "<label for='status' class='control-label'>Status</label>";
                echo "<select name='status' id='status-".$row['id']."' class='form-select form-select-sm rounded-0' required='required'>";
                echo "<option value='0' " . ($row['status'] == 0 ? 'selected' : '') . ">Pending</option>";
                echo "<option value='1' " . ($row['status'] == 1 ? 'selected' : '') . ">Published</option>";
                echo "<option value='2' " . ($row['status'] == 2 ? 'selected' : '') . ">Claimed</option>";
                echo "<option value='3' " . ($row['status'] == 3 ? 'selected' : '') . ">Surrendered</option>";
                echo "</select>";
                echo "<button class='btn btn-primary save-status-btn' data-id='" . $row['id'] . "'>Save Status</button>";
                echo "</div>";
                
                echo "<dt class='text-muted'>Status</dt>";
                if ($row['status'] == 1) {
                    echo "<span class='badge bg-primary px-3 rounded-pill'>Published</span>";
                } elseif ($row['status'] == 2) {
                    echo "<span class='badge bg-success px-3 rounded-pill'>Claimed</span>";
                } elseif ($row['status'] == 3) {
                    echo "<span class='badge bg-secondary px-3 rounded-pill'>Surrendered</span>";
                } else {
                    echo "<span class='badge bg-secondary px-3 rounded-pill'>Pending</span>";
                }
                
                echo "</div>";
            }
        } else {
            echo "No details found for this item.";
        }
                
                if (!empty($msgData['images'])) {
                    echo "<p><strong>Images:</strong></p>";
                    echo "<div class='image-grid'>";
                    foreach ($msgData['images'] as $imagePath) {
                        echo "<a href='" . htmlspecialchars($imagePath) . "' data-lightbox='message-" . htmlspecialchars($msgId) . "' data-title='Image'><img src='" . htmlspecialchars($imagePath) . "' alt='Image'></a>";
                    }
                    echo "</div>";
                }
                
                // Disable the publish button if status is not "Published"
                echo "<button class='publish-btn' data-id='" . htmlspecialchars($msgId) . "' " . ($msgData['status'] != 1 ? 'disabled title=\"Status is not set to Published\"' : '') . ">Publish</button>";
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
      $(document).ready(function() {
        // SweetAlert for delete confirmation
        $('.delete-btn').on('click', function() {
            var messageId = $(this).data('id');
            Swal.fire({
                title: 'Are you sure you want to delete this item entry?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'delete_message.php',
                        type: 'POST',
                        data: { id: messageId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Deleted!', 'The item has been deleted.', 'success');
                                location.reload();
                            } else {
                                Swal.fire('Error!', response.error, 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire('Error!', 'An error occurred: ' + error, 'error');
                        }
                    });
                }
            });
        });

        // SweetAlert for publish confirmation
        $('.publish-btn').on('click', function() {
            var messageId = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to publish this found item entry?",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, publish it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'publish_message.php',
                        type: 'POST',
                        data: { id: messageId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Published!', 'The found item has been published.', 'success');
                                location.reload();
                            } else {
                                Swal.fire('Error!', response.error, 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire('Error!', 'An error occurred: ' + error, 'error');
                        }
                    });
                }
            });
        });

        // SweetAlert for status update confirmation
        $('.save-status-btn').on('click', function() {
            var messageId = $(this).data('id');
            var selectedStatus = $('#status-' + messageId).val(); // Get the selected status

            Swal.fire({
                title: 'Update Status?',
                text: "Are you sure you want to update the status?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'update_status.php',
                        type: 'POST',
                        data: {
                            id: messageId,
                            status: selectedStatus
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Updated!', 'Status has been updated successfully.', 'success');
                                location.reload();
                            } else {
                                Swal.fire('Error!', response.error, 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire('Error!', 'An error occurred: ' + error, 'error');
                        }
                    });
                }
            });
        });

      });
      $(document).on('change', '.form-select', function() {
    var messageId = $(this).attr('id').split('-')[1];
    var selectedStatus = $(this).val();
    
    if (selectedStatus == 1) { // Published
        $('.publish-btn[data-id="' + messageId + '"]').prop('disabled', false);
    } else {
        $('.publish-btn[data-id="' + messageId + '"]').prop('disabled', true);
    }
});

    </script>
</body>
<?php require_once('../inc/footer.php') ?>
</html>

<?php
$conn->close();
?>

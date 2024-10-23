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
    // SQL query to fetch the details of the selected message by its ID from both user_member and user_staff
    $sql = "
    SELECT mh.id, mh.message, mi.image_path, mh.title, mh.landmark, user_info.first_name, user_info.college, user_info.email, user_info.avatar, user_info.school_type, mh.contact, mh.founder, mh.time_found, mh.status, c.name as category_name
    FROM message_history mh
    LEFT JOIN message_images mi ON mh.id = mi.message_id
    LEFT JOIN (
        SELECT id AS user_id, first_name, college, email, avatar, school_type, 'member' AS user_type FROM user_member
        UNION
        SELECT id AS user_id, first_name, department AS college, email, avatar, 'staff' AS user_type FROM user_staff
    ) AS user_info ON mh.user_id = user_info.user_id
    LEFT JOIN categories c ON mh.category_id = c.id
    WHERE mh.is_denied = 0 AND mh.id = $message_id"; // Exclude denied items

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        .publish-btn {
    background-color: #28a745;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    position: absolute;
    bottom: 20px;
    right: 130px; /* Adjust this as needed */
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
    right: 20px;
}

.deny-btn:hover {
    background-color: #c82333;
}

.publish-btn:hover {
    background-color: #218838;
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

        /* Table responsiveness and scrollable table container */
        .table-responsive {
            overflow-x: auto;
            width: 100%;
            scrollbar-width: thin;
        }

        table {
            width: 100%;
            min-width: 600px;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .table-responsive::-webkit-scrollbar {
            height: 10px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background-color: #b0b0b0;
            border-radius: 10px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background-color: #f4f4f4;
        }

        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
            }
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

.disabled-btn {
    background-color: #d3d3d3;
    color: #999;
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: not-allowed;
    position: absolute;
    bottom: 20px;
    right: 10px;
    pointer-events: none; /* Disable click events */
}

    </style>
</head>
<body>
    <?php require_once('../inc/topBarNav.php'); ?>
    <?php require_once('../inc/navigation.php'); ?>

    <div class="container">
        <h1>Found Item Details</h1>

        <div class="table-responsive">
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
                            'school_type' => $row['school_type'],
                            'college' => $row['college'],
                            'email' => $row['email'],
                            'avatar' => $row['avatar'],
                            'contact' => $row['contact'],
                            'founder' => $row['founder'],
                            'time_found' => $row['time_found'],
                            'category_name' => $row['category_name'],
                            'is_staff' => $row['is_staff'],
                            'status' => $row['status']
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
                    $founder = htmlspecialchars($msgData['founder'] ?? '');
                    $timeFound = htmlspecialchars($msgData['time_found'] ?? '');
                    $categoryName = htmlspecialchars($msgData['category_name'] ?? '');
                    $isStaff = htmlspecialchars($msgData['is_staff'] ?? '');

                    if ($firstName || $email || $college) {
                        if ($avatar) {
                            $fullAvatar = base_url . 'uploads/avatars/' . $avatar;
                            echo "<img src='" . htmlspecialchars($fullAvatar) . "' alt='Avatar' class='avatar'>";
                        } else {
                            echo "<img src='uploads/avatars/default-avatar.png' alt='Default Avatar' class='avatar'>";
                        }
                    } else {
                        echo "<p><strong>User Info:</strong>No Info</p>";
                    }

                    echo "<p><strong>Item Name:</strong> " . $title . "</p>";
                    echo "<p><strong>Category:</strong> " . $categoryName . "</p>";
                    echo "<p><strong>Finder's Name:</strong> " . $founder . "</p>";
                    echo "<p><strong>Location where the item was found:</strong> " . $landmark . "</p>";
                    echo "<p><strong>Date and Time Found:</strong> " . $timeFound . "</p>";
                    echo "<p><strong>Description:</strong> " . $message . "</p>";
                    echo "<p><strong>Contact:</strong> " . $contact . "</p>";

                    if ($schoolType == 2) { // Employee
                        echo "<p><strong>User Info:</strong> " . ($firstName ? $firstName : 'N/A') . " (" . ($email ? $email : 'N/A') . ")</p>";
                        echo "<p><strong>Department:</strong> " . ($department ? $department : 'N/A') . "</p>";
                        echo "<p><strong>Teaching Status:</strong> " . ($teachingStatus ? $teachingStatus : 'N/A') . "</p>";
                    } elseif ($schoolType == 0) { // High School Student
                        echo "<p><strong>User Info:</strong> " . ($firstName ? $firstName : 'N/A') . " (" . ($email ? $email : 'N/A') . ")</p>";
                        echo "<p><strong>Grade:</strong> " . ($year ? $year : 'N/A') . "</p>";
                    } elseif ($schoolType == 1) { // College Student
                        echo "<p><strong>User Info:</strong> " . ($firstName ? $firstName : 'N/A') . " (" . ($email ? $email : 'N/A') . ")</p>";
                        echo "<p><strong>College:</strong> " . ($college ? $college : 'N/A') . "</p>";
                        echo "<p><strong>Course:</strong> " . ($course ? $course : 'N/A') . "</p>";
                        echo "<p><strong>Year Level:</strong> " . ($year ? $year : 'N/A') . "</p>";
                    } else { // Unknown or Guest
                        echo "<p><strong>User Info:</strong> " . ($firstName ? $firstName : 'N/A') . " (" . ($email ? $email : 'N/A') . ")</p>";
                        echo "<p><strong>User Role:</strong> Guest</p>";
                    }

                    echo "<div class='form-group'>";
                    echo "<label for='status' class='control-label'>Status</label>";
                    echo "<select name='status' id='status-".$msgId."' class='form-select form-select-sm rounded-0' required='required'>";
                    echo "<option value='0' " . ($msgData['status'] == 0 ? 'selected' : '') . ">Pending</option>";
                    echo "<option value='1' " . ($msgData['status'] == 1 ? 'selected' : '') . ">Published</option>";
                    echo "<option value='2' " . ($msgData['status'] == 2 ? 'selected' : '') . ">Claimed</option>";
                    echo "<option value='3' " . ($msgData['status'] == 3 ? 'selected' : '') . ">Surrendered</option>";
                    echo "<option value='4' " . ($msgData['status'] == 4 ? 'selected' : '') . ">Denied</option>"; // New option for Denied
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
                    } elseif ($msgData['status'] == 4) {
                        echo "<span class='badge bg-danger px-3 rounded-pill'>Denied</span>"; // New badge for Denied
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

                    $denyDisabled = $msgData['status'] != 4 ? 'disabled' : '';
                    $denyClass = $msgData['status'] != 4 ? 'disabled-btn' : 'deny-btn';
                    
                    echo "<button class='publish-btn' data-id='" . htmlspecialchars($msgId) . "' " . ($msgData['status'] != 1 ? 'disabled title=\"Status is not set to Published\"' : '') . ">Publish Report</button>";
                    echo "<button class='" . $denyClass . "' data-id='" . htmlspecialchars($msgId) . "' " . $denyDisabled . ">Deny Report</button>";

                    echo "</div>";
                }
            }
            ?>
        </div>
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
        $(document).ready(function() {
    // Handle the denied action using SweetAlert confirmation
    $('.deny-btn').on('click', function() {
        var messageId = $(this).data('id');
        Swal.fire({
            title: 'Are you sure you want to deny this item?',
            text: "This item will be moved to the Denied Items section.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, deny it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'deny_item.php',
                    type: 'POST',
                    data: { id: messageId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Denied!', 'The item has been moved to the Denied Items section.', 'success');
                            location.reload(); // Reload the page after success
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
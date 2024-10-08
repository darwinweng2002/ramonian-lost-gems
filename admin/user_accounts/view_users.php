<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../../config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';


// Database connection
$conn = new mysqli("localhost", "u450897284_root", "Lfisgemsdb1234", "u450897284_lfis_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user_id is set in the POST request
if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']); // Sanitize input
} else {
    die("User ID is not set.");
}

// Prepared statement to update the user's status to 'approved'
$stmt = $conn->prepare("UPDATE users SET status='approved' WHERE id=?");
$stmt->bind_param("i", $user_id); // "i" indicates the parameter is an integer

if ($stmt->execute()) {
    // Send approval email
    if (sendApprovalEmail($user_id, $conn)) {
        echo '1'; // Success
    } else {
        echo '0'; // Error sending email
    }
} else {
    echo '0'; // Error updating record
}

$stmt->close();
$conn->close();

// Function to send approval email
function sendApprovalEmail($user_id, $conn) {
    $sql = "SELECT email FROM users WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Fetch user email
        $row = $result->fetch_assoc();
        $to = $row['email'];
        $subject = "Account Approved";
        $message = "Congratulations! Your account has been approved.";
        
        $mail = new PHPMailer(true);
        
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'randolfh.wizworxx@gmail.com';
            $mail->Password   = '@Thelastkidds05!!@2024';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            //Recipients
            $mail->setFrom('randolfh.wizworxx@gmail.com', 'Admin');
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    return false; // User not found
}
?>

<!DOCTYPE html>
<html lang="en">
<?php require_once('../inc/header.php') ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <title>View Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            padding: 20px;
        }

        .container {
            margin: 30px auto;
            max-width: 1200px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h3 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        /* Make the table horizontally scrollable */
        .table-responsive {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow-x: auto; /* Enable horizontal scrolling */
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: center;
        }

        th {
            white-space: nowrap; /* Prevents wrapping */
        }

        thead th {
            background-color: #f2f2f2;
            color: #444;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tbody tr:hover {
            background-color: #f1f1f1;
        }

        .btn {
            border-radius: 5px;
            padding: 8px 12px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s;
            color: #fff;
        }

        .btn-delete {
            background-color: #dc3545;
            border: none;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }

        /* Style for the input group */
        .input-group {
            display: flex;
            align-items: center;
            border-radius: 8px;
            overflow: hidden;   /* Ensures the border-radius applies to all child elements */
        }

        /* Search input field */
        .search-input {
            border: 1px solid #ddd;
            border-right: none;
            border-radius: 0;
            padding: 10px;
            outline: none;
            box-shadow: none;
            width: 200px;
            flex-grow: 1;
        }

        /* Button */
        .search-button {
            border-radius: 0;
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 10px 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-left: -5px;
        }

        /* Button hover */
        .search-button:hover {
            background-color: #218838;
        }

        /* Icon styling */
        .input-group-text {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 10px;
            border-right: none;
            color: #333;
        }

        .input-group-text i {
            font-size: 14px;
        }

        .no-data {
            text-align: center;
            font-size: 1.2rem;
            color: #333;
            padding: 30px 0;
        }

        /* Media Queries for Responsive Design */
        @media (max-width: 512px) {
            .container {
                padding: 15px;
                margin: 20px auto;
                max-width: 95%;
            }

            h2 {
                font-size: 24px;
            }

            table {
                width: 100%;
                min-width: 600px; /* Set a minimum width to ensure scrolling */
            }

            th, td {
                padding: 8px;
                white-space: nowrap; /* Prevent wrapping of table content */
            }

            .btn {
                font-size: 12px;
                padding: 6px 10px;
            }

            .input-group {
                flex-direction: column;
                align-items: stretch;
            }

            .search-input {
                width: 100%;
                margin-bottom: 10px;
            }

            .search-button {
                width: 100%;
            }

            /* Hide less important columns for mobile */
            .hide-mobile {
                display: none;
            }
        }
    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php') ?>
<?php require_once('../inc/navigation.php') ?> 
<br>
<br>
<section class="section">
    <div class="container">
        <h3>Pending Student Account Approvals - PRMSU Iba</h3>

        <!-- Search Form -->
        <form class="search-form" method="GET" action="view_users.php">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="search-input form-control" placeholder="Search users..." value="<?= htmlspecialchars($searchTerm) ?>">
                <button type="submit" class="search-button">Search</button>
            </div>
        </form>

        <!-- Table Wrapper with Horizontal Scroll -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Level</th>
                        <th>College</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Username</th> <!-- Adjusted Email Header -->
                        <th>Actions</th> <!-- Adjusted Actions Header -->
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr data-id="<?= htmlspecialchars($row['id']) ?>">
                                <td><?= htmlspecialchars($row['first_name']) ?></td>
                                <td><?= htmlspecialchars($row['last_name']) ?></td>

                                <!-- Logic to display "High School" or "College" based on the value -->
                                <td>
                                <?php
                                    // Determine the Level
                                    $level = htmlspecialchars($row['school_type']) == '1' ? 'College' : 'High School';

                                    // Fetch the grade information, assuming it is stored in 'grade' column
                                    $grade = htmlspecialchars($row['grade']);

                                    // Logic to display the level with grade or without it
                                    if ($level === 'College') {
                                        echo $level;
                                    } else {
                                        echo $level . ($grade !== 'N/A' ? ' (Grade ' . $grade . ')' : '');
                                    }
                                ?>
                                </td>

                                <td><?= htmlspecialchars($row['college']) ?></td>
                                <td><?= htmlspecialchars($row['course']) ?></td>
                                <td><?= htmlspecialchars($row['year']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td>
                                    <div class="d-flex justify-content-center">
                                        <a href="viewpage.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-info btn-sm">
                                            <i class="fa fa-eye"></i> View Details
                                        </a>
                                        <button class="btn btn-delete btn-sm ms-2" onclick="deleteUser(event, <?= htmlspecialchars($row['id']) ?>)">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                        <?php if ($row['status'] !== 'approved'): ?> 
                                        <button class="btn btn-success btn-sm ms-2" onclick="approveUser(event, <?= htmlspecialchars($row['id']) ?>)">
                                            <i class="fa fa-check"></i> Approve
                                        </button>
                                        <?php else: ?>
                                        <span class="badge bg-success ms-2">Approved</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="no-data">
                                No registered users found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>
// Function to delete user with confirmation
function deleteUser(event, id) {
    event.preventDefault(); // Prevent default form submission

    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('delete_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + id
            })
            .then(response => response.text())
            .then(result => {
                console.log('Response from server:', result); // Log the response for debugging
                if (result.trim() === '1') {
                    Swal.fire(
                        'Deleted!',
                        'The user has been deleted.',
                        'success'
                    ).then(() => {
                        location.reload(); // Reload the page to reflect changes
                    });
                } else {
                    Swal.fire(
                        'Error!',
                        'An error occurred while deleting the user.',
                        'error'
                    );
                }
            })
            .catch(() => {
                Swal.fire(
                    'Error!',
                    'An error occurred while deleting the user.',
                    'error'
                );
            });
        }
    });
}

// Function to approve user with confirmation
function approveUser(event, id) {
    event.preventDefault(); // Prevent default form submission

    Swal.fire({
        title: 'Are you sure?',
        text: "You are about to approve this user!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, approve it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('approve_users.php', { // Use the backend script to handle approval
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'user_id=' + id // Send the user ID in the request body
            })
            .then(response => response.text())  // Expecting text response
            .then(result => {
                console.log('Response from server:', result); // Log the response for debugging
                
                // Check for success (1) or failure
                if (result.trim() === '1') {
                    Swal.fire(
                        'Approved!',
                        'The user has been approved successfully.',
                        'success'
                    ).then(() => {
                        // Remove the approved row from the table without refreshing the page
                        const userRow = document.querySelector(`tr[data-id="${id}"]`);
                        if (userRow) {
                            userRow.remove(); // Remove the approved user from the table
                        }
                    });
                } else {
                    Swal.fire(
                        'Error!',
                        'An error occurred while approving the user. Please try again.',
                        'error'
                    );
                }
            })
            .catch((error) => {
                console.error('Error occurred during approval:', error);
                Swal.fire(
                    'Error!',
                    'An unexpected error occurred while approving the user. Please try again.',
                    'error'
                );
            });
        }
    });
}
</script>

<?php
$conn->close();
?>
<?php require_once('../inc/footer.php') ?>
</body>
</html>
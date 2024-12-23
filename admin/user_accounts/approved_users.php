<?php
include '../../config.php';

// Database connection
$conn = new mysqli("localhost", "u450897284_root", "Lfisgemsdb1234", "u450897284_lfis_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize search term and ensure it's sanitized
$searchTerm = isset($_GET['search']) ? $conn->real_escape_string(trim($_GET['search'])) : '';

// Update SQL query to include search functionality based on relevant columns
$sql = "SELECT * FROM user_member WHERE status = 'approved'";

// Add search conditions only if a search term is provided
if (!empty($searchTerm)) {
    $sql .= " AND (first_name LIKE '%$searchTerm%' 
                OR last_name LIKE '%$searchTerm%' 
                OR college LIKE '%$searchTerm%' 
                OR course LIKE '%$searchTerm%' 
                OR email LIKE '%$searchTerm%')";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once('../inc/header.php') ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved Users</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
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

        .table-responsive {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow-x: auto; /* Enables horizontal scrolling */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px; /* This ensures the table will scroll when content is wide */
        }

        th, td {
            padding: 12px;
            text-align: center;
        }

        th {
            white-space: nowrap;
            background-color: #f2f2f2;
            color: #444;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tbody tr:hover {
            background-color: #f1f1f1;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .search-input {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            flex-grow: 1;
        }

        .search-button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-button:hover {
            background-color: #218838;
        }

        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }

        .no-data {
            text-align: center;
            font-size: 1.2rem;
            color: #333;
            padding: 30px 0;
        }

        /* Custom scrollbar for table container */
        .table-responsive::-webkit-scrollbar {
            height: 8px; /* Adjust the height for horizontal scrollbar */
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background-color: #b0b0b0;
            border-radius: 5px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background-color: #f4f4f4;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .input-group {
                flex-direction: column;
            }

            .search-input {
                margin-bottom: 10px;
            }

            .btn-info {
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php') ?>
<?php require_once('../inc/navigation.php') ?>
    <div class="container">
        <br>
        <br>
        <h3>Approved User Account - PRMSU Iba</h3>

        <!-- Search Form -->
        <form class="search-form" method="GET" action="approved_users.php">
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="search-input form-control" placeholder="Search users..." value="<?= htmlspecialchars($searchTerm) ?>">
                <button type="submit" class="search-button">Search</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Level</th> <!-- Adjusted column to show Level -->
                        <th>College</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Username</th> <!-- Replace email with username if needed -->
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['first_name']) ?></td>
                <td><?= htmlspecialchars($row['last_name']) ?></td>
                <td>
                    <?php
                    // Determine user role based on school_type and related fields
                    $schoolType = htmlspecialchars($row['school_type']);
                    $userRole = '';

                    if ($schoolType == '1') {
                        $userRole = 'Student - College';
                    } elseif ($schoolType == '0') {
                        $userRole = 'Student - High School (Grade ' . htmlspecialchars($row['grade']) . ')';
                    } elseif ($schoolType == '2') {
                        $teachingStatus = htmlspecialchars($row['teaching_status']);
                        $departmentOrPosition = htmlspecialchars($row['department_or_position']);

                        // Customize the display for teaching or non-teaching employees
                        if ($teachingStatus == 'Teaching') {
                            $userRole = 'Employee (Teaching, ' . $departmentOrPosition . ')';
                        } else {
                            $userRole = 'Employee (Non-Teaching, ' . $departmentOrPosition . ')';
                        }
                    } elseif ($schoolType == '3' || empty($schoolType)) {
                        // Guest user (if school_type is 3 or empty)
                        $userRole = 'Guest';
                    }

                    echo $userRole;
                    ?>
                </td>
                <td><?= htmlspecialchars($row['college']) ?></td>
                <td><?= htmlspecialchars($row['course']) ?></td>
                <td><?= htmlspecialchars($row['year']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td>
                    <!-- Add View Button for actions -->
                    <a href="https://ramonianlostgems.com/admin/user_accounts/viewpage.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-info btn-sm">
                        <i class="fa fa-eye"></i> View Details
                    </a>

                    <!-- Add Delete Button -->
                    <button class="btn btn-danger btn-sm delete-user-btn" data-id="<?= htmlspecialchars($row['id']) ?>">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="8" class="no-data">No registered users found.</td>
        </tr>
    <?php endif; ?>
</tbody>

            </table>
        </div>
    </div>
    <?php require_once('../inc/footer.php') ?>
    <script>
    // Handle the delete user button click event
    document.querySelectorAll('.delete-user-btn').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');

            // Show confirmation dialog using SweetAlert
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send AJAX request to delete user
                    fetch('delete_approved.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'id=' + userId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            Swal.fire('Deleted!', 'The user account has been deleted.', 'success').then(() => {
                                // Reload the page to update the user list
                                location.reload();
                            });
                        } else {
                            // Show error message
                            Swal.fire('Error!', data.message || 'Failed to delete user.', 'error');
                        }
                    })
                    .catch(error => {
                        // Handle errors during the AJAX request
                        console.error('Error:', error);
                        Swal.fire('Error!', 'An error occurred while deleting the user.', 'error');
                    });
                }
            });
        });
    });
    </script>
</body>
</html>

<?php
$conn->close();
?>

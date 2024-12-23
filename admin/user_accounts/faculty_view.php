<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../../config.php';

// Database connection
$conn = new mysqli("localhost", "u450897284_root", "Lfisgemsdb1234", "u450897284_lfis_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize search term
$searchTerm = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Update SQL query to include search functionality based on relevant columns
$sql = "SELECT id, user_type, first_name, last_name, email, avatar, position, department, registration_date, status 
        FROM user_staff 
        WHERE CONCAT_WS(' ', first_name, last_name, email, user_type, position, department) LIKE '%$searchTerm%'";

$result = $conn->query($sql);
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
            max-width: 100%; /* Set to full width for larger tables */
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto; /* Allow horizontal scrolling if necessary */
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch; /* Smooth scrolling on mobile */
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: center;
            white-space: nowrap; /* Prevent wrapping of table cells */
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
            min-width: 100px;
            text-align: center;
            padding: 8px 12px;
            display: inline-block;
        }

        .btn-sm {
            font-size: 14px;
        }

        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .input-group {
            display: flex;
            align-items: center;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .search-input {
            border: 1px solid #ddd;
            padding: 10px;
            outline: none;
            width: 300px;
            flex-grow: 1;
        }

        .search-button {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 10px 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-button:hover {
            background-color: #218838;
        }

        .input-group-text {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 10px;
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

        .btn-group-container {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* Adjustments for responsiveness */
        @media (max-width: 768px) {
            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
        @media (min-width: 768px) {
            .btn-group-container {
                justify-content: center; /* Center buttons for larger screens */
            }
        }
    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php') ?>
<?php require_once('../inc/navigation.php') ?>
<br><br>

<section class="section">
    <div class="container">
        <h2>Employee Accounts</h2>

        <!-- Search Form -->
        <form class="search-form" method="GET" action="view_users.php">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="search-input form-control" placeholder="Search users..." value="<?= htmlspecialchars($searchTerm) ?>">
                <button type="submit" class="search-button">Search</button>
            </div>
        </form>

        <!-- Table wrapper for horizontal scrolling -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>User Type</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Position</th>
                        <th>Department</th>
                        <th>Registration Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= !empty($row['user_type']) ? htmlspecialchars($row['user_type']) : 'teaching' ?></td>
                <td><?= htmlspecialchars($row['first_name'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['last_name'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['email'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['position'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['department'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['registration_date'] ?? 'N/A') ?></td>
                <td>
                    <div class="btn-group-container">
                        <a href="https://ramonianlostgems.com/admin/user_accounts/viewfaculty.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> View Details
                        </a>

                        <?php if ($row['status'] !== 'active'): ?>  
                            <button id="approve-btn-<?= htmlspecialchars($row['id']) ?>" class="btn btn-success btn-sm approve-btn" onclick="approveUser(event, <?= htmlspecialchars($row['id']) ?>)">
                                <i class="fas fa-check"></i> Approve
                            </button>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-sm" disabled>Approved</button>
                        <?php endif; ?>

                        <button class="btn btn-danger btn-sm" onclick="deleteUser(<?= htmlspecialchars($row['id']) ?>)">
                            <i class="fas fa-trash-alt"></i> Delete
                        </button>
                    </div>
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
</section>

<script>
function deleteUser(id) {
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
            fetch('delete_users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ id }) // Pass the user ID
            })
            .then(response => response.text())
            .then(result => {
                if (result.trim() === '1') {  // Expecting '1' from backend
                    Swal.fire('Deleted!', 'The user has been deleted.', 'success')
                    .then(() => location.reload()); // Reload the page to reflect changes
                } else {
                    Swal.fire('Error!', 'An error occurred while deleting the user.', 'error');
                }
            })
            .catch((error) => {
                console.error('Delete error:', error);
                Swal.fire('Error!', 'An error occurred while deleting the user.', 'error');
            });
        }
    });
}

function approveUser(event, id) {
    event.preventDefault();
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
            fetch('approve_user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ user_id: id }) // Pass the user ID
            })
            .then(response => response.text())
            .then(result => {
                if (result.trim() === '1') {  // Expecting '1' from backend
                    const approveBtn = document.getElementById('approve-btn-' + id);
                    approveBtn.classList.replace('btn-success', 'btn-secondary');
                    approveBtn.innerHTML = 'Approved';
                    approveBtn.disabled = true;
                    Swal.fire('Approved!', 'The user has been approved.', 'success');
                } else {
                    Swal.fire('Error!', 'An error occurred while approving the user.', 'error');
                }
            })
            .catch((error) => {
                console.error('Approval error:', error);
                Swal.fire('Error!', 'An unexpected error occurred while approving the user.', 'error');
            });
        }
    });
}
</script>

<?php
$conn->close();
require_once('../inc/footer.php');
?>
</body>
</html>
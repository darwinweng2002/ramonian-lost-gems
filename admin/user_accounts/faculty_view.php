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
            max-width: 1200px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .table-responsive {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
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

        .btn-secondary {
            background-color: #6c757d;
            color: #fff;
            cursor: not-allowed;
        }

        .input-group {
            display: flex;
            align-items: center;
            border-radius: 8px;
            overflow: hidden;
        }

        .search-input {
            border: 1px solid #ddd;
            padding: 10px;
            outline: none;
            width: 200px;
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

    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php') ?>
<?php require_once('../inc/navigation.php') ?>
<br><br>

<section class="section">
    <div class="container">
        <h2>Registered Users</h2>

        <!-- Search Form -->
        <form class="search-form" method="GET" action="view_users.php">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="search-input form-control" placeholder="Search users..." value="<?= htmlspecialchars($searchTerm) ?>">
                <button type="submit" class="search-button">Search</button>
            </div>
        </form>

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
                                <td><?= htmlspecialchars($row['user_type'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['first_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['last_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['email'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['position'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['department'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['registration_date'] ?? 'N/A') ?></td>
                                <td>
                                    <div class="d-flex justify-content-center">
                                        <a href="view_user.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                        <?php if ($row['status'] !== 'approved'): ?>
                                            <button id="approve-btn-<?= htmlspecialchars($row['id']) ?>" class="btn btn-success btn-sm ms-2 approve-btn" onclick="approveUser(event, <?= htmlspecialchars($row['id']) ?>)">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-sm ms-2" disabled>Approved</button>
                                        <?php endif; ?>
                                        <button class="btn btn-danger btn-sm ms-2" onclick="deleteUser(<?= htmlspecialchars($row['id']) ?>)">
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
                body: new URLSearchParams({ id }) // Use URLSearchParams for POST parameters
            })
            .then(response => response.text())
            .then(result => {
                console.log('Delete result:', result); // For debugging
                if (result.trim() === '1') {  // Ensure the backend returns '1' on successful deletion
                    Swal.fire('Deleted!', 'The user has been deleted.', 'success')
                    .then(() => location.reload());
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
                body: new URLSearchParams({ user_id: id }) // Use URLSearchParams for POST parameters
            })
            .then(response => response.text())
            .then(result => {
                console.log('Approval result:', result); // For debugging
                if (result.trim() === '1') {
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
                Swal.fire('Error!', 'An unexpected error occurred.', 'error');
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
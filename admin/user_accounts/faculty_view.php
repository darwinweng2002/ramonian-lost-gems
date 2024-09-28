<?php
include '../../config.php';

// Database connection
$conn = new mysqli("localhost", "u450897284_root", "Lfisgemsdb1234", "u450897284_lfis_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Search functionality
$searchTerm = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Fetch all users
$sql = "SELECT * FROM user_staff WHERE CONCAT(first_name, ' ', last_name, email, user_type) LIKE '%$searchTerm%' AND status = 'pending'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Pending Accounts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .table th, .table td {
            text-align: center;
        }
        .search-input {
            margin-bottom: 20px;
            width: 300px;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 5px 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center">Registered Users</h2>

    <!-- Search Form -->
    <form class="d-flex mb-4" method="GET" action="">
        <input class="form-control search-input" type="text" name="search" placeholder="Search users..." value="<?= htmlspecialchars($searchTerm) ?>">
        <button class="btn btn-success ms-2" type="submit">Search</button>
    </form>

    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>College</th>
                    <th>Position</th>
                    <th>Department</th>
                    <th>Email</th>
                    <th>Actions</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['first_name']) ?></td>
                        <td><?= htmlspecialchars($row['last_name']) ?></td>
                        <td><?= htmlspecialchars($row['college']) ?></td>
                        <td><?= htmlspecialchars($row['position']) ?></td> <!-- Assuming position holds course/role -->
                        <td><?= htmlspecialchars($row['department']) ?></td> <!-- Assuming department for year -->
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td>
                            <div class="d-flex justify-content-center">
                                <a href="view_user.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-info btn-sm btn-action">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <button class="btn btn-success btn-sm ms-2 btn-action" onclick="approveUser(<?= $row['id'] ?>)">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button class="btn btn-danger btn-sm ms-2 btn-action" onclick="deleteUser(<?= $row['id'] ?>)">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-warning"><?= $row['status'] === 'pending' ? 'Pending' : 'Approved' ?></span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No registered users found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function approveUser(userId) {
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
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'user_id=' + userId
            })
            .then(response => response.text())
            .then(result => {
                if (result.trim() === '1') {
                    Swal.fire(
                        'Approved!',
                        'The user has been approved.',
                        'success'
                    ).then(() => {
                        location.reload(); // Reload the page to reflect changes
                    });
                } else {
                    Swal.fire(
                        'Error!',
                        'An error occurred while approving the user.',
                        'error'
                    );
                }
            })
            .catch(error => {
                Swal.fire(
                    'Error!',
                    'An unexpected error occurred.',
                    'error'
                );
            });
        }
    });
}

function deleteUser(userId) {
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
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'user_id=' + userId
            })
            .then(response => response.text())
            .then(result => {
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
            .catch(error => {
                Swal.fire(
                    'Error!',
                    'An unexpected error occurred.',
                    'error'
                );
            });
        }
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>

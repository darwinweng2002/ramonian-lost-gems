<?php
include '../../config.php';

// Database connection
$conn = new mysqli("localhost", "u450897284_root", "Lfisgemsdb1234", "u450897284_lfis_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle approve and delete actions
if (isset($_POST['approve'])) {
    $user_id = $_POST['user_id'];

    // Update user status to 'approved'
    $stmt = $conn->prepare("UPDATE user_staff SET status = 'approved' WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['delete'])) {
    $user_id = $_POST['user_id'];

    // Delete the user account
    $stmt = $conn->prepare("DELETE FROM user_staff WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

// Search functionality
$searchTerm = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Fetch all users with status 'pending'
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

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>College</th>
                <th>Course</th>
                <th>Year</th>
                <th>Section</th>
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
                    <td><?= htmlspecialchars($row['user_type']) ?></td> <!-- Assuming user_type is section -->
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <div class="d-flex justify-content-center">
                            <a href="view_user.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                            <form method="POST" class="ms-2">
                                <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="approve" class="btn btn-success btn-sm">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button type="submit" name="delete" class="btn btn-danger btn-sm ms-2">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </form>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-warning"><?= $row['status'] === 'pending' ? 'Pending' : 'Approved' ?></span>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="9" class="text-center">No registered users found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>

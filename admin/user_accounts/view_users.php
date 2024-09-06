<?php
include '../../config.php';

// Database connection
$conn = new mysqli("localhost", "root", "1234", "lfis_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize search term
$searchTerm = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Update SQL query to include search functionality
$sql = "SELECT * FROM user_member WHERE 
        CONCAT_WS(' ', first_name, last_name, course, year, section, email) LIKE '%$searchTerm%'";

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
    <style>
        body {
            background-color: #f4f4f4;
            color: #333;
            font-family: Arial, sans-serif;
        }

        .container {
            margin-top: 30px;
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

        thead th {
            background-color: #2C3E50;
            color: white;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tbody tr:hover {
            background-color: #f1f1f1;
        }

        .btn {
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s;
            color: #fff;
        }

        .btn-edit {
            background-color: #007bff;
            border: none;
        }

        .btn-edit:hover {
            background-color: #0056b3;
        }

        .btn-delete {
            background-color: #dc3545;
            border: none;
            position: relative;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }

        .btn-delete .spinner-border {
            display: none;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }

        .search-form {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
        }

        .search-input {
            border-radius: 4px;
            box-shadow: none;
            border: 1px solid #ddd;
            width: 200px;
            margin-right: 10px;
            padding: 8px;
        }

        .search-button {
            border-radius: 4px;
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 8px 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-button:hover {
            background-color: #218838;
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
<br>
<br>
<section class="section">
    <div class="container">
        <h2>Registered Users</h2>

        <!-- Search Form -->
        <form class="search-form" method="GET" action="view_users.php">
            <input type="text" name="search" class="search-input" placeholder="Search users..." value="<?= htmlspecialchars($searchTerm) ?>">
            <button type="submit" class="search-button">Search</button>
        </form>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>College</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Section</th>
                        <th>Verified</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['first_name']) ?></td>
                            <td><?= htmlspecialchars($row['last_name']) ?></td>
                            <td><?= htmlspecialchars($row['college']) ?></td>
                            <td><?= htmlspecialchars($row['course']) ?></td>
                            <td><?= htmlspecialchars($row['year']) ?></td>
                            <td><?= htmlspecialchars($row['section']) ?></td>
                            <td><?= $row['verified'] ? 'Yes' : 'No' ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td>
                                <div class="d-flex justify-content-center">
                                    <a href="edit_user.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-edit btn-sm me-2">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                    <button class="btn btn-delete btn-sm" onclick="deleteUser(event, <?= htmlspecialchars($row['id']) ?>)">
                                        <i class="fa fa-trash"></i> Delete
                                        <span class="spinner-border spinner-border-sm"></span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="no-data"><svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 25 25" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-x"><path d="M2 21a8 8 0 0 1 11.873-7"/><circle cx="10" cy="8" r="5"/><path d="m17 17 5 5"/><path d="m22 17-5 5"/></svg> No registered users found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<script>
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

</script>


<?php
$conn->close();
?>
<?php require_once('../inc/footer.php') ?>
</body>
</html>

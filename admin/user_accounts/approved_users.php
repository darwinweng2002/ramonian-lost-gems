<?php
include '../../config.php';

// Database connection
$conn = new mysqli("localhost", "u450897284_root", "Lfisgemsdb1234", "u450897284_lfis_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch only approved users along with their school ID file
$sql = "SELECT * FROM user_member WHERE status = 'approved'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once('../inc/header.php') ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<?php require_once('../inc/topBarNav.php') ?>
<?php require_once('../inc/navigation.php') ?>
    <div class="container">
        <br>
        <br>
        <br>
        <br>
        <h3>Approved Users - PRMSU Iba</h3>

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
                    <th>School ID</th> <!-- New column for school ID image -->
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['first_name']) ?></td>
                            <td><?= htmlspecialchars($row['last_name']) ?></td>
                            <td><?= htmlspecialchars($row['college']) ?></td>
                            <td><?= htmlspecialchars($row['course']) ?></td>
                            <td><?= htmlspecialchars($row['year']) ?></td>
                            <td><?= htmlspecialchars($row['section']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td>
                                <?php if (!empty($row['school_id_file'])): ?>
                                    <!-- Display the school ID image if it exists -->
                                    <img src="../../uploads/school_ids/<?= htmlspecialchars($row['school_id_file']) ?>" alt="School ID Image" style="width: 100px; height: auto;">
                                <?php else: ?>
                                    <!-- Fallback if no school ID is provided -->
                                    <span>No ID Uploaded</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">No approved users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php require_once('../inc/footer.php') ?>
</body>
</html>

<?php
$conn->close();
?>

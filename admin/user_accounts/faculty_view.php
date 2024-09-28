<?php
include '../../config.php';

// Database connection
$conn = new mysqli("localhost", "u450897284_root", "Lfisgemsdb1234", "u450897284_lfis_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['approve'])) {
    $user_id = $_POST['user_id'];

    // Update user status to 'active'
    $stmt = $conn->prepare("UPDATE user_staff SET status = 'active' WHERE id = ?");
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

// Fetch all users with status 'pending'
$result = $conn->query("SELECT * FROM user_staff WHERE status = 'pending'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Pending Accounts</title>
</head>
<body>
<h1>Pending Accounts for Approval</h1>
<table border="1">
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>User Type</th>
        <th>Action</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?= $row['first_name'] . ' ' . $row['last_name'] ?></td>
        <td><?= $row['email'] ?></td>
        <td><?= $row['user_type'] ?></td>
        <td>
            <form method="POST" action="">
                <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                <button type="submit" name="approve">Approve</button>
                <button type="submit" name="delete">Delete</button>
            </form>
        </td>
    </tr>
    <?php } ?>
</table>
</body>
</html>

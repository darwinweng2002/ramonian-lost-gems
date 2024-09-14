<?php
include '../../config.php'; // Ensure this includes your database connection

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db'); // Replace with your actual DB connection details

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch claim requests with user emails
$sql = "SELECT c.id, c.item_id, c.user_id, c.message, c.proof_file, c.status, c.course, c.year, c.section, mh.title, u.email
        FROM claims c
        LEFT JOIN message_history mh ON c.item_id = mh.id
        LEFT JOIN user_member u ON c.user_id = u.id"; // Ensure `users` table and `email` field match your actual table structure
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once('../inc/header.php') ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Claims</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            margin: 30px auto;
            width: 90%;
            max-width: 1200px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #3498db;
            color: #fff;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .action-btn {
            display: inline-block;
            padding: 5px 10px;
            font-size: 14px;
            color: #fff;
            background-color: #3498db;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
        }
        .action-btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <?php require_once('../inc/topBarNav.php') ?>
    <div class="container">
        <h1>Claim Requests</h1>
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Item Title</th>
                        <th>User Email</th> <!-- Updated Column Header -->
                        <th>Message</th>
                        <th>Proof File</th>
                        <th>Status</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Section</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['title'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['email'] ?? ''); ?></td> <!-- Updated Column Data -->
                            <td><?php echo htmlspecialchars($row['message'] ?? ''); ?></td>
                            <td>
                                <?php if ($row['proof_file']): ?>
                                    <a href="../uploads/proofs/<?php echo htmlspecialchars($row['proof_file'] ?? ''); ?>" class="action-btn" target="_blank">View Proof</a>
                                <?php else: ?>
                                    No proof
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['status'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['course'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['year'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['section'] ?? ''); ?></td>
                            <td>
                                <!-- Add any additional actions if needed, e.g., Edit, Delete -->
                                <a href="view_claim_details.php?id=<?php echo htmlspecialchars($row['id'] ?? ''); ?>" class="action-btn">View Details</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No claims found.</p>
        <?php endif; ?>
    </div>
    <?php require_once('../inc/footer.php') ?>
</body>
</html>

<?php
// Close the connection
$conn->close();
?>

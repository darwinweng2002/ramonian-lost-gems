<?php
include '../../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to fetch claim requests
$sql = "SELECT cr.id, cr.message, cr.proof_file, cr.course, cr.year, cr.section, cr.submitted_at, 
               u.username, i.title AS item_title 
        FROM claim_requests cr
        JOIN user_member u ON cr.user_id = u.id
        JOIN message_history i ON cr.item_id = i.id
        ORDER BY cr.submitted_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Claim Requests</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            padding-top: 70px;
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
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #ddd;
        }
        .view-proof {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Submitted Claim Requests</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Item Title</th>
                    <th>Message</th>
                    <th>Proof File</th>
                    <th>Course</th>
                    <th>Year</th>
                    <th>Section</th>
                    <th>Date Submitted</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $id = htmlspecialchars($row['id']);
                        $username = htmlspecialchars($row['username']);
                        $itemTitle = htmlspecialchars($row['item_title']);
                        $message = htmlspecialchars($row['message']);
                        $proofFile = htmlspecialchars($row['proof_file']);
                        $course = htmlspecialchars($row['course']);
                        $year = htmlspecialchars($row['year']);
                        $section = htmlspecialchars($row['section']);
                        $submittedAt = htmlspecialchars($row['submitted_at']);
                        $proofFileLink = $proofFile ? "<a href='" . base_url . 'uploads/proofs/' . $proofFile . "' class='view-proof' target='_blank'>View Proof</a>" : "No Proof";

                        echo "<tr>
                                <td>{$id}</td>
                                <td>{$username}</td>
                                <td>{$itemTitle}</td>
                                <td>{$message}</td>
                                <td>{$proofFileLink}</td>
                                <td>{$course}</td>
                                <td>{$year}</td>
                                <td>{$section}</td>
                                <td>{$submittedAt}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='9'>No claim requests found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php require_once('../inc/footer.php'); ?>
</body>
</html>

<?php
$conn->close();
?>

<?php
// Fetch user details from the database
$sql = "SELECT first_name, last_name, verified, email, school_id_file, registration_date, college, course, year, section, status 
        FROM user_member 
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found";
    exit;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Your CSS styling */
        .school-id-image {
            width: 100%;
            max-width: 300px;
            height: auto;
            display: block;
            margin: 20px auto;
            border: 2px solid #ddd;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>User Details</h2>
    <div class="user-info">
        <div><span class="label">First Name: </span><?= htmlspecialchars($user['first_name']) ?></div>
        <div><span class="label">Last Name: </span><?= htmlspecialchars($user['last_name']) ?></div>
        <div><span class="label">Email: </span><?= htmlspecialchars($user['email']) ?></div>
        <div><span class="label">Verified: </span><?= $user['verified'] ? 'Yes' : 'No' ?></div>
        <div><span class="label">College: </span><?= htmlspecialchars($user['college']) ?></div>
        <div><span class="label">Course: </span><?= htmlspecialchars($user['course']) ?></div>
        <div><span class="label">Year: </span><?= htmlspecialchars($user['year']) ?></div>
        <div><span class="label">Section: </span><?= htmlspecialchars($user['section']) ?></div>
        <div><span class="label">Status: </span><?= htmlspecialchars($user['status']) ?></div>
        <div><span class="label">Registration Date: </span><?= htmlspecialchars($user['registration_date']) ?></div>

        <!-- Display the uploaded School ID image -->
        <div>
            <span class="label">School ID: </span>
            <?php
            $schoolIdPath = 'uploads/school_ids/' . htmlspecialchars($user['school_id_file']);

            if (!empty($user['school_id_file']) && file_exists($schoolIdPath)) {
                echo '<img src="' . $schoolIdPath . '" alt="School ID" class="school-id-image">';
            } else {
                echo '<p>No School ID uploaded.</p>';
            }
            ?>
        </div>
    </div>
    <div class="text-center mt-4">
        <a href="view_users.php" class="btn btn-primary">Back to Users List</a>
    </div>
</div>

</body>
</html>
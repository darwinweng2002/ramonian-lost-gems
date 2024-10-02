<?php
include '../../config.php';

// Database connection
$conn = new mysqli("localhost", "u450897284_root", "Lfisgemsdb1234", "u450897284_lfis_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user ID is provided in the URL
if (isset($_GET['id'])) {
    $userId = $conn->real_escape_string($_GET['id']);

    // Fetch the user details from the database
    $sql = "SELECT first_name, last_name, email, user_type, department, position, profile_image 
            FROM user_staff 
            WHERE id = '$userId'";

    $result = $conn->query($sql);

    // Check if the user exists
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        die("User not found.");
    }
} else {
    die("No user ID provided.");
}
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once('../inc/header.php'); ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Employee Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            padding: 20px;
        }

        .container {
            margin: 30px auto;
            max-width: 800px;
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

        .user-details {
            list-style-type: none;
            padding: 0;
            margin-bottom: 20px;
        }

        .user-details li {
            margin-bottom: 10px;
            font-size: 18px;
        }

        .user-details span {
            font-weight: bold;
        }

        .uploaded-id-label {
            font-weight: bold;
            margin-top: 20px;
            text-align: center;
            display: block;
            font-size: 20px;
            color: #444;
        }

        .profile-image {
            text-align: center;
            margin-top: 10px;
        }

        .profile-image img {
            max-width: 150px;
        }

        .back-btn {
            margin-top: 20px;
            text-align: center;
        }

        .back-btn a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .back-btn a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<section class="section">
    <div class="container">
        <h2>Employee Details</h2>

        <ul class="user-details">
    <li><span>First Name:</span> <?= htmlspecialchars($user['first_name']) ?></li>
    <li><span>Last Name:</span> <?= htmlspecialchars($user['last_name']) ?></li>
    <li><span>Email:</span> <?= htmlspecialchars($user['email']) ?></li>
    <li><span>User Type:</span> <?= !empty($user['user_type']) ? htmlspecialchars($user['user_type']) : 'teaching' ?></li>
    <li><span>Department:</span> <?= htmlspecialchars($user['department'] ?? 'N/A') ?></li>
    <li><span>Position:</span> <?= htmlspecialchars($user['position'] ?? 'N/A') ?></li>
</ul>


        <!-- Add the Uploaded ID label and move the profile image here -->
        <span class="uploaded-id-label">Uploaded ID</span>
        <div class="profile-image">
            <?php if (!empty($user['profile_image'])): ?>
                <img src="../../uploads/profiles/<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile Image">
            <?php else: ?>
                <img src="https://via.placeholder.com/150" alt="No Profile Image">
            <?php endif; ?>
        </div>

        <div class="back-btn">
            <a href="faculty_view.php"><i class="fas fa-arrow-left"></i> Back to User List</a>
        </div>
    </div>
</section>

<?php
$conn->close();
require_once('../inc/footer.php');
?>
</body>
</html>
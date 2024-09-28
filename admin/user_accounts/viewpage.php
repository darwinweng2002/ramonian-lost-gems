<?php
include '../../config.php';

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user ID from URL
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id <= 0) {
    echo "Invalid user ID";
    exit;
}

// Fetch user details from the database
$sql = "SELECT first_name, last_name, email, school_id_file, registration_date, college, course, year, section, status 
        FROM user_member 
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
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
    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #000;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .user-info p {
            margin: 10px 0;
        }
        .proof-image, .id-image {
            max-width: 200px;
            max-height: 150px;
            width: auto;
            height: auto;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .proof-image:hover, .id-image:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>

<div class="container">
    <h2>User Details</h2>
    <div class="user-info">
        <p><strong>First Name:</strong> <?= htmlspecialchars($user['first_name']) ?></p>
        <p><strong>Last Name:</strong> <?= htmlspecialchars($user['last_name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>College:</strong> <?= htmlspecialchars($user['college']) ?></p>
        <p><strong>Course:</strong> <?= htmlspecialchars($user['course']) ?></p>
        <p><strong>Year:</strong> <?= htmlspecialchars($user['year']) ?></p>
        <p><strong>Section:</strong> <?= htmlspecialchars($user['section']) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($user['status']) ?></p>
        <p><strong>Registration Date:</strong> <?= htmlspecialchars($user['registration_date']) ?></p>

        <!-- Display School ID -->
        <p><strong>School ID:</strong></p>
        <?php
        // Check if school_id_file is not NULL
        if (!empty($user['school_id_file'])) {
            // Ensure the path is correctly generated
            $schoolIdPath = '/' . htmlspecialchars($user['school_id_file']);  // Add leading '/' to make it relative to the root
            echo '<p>Image Path: ' . $schoolIdPath . '</p>'; // Debugging line to show the path
            echo '<a href="' . $schoolIdPath . '" data-lightbox="school-id" data-title="School ID">
                    <img src="' . $schoolIdPath . '" alt="School ID" class="proof-image" />
                  </a>';
        } else {
            echo '<p>No School ID uploaded.</p>';
        }
        ?>
    </div>
    <div class="text-center mt-4">
        <a href="view_users.php" class="btn btn-primary">Back to Users List</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox-plus-jquery.min.js"></script>
</body>
</html>

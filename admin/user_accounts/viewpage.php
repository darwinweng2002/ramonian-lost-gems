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

// Fetch user details from the database, including the fields for employees and high school students
$sql = "SELECT first_name, last_name, email, school_id_file, registration_date, 
               college, course, year, status, school_type, grade, teaching_status, department_or_position 
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
<?php require_once('../inc/header.php') ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #f7f7f7;
            color: #333;
            padding: 40px 20px;
        }

        .container {
            max-width: 700px;
            margin: auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            font-size: 28px;
            color: #333;
            margin-bottom: 25px;
        }

        .user-info {
            margin-top: 20px;
        }

        .user-info p {
            margin-bottom: 12px;
            font-size: 16px;
        }

        .user-info p strong {
            color: #555;
        }

        .proof-image {
            width: 100%;
            max-width: 300px;
            margin: 15px auto;
            display: block;
            border-radius: 8px;
            border: 2px solid #e1e1e1;
            transition: transform 0.3s ease;
        }

        .proof-image:hover {
            transform: scale(1.05);
        }

        .btn {
            border-radius: 8px;
            padding: 12px 20px;
            font-size: 16px;
            text-transform: uppercase;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .back-link {
            margin-top: 30px;
            text-align: center;
        }

        /* Lightbox styling */
        .lightbox {
            max-width: 100%;
            max-height: 100%;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            h2 {
                font-size: 24px;
            }

            .btn {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php') ?>
<?php require_once('../inc/navigation.php') ?> 
<div class="container">
    <h2>User Details</h2>
    <div class="user-info">
        <p><strong>First Name:</strong> <?= htmlspecialchars($user['first_name']) ?></p>
        <p><strong>Last Name:</strong> <?= htmlspecialchars($user['last_name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>

        <!-- Display Level based on school_type -->
        <?php if ($user['school_type'] == '1'): ?>
    <!-- College Fields -->
    <p id="collegeField"><strong>College:</strong> <?= htmlspecialchars($user['college']) ?></p>
    <p id="courseField"><strong>Course:</strong> <?= htmlspecialchars($user['course']) ?></p>
    <p id="yearField"><strong>Year:</strong> <?= htmlspecialchars($user['year']) ?></p>

<?php elseif ($user['school_type'] == '0'): ?>
    <!-- High School Fields -->
    <p><strong>User Role:</strong> Student - High School (Grade <?= htmlspecialchars($user['grade']) ?>)</p>

<?php elseif ($user['school_type'] == '2'): ?>
    <!-- Employee Fields -->
    <p><strong>Role:</strong> Employee</p>
    <p><strong>Teaching Status:</strong> <?= htmlspecialchars($user['teaching_status']) ?></p>
    <p><strong>Department/Position:</strong> <?= htmlspecialchars($user['department_or_position']) ?></p>

<?php elseif ($user['school_type'] == '3'): ?>
    <!-- Guest User Fields -->
    <p><strong>User Role:</strong> Guest</p>
<?php endif; ?>


        <p><strong>Status:</strong> <?= htmlspecialchars($user['status']) ?></p>
        <p><strong>Registration Date:</strong> <?= htmlspecialchars($user['registration_date']) ?></p>

        <!-- Display School ID -->
        <p><strong>School ID:</strong></p>
        <?php
        if (!empty($user['school_id_file'])) {
            $schoolIdPath = '/' . htmlspecialchars($user['school_id_file']);
            echo '<a href="' . $schoolIdPath . '" data-lightbox="school-id" data-title="School ID">
                    <img src="' . $schoolIdPath . '" alt="School ID" class="proof-image" />
                  </a>';
        } else {
            echo '<p>No School ID uploaded.</p>';
        }
        ?>
    </div>
    <div class="back-link">
        <a href="javascript:void(0);" onclick="history.back();" class="btn btn-primary">Back to Users List</a>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox-plus-jquery.min.js"></script>

<!-- JavaScript to hide fields if their value is 'N/A' -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Hide the fields if the value is 'N/A'
        const collegeField = document.getElementById('collegeField');
        const courseField = document.getElementById('courseField');
        const yearField = document.getElementById('yearField');

        if (collegeField.textContent.includes('N/A')) {
            collegeField.style.display = 'none';
        }
        if (courseField.textContent.includes('N/A')) {
            courseField.style.display = 'none';
        }
        if (yearField.textContent.includes('N/A')) {
            yearField.style.display = 'none';
        }
    });
</script>

</body>
</html>

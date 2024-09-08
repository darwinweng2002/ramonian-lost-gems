<?php
include '../config.php';

// Ensure user_id is set in session
if (!isset($_SESSION['user_id'])) {
    echo '<script>alert("User not logged in."); location.replace("./login.php")</script>';
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch item details
if (isset($_GET['item_id']) && $_GET['item_id'] > 0) {
    $item_id = $_GET['item_id'];

    $qry = $conn->prepare("
        SELECT 
            i.id, 
            i.title, 
            i.fullname,
            i.contact,
            i.description,
            i.landmark,
            i.time_found,
            COALESCE((SELECT c.name FROM category_list c WHERE c.id = i.category_id), 'N/A') AS category
        FROM 
            item_list i 
        WHERE 
            i.id = ?
    ");
    $qry->bind_param("i", $item_id);
    $qry->execute();
    $result = $qry->get_result();
    
    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
    } else {
        echo '<script>alert("Item ID is not valid."); location.replace("./?page=items")</script>';
        exit;
    }
} else {
    echo '<script>alert("Item ID is Required."); location.replace("./?page=items")</script>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id = $_POST['item_id'];
    $additional_info = $_POST['additional_info'];
    $email = $_POST['email'];
    $course = $_POST['course'];
    $year = $_POST['year'];
    $section = $_POST['section'];
    $college = $_POST['college'];

    // Database insertion
    $stmt = $conn->prepare("INSERT INTO claims (user_id, item_id, additional_info, email, course, year, section, college) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissssss", $user_id, $item_id, $additional_info, $email, $course, $year, $section, $college);

    if ($stmt->execute()) {
        echo '<script>alert("Claim request submitted successfully."); location.replace("./dashboard.php")</script>';
    } else {
        echo '<script>alert("An error occurred while submitting your claim.");</script>';
    }
    $stmt->close();
}

// Fetch the user's information from the database
$stmt = $conn->prepare("SELECT first_name, last_name, course, year, section, email, college FROM user_member WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($first_name, $last_name, $course, $year, $section, $email, $college);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once('../inc/header.php') ?>
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .content {
            flex: 1;
            padding: 20px;
        }

        form {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-top: 0;
        }

        label {
            display: block;
            margin: 10px 0 5px;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
    </style>
</head>
<body>
    <?php require_once('../inc/topBarNav.php') ?>
    <div class="content">
        <br>
        <br>
        <form method="post">
            <input type="hidden" name="item_id" value="<?= htmlspecialchars($item['id'] ?? '') ?>">
            <h2><?= htmlspecialchars($item['title'] ?? 'Title not available') ?> | <?= htmlspecialchars($item['category'] ?? 'Category not available') ?></h2>

            <label for="email">Username:</label>
            <input type="text" name="email" id="email" value="<?= htmlspecialchars($email) ?>" readonly>

            <label for="college">College:</label>
            <input type="text" name="college" id="college" value="<?= htmlspecialchars($college) ?>" readonly>

            <label for="course">Course:</label>
            <input type="text" name="course" id="course" value="<?= htmlspecialchars($course) ?>" readonly>

            <label for="year">Year:</label>
            <input type="text" name="year" id="year" value="<?= htmlspecialchars($year) ?>" readonly>

            <label for="section">Section:</label>
            <input type="text" name="section" id="section" value="<?= htmlspecialchars($section) ?>" readonly>

            <label for="additional_info">Additional Information:</label>
            <textarea name="additional_info" id="additional_info"></textarea>

            <button type="submit">Submit Claim</button>
        </form>
    </div>
    <?php require_once('../inc/footer.php') ?>
</body>
</html>

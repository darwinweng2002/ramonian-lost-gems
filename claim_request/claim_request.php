<?php
include '../config.php';  // Ensure this includes your database connection

// Database connection
<<<<<<< HEAD
$conn = new mysqli('localhost', 'root', '1234', 'lfis_db'); // Replace with your actual DB connection details
=======
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db'); // Replace with your actual DB connection details
>>>>>>> 9a847130ad55804bf61cf6bbb72da1dde26168f9

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get item ID from query string
$itemId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validate item ID
if ($itemId <= 0) {
    die("Invalid item ID.");
}

// SQL query to get item details
$sql = "SELECT mh.id, mh.message, mi.image_path, mh.title, mh.landmark, mh.time_found
        FROM message_history mh
        LEFT JOIN message_images mi ON mh.id = mi.message_id
        WHERE mh.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $itemId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once('../inc/header.php') ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Request</title>
    <style>
        /* Add your styles here */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            padding-top: 70px;
            background-color: #f4f4f4;
        }
        .container {
            margin: 30px auto;
            width: 90%;
            max-width: 1200px;
        }
        .form-box {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .form-box img {
            max-width: 100%;
            border-radius: 5px;
        }
        .form-box label {
            display: block;
            margin-top: 10px;
        }
        .form-box input[type="text"], .form-box textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-box input[type="file"] {
            margin-top: 10px;
        }
        .form-box button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #3498db;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }
        .form-box button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <?php require_once('../inc/topBarNav.php') ?>
    <div class="container">
        <h1>Claim Request</h1>
        <?php
        if ($result->num_rows > 0) {
            $item = $result->fetch_assoc();
            $message = htmlspecialchars($item['message']);
            $title = htmlspecialchars($item['title']);
            $landmark = htmlspecialchars($item['landmark']);
            $timeFound = htmlspecialchars($item['time_found']);
            $imagePath = $item['image_path'] ? base_url . 'uploads/items/' . htmlspecialchars($item['image_path']) : '';

            echo "<div class='form-box'>";
            echo "<h2>Item Title: " . $title . "</h2>";
            echo "<p><strong>Landmark:</strong> " . $landmark . "</p>";
            echo "<p><strong>Date and Time Found:</strong> " . $timeFound . "</p>";
            echo "<p><strong>Description:</strong> " . $message . "</p>";
            if ($imagePath) {
                echo "<img src='" . $imagePath . "' alt='Item Image'>";
            }
            ?>
            <form action="process_claim.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($itemId); ?>">
                <label for="proof">Upload Proof of Ownership:</label>
                <input type="file" id="proof" name="proof" required>
                <label for="message">Additional Information:</label>
                <textarea id="message" name="message" rows="4" required></textarea>
                <button type="submit">Submit Claim</button>
            </form>
            <?php
            echo "</div>";
        } else {
            echo "<p>Item not found.</p>";
        }
        ?>
    </div>
    <?php require_once('../inc/footer.php') ?>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

<?php
include '../../config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize $result as null
$result = null;

if (isset($_GET['id'])) {
    $itemId = $_GET['id'];

    // SQL query to get missing item details and associated images
    $sql = "
    SELECT mi.id, mi.title, mi.description, mi.last_seen_location, mi.time_missing, mi.status, mi.contact, mi.owner, user_info.first_name, user_info.college, user_info.email, user_info.avatar, c.name AS category_name, imi.image_path
    FROM missing_items mi
    LEFT JOIN (
        -- Fetch from user_member
        SELECT id AS user_id, first_name, college, email, avatar FROM user_member
        UNION
        -- Fetch from user_staff
        SELECT id AS user_id, first_name, department AS college, email, avatar FROM user_staff
    ) user_info ON mi.user_id = user_info.user_id
    LEFT JOIN missing_item_images imi ON mi.id = imi.missing_item_id
    LEFT JOIN categories c ON mi.category_id = c.id
    WHERE mi.id = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Missing Items - Admin View</title>
    <?php require_once('../inc/header.php'); ?>
    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet">
    <style>
        /* Your existing styles */
    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php') ?>
<?php require_once('../inc/navigation.php') ?> 

<div class="container">
    <h1>View Missing Item Details</h1>
    <?php
    if ($result->num_rows > 0) {
        $items = [];
        while ($row = $result->fetch_assoc()) {
            if (!isset($items[$row['id']])) {
                $items[$row['id']] = [
                    'description' => $row['description'],
                    'last_seen_location' => $row['last_seen_location'],
                    'owner' => $row['owner'],
                    'time_missing' => $row['time_missing'],
                    'title' => $row['title'],
                    'status' => $row['status'],
                    'first_name' => $row['first_name'],
                    'college' => $row['college'],
                    'email' => $row['email'],
                    'avatar' => $row['avatar'],
                    'images' => [],
                    'contact' => $row['contact'],
                    'category_name' => $row['category_name']
                ];
            }
            if ($row['image_path']) {
                $fullImagePath = base_url . 'uploads/missing_items/' . $row['image_path'];
                $items[$row['id']]['images'][] = $fullImagePath;
            }
        }

        foreach ($items as $itemId => $itemData) {
            $firstName = htmlspecialchars($itemData['first_name'] ?? '');
            $email = htmlspecialchars($itemData['email'] ?? '');
            $college = htmlspecialchars($itemData['college'] ?? '');
            $title = htmlspecialchars($itemData['title'] ?? '');
            $lastSeenLocation = htmlspecialchars($itemData['last_seen_location'] ?? '');
            $description = htmlspecialchars($itemData['description'] ?? '');
            $owner = htmlspecialchars($itemData['owner'] ?? '');
            $avatar = htmlspecialchars($itemData['avatar'] ?? '');
            $timeMissing = htmlspecialchars($itemData['time_missing'] ?? '');
            $contact = htmlspecialchars($itemData['contact'] ?? '');
            $categoryName = htmlspecialchars($itemData['category_name'] ?? '');
            $status = intval($itemData['status']);

            echo "<div class='message-box'>";

            if ($avatar) {
                $fullAvatar = base_url . 'uploads/avatars/' . $avatar;
                echo "<img src='" . htmlspecialchars($fullAvatar) . "' alt='Avatar' class='avatar'>";
            } else {
                echo "<img src='uploads/avatars/default-avatar.png' alt='Default Avatar' class='avatar'>";
            }

            echo "<p><strong>Item Name:</strong> " . $title . "</p>";
            echo "<p><strong>Owner Name:</strong> " . $owner . "</p>";

            // Check if user is a guest
            if (empty($firstName) || empty($college)) {
                echo "<p><strong>User Info:</strong> Guest User</p>";
            } else {
                echo "<p><strong>User Name:</strong> " . $firstName . " (" . $email . ")</p>";
                echo "<p><strong>College:</strong> " . $college . "</p>";
            }

            echo "<p><strong>Last Seen Location:</strong> " . $lastSeenLocation . "</p>";
            echo "<p><strong>Date and time the item was lost:</strong> " . $timeMissing . "</p>";
            echo "<p><strong>Description:</strong> " . $description . "</p>";
            echo "<p><strong>Category:</strong> " . $categoryName . "</p>";
            echo "<p><strong>Contact:</strong> " . $contact . "</p>";

            // Status dropdown
            echo "<div class='form-group col-lg-12 col-md-12 col-sm-12 col-xs-12'>";
            echo "<label for='status' class='control-label'>Status</label>";
            echo "<select name='status' id='status-" . $itemId . "' class='form-select form-select-sm rounded-0' required='required'>";
            echo "<option value='0' " . ($status == 0 ? 'selected' : '') . ">Pending</option>";
            echo "<option value='1' " . ($status == 1 ? 'selected' : '') . ">Published</option>";
            echo "<option value='2' " . ($status == 2 ? 'selected' : '') . ">Claimed</option>";
            echo "<option value='3' " . ($status == 3 ? 'selected' : '') . ">Surrendered</option>";
            echo "</select>";
            echo "<button class='btn btn-primary save-status-btn' data-id='" . $itemId . "'>Save Status</button>";
            echo "</div>";

            echo "<dt class='text-muted'>Status</dt>";
            if ($status == 1) {
                echo "<span class='badge bg-primary px-3 rounded-pill'>Published</span>";
            } elseif ($status == 2) {
                echo "<span class='badge bg-success px-3 rounded-pill'>Claimed</span>";
            } elseif ($status == 3) {
                echo "<span class='badge bg-secondary px-3 rounded-pill'>Surrendered</span>";
            } else {
                echo "<span class='badge bg-secondary px-3 rounded-pill'>Pending</span>";
            }

            if (!empty($itemData['images'])) {
                echo "<p><strong>Images:</strong></p>";
                echo "<div class='image-grid'>";
                foreach ($itemData['images'] as $imagePath) {
                    echo "<a href='" . htmlspecialchars($imagePath) . "' data-lightbox='item-" . htmlspecialchars($itemId) . "' data-title='Image'><img src='" . htmlspecialchars($imagePath) . "' alt='Image'></a>";
                }
                echo "</div>";
            } else {
                echo "<p>No images available.</p>";
            }

            echo "<button class='publish-btn' data-id='" . htmlspecialchars($itemId) . "'>Publish</button>";
            echo "<button class='delete-btn' data-id='" . htmlspecialchars($itemId) . "'>Delete</button>";
            echo "</div>";
        }
    } else {
        echo "<p>No details available for this item.</p>";
    }
    ?>
</div>

<!-- Include JavaScript files -->
<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox-plus-jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Your existing JavaScript code for status updates and deletion -->
<?php require_once('../inc/footer.php'); ?>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

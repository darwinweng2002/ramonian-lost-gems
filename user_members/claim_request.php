<?php
include '../config.php';

$targetDir = "../uploads/items/"; // Update directory
$uploadStatus = 1;
$allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

// Create the uploads directory if it does not exist
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

// Ensure user_id is set in session
if (!isset($_SESSION['user_id'])) {
    echo '<!DOCTYPE html><html lang="en"><head><title>Error</title>';
    echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
    echo '</head><body>';
    echo '<script>
        Swal.fire({
            title: "Error",
            text: "User not logged in.",
            icon: "error",
            confirmButtonText: "OK",
            didClose: () => { window.location.replace("./login.php"); }
        });
    </script>';
    echo '</body></html>';
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch item details
if (isset($_GET['item_id']) && $_GET['item_id'] > 0) {
    $item_id = $_GET['item_id'];

    // Check if the user has already submitted a claim for this item
    $qry = $conn->prepare("SELECT id FROM claims WHERE user_id = ? AND item_id = ?");
    $qry->bind_param("ii", $user_id, $item_id);
    $qry->execute();
    $qry->store_result();
    
    if ($qry->num_rows > 0) {
        echo '<!DOCTYPE html><html lang="en"><head><title>Duplicate Claim</title>';
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '</head><body>';
        echo '<script>
            Swal.fire({
                title: "Duplicate Claim",
                text: "You have already submitted a claim request for this item.",
                icon: "warning",
                confirmButtonText: "OK",
                didClose: () => { window.location.replace("./dashboard.php"); }
            });
        </script>';
        echo '</body></html>';
        $qry->close();
        exit;
    }
    $qry->close();

    // Fetch item details
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
        echo '<!DOCTYPE html><html lang="en"><head><title>Invalid Item</title>';
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '</head><body>';
        echo '<script>
            Swal.fire({
                title: "Invalid Item",
                text: "Item ID is not valid.",
                icon: "error",
                confirmButtonText: "OK",
                didClose: () => { window.location.replace("./?page=items"); }
            });
        </script>';
        echo '</body></html>';
        exit;
    }
} else {
    echo '<!DOCTYPE html><html lang="en"><head><title>Missing Item ID</title>';
    echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
    echo '</head><body>';
    echo '<script>
        Swal.fire({
            title: "Missing Item ID",
            text: "Item ID is Required.",
            icon: "error",
            confirmButtonText: "OK",
            didClose: () => { window.location.replace("./?page=items"); }
        });
    </script>';
    echo '</body></html>';
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

    // Database insertion for the claim
    $stmt = $conn->prepare("INSERT INTO claims (user_id, item_id, additional_info, email, course, year, section, college) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissssss", $user_id, $item_id, $additional_info, $email, $course, $year, $section, $college);

    // Output HTML header first
    echo '<!DOCTYPE html><html lang="en"><head><title>Redirecting...</title>';
    echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
    echo '</head><body>';

    if ($stmt->execute()) {
        // Handle file uploads
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $fileName = basename($_FILES['images']['name'][$key]);
            $targetFile = $targetDir . $fileName;
            $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            // Check if file is an image
            $check = getimagesize($_FILES['images']['tmp_name'][$key]);
            if ($check === false) {
                echo "File is not an image.";
                $uploadStatus = 0;
            }

            // Check file size (e.g., limit to 5MB)
            if ($_FILES['images']['size'][$key] > 5000000) {
                echo "Sorry, your file is too large.";
                $uploadStatus = 0;
            }

            // Check file type
            if (!in_array($fileType, $allowedTypes)) {
                echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                $uploadStatus = 0;
            }

            // Check if $uploadStatus is set to 0 by an error
            if ($uploadStatus == 1) {
                if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $targetFile)) {
                    echo "The file " . htmlspecialchars($fileName) . " has been uploaded.<br>";

                    // Insert image path into the claims_images table
                    $stmt = $conn->prepare("INSERT INTO claim_images (claim_id, image_path) VALUES (?, ?)");
                    $stmt->bind_param("is", $claim_id, $fileName);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    echo "Sorry, there was an error uploading your file.";
                }
            }
        }

        echo '<script>
            Swal.fire({
                title: "Claim Submitted",
                html: `
                    <p>Thank you for your claim request.</p>
                    <p>Your submission, including the information and proof of ownership you provided, will be reviewed by our admins.</p>
                    <p>They will verify if you are the legitimate owner of the missing or found item.</p>
                    <p>You will be notified once the verification process is complete.</p>
                `,
                icon: "success",
                confirmButtonText: "OK",
                didClose: () => { window.location.replace("./dashboard.php"); }
            });
        </script>';
    } else {
        echo '<script>
            Swal.fire({
                title: "Error",
                text: "An error occurred while submitting your claim.",
                icon: "error",
                confirmButtonText: "OK"
            });
        </script>';
    }
    $stmt->close();
    echo '</body></html>';
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Claim Item</title>
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
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-group input[type="file"] {
            border: none;
        }

        .form-group button {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 4px;
        }

        .form-group button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="content">
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['id']); ?>">
            <h2>Claim Request for <?php echo htmlspecialchars($item['title']); ?></h2>
            <div class="form-group">
                <label for="additional_info">Additional Information:</label>
                <textarea id="additional_info" name="additional_info" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="images">Upload Images (optional):</label>
                <input type="file" id="images" name="images[]" multiple>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="course">Course:</label>
                <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($course); ?>" required>
            </div>
            <div class="form-group">
                <label for="year">Year:</label>
                <input type="text" id="year" name="year" value="<?php echo htmlspecialchars($year); ?>" required>
            </div>
            <div class="form-group">
                <label for="section">Section:</label>
                <input type="text" id="section" name="section" value="<?php echo htmlspecialchars($section); ?>" required>
            </div>
            <div class="form-group">
                <label for="college">College:</label>
                <input type="text" id="college" name="college" value="<?php echo htmlspecialchars($college); ?>" required>
            </div>
            <div class="form-group">
                <button type="submit">Submit Claim</button>
            </div>
        </form>
    </div>
</body>
</html>

<?php
include '../config.php';

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

    // Database insertion
    $stmt = $conn->prepare("INSERT INTO claims (user_id, item_id, additional_info, email, course, year, section, college) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissssss", $user_id, $item_id, $additional_info, $email, $course, $year, $section, $college);

    // Output HTML header first
    echo '<!DOCTYPE html><html lang="en"><head><title>Redirecting...</title>';
    echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
    echo '</head><body>';

    if ($stmt->execute()) {
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
    }else {
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

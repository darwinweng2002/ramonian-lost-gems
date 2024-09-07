<?php
$targetDir = "uploadz/";
$uploadStatus = 1;
$allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

// Create the uploads directory if it does not exist
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

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
    if ($uploadStatus == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $targetFile)) {
            echo "The file " . htmlspecialchars($fileName) . " has been uploaded.<br>";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}
?>
<a href="admin.php">Go to Admin Panel</a>

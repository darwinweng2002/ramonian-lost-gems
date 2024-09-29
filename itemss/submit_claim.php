<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in as either a regular user or staff
if (!isset($_SESSION['user_id']) && !isset($_SESSION['staff_id'])) {
    header('Location: login.php');
    exit();
}

// Determine user type and fetch user info accordingly
if (isset($_SESSION['user_id'])) {
    // Regular user
    $claimantId = $_SESSION['user_id'];
    $isStaff = false;
    $sqlClaimant = "SELECT first_name, last_name, email, college, course, year, section FROM user_member WHERE id = ?";
} elseif (isset($_SESSION['staff_id'])) {
    // Staff user
    $claimantId = $_SESSION['staff_id'];
    $isStaff = true;
    $sqlClaimant = "SELECT first_name, last_name, email, department AS college, position FROM user_staff WHERE id = ?";
}

// Fetch claimant's user info
$stmtClaimant = $conn->prepare($sqlClaimant);
$stmtClaimant->bind_param('i', $claimantId);
$stmtClaimant->execute();
$claimantResult = $stmtClaimant->get_result();
$claimantData = $claimantResult->fetch_assoc();

// Process the form submission to save the claim request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_description = $_POST['item_description'];
    $date_lost = $_POST['date_lost'];
    $location_lost = $_POST['location_lost'];
    $proof_of_ownership = $_FILES['proof_of_ownership']['name'];
    $security_question = $_POST['security_question'];
    $personal_id = $_FILES['personal_id']['name'];
    
    // File Uploads (Move uploaded files to the appropriate folder)
    $target_dir = "../uploads/claims/";
    
    // Upload proof of ownership file
    if (!empty($proof_of_ownership)) {
        $target_file_ownership = $target_dir . basename($proof_of_ownership);
        move_uploaded_file($_FILES["proof_of_ownership"]["tmp_name"], $target_file_ownership);
    }
    
    // Upload personal ID file
    if (!empty($personal_id)) {
        $target_file_id = $target_dir . basename($personal_id);
        move_uploaded_file($_FILES["personal_id"]["tmp_name"], $target_file_id);
    }

    // Insert the claim into the `claimer` table, determining whether it's a staff or regular user
    $sql = $isStaff
        ? "INSERT INTO claimer (item_id, staff_id, item_description, date_lost, location_lost, proof_of_ownership, security_question, personal_id, status, claim_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())"
        : "INSERT INTO claimer (item_id, user_id, item_description, date_lost, location_lost, proof_of_ownership, security_question, personal_id, status, claim_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";

    $stmt = $conn->prepare($sql);
    if ($isStaff) {
        $stmt->bind_param('iissssss', $itemId, $claimantId, $item_description, $date_lost, $location_lost, $proof_of_ownership, $security_question, $personal_id);
    } else {
        $stmt->bind_param('iissssss', $itemId, $claimantId, $item_description, $date_lost, $location_lost, $proof_of_ownership, $security_question, $personal_id);
    }

    if ($stmt->execute()) {
        echo "<script>
            Swal.fire({
                title: 'Claim Submitted!',
                text: 'Your claim has been submitted successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(function() {
                window.location.href = 'dashboard.php';
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                title: 'Error!',
                text: 'There was an error submitting your claim.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
    }
    $stmt->close();
}

?>

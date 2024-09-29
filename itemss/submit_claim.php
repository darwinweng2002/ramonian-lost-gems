<?php
// Adjust the claim submission logic

if (isset($_SESSION['staff_id'])) {
    // For staff user
    $claimantId = $_SESSION['staff_id'];
    $isStaff = 1;  // Flag to identify it's a staff member
} elseif (isset($_SESSION['user_id'])) {
    // For regular user
    $claimantId = $_SESSION['user_id'];
    $isStaff = 0;  // Regular user
}

// Insert the claim into the `claimer` table
$sql = "
    INSERT INTO claimer (item_id, user_id, staff_id, is_staff, item_description, date_lost, location_lost, proof_of_ownership, security_question, personal_id, status, claim_date) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
";

$stmt = $conn->prepare($sql);
if ($isStaff) {
    $stmt->bind_param('iiiiisssss', $itemId, null, $claimantId, $isStaff, $item_description, $date_lost, $location_lost, $proof_of_ownership, $security_question, $personal_id);
} else {
    $stmt->bind_param('iiiiisssss', $itemId, $claimantId, null, $isStaff, $item_description, $date_lost, $location_lost, $proof_of_ownership, $security_question, $personal_id);
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
            text: 'There was an error submitting your claim. Please try again later.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    </script>";
}
$stmt->close();
?>

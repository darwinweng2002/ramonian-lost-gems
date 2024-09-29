<?php
if (isset($_SESSION['staff_id'])) {
    // Staff user
    $claimantId = $_SESSION['staff_id'];
    $userType = 'staff'; // Indicate it's a staff user
    
    // Insert the claim into the claimer table for staff users
    $stmt = $conn->prepare("
        INSERT INTO claimer 
        (item_id, staff_id, item_description, date_lost, location_lost, proof_of_ownership, security_question, personal_id, status, claim_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->bind_param('iissssss', $itemId, $claimantId, $item_description, $date_lost, $location_lost, $proof_of_ownership, $security_question, $personal_id);
    
} elseif (isset($_SESSION['user_id'])) {
    // Regular user
    $claimantId = $_SESSION['user_id'];
    
    // Insert the claim into the claimer table for regular users
    $stmt = $conn->prepare("
        INSERT INTO claimer 
        (item_id, user_id, item_description, date_lost, location_lost, proof_of_ownership, security_question, personal_id, status, claim_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
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
            window.location.href = 'dashboard.php'; // Redirect to dashboard after submission
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
$conn->close();

?>

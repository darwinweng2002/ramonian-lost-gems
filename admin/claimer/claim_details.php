<?php
include '../config.php';

// Start session to use session variables
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get the POST data from the form
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $item_description = isset($_POST['item_description']) ? $_POST['item_description'] : '';
    $date_lost = isset($_POST['date_lost']) ? $_POST['date_lost'] : '';
    $location_lost = isset($_POST['location_lost']) ? $_POST['location_lost'] : '';
    $security_question = isset($_POST['security_question']) ? $_POST['security_question'] : '';
    
    // Fetch the user_id from the session
    $user_id = $_SESSION['user_id'];
    
    // File handling for proof of ownership and personal ID
    $proof_of_ownership = '';
    $personal_id = '';

    if (isset($_FILES['proof_of_ownership']) && $_FILES['proof_of_ownership']['error'] == 0) {
        $proof_of_ownership = time() . '_' . basename($_FILES['proof_of_ownership']['name']);
        $target_dir = '../uploads/proof/';
        $target_file = $target_dir . $proof_of_ownership;
        move_uploaded_file($_FILES['proof_of_ownership']['tmp_name'], $target_file);
    }

    if (isset($_FILES['personal_id']) && $_FILES['personal_id']['error'] == 0) {
        $personal_id = time() . '_' . basename($_FILES['personal_id']['name']);
        $target_dir = '../uploads/id/';
        $target_file = $target_dir . $personal_id;
        move_uploaded_file($_FILES['personal_id']['tmp_name'], $target_file);
    }

    // Prepare the SQL query to insert the claim into the database
    $sql = "INSERT INTO claimer (user_id, item_id, item_description, date_lost, location_lost, proof_of_ownership, security_question, personal_id, claim_date, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Pending')";

    // Prepare and bind the SQL statement
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('iissssss', $user_id, $item_id, $item_description, $date_lost, $location_lost, $proof_of_ownership, $security_question, $personal_id);

        // Execute the query and check if it was successful
        if ($stmt->execute()) {
            // Redirect back to the claim page with a success message
            echo "<script>
                alert('Claim Submitted Successfully');
                window.location.href = 'claim_page.php'; // Adjust the redirect if needed
            </script>";
        } else {
            // If there was an error, display it
            echo "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        // If there was an error preparing the statement, display it
        echo "Error: " . $conn->error;
    }

    // Close the database connection
    $conn->close();
}
?>

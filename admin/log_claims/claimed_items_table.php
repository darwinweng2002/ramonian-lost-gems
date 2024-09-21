<?php
include '../../config.php';

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Fetch claimed items (status = 2)
$sql = "SELECT 
            mi.id, 
            mi.title, 
            mi.description, 
            mi.time_missing, 
            um.email, 
            c.name AS category_name
        FROM missing_items mi
        LEFT JOIN user_member um ON mi.user_id = um.id
        LEFT JOIN categories c ON mi.category_id = c.id
        WHERE mi.status = 2";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claimed Items Table View</title>
    <?php require_once('../inc/header.php'); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.5/dist/sweetalert2.min.css">
    <style>
        /* Your existing styles */
    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php'); ?>
<?php require_once('../inc/navigation.php'); ?>

<div class="container">
    <h1>Claimed Items History</h1>
    
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Item Name</th>
                <th>Description</th>
                <th>Time Missing</th>
                <th>User Email</th>
                <th>Category</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['time_missing']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
                    echo "<td>";
                    echo "<a href='https://ramonianlostgems.com/admin/log_claims/claimed_items.php?id=" . urlencode($row['id']) . "' class='btn btn-view'>View</a>";
                    
                    // Wrap the delete button with a form for SweetAlert confirmation
                    echo "<form class='delete-form' method='POST'>";
                    echo "<input type='hidden' name='id' value='" . htmlspecialchars($row['id']) . "' />";
                    echo "<button type='submit' class='btn btn-delete'>Delete</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7' class='text-center'>No claimed items found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Include SweetAlert and jQuery -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.5/dist/sweetalert2.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Your SweetAlert logic -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delete-form').forEach(function(form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault(); // Prevent the default form submission
                const formElement = event.target;
                const itemId = formElement.querySelector('input[name="id"]').value;

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You won\'t be able to revert this!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // AJAX request to update the item's status (not delete it)
                        $.ajax({
                            url: 'delete_claimed_item.php', // Path to the file that updates the status
                            type: 'POST',
                            data: { id: itemId },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire(
                                        'Deleted!',
                                        'The item has been removed from the view.',
                                        'success'
                                    ).then(() => {
                                        location.reload(); // Reload the page after deletion
                                    });
                                } else {
                                    Swal.fire('Error', response.error, 'error');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('AJAX error:', status, error);
                                Swal.fire('Error', 'An error occurred while processing the request.', 'error');
                            }
                        });
                    }
                });
            });
        });
    });
</script>

<?php require_once('../inc/footer.php'); ?>
</body>
</html>

<?php
$conn->close();
?>

<?php
include '../../config.php';

// Initialize $result_missing as null
$result_missing = null;

// Fetch denied missing items
$sql_missing = "
    SELECT mi.id, mi.title, mi.last_seen_location AS landmark, mi.contact, mi.owner AS founder, mi.time_missing AS time_found, mi.description, c.name AS category_name
    FROM missing_items mi
    LEFT JOIN categories c ON mi.category_id = c.id
    WHERE mi.is_denied = 1"; // Fetch denied missing items

// Execute the query
$result_missing = $conn->query($sql_missing);

// Check if query executed successfully
if (!$result_missing) {
    // Output the SQL error if the query failed
    echo "Error: " . $conn->error;
    exit;
}

// Debugging: Check if the query returned any results
if ($result_missing->num_rows == 0) {
    echo "No denied missing items found.";
} else {
    echo $result_missing->num_rows . " denied missing items found.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Denied Missing Items</title>
    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet">
    <style>
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
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        table th {
            background-color: #f4f4f4;
        }
        .no-items {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Denied Missing Items</h1>

        <?php if ($result->num_rows > 0) { ?>
            <table>
            <thead>
                <tr>
                    <th>Item Title</th>
                    <th>Category</th>
                    <th>Owner</th>
                    <th>Last Seen Location</th>
                    <th>Time Missing</th>
                    <th>Contact</th>
                    <th>Description</th>
                    <th>Action</th> <!-- Add Action column -->
                </tr>
            </thead>
            <tbody>
                <?php
                // Display denied missing items
                if ($result_missing->num_rows > 0) {
                    while ($row = $result_missing->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['owner']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['last_seen_location']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['time_missing']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['contact']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                        echo "<td><button class='undo-btn' data-id='" . htmlspecialchars($row['id']) . "'>Undo Deny</button></td>"; // Add Undo button
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>No denied missing items found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <?php } else { ?>
            <p class="no-items">No denied missing items found.</p>
        <?php } ?>

    </div>
        <!-- Include jQuery and SweetAlert2 for the prompt -->
        <script src="../js/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    $(document).ready(function() {
        // Handle Undo Deny action
        $('.undo-btn').on('click', function() {
            var itemId = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to undo deny this missing item?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, undo deny it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'undo.php', // Point to the new endpoint for undoing the deny
                        type: 'POST',
                        data: { id: itemId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Success!', 'The item has been moved back to the active missing items.', 'success')
                                .then(() => location.reload());  // Reload the page to reflect the change
                            } else {
                                Swal.fire('Error!', response.error, 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire('Error!', 'An error occurred: ' + error, 'error');
                        }
                    });
                }
            });
        });
    });
    </script>
</body>
</html>

<?php
$conn->close();
?>

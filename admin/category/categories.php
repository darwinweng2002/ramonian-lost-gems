<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../../config.php';

// Add new category logic
if (isset($_POST['add_category'])) {
    $newCategory = $_POST['new_category'];
    if (!empty($newCategory)) {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $newCategory);
        $stmt->execute();
        $stmt->close();
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Category added successfully!',
                confirmButtonText: 'OK'
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Category name cannot be empty!',
                confirmButtonText: 'OK'
            });
        </script>";
    }
}

// Update category logic
if (isset($_POST['update_category'])) {
    $categoryId = $_POST['category_id'];
    $updatedName = $_POST['category_name'];
    if (!empty($updatedName)) {
        $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $updatedName, $categoryId);
        $stmt->execute();
        $stmt->close();
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Category updated successfully!',
                confirmButtonText: 'OK'
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Category name cannot be empty!',
                confirmButtonText: 'OK'
            });
        </script>";
    }
}

// Delete category logic
if (isset($_POST['delete_category'])) {
    $categoryId = $_POST['category_id'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $stmt->close();
    echo "<script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Category deleted successfully!',
            confirmButtonText: 'OK'
        });
    </script>";
}

// Fetch all categories
$categories = [];
$stmt = $conn->prepare("SELECT id, name FROM categories");
$stmt->execute();
$stmt->bind_result($categoryId, $categoryName);
while ($stmt->fetch()) {
    $categories[] = ['id' => $categoryId, 'name' => $categoryName];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<?php require_once('../inc/header.php'); ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
/* Basic styles */
body {
    font-family: 'Arial', sans-serif;
    background-color: #f7f7f7;
    margin: 0;
    padding: 20px;
}

.container {
    max-width: 800px;
    margin: 0 auto;
    background: #fff;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

h2 {
    text-align: center;
    color: #333;
    margin-bottom: 30px;
}

/* Add Category form styling */
.add-category-form {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
}

.add-category-form input[type="text"] {
    flex: 1;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
    margin-right: 10px;
}

.add-category-form button {
    padding: 10px 20px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

.add-category-form button:hover {
    background-color: #218838;
}

/* Table and button styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

table, th, td {
    border: 1px solid #ccc;
}

th, td {
    padding: 10px;
    text-align: left;
}

th {
    background-color: #f4f4f4;
    text-align: center;
}

td {
    text-align: center;
}

/* Styling for actions and inputs */
.actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.actions form {
    width: 100%;
}

.actions input[type="text"] {
    padding: 8px;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 4px;
    width: 100%; /* Make input width 100% */
    box-sizing: border-box;
}

.actions button {
    width: 100%;
    padding: 10px;
    font-size: 14px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.actions .edit {
    background-color: #28a745;
    color: white;
}

.actions .edit:hover {
    background-color: #218838;
}

.actions .delete {
    background-color: #dc3545;
    color: white;
}

.actions .delete:hover {
    background-color: #c82333;
}

/* Success and error message styling */
.message {
    text-align: center;
    padding: 10px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.success {
    background-color: #d4edda;
    color: #155724;
}

.error {
    background-color: #f8d7da;
    color: #721c24;
}

/* Media queries for responsiveness */
@media (max-width: 768px) {
    /* Stack Add Category form elements vertically on smaller screens */
    .add-category-form {
        flex-direction: column;
        align-items: stretch;
    }

    .add-category-form input[type="text"] {
        width: 100%;
        margin-right: 0;
        margin-bottom: 10px;
    }

    .add-category-form button {
        width: 100%;
    }

    /* Adjust the table for smaller screens */
    table, th, td {
        font-size: 14px;
        padding: 8px;
    }

    .actions {
        flex-direction: column;
        gap: 5px;
    }

    .actions button {
        width: 100%;
    }
}

@media (max-width: 480px) {
    /* Further adjustments for very small screens */
    .container {
        padding: 10px;
    }

    h2 {
        font-size: 20px;
    }

    .add-category-form input[type="text"] {
        font-size: 14px;
    }

    .add-category-form button {
        font-size: 14px;
        padding: 8px 16px;
    }

    table, th, td {
        font-size: 12px;
        padding: 6px;
    }

    .actions {
        flex-direction: column;
        gap: 5px;
    }

    .actions button {
        font-size: 12px;
        padding: 6px 12px;
    }
}

    </style>
</head>
<body>
<?php require_once('../inc/topBarNav.php'); ?>
<?php require_once('../inc/navigation.php'); ?>
<br>
<br>
    <div class="container">
        <h2>Category Management</h2>

        <!-- Display success or error messages -->
        <?php if (isset($successMessage)): ?>
            <div class="message success"><?php echo $successMessage; ?></div>
        <?php elseif (isset($errorMessage)): ?>
            <div class="message error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <!-- Add New Category Form -->
        <form action="" method="POST" class="add-category-form">
            <input type="text" name="new_category" placeholder="Enter new category" required>
            <button type="submit" name="add_category">Add Category</button>
        </form>

        <!-- Display Categories -->
        <table>
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                    <td>
                        <div class="actions">
                            <!-- Edit Category -->
                            <form action="" method="POST">
                                <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                <input type="text" name="category_name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                                <button type="submit" name="update_category" class="edit">Update</button>
                            </form>

                            <!-- Delete Category -->
                            <form action="" method="POST">
                                <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                <button type="submit" name="delete_category" class="delete" onclick="return confirm('Are you sure you want to delete this category?');">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php require_once('../inc/footer.php'); ?>
<script>
    // SweetAlert confirmation for Delete
    document.querySelectorAll('.delete-button').forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.getAttribute('data-category-id');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit the delete form after confirmation
                    const form = this.closest('form');
                    form.submit();
                }
            });
        });
    });

    // SweetAlert confirmation for Edit
    document.querySelectorAll('.edit-button').forEach(button => {
        button.addEventListener('click', function() {
            const categoryName = this.getAttribute('data-category-name');
            Swal.fire({
                title: 'Confirm Update',
                text: `Are you sure you want to update the category to "${categoryName}"?`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit the edit form after confirmation
                    const form = this.closest('form');
                    form.submit();
                }
            });
        });
    });
</script>
</body>
</html>


<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../../config.php';

$successMessage = '';
$errorMessage = '';

// Add new category logic
if (isset($_POST['add_category'])) {
    $newCategory = $_POST['new_category'];
    if (!empty($newCategory)) {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $newCategory);
        if ($stmt->execute()) {
            $successMessage = "Category added successfully!";
        } else {
            $errorMessage = "Failed to add category: " . $stmt->error;
        }
        $stmt->close();
        // Redirect to the same page after submission
        header("Location: ".$_SERVER['PHP_SELF']);
        exit; // Make sure to stop further execution after redirect
    } else {
        $errorMessage = "Category name cannot be empty!";
    }
}

// Update category logic
if (isset($_POST['update_category'])) {
    $categoryId = $_POST['category_id'];
    $updatedName = $_POST['category_name'];
    if (!empty($updatedName)) {
        $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $updatedName, $categoryId);
        if ($stmt->execute()) {
            $successMessage = "Category updated successfully!";
        } else {
            $errorMessage = "Failed to update category: " . $stmt->error;
        }
        $stmt->close();
        // Redirect to the same page after submission
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    } else {
        $errorMessage = "Category name cannot be empty!";
    }
}

// Delete category logic
if (isset($_POST['delete_category'])) {
    $categoryId = $_POST['category_id'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $categoryId);
    if ($stmt->execute()) {
        $successMessage = "Category deleted successfully!";
    } else {
        $errorMessage = "Failed to delete category: " . $stmt->error;
    }
    $stmt->close();
    // Redirect to the same page after submission
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
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
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management</title>
    <link rel="stylesheet" href="styles.css"> <!-- Assuming you have a CSS file -->
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

        h1 {
            text-align: center;
            color: #333;
        }

        form {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        input[type="text"] {
            padding: 8px;
            width: 80%;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            padding: 8px 16px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button.delete {
            background-color: #dc3545;
        }

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
        }

        .message {
            text-align: center;
            padding: 10px;
            margin-bottom: 10px;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Category Management</h1>

        <!-- Display success or error messages -->
        <?php if (isset($successMessage)): ?>
            <div class="message success"><?php echo $successMessage; ?></div>
        <?php elseif (isset($errorMessage)): ?>
            <div class="message error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <!-- Add New Category Form -->
        <form action="" method="POST">
            <input type="text" name="new_category" placeholder="Enter new category">
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
                        <!-- Edit Category -->
                        <form action="" method="POST" style="display:inline;">
                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                            <input type="text" name="category_name" value="<?php echo htmlspecialchars($category['name']); ?>">
                            <button type="submit" name="update_category">Edit</button>
                        </form>

                        <!-- Delete Category -->
                        <form action="" method="POST" style="display:inline;">
                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                            <button type="submit" name="delete_category" class="delete" onclick="return confirm('Are you sure you want to delete this category?');">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

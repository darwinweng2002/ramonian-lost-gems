<?php
include 'config.php'; 
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Registration</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        h2 {
            text-align: center;
        }
        label {
            display: block;
            margin-bottom: 8px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Staff Registration</h2>
        <form id="registrationForm" enctype="multipart/form-data">
            <!-- User Type -->
            <label for="user_type">User Type</label>
            <select name="user_type" id="user_type" required>
                <option value="teaching">Teaching</option>
                <option value="non-teaching">Non-teaching</option>
            </select>

            <!-- First Name -->
            <label for="first_name">First Name</label>
            <input type="text" name="first_name" id="first_name" required>

            <!-- Last Name -->
            <label for="last_name">Last Name</label>
            <input type="text" name="last_name" id="last_name" required>

            <!-- Email -->
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>

            <!-- Password -->
            <label for="password">Password (8-16 characters)</label>
            <input type="password" name="password" id="password" minlength="8" maxlength="16" required>

            <!-- Confirm Password -->
            <label for="confirm_password">Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" minlength="8" maxlength="16" required>

            <!-- School ID File Upload -->
            <label for="id_file">Upload School ID</label>
            <input type="file" name="id_file" id="id_file" accept=".jpg,.jpeg,.png,.pdf" required>

            <!-- Submit Button -->
            <button type="submit">Register</button>
        </form>
    </div>

    <script>
        document.getElementById('registrationForm').addEventListener('submit', function (e) {
            e.preventDefault();
            var formData = new FormData(this);

            fetch('staff_register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success!', data.message, 'success');
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'An unexpected error occurred.', 'error');
            });
        });
    </script>
</body>
</html>

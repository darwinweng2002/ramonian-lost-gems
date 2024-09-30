<?php
include 'config.php'; // Include the database configuration file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data and validate
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $user_type = trim($_POST['user_type']);
    $username = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Handle file upload (profile picture)
    $profile_image = '';
    $target_dir = "uploads/profiles/"; // Directory to store uploaded images

    // Check if a file was uploaded
    if (!empty($_FILES['profile_image']['name'])) {
        $profile_image = basename($_FILES['profile_image']['name']);
        $target_file = $target_dir . $profile_image;

        // Check if the directory exists and is writable
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true); // Create the directory if it doesn't exist
        }

        // Move the uploaded file to the server
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            // Successfully uploaded
        } else {
            // Upload failed
            $response = ['success' => false, 'message' => 'Failed to upload profile picture.'];
            echo json_encode($response);
            exit;
        }
    } else {
        // No file was uploaded
        $response = ['success' => false, 'message' => 'No file uploaded. Please upload a profile picture.'];
        echo json_encode($response);
        exit;
    }

    // For teaching staff, department is required
    if ($user_type === 'teaching') {
        $department = trim($_POST['department']);
        $position = null; // No need for position in teaching
        if (empty($department)) {
            $response = ['success' => false, 'message' => 'Please enter the department for teaching staff.'];
            echo json_encode($response);
            exit;
        }
    } else {
        // For non-teaching staff, position is required and department is disabled
        $position = trim($_POST['position']);
        $department = null; // No need for department in non-teaching
        if (empty($position)) {
            $response = ['success' => false, 'message' => 'Please enter the role/position for non-teaching staff.'];
            echo json_encode($response);
            exit;
        }
    }

    // Check if all required fields are provided
    if (empty($first_name) || empty($last_name) || empty($username) || empty($password)) {
        $response = ['success' => false, 'message' => 'Please fill in all the required fields.'];
        echo json_encode($response);
        exit;
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        $response = ['success' => false, 'message' => 'Passwords do not match.'];
        echo json_encode($response);
        exit;
    }

    // Validate password length (8-16 characters)
    if (strlen($password) < 8 || strlen($password) > 16) {
        $response = ['success' => false, 'message' => 'Password must be between 8 and 16 characters long.'];
        echo json_encode($response);
        exit;
    }

    // Hash the password before inserting into the database
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Check if the email already exists in the database
    $stmt = $conn->prepare("SELECT id FROM user_staff WHERE email = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Email already exists
        $response = ['success' => false, 'message' => 'This email is already registered.'];
        echo json_encode($response);
        $stmt->close();
        $conn->close();
        exit;
    }

    $stmt->close();

    // Prepare the SQL statement to insert new user (including profile_image)
    $stmt = $conn->prepare("INSERT INTO user_staff (first_name, last_name, email, password, department, position, user_type, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        $response = ['success' => false, 'message' => 'Failed to prepare the database statement.'];
        echo json_encode($response);
        exit;
    }

    // Bind parameters including the user_type and profile_image fields
    $stmt->bind_param("ssssssss", $first_name, $last_name, $username, $hashed_password, $department, $position, $user_type, $profile_image);

    // Execute the query and check for success
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Registration successful!'];
    } else {
        $response = ['success' => false, 'message' => 'Failed to register user.'];
    }

    $stmt->close();
    $conn->close();

    // Return a JSON response
    echo json_encode($response);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta name="google-signin-client_id" content="462546722729-vflluo934lv9qei2jbeaqcib5sllh9t6.apps.googleusercontent.com">
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script src="https://apis.google.com/js/platform.js" async defer></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<title>Staff Registration</title>
</head>
<?php require_once('inc/header.php'); ?>
<body>
  <style>
  .swal2-popup {
    position: fixed !important; 
    top: 50% !important;        
    left: 50% !important;       
    transform: translate(-50%, -50%) !important; 
    z-index: 9999 !important;   
    overflow: auto;             
}

.swal2-overlay {
    overflow: auto;             
}
body {
      overflow: auto;
    }
    .logo {
  display: flex;
  flex-direction: column; /* Stack logo and text */
  align-items: center; /* Center items horizontally */
  margin-bottom: 10px; /* Space below the logo */
}

.logo img {
  max-height: 60px; /* Adjust height as needed */
}

.logo span {
  color: #fff;
  text-shadow: 0px 0px 10px #000;
  text-align: center; /* Center the text */
  font-size: 24px; /* Adjust font size as needed */
}
.back-btn-container {
            margin: 20px 0;
            display: flex;
            justify-content: flex-start;
        }

        .back-btn {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            font-family: 'Helvetica Neue', Arial, sans-serif;
            transition: background-color 0.3s ease;
        }

        .back-btn svg {
            margin-right: 8px;
        }

        .back-btn:hover {
            background-color: #0056b3;
        }

        .back-btn:focus {
            outline: none;
            box-shadow: 0 0 4px rgba(0, 123, 255, 0.5);
        }
  </style>
  <main>
    <div class="container">
      <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">
            <div class="d-flex justify-content-center py-4">
            <a href="#" class="logo d-flex align-items-center w-auto">
                <img src="<?= validate_image($_settings->info('logo')) ?>" alt="">
                <span><?= $_settings->info('name') ?></span>
            </a>
            </div><!-- End Logo -->

              <div class="card mb-3">
                <div class="card-body">
                  <div class="pt-4 pb-2">
                    <h5 class="card-title text-center pb-0 fs-4">Register as Faculty</h5>
                    <p class="text-center small">Fill in the form to create a staff account</p>
                  </div>
                  
                  <form class="row g-3 needs-validation" novalidate method="POST" action="register_staff.php" enctype="multipart/form-data">
    <!-- User Type Field (Teaching or Non-teaching) -->
    <div class="col-12">
        <label for="user_type" class="form-label">User Type</label>
        <select id="user_type" name="user_type" class="form-control" required>
            <option value="teaching">Teaching</option>
            <option value="non-teaching">Non-teaching</option>
        </select>
        <div class="invalid-feedback">Please select the user type.</div>
    </div>

    <!-- First Name -->
    <div class="col-12">
        <label for="firstName" class="form-label">First Name</label>
        <input type="text" name="first_name" class="form-control" id="firstName" required>
        <div class="invalid-feedback">Please enter your first name.</div>
    </div>
    
    <!-- Last Name -->
    <div class="col-12">
        <label for="lastName" class="form-label">Last Name</label>
        <input type="text" name="last_name" class="form-control" id="lastName" required>
        <div class="invalid-feedback">Please enter your last name.</div>
    </div>

    <!-- Department Field (only for Teaching staff) -->
    <div class="col-12">
        <label for="department" class="form-label">Department</label>
        <input type="text" name="department" class="form-control" id="department">
        <div class="invalid-feedback">Please enter your department.</div>
    </div>

    <!-- Role/Position Field (for Non-teaching staff) -->
    <div class="col-12" id="position_field" style="display: none;">
        <label for="position" class="form-label">Role/Position</label>
        <input type="text" name="position" class="form-control" id="position">
        <div class="invalid-feedback">Please enter your role/position.</div>
    </div>

    <!-- Email -->
    <div class="col-12">
        <label for="email" class="form-label">Email</label> 
        <input type="email" name="email" class="form-control" id="email" required>
        <div class="invalid-feedback">Please use an active email account</div>
    </div>

    <!-- Password -->
    <div class="col-12">
        <label for="yourPassword" class="form-label">Password (8-16 characters)</label>
        <input type="password" name="password" class="form-control" id="yourPassword" minlength="8" maxlength="16" required>
        <div class="invalid-feedback">Password must be between 8 and 16 characters long.</div>
    </div>

    <!-- Confirm Password -->
    <div class="col-12">
        <label for="confirm_password" class="form-label">Confirm Password</label>
        <input type="password" name="confirm_password" class="form-control" id="confirm_password" minlength="8" maxlength="16" required>
        <div class="invalid-feedback">Passwords do not match.</div>
    </div>

    <!-- Image Upload (Profile Picture) -->
    <div class="col-12">
        <label for="avatar" class="form-label">Upload Profile Picture</label>
        <input type="file" name="profile_image" class="form-control" id="profile_image" accept="image/*">
        <div class="invalid-feedback">Please upload an image file.</div>
    </div>

    <!-- Submit Button -->
    <div class="col-12">
        <button class="btn btn-primary w-100" type="submit">Register</button>
    </div>
</form>
                  <!-- End form -->
                  
                  <div id="g_id_onload"
                       data-client_id="462546722729-vflluo934lv9qei2jbeaqcib5sllh9t6.apps.googleusercontent.com"
                       data-context="signin"
                       data-ux_mode="popup"
                       data-callback="handleCredentialResponse"
                       data-auto_prompt="false">
                  </div>
                  <div class="g_id_signin"
                       data-type="standard"
                       data-shape="rectangular"
                       data-theme="outline"
                       data-text="signin_with"
                       data-size="large"
                       data-logo_alignment="left">
                  </div>
              </div>
            </div>
          </div>
          <div class="back-btn-container">
    <button class="back-btn" onclick="history.back()">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left">
            <line x1="19" y1="12" x2="5" y2="12"/>
            <polyline points="12 19 5 12 12 5"/>
        </svg>
        Back
    </button>
</div>
        </div>
      </section>
    </div>
  </main>
  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  <script src="<?= base_url ?>assets/js/jquery-3.6.4.min.js"></script>
  <script src="<?= base_url ?>assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="<?= base_url ?>assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="<?= base_url ?>assets/vendor/chart.js/chart.umd.js"></script>
  <script src="<?= base_url ?>assets/vendor/echarts/echarts.min.js"></script>
  <script src="<?= base_url ?>assets/vendor/quill/quill.min.js"></script>
  <script src="<?= base_url ?>assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="<?= base_url ?>assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="<?= base_url ?>assets/vendor/php-email-form/validate.js"></script>
  <script src="<?= base_url ?>assets/js/main.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script> <!-- Ensure jQuery is included -->
  <script>
    $(document).ready(function() {
        $('form').on('submit', function(e) {
            e.preventDefault(); // Prevent form submission

            // Trim password fields to remove leading/trailing spaces
            var password = $('#yourPassword').val().trim();
            var confirmPassword = $('#confirm_password').val().trim();

           // Get the email value
const email = $('#email').val().trim();

// Define a regex for validating a legitimate email format
const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

// Check if the email matches the correct format
if (!emailRegex.test(email)) {
    Swal.fire({
        title: 'Error!',
        text: 'Please enter a valid email address.',
        icon: 'error',
        confirmButtonText: 'OK'
    });
    return;
}
            // Password length validation (min 8, max 16)
            if (password.length < 8 || password.length > 16) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Password must be between 8 and 16 characters long.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Confirm password match validation
            if (password !== confirmPassword) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Passwords do not match.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // If validation passes, submit the form using AJAX
            var formData = $(this).serialize();
            $.ajax({
                url: 'staff_process.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Staff registration successful!',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'https://ramonianlostgems.com';
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message || 'An error occurred.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Registration successful! You are all set to access your account as faculty.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'https://ramonianlostgems.com/staff_login.php';
                        }
                    });
                }
            });
        });
    });
    $(document).ready(function() {
        $('#user_type').on('change', function() {
            if ($(this).val() === 'teaching') {
                $('#department').prop('disabled', false);
                $('#position_field').hide();
            } else {
                $('#department').prop('disabled', true);
                $('#position_field').show();
            }
        });
    });
    document.querySelector('#registrationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var email = document.querySelector('#email').value;
        var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        if (!emailPattern.test(email)) {
          Swal.fire({
            title: 'Error!',
            text: 'Please enter a valid email address.',
            icon: 'error',
            confirmButtonText: 'OK'
          });
        } else {
          this.submit();
        }
      });
  </script>
<?php require_once('inc/footer.php') ?>
</body>
</html>
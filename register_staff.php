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

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM user_staff WHERE email = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $response = ['success' => false, 'message' => 'This email address is already taken, please use another.'];
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    // Handle file upload (profile picture)
    $profile_image = '';
    $target_dir = "uploads/profiles/"; // Directory to store uploaded images

    // Ensure the directory exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true); // Create directory if not exists
    }

    if (!empty($_FILES['profile_image']['name'])) {
        // Ensure unique file name to avoid conflicts
        $profile_image = uniqid() . '_' . basename($_FILES['profile_image']['name']);
        $target_file = $target_dir . $profile_image;

        // Move the uploaded file to the server
        if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            $response = ['success' => false, 'message' => 'Failed to upload profile picture.'];
            echo json_encode($response);
            exit;
        }
    }

    // Password validation, hashing, etc.
    if ($password !== $confirm_password) {
        $response = ['success' => false, 'message' => 'Passwords do not match.'];
        echo json_encode($response);
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Handle department and position based on user type
    $department = null;
    $position = null;

    if ($user_type === 'teaching') {
        $department = trim($_POST['department']);
    } else {
        $position = trim($_POST['position']);
    }

    // Database insert
    $stmt = $conn->prepare("INSERT INTO user_staff (first_name, last_name, email, password, department, position, user_type, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        $response = ['success' => false, 'message' => 'Failed to prepare the database statement.'];
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param("ssssssss", $first_name, $last_name, $username, $hashed_password, $department, $position, $user_type, $profile_image);

    // Execute the query and check for success
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Registration successful!'];
    } else {
        $response = ['success' => false, 'message' => 'Failed to register user.'];
    }

    $stmt->close();
    $conn->close();

    // Return JSON response
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
        .role-selector {
        width: 100%;
        margin-bottom: 20px;
    }

    .role-selector label {
        display: block;
        font-weight: 500;
        margin-bottom: 8px;
        color: #333;
    }

    .role-selector select {
        width: 100%;
        padding: 10px;
        border: 2px solid #0d6efd;
        border-radius: 5px;
        font-size: 16px;
        background-color: #fff;
        color: #333;
        outline: none;
        box-shadow: none;
        transition: border-color 0.3s ease-in-out;
    }

    .role-selector select:focus {
        border-color: #0d6efd;
        outline: none;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .role-selector select option {
        padding: 10px;
        font-size: 16px;
    }
    .back-btn-container {
    display: flex;
    justify-content: center; /* Centers the button horizontally */
    margin: 20px 0; /* Adds some space above and below the button */
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
                        <div class="role-selector">
                        <select id="role-select" class="form-select">
                            <option value="" disabled selected>Register as</option>
                            <option value="student">Register as Student</option>
                            <option value="faculty">Register as Employee</option>
                        </select>
                    </div>
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="pt-4 pb-2">
                                    <h5 class="card-title text-center pb-0 fs-4">Register as Employee</h5>
                                    <p class="text-center small">Fill in the form to create a employee user account.</p>
                                </div>

                                <!-- Form starts here -->
                                <form class="row g-3 needs-validation" id="registrationForm" novalidate method="POST" enctype="multipart/form-data">
                                    <!-- User Type Field (Teaching or Non-teaching) -->
                                    <div class="col-12">
                                        <label for="user_type" class="form-label">User Role</label>
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
                                        <div class="invalid-feedback">Password is too short or didn't match</div>
                                    </div>

                                    <!-- Confirm Password -->
                                    <div class="col-12">
                                        <label for="confirm_password" class="form-label">Confirm Password</label>
                                        <input type="password" name="confirm_password" class="form-control" id="confirm_password" minlength="8" maxlength="16" required>
                                        <div class="invalid-feedback">Password is too short or didn't match</div>
                                    </div>

                                    <!-- Image Upload (Profile Picture) -->
                                    <div class="col-12">
                                        <label for="profile_image" class="form-label">Upload ID</label>
                                        <input type="file" name="profile_image" class="form-control" id="profile_image" accept="image/*" required>
                                        <div class="invalid-feedback">Please upload a picture of your ID</div>
                                        <small class="text-muted">Please upload a clear image of your valid ID (front side only). Ensure that the ID is visible and in JPG or PNG format. This will be used for verification purposes.</small>
                                    </div>


                                    <!-- Submit Button (Disabled by default) -->
                                    <div class="col-12">
                                        <button class="btn btn-primary w-100" type="submit">Register</button>
                                    </div>

                                </form>
                                <!-- End form -->

                            </div>
                        </div>
                    </div>
                </div>
                <div class="back-btn-container">
    <a href="https://ramonianlostgems.com/" class="back-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left">
            <line x1="19" y1="12" x2="5" y2="12"/>
            <polyline points="12 19 5 12 12 5"/>
        </svg>
        Cancel
    </a>
</div>
            </div>
        </section>
    </div>
</main>

<!-- jQuery and necessary scripts -->
<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  <script src="<?= base_url ?>assets/js/jquery-3.6.4.min.js"></script>
  <script src="<?= base_url ?>assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="<?= base_url ?>assets/js/main.js"></script>
<script>
$(document).ready(function () {
    // Initially disable the register button
    $('button[type="submit"]').prop('disabled', true);

    // Function to check if all fields are valid
    function validateForm() {
        let formIsValid = true; // Assume the form is valid

        // Validate email/username format
        const email = $('#email').val().trim();

        // Regex for either an email OR a username (8-16 characters)
        const pattern = /^([a-zA-Z0-9._-]{8,16}|[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})$/;

        // Validate the email/username format using the combined regex
        if (!pattern.test(email)) {
            formIsValid = false;
            $('#email').addClass('is-invalid');
            $('#email-error').text('Please enter a valid email or a username (8-16 characters)').show();
        } else {
            $('#email').removeClass('is-invalid').addClass('is-valid');
            $('#email-error').hide();
        }

        // Validate profile image is uploaded
        const profileImage = $('#profile_image').val();
        if (!profileImage) {
            formIsValid = false;
            $('#profile_image').addClass('is-invalid');
        } else {
            const allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif)$/i;
            if (!allowedExtensions.exec(profileImage)) {
                formIsValid = false;
                $('#profile_image').addClass('is-invalid');
                Swal.fire({
                    title: 'Error!',
                    text: 'Please upload a valid image file (jpg, jpeg, png, gif).',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            } else {
                $('#profile_image').removeClass('is-invalid').addClass('is-valid');
            }
        }

        // Validate passwords
        const password = $('#yourPassword').val().trim();
        const confirmPassword = $('#confirm_password').val().trim();
        if (password !== confirmPassword || password.length < 8 || password.length > 16) {
            formIsValid = false;
            $('#yourPassword, #confirm_password').addClass('is-invalid');
        } else {
            $('#yourPassword, #confirm_password').removeClass('is-invalid').addClass('is-valid');
        }

        // Enable/disable submit button based on validation status
        $('button[type="submit"]').prop('disabled', !formIsValid);
    }

    // Listen for input events on each field
    $('#email, #profile_image, #yourPassword, #confirm_password').on('input change', function () {
        validateForm();
    });

    // Handle form submission via AJAX
    $('#registrationForm').on('submit', function (e) {
        e.preventDefault(); // Prevent default form submission

        if ($('button[type="submit"]').prop('disabled')) {
            return; // If form is not valid, do not submit
        }

        var formData = new FormData(this);

        // Ajax request to handle the registration form submission
        $.ajax({
            url: 'staff_process.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message || 'Registration successful!',
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
            error: function () {
                Swal.fire({
                    title: 'Success!',
                    text: 'Thank you for registering. Your account is currently pending approval by the admin.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'https://ramonianlostgems.com/';
                    }
                });
            }
        });
    });

    // User type dropdown change listener
    $('#user_type').on('change', function () {
        if ($(this).val() === 'teaching') {
            $('#department').prop('disabled', false);
            $('#position_field').hide();
        } else {
            $('#department').prop('disabled', true);
            $('#position_field').show();
        }
    });
});
document.addEventListener('DOMContentLoaded', function () {
            // Handle role selection change
            const roleSelect = document.getElementById('role-select');

            roleSelect.addEventListener('change', function () {
                const selectedRole = this.value;

                // Redirect based on the selected role
                if (selectedRole === 'student') {
                    window.location.href = 'https://ramonianlostgems.com/register.php/'; // Redirect to student registration page
                } else if (selectedRole === 'faculty') {
                    window.location.href = 'https://ramonianlostgems.com/register_staff.php'; // Redirect to faculty registration page
                }
            });
        });
</script>

<?php require_once('inc/footer.php'); ?>
</body>
</html>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Debugging file upload
echo "<pre>";
print_r($_FILES);
echo "</pre>";

include 'config.php'; // Include the database configuration file
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Temporary debugging to ensure form data is received correctly
    var_dump($_POST);
    var_dump($_FILES);
    
    // Database connection debugging
    if ($conn->connect_error) {
        die('Database connection error: ' . $conn->connect_error);
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data and validate
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $user_type = trim($_POST['user_type']);
    $username = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

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

    // Check if file was uploaded
    if (!isset($_FILES['id_file']) || $_FILES['id_file']['error'] !== UPLOAD_ERR_OK) {
        $response = ['success' => false, 'message' => 'Please upload your ID.'];
        echo json_encode($response);
        exit;
    }

    // Handle the uploaded ID file
    $fileTmpPath = $_FILES['id_file']['tmp_name'];
    $fileName = $_FILES['id_file']['name'];
    $fileSize = $_FILES['id_file']['size'];
    $fileType = $_FILES['id_file']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    // Sanitize file name
    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

    // Allowed file extensions
    $allowedFileExtensions = array('jpg', 'jpeg', 'png', 'pdf');

    if (in_array($fileExtension, $allowedFileExtensions)) {
        // Directory where the file will be uploaded
        $uploadFileDir = 'uploads/ids/';
        $dest_path = $uploadFileDir . $newFileName;

        // Move the file to the destination directory
        if (move_uploaded_file($_FILES['id_file']['tmp_name'], $dest_path)) {
            $id_file = $newFileName;
        } else {
            // Check the permissions of the folder
            if (!is_writable($uploadFileDir)) {
                $response = ['success' => false, 'message' => 'Upload directory is not writable.'];
            } else {
                $response = ['success' => false, 'message' => 'Error moving the uploaded file.'];
            }
            echo json_encode($response);
            exit;
        }
    }        

    // Hash the password before inserting into the database
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Check if the username already exists in the database
    $stmt = $conn->prepare("SELECT id FROM user_staff WHERE email = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $response = ['success' => false, 'message' => 'This email is already registered.'];
        echo json_encode($response);
        $stmt->close();
        $conn->close();
        exit;
    }

    $stmt->close();

    // Prepare the SQL statement to insert new user
    $stmt = $conn->prepare("INSERT INTO user_staff (first_name, last_name, email, password, department, position, user_type, id_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die(json_encode(['success' => false, 'message' => 'SQL prepare error: ' . $conn->error]));
    }
    
    // Bind the parameters
    $stmt->bind_param("ssssssss", $first_name, $last_name, $username, $hashed_password, $department, $position, $user_type, $id_file);
    
    // Execute the query
    if (!$stmt->execute()) {
        die(json_encode(['success' => false, 'message' => 'SQL execution error: ' . $stmt->error]));
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
    <input type="text" name="department" class="form-control" id="department" required>
    <div class="invalid-feedback">Please enter your department.</div>
</div>

<!-- Role/Position Field (for Non-teaching staff) -->
<div class="col-12" id="position_field" style="display: none;">
    <label for="position" class="form-label">Role/Position</label>
    <input type="text" name="position" class="form-control" id="position" required>
    <div class="invalid-feedback">Please enter your role/position.</div>
</div>


    <!-- Email -->
    <div class="col-12">
        <label for="email" class="form-label">Email</label> 
        <input type="email" name="email" class="form-control" id="email" required>
        <div class="invalid-feedback">Please use an active email account</div>
    </div>
     <!-- ID File Upload Field -->
<div class="col-12">
    <label for="id_file" class="form-label">Upload School ID</label>
    <input type="file" name="id_file" class="form-control" id="id_file" required>
    <div class="invalid-feedback">Please upload your school ID.</div>
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
        e.preventDefault(); // Prevent the default form submission

        // Create FormData to handle file uploads along with other data
        var formData = new FormData(this);

        // Validate the password fields
        var password = $('#yourPassword').val().trim();
        var confirmPassword = $('#confirm_password').val().trim();

        // Get the email value and validate it
        const email = $('#email').val().trim();
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        if (!emailRegex.test(email)) {
            Swal.fire({
                title: 'Error!',
                text: 'Please enter a valid email address.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Check password length
        if (password.length < 8 || password.length > 16) {
            Swal.fire({
                title: 'Error!',
                text: 'Password must be between 8 and 16 characters long.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Check if passwords match
        if (password !== confirmPassword) {
            Swal.fire({
                title: 'Error!',
                text: 'Passwords do not match.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        $.ajax({
    url: 'register_staff.php',
    type: 'POST',
    data: formData,
    contentType: false,
    processData: false,
    dataType: 'json',  // Make sure the response is JSON formatted
    success: function(response) {
        if (response && response.success) {
            Swal.fire({
                title: 'Success!',
                text: response.message || 'Staff registration successful!',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        } else {
            Swal.fire({
                title: 'Error!',
                text: response.message || 'An error occurred during registration.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    },
    error: function(xhr, status, error) {
        console.error('AJAX Error: ', status, error);
        Swal.fire({
            title: 'Error!',
            text: 'An unexpected error occurred. Please try again later.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
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
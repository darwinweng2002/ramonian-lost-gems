<?php
session_start(); // Start the session at the very beginning

// Include the database configuration file
include 'config.php'; // Adjust the path if necessary

// Variable to hold the error message
$error_message = '';

// Check if the form is submitted for staff login
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['guest_login'])) {
    // Get form data
    $username = $_POST['email'] ?? ''; // Using null coalescing operator to avoid undefined array key notice
    $password = $_POST['password'] ?? '';

    // Prepare and execute query for staff login
    if ($stmt = $conn->prepare("SELECT id, password FROM user_staff WHERE email = ?")) { // 'email' column is still used for usernames
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($staff_id, $hashed_password);
            $stmt->fetch();

            // Verify password
            if (password_verify($password, $hashed_password)) {
                // Password is correct, start a session
                $_SESSION['staff_id'] = $staff_id;
                $_SESSION['email'] = $username;  // Store the username in the session (same variable for compatibility)

                // Redirect to a protected page
                header("Location: https://ramonianlostgems.com/main.php");
                exit();
            } else {
                $error_message = 'Invalid username or password.'; // Update message to reflect username
            }
        } else {
            $error_message = 'No staff found with that username.'; // Update message to reflect username
        }
    } else {
        $error_message = 'Error preparing statement: ' . $conn->error;
    }
}

// Check if "Login as Guest" button is clicked
if (isset($_POST['guest_login'])) {
  // Generate a unique guest session ID
  $_SESSION['user_id'] = 'guest_' . bin2hex(random_bytes(5)); // Unique guest ID
  $_SESSION['email'] = 'guest@example.com';  // Identifier remains generic for guests

  // Redirect guest user to the main page
  header("Location: https://ramonianlostgems.com/main.php");
  exit();
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
</head>
<?php require_once('inc/header.php'); ?>
<body>
  <style>
    body {
      background-size: cover;
      background-repeat: no-repeat;
      backdrop-filter: brightness(.7);
      overflow-x: hidden;
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

    .loader-wrapper {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: rgba(255, 255, 255, 0.8);
      z-index: 9999;
    }
  
    .loader {
      border: 8px solid #f3f3f3;
      border-top: 8px solid #3498db;
      border-radius: 50%;
      width: 60px;
      height: 60px;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .swal2-popup {
      position: fixed !important; 
      top: 50% !important;        
      left: 50% !important;       
      transform: translate(-50%, -50%) !important; 
      z-index: 9999 !important;   
      overflow: auto;             
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
                  <br>
                  <span><?= $_settings->info('name') ?></span>
                </a>
              </div><!-- End Logo -->

              <div class="card mb-3">
                <div class="card-body">
                  <div class="pt-4 pb-2">
                    <h5 class="card-title text-center pb-0 fs-4">Staff Login</h5>
                    <p class="text-center small">Enter your email & password to login</p>
                  </div>
                  <form class="row g-3 needs-validation" novalidate method="POST">
                    <div class="col-12">
                      <label for="yourEmail" class="form-label">Username</label>
                      <div class="input-group has-validation">
                        <input type="text" name="email" class="form-control" id="yourEmail" required>
                        <div class="invalid-feedback">Please enter your username.</div>
                      </div>
                    </div>
                    <div class="col-12">
                      <label for="yourPassword" class="form-label">Password</label>
                      <input type="password" name="password" class="form-control" id="yourPassword" required>
                      <div class="invalid-feedback">Please enter your password!</div>
                    </div>
                    <div class="col-12">
                      <button class="btn btn-primary w-100" type="submit" id="loginButton" disabled>Login</button>
                    </div>
                  </form>

                  <br>
                  <div class="text-center mt-3">
                    <p>Don't have an account? 
                      <a href="https://ramonianlostgems.com/register.php/" class="btn btn-primary w-100">Register here</a>
                    </p>
                  </div>

                  <br>
                  <br>
                  <div id="g_id_onload"
                    data-client_id="YGOCSPX-kVEygpsdOrU_3FQ8fHnfv86qUrRM"
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
          </div>
        </div>
      </section>
    </div>

    <div id="loader" class="loader-wrapper" style="display:none;">
      <div class="loader"></div>
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

  <script>
    $(document).ready(function() {
      end_loader();
      // Check if there's an error message
      <?php if ($error_message): ?>
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: '<?php echo $error_message; ?>',
          confirmButtonText: 'OK'
        });
      <?php endif; ?>
      
      // Show loader on form submission
      $('form').on('submit', function(e) {
        $('#loader').show(); // Show the loader
      });
    });

    function handleCredentialResponse(response) {
      const data = jwt_decode(response.credential);
      $('#loader').show();

      // Send the Google ID token to your server for verification and user registration/login
      $.post("google-signin.php", {
        id_token: response.credential,
        first_name: data.given_name,
        last_name: data.family_name,
        email: data.email
      }, function(result) {
        $('#loader').hide();  // Hide the loader after response
        if (result.success) {
          window.location.href = "dashboard.php";
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: result.message,
            confirmButtonText: 'OK'
          });
        }
      });
    }

    $(document).ready(function() {
      function checkForm() {
        var username = $('#yourEmail').val().trim();
        var password = $('#yourPassword').val().trim();
        
        // Enable the login button only if both fields have values
        if (username && password) {
          $('#loginButton').removeAttr('disabled');
        } else {
          $('#loginButton').attr('disabled', 'disabled');
        }
      }

      // Trigger checkForm on keyup for both fields
      $('#yourEmail, #yourPassword').on('keyup', function() {
        checkForm();
      });
    });
  </script>

<?php require_once('inc/footer.php') ?>
</body>
</html>
<?php
session_start(); // Start the session

// Include the database configuration file
include 'config.php';

// Variable to hold the error message
$error_message = '';

// Check if the form is submitted for regular login
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['guest_login'])) {
    // Get form data
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Prepare and execute query
    if ($stmt = $conn->prepare("SELECT id, password, status FROM user_member WHERE email = ?")) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $hashed_password, $status);
            $stmt->fetch();

            if ($status === 'pending') {
                $error_message = 'Your account is awaiting admin approval. Please wait for an email confirmation.';
            } elseif (password_verify($password, $hashed_password)) {
                // Start session and store user info
                $_SESSION['user_id'] = $user_id;
                $_SESSION['email'] = $email;

                // Redirect to protected page
                header("Location: https://ramonianlostgems.com/main.php");
                exit();
            } else {
                $error_message = 'Invalid email or password.';
            }
        } else {
            $error_message = 'No user found with that email.';
        }
    } else {
        $error_message = 'Error preparing statement: ' . $conn->error;
    }
}

// Handle "Login as Guest"
if (isset($_POST['guest_login'])) {
    $_SESSION['user_id'] = 'guest_' . bin2hex(random_bytes(5));
    $_SESSION['email'] = 'guest@example.com';
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
.hyper-link {
  text-align: center; 
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
                    <h5 class="card-title text-center pb-0 fs-4">User Account Login</h5>
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
                  <form method="POST" action="">
                    <div class="col-12">
                      <br>
                      <p style="text-align: center;">Not a student or employee member? Proceed as guest.
                      <button class="btn btn-secondary w-100" type="submit" name="guest_login" value="1">Login as Guest</button>
                      </p>
                    </div>
                  </form>

                  <div class="text-center mt-3">
                  <p>Don't have an account? 
                  <a href="https://ramonianlostgems.com/register.php" class="btn btn-primary w-100" style="color: #fff;">Sign up</a>
                  </p>
              </div>
            <div class="hyper-link">
            <a href="https://ramonianlostgems.com/admin/login.php">
            Login as Admin
          </a>
          <br>
          <br>
          <a href="https://ramonianlostgems.com/staff_login.php">
            Login as Employee
          </a>
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
    // Ensure loader shows when clicking Admin Login, Faculty Login, or Register links
    $(document).on('click', 'a[href="https://ramonianlostgems.com/admin/login.php"], a[href="https://ramonianlostgems.com/staff_login.php"], a[href="https://ramonianlostgems.com/register.php"]', function(e) {
        // Show the loader
        $('#loader').show();
    });

    // Show loader on form submission for user login and guest login
    $('form').on('submit', function(e) {
        $('#loader').show();
    });

    // Check if there's an error message and show it
    <?php if ($error_message): ?>
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: '<?php echo $error_message; ?>',
        confirmButtonText: 'OK'
      });
    <?php endif; ?>
});

  // Function to handle Google Sign-In response (already existing)
  function handleCredentialResponse(response) {
    const data = jwt_decode(response.credential);

    // Show the loader
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
                // Redirect or notify the user
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
    // Function to check if both username and password fields are filled
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
  $(document).ready(function() {
    // Check if there's an error message and show it with SweetAlert
    <?php if ($error_message): ?>
      <?php if (strpos($error_message, 'awaiting admin approval') !== false): ?>
        // Use SweetAlert with a custom icon for the "pending" status
        Swal.fire({
          icon: 'info',
          title: 'Pending Approval',
          text: '<?php echo $error_message; ?>',
          confirmButtonText: 'OK',
          customClass: {
            icon: 'swal-custom-icon'  // Custom class if needed
          }
        });
      <?php else: ?>
        // Default SweetAlert error message
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: '<?php echo $error_message; ?>',
          confirmButtonText: 'OK'
        });
      <?php endif; ?>
    <?php endif; ?>
  });
</script>

<?php require_once('inc/footer.php') ?>
</body>
</html>

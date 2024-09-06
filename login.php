<?php
// Start the session at the very beginning


// Include the database configuration file
include 'config.php'; // Adjust the path if necessary

// Variable to hold the error message
$error_message = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = $_POST['email'] ?? ''; // Using null coalescing operator to avoid undefined array key notice
    $password = $_POST['password'] ?? '';

    // Prepare and execute query
    if ($stmt = $conn->prepare("SELECT id, password FROM user_member WHERE email = ?")) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $hashed_password);
            $stmt->fetch();

            // Verify password
            if (password_verify($password, $hashed_password)) {
                // Password is correct, start a session
                $_SESSION['user_id'] = $user_id;
                $_SESSION['email'] = $email;  

                // Redirect to a protected page
                header("Location: http://localhost/lostgemramonian/");
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
    .logo img {
      max-height: 55px;
      margin-right: 25px;
    }
    .logo span {
      color: #fff;
      text-shadow: 0px 0px 10px #000;
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
                  <span class="d-none d-lg-block text-center"><?= $_settings->info('name') ?></span>
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
                      <label for="yourEmail" class="form-label">Email</label>
                      <div class="input-group has-validation">
                        <input type="email" name="email" class="form-control" id="yourEmail" required>
                        <div class="invalid-feedback">Please enter your email.</div>
                      </div>
                    </div>
                    <div class="col-12">
                      <label for="yourPassword" class="form-label">Password</label>
                      <input type="password" name="password" class="form-control" id="yourPassword" required>
                      <div class="invalid-feedback">Please enter your password!</div>
                    </div>
                    <div class="col-12">
                      <button class="btn btn-primary w-100" type="submit">Login</button>
                    </div>
                  </form>
                  <br>
                  <button class="btn btn-primary w-100"><a style="color: #fff;" href="http://localhost/lostgemramonian/admin/login.php">Login as Admin</a></button>
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
              <footer>
                <div class="container text-center py-4">
                  <!-- Copyright Section -->
                  <div class="copyright mb-2">
                    &copy; <strong><span>Ramonian LostGems</span></strong>. All Rights Reserved
                  </div>
                  <!-- Credits Section -->
                  <div class="credits">
                    <p>
                      <a href="http://localhost/lostgemramonian/register.php">prmsuramonianlostgems.com</a>
                    </p>
                  </div>
                  <!-- Logo Section -->
                  <div class="logo mb-2">
                    <a href="<?= base_url ?>">
                      <img style="height: 55px; width: 55px;" src="<?= validate_image($_settings->info('logo')) ?>" alt="System Logo">
                    </a>
                  </div>
                </div>
              </footer>

            </div>
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
    });
    function handleCredentialResponse(response) {
        // This function handles the response from Google Sign-In
        const data = jwt_decode(response.credential);

        // Send the Google ID token to your server for verification and user registration/login
        $.post("google-signin.php", {
            id_token: response.credential,
            first_name: data.given_name,
            last_name: data.family_name,
            email: data.email
        }, function(result) {
            if (result.success) {
                // Redirect or notify the user
                window.location.href = "dashboard.php";
            } else {
                alert(result.message);
            }
        }, 'json');
    }
  </script>
</body>
</html>

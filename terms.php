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
                header("Location: https://ramonianlostgems.com/itemss/items.php");
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
    header("Location: https://ramonianlostgems.com/itemss/items.php");
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

    .terms-container {
      background-color: #ffffff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
      margin-bottom: 30px;
    }

    h5 {
      font-size: 1.5rem;
      font-weight: 600;
      text-align: center;
      color: #333;
    }

    p {
      color: #555;
      font-size: 1rem;
      line-height: 1.8;
      margin-bottom: 20px;
      text-align: justify;
    }

    ol {
      padding-left: 20px;
    }

    ol li {
      margin-bottom: 15px;
      color: #555;
      font-size: 1rem;
    }

    .hyper-link {
      text-align: center;
    }
  </style>
</head>
<?php require_once('inc/header.php'); ?>
<body>

  <main>
    <div class="container">
      <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 d-flex flex-column align-items-center justify-content-center">
              
              <div class="d-flex justify-content-center py-4">
                <a href="#" class="logo d-flex align-items-center w-auto">
                  <img src="<?= validate_image($_settings->info('logo')) ?>" alt="">
                  <br>
                  <span><?= $_settings->info('name') ?></span>
                </a>
              </div><!-- End Logo -->

              <div class="terms-container">
                <h5>Terms and Conditions for Ramonian Lost Gems</h5>

                <p>Welcome to Ramonian Lost Gems. By using our mobile application, you agree to comply with and be bound by the following terms and conditions. Please read them carefully before registering an account.</p>

                <ol>
                  <li><strong>Acceptance of Terms:</strong> By registering and using the Ramonian Lost Gems mobile application, you agree to abide by these Terms and Conditions. These terms govern your access to and use of the application and its features, including the submission of personal information and ID verification documents for registration.</li>
                  
                  <li><strong>User Registration and Approval:</strong> To access the full features of the app, users must register an account. During registration, you are required to submit either a School ID (for students), Employee ID (for PRMSU employees), or any valid ID (for guest users). These documents are used for the sole purpose of validating your identity as a legitimate user of PRMSU Iba Campus or a guest. Once your registration is submitted, your account will not be immediately active. All accounts must be reviewed and approved by an administrator. You will be notified once your account is approved and ready for use. If your account is rejected, you will be notified via the email address you provided.</li>
                  
                  <li><strong>Posting Lost and Found Items:</strong> As a registered user, you can report lost and found items through the application. Reports must include specific details such as item name, description, and, optionally, multiple images of the item. Your reports will be reviewed by an admin before being published.</li>
                  
                  <li><strong>Claiming Items:</strong> Only registered users with approved accounts can submit claim requests for lost or found items. Guest users may report lost or found items but cannot claim them. All claims will be verified by an admin before final approval.</li>
                  
                  <li><strong>Responsibilities of Admin Users:</strong> Admins have the responsibility of managing and reviewing all reported lost and found items. Admins can publish, delete, or reject reported items. They can also manage claim requests and change the status of an item to 'claimed' once it has been returned to its owner. Admins are authorized to update the status of reports and manage user accounts.</li>
                  
                  <li><strong>Prohibited Use:</strong> You agree not to misuse the Ramonian Lost Gems application for any unauthorized purposes, including providing false information or attempting to claim items that do not belong to you.</li>
                  
                  <li><strong>Modification of Terms:</strong> We reserve the right to modify these Terms and Conditions at any time. Any changes will be posted on this page, and it is your responsibility to review the terms regularly. Continued use of the app after changes constitutes your acceptance of the modified terms.</li>
                  
                  <li><strong>Limitation of Liability:</strong> Ramonian Lost Gems is not responsible for any direct or indirect damages resulting from the use of the mobile application, including the loss or theft of personal items.</li>
                </ol>
              </div>
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

<?php require_once('inc/footer.php') ?>
</body>
</html>
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

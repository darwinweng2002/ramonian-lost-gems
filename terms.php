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
        </div>
        <p>borat</p>
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

<?php  
// Include the database configuration file
include 'config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Retrieve form data
  $first_name = $_POST['first_name'];
  $last_name = $_POST['last_name'];
  $college = $_POST['college'];
  $course = $_POST['course'];
  $year = $_POST['year'];
  $section = $_POST['section'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Encrypt the password

  // Prepare the SQL statement
  $stmt = $conn->prepare("INSERT INTO user_member (first_name, last_name, college, course, year, section, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("ssssssss", $first_name, $last_name, $college, $course, $year, $section, $email, $password);

  // Execute the query and check for success
  if ($stmt->execute()) {
      $response = ['success' => true];
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
<title>Register Account</title>
</head>
<?php require_once('inc/header.php'); ?>
<body>
  <style>
  .swal2-popup {
    position: fixed !important; /* Fix position relative to viewport */
    top: 50% !important;        /* Center vertically */
    left: 50% !important;       /* Center horizontally */
    transform: translate(-50%, -50%) !important; /* Adjust for exact center */
    z-index: 9999 !important;   /* Ensure it appears above other elements */
    overflow: auto;              /* Allow scrolling within the popup if needed */
}

/* Optional: To ensure that the page content can be scrolled while the popup is visible */


/* Optional: If you have any styles that could impact the body overflow */
.swal2-overlay {
    overflow: auto;             /* Allow scrolling of the page if necessary */
}
body {
      overflow: auto;
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
                <a href="user_login.php" class="logo d-flex align-items-center w-auto">
                  <img src="<?= validate_image($_settings->info('logo')) ?>" alt="">
                  <span class="d-none d-lg-block text-center"><?= $_settings->info('name') ?></span>
                </a>
              </div><!-- End Logo -->
              <div class="card mb-3">
                <div class="card-body">
                  <div class="pt-4 pb-2">
                    <h5 class="card-title text-center pb-0 fs-4">User Registration</h5>
                    <p class="text-center small">Fill in the form to create an account</p>
                  </div>
                  <form class="row g-3 needs-validation" novalidate method="POST" action="register_process.php">
    <div class="col-12">
        <label for="firstName" class="form-label">First Name</label>
        <input type="text" name="first_name" class="form-control" id="firstName" required>
        <div class="invalid-feedback">Please enter your first name.</div>
    </div>
    <div class="col-12">
        <label for="lastName" class="form-label">Last Name</label>
        <input type="text" name="last_name" class="form-control" id="lastName" required>
        <div class="invalid-feedback">Please enter your last name.</div>
    </div>
    <div class="col-12">
        <label for="college" class="form-label">College</label>
        <select name="college" class="form-control" id="college" required>
            <option value="" disabled selected>Select your college</option>
            <option value="CABA">CABA</option>
            <option value="CTHM">CTHM</option>
            <option value="CTE">CTE</option>
            <option value="CAS">CAS</option>
            <option value="CIT">CIT</option>
            <option value="CON">CON</option>
            <option value="CCIT">CCIT</option>
            <option value="COE">COE</option>
        </select>
        <div class="invalid-feedback">Please select your college.</div>
    </div>
    <div class="col-12">
        <label for="course" class="form-label">Course</label>
        <select name="course" class="form-control" id="course" required>
            <option value="" disabled selected>Select your course</option>
            <option value="Bachelor of Science in Information Technology">Bachelor of Science in Information Technology</option>
            <option value="Bachelor of Science in Computer Science">Bachelor of Science in Computer Science</option>
            <option value="Bachelor of Science in Computer Engineering">Bachelor of Science in Computer Engineering</option>
            <option value="Bachelor of Science in Civil Engineering">Bachelor of Science in Civil Engineering</option>
            <option value="Bachelor of Science in Mechanical Engineering">Bachelor of Science in Mechanical Engineering</option>
        </select>
        <div class="invalid-feedback">Please select your course.</div>
    </div>
    <div class="col-12">
        <label for="year" class="form-label">Year</label>
        <select name="year" class="form-control" id="year" required>
            <option value="" disabled selected>Select your year</option>
            <option value="1st - year">1st - year</option>
            <option value="2nd - year">2nd - year</option>
            <option value="3rd - year">3rd - year</option>
            <option value="4th - year">4th - year</option>
        </select>
        <div class="invalid-feedback">Please select your year.</div>
    </div>
    <div class="col-12">
        <label for="section" class="form-label">Section</label>
        <select name="section" class="form-control" id="section" required>
            <option value="" disabled selected>Select your section</option>
            <option value="Section A">Section A</option>
            <option value="Section B">Section B</option>
            <option value="Section C">Section C</option>
            <option value="Section D">Section D</option>
            <option value="Section E">Section E</option>
            <option value="Section F">Section F</option>
        </select>
        <div class="invalid-feedback">Please select your section.</div>
    </div>
    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="verified" id="verified" required>
            <label class="form-check-label" for="verified">Verified Student in PRMSU IBA Main Campus</label>
            <div class="invalid-feedback">You must verify your student status.</div>
        </div>
    </div>
    <div class="col-12">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" class="form-control" id="email" required>
        <div class="invalid-feedback">Please enter a valid email address.</div>
    </div>
    <div class="col-12">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" class="form-control" id="password" required>
        <div class="invalid-feedback">Please enter your password.</div>
    </div>
    <div class="col-12">
        <button class="btn btn-primary w-100" type="submit">Register</button>
    </div>
</form>

                  <div class="text-center mt-3">
                    <p>Already have an account? <a href="http://localhost/lostgemramonian/login.php/">Login here</a></p>
                  </div>
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
              <div class="col-12">
              <footer>
                <div class="copyright">
                  &copy; Copyright <strong><span>Ramonian LostGems</span></strong>. All Rights Reserved
                </div>
                <div class="credits">
                  <p style="text-align: center;">Developed by BSINFOTECH 3-C <a href="http://localhost/lostgemramonian/login.php">prmsuramonianlostgems.com</a></p>
                  <a href="<?= base_url ?>">
                    <center><img style="height: 55px; width: 55px;" src="<?= validate_image($_settings->info('logo')) ?>" alt="System Logo"></center>
                  </a>
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
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script> <!-- Ensure jQuery is included -->
  <script>
  $(document).ready(function() {
    $('form').on('submit', function(e) {
      e.preventDefault(); // Prevent the default form submission

      // Check if all required fields are filled
      var isValid = true;
      $(this).find('input[required], select[required]').each(function() {
        if ($.trim($(this).val()) === '') {
          isValid = false;
          $(this).addClass('is-invalid'); // Add bootstrap invalid class
        } else {
          $(this).removeClass('is-invalid'); // Remove bootstrap invalid class
        }
      });

      if (!isValid) {
        Swal.fire({
          title: 'Error!',
          text: 'Please fill all required fields.',
          icon: 'error',
          confirmButtonText: 'OK'
        });
        return; // Exit the function if validation fails
      }

      // Collect form data
      var formData = $(this).serialize();

      // Submit form data using AJAX  
      $.ajax({
        url: 'register_process.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
          console.log(response); // Log the response to check
          if (response.success) {
            Swal.fire({
              title: 'Success!',
              text: 'Registration successful!',
              icon: 'success',
              confirmButtonText: 'OK'
            }).then((result) => {
              if (result.isConfirmed) {
                window.location.href = 'login.php'; // Redirect or do something else
              }
            });
          } else {
            Swal.fire({
              title: 'Error!',
              text: response.message,
              icon: 'error',
              confirmButtonText: 'OK'
            });
          }
        },
        error: function(xhr, status, error) {
          console.error('AJAX Error: ', status, error); // Log the AJAX error
          Swal.fire({
            title: 'Success!',
            text: 'Registration Successfull!',
            icon: 'success',
            confirmButtonText: 'OK'
          }).then((result) => {
            if (result.isConfirmed) {
              window.location.href = 'login.php'; // Redirect or do something else
            }
          });
        }
      });
    });
  });
</script>

</body>
</html>
</body>
</html>
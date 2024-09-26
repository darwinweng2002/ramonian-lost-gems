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
  $username = $_POST['email']; // This is now the username field, but keep the variable name as 'email'
  
  // Check if passwords match
  if ($_POST['password'] !== $_POST['confirm_password']) {
      $response = ['success' => false, 'message' => 'Passwords do not match.'];
      echo json_encode($response);
      exit;
  }

  // Hash the entire password, no truncation
  $password = password_hash($_POST['password'], PASSWORD_BCRYPT); 

  // Prepare the SQL statement
  $stmt = $conn->prepare("INSERT INTO user_member (first_name, last_name, college, course, year, section, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("ssssssss", $first_name, $last_name, $college, $course, $year, $section, $username, $password);

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
#role-select {
        appearance: none; /* Remove default dropdown arrow */
        width: 100%;
        padding: 12px 16px;
        font-size: 16px;
        border-radius: 8px;
        border: 2px solid #007bff;
        background-color: #f8f9fa;
        color: #333;
        cursor: pointer;
        box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        outline: none; /* Remove outline on focus */
    }

    /* Hover effect */
    #role-select:hover {
        border-color: #0056b3;
        box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Focus effect */
    #role-select:focus {
        border-color: #0056b3;
        background-color: #e9ecef;
    }

    /* Styling the dropdown arrow */
    #role-select::after {
        content: '▼';
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 12px;
        color: #007bff;
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
                                <label for="role-select">Select your role:</label>
                                <select id="role-select">
                                    <option value="" disabled selected>-- Select Role --</option>
                                    <option value="student">Register as Student</option>
                                    <option value="faculty">Register as Faculty</option>
                                </select>
                            </div>
              <div class="card mb-3">
                <div class="card-body">
                  <div class="pt-4 pb-2">
                    <h5 class="card-title text-center pb-0 fs-4">Students User Registration</h5>
                    <p class="text-center small">Fill in the form to create an account</p>
                  </div>
                  
                  <!-- Updated registration form -->
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
                              <option value="CABA">College of Accountancy and Business Administration</option>
                              <option value="CAS">College of Arts and Sciences</option>
                              <option value="CCIT">College of Communication and Information Technology</option>
                              <option value="CTE">College of Teacher Education</option>
                              <option value="CE">College of Engineering</option>
                              <option value="CIT">College of Industrial Technology</option>
                              <option value="CAF">College of Agriculture and Forestry</option>
                              <option value="NUR">College of Nursing</option>
                              <option value="CTHM">College of Tourism and Hospitality Management</option>
                          </select>
                          <div class="invalid-feedback">Please select your college.</div>
                      </div>
                      <div class="col-12">
                          <label for="course" class="form-label">Course</label>
                          <select name="course" class="form-control" id="course" required>
                              <option value="" disabled selected>Select your course</option>
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
                      <!-- Updated username field -->
                      <div class="col-12">
                      <label for="email" class="form-label">Username</label> 
                      <input type="text" name="email" class="form-control" id="email" pattern="^[a-zA-Z0-9]+$" required>
                      <div class="invalid-feedback">Please enter a valid username (alphanumeric characters only, no "@" or email-like formats).</div>
                      </div>
                      <!-- Password and Confirm Password Fields -->
                      <div class="col-12">
                          <label for="yourPassword" class="form-label">Password (8-16 characters)</label>
                          <input type="password" name="password" class="form-control" id="yourPassword" minlength="8" maxlength="16" required>
                          <div class="invalid-feedback">Password must be between 8 and 16 characters long.</div>
                      </div>
                      <div class="col-12">
                          <label for="confirm_password" class="form-label">Confirm Password</label>
                          <input type="password" name="confirm_password" class="form-control" id="confirm_password" minlength="8" maxlength="16" required>
                          <div class="invalid-feedback">Passwords do not match. Please ensure both passwords are the same.</div>
                      </div>
                      <div class="col-12">
                          <button class="btn btn-primary w-100" type="submit">Register</button>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Define the courses for each college
        const coursesByCollege = {
            "CABA": [
                "Bachelor of Science in Accountancy",
                "Bachelor of Science in Accounting and Information System",
                "Bachelor of Science in Business Administration - Marketing",
                "Bachelor of Science in Business Administration - Financial Management",
                "Bachelor of Science in Business Administration - Human Resource Development Management",
                "Bachelor of Public Administration"
            ],
            "CAS": [
                "Bachelor of Science in Biology",
                "Bachelor of Science in Psychology"
            ],
            "CCIT": [
                "Bachelor of Science in Computer Science",
                "Bachelor of Science in Information Technology"
            ],
            "CTE": [
                "Bachelor of Secondary Education - English Education",
                "Bachelor of Secondary Education - Filipino Education",
                "Bachelor of Secondary Education - Mathematics Education",
                "Bachelor of Secondary Education - Science Education",
                "Bachelor of Secondary Education - Social Studies Education",
                "Bachelor of Elementary Education",
                "Bachelor of Physical Education",
                "Bachelor of Professional Education"
            ],
            "CE": [
                "Bachelor of Science in Civil Engineering",
                "Bachelor of Science in Electrical Engineering",
                "Bachelor of Science in Mechanical Engineering",
                "Bachelor of Science in Computer Engineering",
                "Bachelor of Science in Mining Engineering"
            ],
            "CIT": [
                "Bachelor of Technology and Livelihood Education - Industrial Arts",
                "Bachelor of Technical Vocational Teacher Education - Computer Programming",
                "Bachelor of Technical Vocational Teacher Education - Drafting Technology",
                "Bachelor of Technical Vocational Teacher Education - Mechanical Technology (Machine)",
                "Bachelor of Technical Vocational Teacher Education - Electrical Technology",
                "Bachelor of Technical Vocational Teacher Education - Food and Service Management Technology",
                "Bachelor of Technical Vocational Teacher Education - Automotive Technology",
                "Bachelor of Technical Vocational Teacher Education - Electronics Technology",
                "Bachelor of Technical Vocational Teacher Education - Welding and Fabrication Technology",
                "Bachelor of Science in Industrial Technology - Automotive Technology",
                "Bachelor of Science in Industrial Technology - Computer Technology",
                "Bachelor of Science in Industrial Technology - Drafting Technology",
                "Bachelor of Science in Industrial Technology - Electrical Technology",
                "Bachelor of Science in Industrial Technology - Electronics Technology",
                "Bachelor of Science in Industrial Technology - Food Technology",
                "Bachelor of Science in Industrial Technology - Furniture and Cabinet Marketing Technology",
                "Bachelor of Science in Industrial Technology - Mechanical Technology"
            ],
            "CAF": [
                "Bachelor of Science in Environmental Science"
            ],
            "NUR": [
                "Bachelor of Science in Nursing"
            ],
            "CTHM": [
                "Bachelor of Science in Hospitality Management",
                "Bachelor of Science in Tourism Management"
            ]
        };

        const collegeSelect = document.getElementById('college');
        const courseSelect = document.getElementById('course');

        collegeSelect.addEventListener('change', function() {
            const selectedCollege = this.value;
            const courses = coursesByCollege[selectedCollege] || [];

            // Clear existing options
            courseSelect.innerHTML = '<option value="" disabled selected>Select your course</option>';

            // Populate new options
            courses.forEach(function(course) {
                const option = document.createElement('option');
                option.value = course;
                option.textContent = course;
                courseSelect.appendChild(option);
            });
        });
    });

    function handleCredentialResponse(response) {
        const id_token = response.credential;

        // Send the ID token to your server
        $.ajax({
            url: 'google_login_process.php',
            type: 'POST',
            data: { id_token: id_token },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Login successful!',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'dashboard.php'; // Redirect or do something else
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
                    title: 'Error!',
                    text: 'An error occurred during Google sign-in.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    }

    $(document).ready(function() {
        $('form').on('submit', function(e) {
            e.preventDefault(); // Prevent form submission

            // Trim password fields to remove leading/trailing spaces
            var password = $('#yourPassword').val().trim();
            var confirmPassword = $('#confirm_password').val().trim();

            // Get the username value
            const username = $('#email').val().trim();

            // Disallow email-like formats in the username
            const emailRegex = /@|\.com|\.net|\.org|\.edu/i;
            if (emailRegex.test(username)) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Usernames cannot contain "@" or resemble email addresses.',
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
                url: 'register_process.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Registration successful!',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'https://ramonianlostgems.com/login.php';
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
                        text: 'Registration successful! You are all set to report or search for lost items.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'https://ramonianlostgems.com/'; // Redirect or do something else
                        }
                    });
                }
            });
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


</body>
</html>
<?php require_once('inc/footer.php') ?>
</body>
</html>
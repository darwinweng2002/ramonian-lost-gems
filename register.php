<?php  
require 'vendor/autoload.php';

// Include the database configuration file
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'config.php';

// Include PHPMailer for email notifications (add PHPMailer to your project)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Correct path to PHPMailer files
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $college = $_POST['college'];
    $course = $_POST['course'];
    $year = $_POST['year'];
    $email = $_POST['email'];
    $school_type = $_POST['school_type'];
    $grade = $_POST['grade']; // Email for username
    
    // Check if passwords match
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $response = ['success' => false, 'message' => 'Passwords do not match.'];
        echo json_encode($response);
        exit;
    }

    // Hash the password
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); 

    // Handle file upload (School ID)
    $target_dir = "uploads/school_ids/";
    $school_id_file = $target_dir . basename($_FILES["school_id"]["name"]);
    $imageFileType = strtolower(pathinfo($school_id_file, PATHINFO_EXTENSION));

    // Check if file is a valid image type
    $valid_file_types = ['jpg', 'jpeg', 'png'];
    if (!in_array($imageFileType, $valid_file_types)) {
        $response = ['success' => false, 'message' => 'Invalid file format for school ID. Only JPG, JPEG, and PNG are allowed.'];
        echo json_encode($response);
        exit;
    }

    // Attempt to move the uploaded file
    if (!move_uploaded_file($_FILES["school_id"]["tmp_name"], $school_id_file)) {
        $response = ['success' => false, 'message' => 'Error uploading school ID.'];
        echo json_encode($response);
        exit;
    }

    // Set user status as "pending"
    $status = 'pending';

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO user_member (first_name, last_name, college, course, year, school_type, grade, email, password, avatar, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssss", $first_name, $last_name, $college, $course, $year, $school_type, $grade, $email, $password, $school_id_file, $status);

    // Execute the query and check for success
    if ($stmt->execute()) {
        // Send email to the user notifying them that the registration is successful and awaiting approval
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'your_gmail_account@gmail.com'; // Add your Gmail account
            $mail->Password = 'your_gmail_password'; // Add your Gmail password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('your_gmail_account@gmail.com', 'Your App Name');
            $mail->addAddress($email);  // Add user email address

            $mail->isHTML(true);
            $mail->Subject = 'Account Registration Pending Approval';
            $mail->Body    = "Hello $first_name, <br>Your registration was successful! Please wait for the admin to approve your account. You will receive an email once it's approved and ready for login.";

            $mail->send();
        } catch (Exception $e) {
            // Handle email sending error
        }

        $response = ['success' => true, 'message' => 'Successfully registered! Please wait for admin approval. You will receive an email once your account is approved.'];
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
<meta name="viewport" content="width=device-width, initial-scale=1">
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
        flex-direction: column;
        align-items: center;
        margin-bottom: 10px;
    }

    .logo img {
        max-height: 60px;
    }

    .logo span {
        color: #fff;
        text-shadow: 0px 0px 10px #000;
        text-align: center;
        font-size: 24px;
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

.loader-overlay {
    display: none; /* Initially hidden */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.8); /* Transparent white background */
    z-index: 9999; /* High z-index to ensure it's on top */
    text-align: center;
    justify-content: center;
    align-items: center;
}

/* The loader itself */
.loader {
    border: 8px solid #f3f3f3; /* Light grey */
    border-top: 8px solid #3498db; /* Blue */
    border-radius: 50%;
    width: 60px;
    height: 60px;
    animation: spin 1s linear infinite;
}

/* Loader animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
/* Modal Background */
.modal {
  display: none; /* Hidden by default */
  position: fixed;
  z-index: 10000; /* On top */
  left: 0;
  top: 0;
  width: 100%; /* Full width */
  height: 100%; /* Full height */
  background-color: rgba(0,0,0,0.5); /* Black background with opacity */
  justify-content: center;
  align-items: center;
}


.modal-content h2 {
  margin-top: 0;
}

.modal-content button {
  margin-top: 20px;
}
/* Centered Logo Styling */
.logo-container {
  text-align: center;
  margin-bottom: 20px;
}

.logo-container img {
  max-width: 100px; /* Adjust the size as needed */
}

/* Modal Content Styling */
/* Modal Content Styling - Reduced Size */
.modal-content {
  background-color: white;
  padding: 10px;
  border-radius: 10px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.25);
  max-width: 500px; /* Set the maximum width to a smaller size */
  width: 100%; /* This ensures it's responsive for smaller screens */
  text-align: center;
  font-size: 12px;
}


/* Justified Text for Terms and Conditions */
.terms-text {
  text-align: justify; /* Justify the text */
  margin-bottom: 15px;
}
/* Button Container - Stack the buttons vertically */
.button-container {
  display: flex;
  flex-direction: column; /* Change to column layout */
  gap: 10px; /* Add spacing between buttons */
  margin-top: 20px;
}

button {
  width: 100%; /* Make buttons take the full width of the container */
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s;
  font-size: 15px;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
  border: none;
}

.btn-secondary:hover {
  background-color: #5a6268;
}

.btn-primary {
  background-color: #007bff;
  color: white;
  border: none;
}

.btn-primary:hover {
  background-color: #0056b3;
}

  </style>
<!-- Terms and Conditions Modal -->
<!-- Terms and Conditions Modal -->
<div id="termsModal" class="modal">
  <div class="modal-content">
    <!-- Centered Logo -->
    <div class="logo-container">
    <img src="/uploads/logo.png" alt="Logo" class="logo-img">
    </div>
    
    <h2>Terms and Conditions</h2>
    <p class="terms-text">
    Welcome to Ramonian Lost Gems
By using our mobile application, you agree to comply with and be bound by the following terms and conditions. Please read them carefully before registering for an account. These terms govern your access to and use of the Ramonian Lost Gems mobile application, including the submission of personal information and verification documents for account registration.
    </p>
    <p class="terms-text">
    Your privacy and security are important to us. The information you provide, including your School ID or Employee ID, will be used solely for the purpose of verifying your status as a legitimate student or employee of PRMSU Iba Campus. All personal information will remain confidential and will not be shared with any third parties. By proceeding, you acknowledge that your data will be handled in accordance with applicable privacy laws and regulations.
    </p>
    <div class="button-container">
      <button id="declineTerms" class="btn btn-secondary">Decline</button>
      <button id="acceptTerms" class="btn btn-primary">I Accept</button>
    </div>
  </div>
</div>

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
                </div>
                <div class="role-selector">
                <select id="role-select" class="form-select">
                    <option value="" disabled selected>Register as</option>
                    <option value="student">Register as Student</option>
                    <option value="faculty">Register as Employee</option>
                </select>
            </div>

                <!-- Updated registration form -->
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="pt-4 pb-2">
                            <h5 class="card-title text-center pb-0 fs-4">Student User Registration</h5>
                            <p class="text-center small">Fill in the form to create an account</p>
                        </div>
                        
                        <form class="row g-3 needs-validation" novalidate method="POST" action="register_process.php" enctype="multipart/form-data">
                        <div class="col-12">
                                <label for="school_type" class="form-label">Are you a College or High School student?</label>
                                <select name="school_type" class="form-control" id="school_type" required>
                                    <option value="1">College</option>
                                    <option value="0">High School</option>
                                </select>
                                <div class="invalid-feedback">Please select your school type.</div>
                            </div>

                            <!-- Grade field -->
                            <div class="col-12">
                                <label for="grade" class="form-label">Grade</label>
                                <select name="grade" class="form-control" id="grade" required>
                                    <option value="" disabled selected>Select your grade level</option>
                                    <option value="N/A">N/A</option>
                                    <option value="7">Grade 7</option>
                                    <option value="8">Grade 8</option>
                                    <option value="9">Grade 9</option>
                                    <option value="10">Grade 10</option>
                                    <option value="11">Grade 11</option>
                                    <option value="12">Grade 12</option>
                                </select>
                                <div class="invalid-feedback">Please select your grade level.</div>
                                <small class="text-muted">Please select N/A if you are not a high school student.</small>
                            </div>
                            <div class="col-12">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" id="firstName" required>
                                <div class="invalid-feedback">Please enter your first name.</div>
                            </div>
                            <div class="col-12">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text"  name="last_name" class="form-control" id="lastName" required>
                                <div class="invalid-feedback">Please enter your last name.</div>
                            </div>
                            <div class="col-12">
                                <label for="college" class="form-label">Department</label>
                                <select name="college" class="form-control" id="college" required>
                                    <option value="" disabled selected>Select your Department</option>
                                    <option value="N/A">N/A</option>
                                    <option value="CABA">College of Accountancy and Business Administration</option>
                                    <option value="CAS">College of Arts and Sciences</option>
                                    <option value="CCIT">College of Communication and Information Technology</option>
                                    <option value="CTE">College of Teacher Education</option>
                                    <option value="COE">College of Engineering</option>
                                    <option value="CIT">College of Industrial Technology</option>
                                    <option value="CAF">College of Agriculture and Forestry</option>
                                    <option value="CON">College of Nursing</option>
                                    <option value="CTHM">College of Tourism and Hospitality Management</option>
                                </select>
                                <div class="invalid-feedback">Please select your college.</div>
                                <small class="text-muted">Please select N/A if you are not a college student.</small>
                            </div>
                            <div class="col-12">
                                <label for="course" class="form-label">Course</label>
                                <select name="course" class="form-control" id="course" required>
                                    <option value="" disabled selected>Select your course</option>
                                </select>
                                <div class="invalid-feedback">Please select your course.</div>
                                <small class="text-muted">Please select N/A if you are not a college student.</small>
                            </div>
                            <div class="col-12">
                                <label for="year" class="form-label">Year</label>
                                <select name="year" class="form-control" id="year" required>
                                    <option value="" disabled selected>Select your year</option>
                                    <option value="N/A">N/A</option>
                                    <option value="1st - year">1st</option>
                                    <option value="2nd - year">2nd</option>
                                    <option value="3rd - year">3rd</option>
                                    <option value="4th - year">4th</option>
                                </select>
                                <div class="invalid-feedback">Please select your year.</div>
                                <small class="text-muted">Please select N/A if you are not a college student.</small>
                            </div>
                            <div class="col-12">
                            <label for="school_id" class="form-label">School ID</label>
                            <input type="file" name="school_id" class="form-control" id="school_id" accept=".jpg,.jpeg,.png" required>
                            <div class="invalid-feedback">Please upload your School ID (acceptable file type: jpg, jpeg, png).</div>
                            <!-- Image preview container -->
                            <div id="imagePreviewContainer" style="margin-top: 10px;">
                                <img id="imagePreview" src="#" alt="Preview will appear here..." style="max-width: 100%; display: none; border: 1px solid #ddd; padding: 5px; border-radius: 4px;">
                            </div>
                            <small class="text-muted">We assure you that the school ID you submit will be used solely for verification purposes, specifically to confirm that you are a legitimate student of PRMSU Iba Campus. All personal information will remain confidential and will not be shared with any third parties. Your data will be handled in strict compliance with applicable privacy laws and ethical guidelines to ensure the protection of your information.</small>
                        </div>

                        <div class="col-12">
                        <label for="email" class="form-label">Username</label>
                        <input type="text" name="email" class="form-control" id="email" required>
                        <div class="invalid-feedback" id="email-error">Please enter a username (8-16 characters).</div>
                    </div>


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
                            <button class="btn btn-primary w-100" type="submit" id="register-btn" disabled>Register</button>
                        </div>
                        </form>
                        <div class="loader-overlay" id="loaderOverlay">
    <div class="loader"></div>
</div>
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
        Back
    </a>
</div>

        </div>
      </section>
    </div>
  </main>

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  <script src="<?= base_url ?>assets/js/jquery-3.6.4.min.js"></script>
  <script src="<?= base_url ?>assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="<?= base_url ?>assets/js/main.js"></script>
  <script>
   $(document).ready(function() {
    // Populate courses dynamically based on selected college

    // Form validation logic
    function validateForm() {
    let formIsValid = true;

    // Validate username or email
    const email = $('#email').val().trim();
    const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    const usernamePattern = /^[a-zA-Z0-9._-]{8,16}$/;

    if (!emailPattern.test(email) && !usernamePattern.test(email)) {
        formIsValid = false;
        $('#email').addClass('is-invalid');
        $('#email-error').text('Please enter a valid email or username (8-16 characters)').show();
    } else {
        $('#email').removeClass('is-invalid').addClass('is-valid');
        $('#email-error').hide();
    }

        // Validate password and confirm password
        const password = $('#yourPassword').val().trim();
        const confirmPassword = $('#confirm_password').val().trim();

        if (password.length < 8 || password.length > 16) {
            formIsValid = false;
            $('#yourPassword').addClass('is-invalid');
            $('#yourPassword').siblings('.invalid-feedback').show().text('Please make sure your password is not too short and matches.');
        } else {
            $('#yourPassword').removeClass('is-invalid').addClass('is-valid');
            $('#yourPassword').siblings('.invalid-feedback').hide();
        }

        if (password !== confirmPassword) {
            formIsValid = false;
            $('#confirm_password').addClass('is-invalid');
            $('#confirm_password').siblings('.invalid-feedback').show().text('Please make sure your password is not too short and matches.');
        } else if (confirmPassword.length >= 8 && confirmPassword.length <= 16) {
            $('#confirm_password').removeClass('is-invalid').addClass('is-valid');
            $('#confirm_password').siblings('.invalid-feedback').hide();
        }

        // Validate school ID file upload
        const schoolId = $('#school_id').val();
        const allowedFileTypes = /(\.jpg|\.jpeg|\.png)$/i;
        if (!schoolId || !allowedFileTypes.test(schoolId)) {
            formIsValid = false;
            $('#school_id').addClass('is-invalid');
        } else {
            $('#school_id').removeClass('is-invalid').addClass('is-valid');
        }

        // Enable/disable the register button based on form validity
        $('#register-btn').prop('disabled', !formIsValid);
    }

    // Real-time validation on form input fields
    $('#email, #yourPassword, #confirm_password, #school_id').on('input change', function() {
        validateForm();
    });

    // School ID image preview
    $('#school_id').on('change', function(event) {
        const file = event.target.files[0];
        const imagePreview = $('#imagePreview');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.attr('src', '').hide();
        }
        validateForm(); // Revalidate form on file change
    });

    // Form submission logic
    $('form').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        // Show the loader when the form is submitted
        $('#loaderOverlay').css('display', 'flex');

        // If validation passes, submit the form via AJAX
        const formData = new FormData(this); // For handling file upload

        $.ajax({
            url: 'register_process.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                // Hide the loader when the request completes
                $('#loaderOverlay').hide();

                // Show SweetAlert based on success or failure
                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'login.php';
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
            error: function() {
                $('#loaderOverlay').hide(); // Hide the loader on error

                Swal.fire({
                    title: 'Registration Successful!',
                    text: 'Thank you for registering. Your account is currently pending approval by the admin. The admins will review your submission, including the school ID you provided, before approving your account. Once approved, you will be able to log in and access your account.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'https://ramonianlostgems.com/';
                });
            }
        });
    });
     $('#college').on('change', function() {
        const selectedCollege = $(this).val();
        const courses = coursesByCollege[selectedCollege] || [];

        $('#course').html('<option value="" disabled selected>Select your course</option>');
        courses.forEach(course => {
            $('#course').append(`<option value="${course}">${course}</option>`);
        });
        validateForm(); // Revalidate the form after selecting a course
    });
    const coursesByCollege = {
        "CABA": [
            "Bachelor of Science in Accountancy",
            "Bachelor of Science in Accounting and Information System",
            "Bachelor of Science in Business Administration - Marketing",
            "Bachelor of Science in Business Administration - Financial Management",
            "Bachelor of Science in Business Administration - Human Resource Development Management",
            "Bachelor of Public Administration"
        ],
        "CAS": ["Bachelor of Science in Biology", "Bachelor of Science in Psychology"],
        "N/A": ["N/A"],
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
        "COE": [
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
        "CAF": ["Bachelor of Science in Environmental Science"],
        "CON": ["Bachelor of Science in Nursing"],
        "CTHM": [
            "Bachelor of Science in Hospitality Management",
            "Bachelor of Science in Tourism Management"
        ]
    };
    // Handle role selection change
    $('#role-select').on('change', function() {
        const selectedRole = $(this).val();

        // Redirect based on the selected role
        if (selectedRole === 'student') {
            window.location.href = 'https://ramonianlostgems.com/register.php/';
        } else if (selectedRole === 'faculty') {
            window.location.href = 'https://ramonianlostgems.com/register_staff.php';
        }
    });
});
    document.getElementById('school_id').addEventListener('change', function(event) {
        const file = event.target.files[0]; // Get the selected file
        const imagePreview = document.getElementById('imagePreview'); // Get the preview element

        if (file) {
            const reader = new FileReader(); // Create a FileReader to read the file

            reader.onload = function(e) {
                // Set the preview image's src to the file's data URL
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block'; // Display the image
            }

            reader.readAsDataURL(file); // Read the file as a Data URL (base64 encoded string)
        } else {
            imagePreview.src = ''; // Clear the preview if no file is selected
            imagePreview.style.display = 'none'; // Hide the image
        }
    });
    $(document).ready(function() {
    var modal = $('#termsModal');
    var registerBtn = $('#register-btn');
    
    modal.css('display', 'flex'); // Show the modal
    registerBtn.prop('disabled', true); // Disable the register button until terms are accepted

    // When the user clicks "I Accept", hide the modal and enable the register button
    $('#acceptTerms').on('click', function() {
        modal.hide(); // Hide the modal
        registerBtn.prop('disabled', false); // Enable the register button
    });

    // When the user clicks "Decline", redirect or handle as needed
    $('#declineTerms').on('click', function() {
        // You can redirect to another page, for example, the homepage
        window.location.href = 'https://ramonianlostgems.com/';
    });
});

  </script>
</body>
</html>
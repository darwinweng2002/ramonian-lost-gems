<?php

// Include the database configuration file
include 'config.php';

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
      text-align: justify; /* Justified alignment */
    }

    ol {
      padding-left: 20px;
    }

    ol li {
      margin-bottom: 15px;
      color: #555;
      font-size: 1rem;
      text-align: justify; /* Justified alignment for list items */
    }

    .hyper-link {
      text-align: center;
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
                  <img src="uploads/logo.png" alt="">
                  <br>
                  <span><?= $_settings->info('name') ?></span>
                </a>
              </div><!-- End Logo -->

              <div class="terms-container">
                <h5>Privacy Policy for Ramonian Lost Gems</h5>

                <p>Welcome to Ramonian Lost Gems. By using our mobile application, you agree to comply with and be bound by the following Privacy Policy.</p>

                <ol>
                  <li><strong>Collection of Personal Information:</strong> When registering for Ramonian Lost Gems, you will be asked to provide certain personal information such as your name, email address, and identification (School ID for students, Employee ID for PRMSU employees, or any valid ID for guest users). This information is required for the verification process to confirm that you are a legitimate user of the app.</li>
                  
                  <li><strong>Use of Information:</strong> The information you provide will be used solely for the purpose of verifying your identity and managing your access to the Ramonian Lost Gems mobile application. Your School ID, Employee ID, or any other valid ID will be used to ensure that you are a legitimate student, employee, or guest user.</li>
                  
                  <li><strong>Protection of Information:</strong> We assure you that the IDs (School ID for students, Employee ID for employees, and any valid ID for guest users) you submit will be used solely for verification purposes. All personal information will remain confidential and will not be shared with any third parties. Your data will be handled in strict compliance with applicable privacy laws and ethical guidelines to ensure the protection of your information.</li>
                  
                  <li><strong>Storage of Information:</strong> The data you provide during registration will be securely stored in our system. We take reasonable precautions to protect your personal information from unauthorized access, disclosure, alteration, or destruction.</li>
                  
                  <li><strong>Access to Information:</strong> Only authorized admin users of Ramonian Lost Gems will have access to the information you submit. Admins use this information to manage user accounts and verify lost and found reports. Admins are obligated to maintain the confidentiality of your personal information.</li>
                  
                  <li><strong>Sharing of Information:</strong> We do not share or sell your personal information to third parties. Your information will be used exclusively for internal operations related to lost and found item management, identity verification, and user account management.</li>
                  
                  <li><strong>Guest User Data:</strong> Guest users are required to provide a valid ID during registration to validate their identity. However, guest users have limited access to the app's features and are not required to provide as much personal information as registered users.</li>
                  
                  <li><strong>Changes to Privacy Policy:</strong> We reserve the right to modify this Privacy Policy at any time. Any changes will be posted on this page, and it is your responsibility to review the policy regularly. Continued use of the app after changes to the policy indicates your acceptance of the revised terms.</li>
                </ol>
              </div>
            </div>
          </div>
        </div>
        <div class="back-btn-container">
    <a href="https://ramonianlostgems.com/register.php" class="back-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left">
            <line x1="19" y1="12" x2="5" y2="12"/>
            <polyline points="12 19 5 12 12 5"/>
        </svg>
        Back
    </a>
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

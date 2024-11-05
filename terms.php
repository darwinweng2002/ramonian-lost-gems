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
      margin: 0;
      padding: 0;
      font-family: 'Arial', sans-serif;
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
      text-align: center;
      font-size: 24px;
    }

    .terms-container {
      background-color: #ffffff;
      padding: 25px; /* Adjusted padding for uniform margins */
      border-radius: 10px;
      box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
      margin-bottom: 30px;
      width: 100%;
      max-width: 800px; /* Set a maximum width for larger screens */
    }

    h5 {
      font-size: 1.5rem;
      font-weight: 600;
      text-align: center;
      color: #333;
    }

    p, ol li {
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

    /* Mobile responsiveness */
    @media (max-width: 768px) {
      body {
        font-size: 14px;
        padding: 10px;
      }

      .terms-container {
        padding: 20px; /* Adjusted padding for mobile devices */
        margin: 10px;
        border-radius: 8px;
      }

      h5 {
        font-size: 1.2rem;
      }

      .back-btn {
        font-size: 14px;
        padding: 8px 15px;
      }

      ol {
        padding-left: 15px;
      }

      ol li {
        font-size: 0.95rem;
        margin-bottom: 12px;
      }
    }

    @media (max-width: 480px) {
      .terms-container {
        padding: 15px; /* Smaller padding for very small screens */
      }

      h5 {
        font-size: 1.1rem;
      }

      p, ol li {
        font-size: 0.9rem;
      }

      .back-btn {
        font-size: 13px;
        padding: 8px 10px;
      }
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
                <h5>Terms and Conditions for Ramonian Lost Gems</h5>

                <p>Welcome to Ramonian Lost Gems. By using our mobile application, you agree to comply with and be bound by the following terms and conditions. Please read them carefully before registering an account.</p>

                <ol>
                  <li><strong>Acceptance of Terms:</strong> By registering and using the Ramonian Lost Gems mobile application, you agree to abide by these Terms and Conditions. These terms govern your access to and use of the application and its features, including the submission of personal information and ID verification documents for registration.</li>
                  
                  <li><strong>User Registration and Approval:</strong> To access the full features of the app, users must register an account. During registration, you are required to submit either a School ID (for students), Employee ID (for PRMSU employees), or any valid ID (for guest users). These documents are used for the sole purpose of validating your identity as a legitimate user of PRMSU Iba Campus or a guest. Once your registration is submitted, your account will not be immediately active. All accounts must be reviewed and approved by an administrator. You will be notified once your account is approved and ready for use. If your account is rejected, you will be notified via the email address you provided.</li>
                  
                  <li><strong>Posting Lost and Found Items:</strong> As a registered user, you can report lost and found items through the application. Reports must include specific details such as item name, description, and, optionally, multiple images of the item. Your reports will be reviewed by an admin before being published.</li>
                  
                  <li><strong>Claiming Items:</strong> Both registered users with approved accounts and guest users are now able to submit claim requests for lost or found items. This means that any user, whether registered or a guest, can initiate the claiming process if they believe they’ve found their lost item. However, all claim requests—whether from guests or registered users—will still undergo an admin verification process before final approval to ensure authenticity and prevent misuse.</li>
                  
                  <li><strong>Responsibilities of Admin Users:</strong> Admins have the responsibility of managing and reviewing all reported lost and found items. Admins can publish, delete, or reject reported items. They can also manage claim requests and change the status of an item to 'claimed' once it has been returned to its owner. Admins are authorized to update the status of reports and manage user accounts.</li>
                  
                  <li><strong>Prohibited Use:</strong> You agree not to misuse the Ramonian Lost Gems application for any unauthorized purposes, including providing false information or attempting to claim items that do not belong to you.</li>
                  
                  <li><strong>Modification of Terms:</strong> We reserve the right to modify these Terms and Conditions at any time. Any changes will be posted on this page, and it is your responsibility to review the terms regularly. Continued use of the app after changes constitutes your acceptance of the modified terms.</li>
                  
                  <li><strong>Limitation of Liability:</strong> Ramonian Lost Gems is not responsible for any direct or indirect damages resulting from the use of the mobile application, including the loss or theft of personal items.</li>
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
<?php require_once('inc/footer.php') ?>
</body>
</html>

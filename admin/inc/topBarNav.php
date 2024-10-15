<style>
  .nav-profile:hover {
    background-color: transparent !important; /* Removes the hover background color */
}

.nav-profile .dropdown-menu.profile {
    background-color: #f8f9fa !important; /* Set a consistent background for the dropdown */
    border-color: #ddd;
}

.nav-profile .dropdown-menu.profile h6, 
.nav-profile .dropdown-menu.profile span {
    color: #333 !important; /* Ensures the username and role text are always visible */
}

.nav-profile .dropdown-menu.profile .dropdown-item:hover {
    background-color: #e0e0e0 !important; /* Sets a soft hover background color for dropdown items */
}

</style>
<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">

  <div class="d-flex align-items-center justify-content-between">
    <a href="<?= base_url.'admin' ?>" class="logo d-flex align-items-center" style="margin-right: 20px; color: #CCC;">
      <img src="<?= validate_image($_settings->info('logo')) ?>" alt="System Logo">
    </a>
    <i style="margin-left: 50px;" class="bi bi-list toggle-sidebar-btn"></i>
  </div><!-- End Logo -->

  <!-- <div class="search-bar">
    <form class="search-form d-flex align-items-center" method="POST" action="#">
      <input type="text" name="query" placeholder="Search" title="Enter search keyword">
      <button type="submit" title="Search"><i class="bi bi-search"></i></button>
    </form>
  </div> -->
  <!-- End Search Bar -->

  <nav class="header-nav ms-auto">
    <ul class="d-flex align-items-center">
      <li class="nav-item dropdown pe-3">

        <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
          <img src="<?= validate_image($_settings->userdata('avatar')) ?>" alt="Profile" class="rounded-circle">
          <span class="d-none d-md-block dropdown-toggle ps-2"><?= $_settings->userdata('username') ?></span>
        </a><!-- End Profile Iamge Icon -->

        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
          <li class="dropdown-header">
            <h6><?= ucwords($_settings->userdata('firstname').' '.$_settings->userdata('lastname')) ?></h6>
            <span><?= $_settings->userdata('type') == 1 ? "Administrator" : "Staff" ?></span>
          </li>
          <li>
            <hr class="dropdown-divider">
          </li>

          <!-- <li>
            <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
              <i class="bi bi-person"></i>
              <span>My Profile</span>
            </a>
          </li> -->
          <li>
            <hr class="dropdown-divider">
          </li>

          <li>
            <a class="dropdown-item d-flex align-items-center" href="<?= base_url."admin?page=user" ?>">
              <i class="bi bi-gear"></i>
              <span>Account Settings</span>
            </a>
          </li>
          <li>
            <hr class="dropdown-divider">
          </li>
         
          <li>
            <hr class="dropdown-divider">
          </li>

          <li>
            <a class="dropdown-item d-flex align-items-center" href="<?= base_url.'classes/Login.php?f=logout' ?>">
              <i class="bi bi-box-arrow-right"></i>
              <span>Sign Out</span>
            </a>
          </li>

        </ul><!-- End Profile Dropdown Items -->
      </li><!-- End Profile Nav -->

    </ul>
  </nav><!-- End Icons Navigation -->

</header><!-- End Header -->
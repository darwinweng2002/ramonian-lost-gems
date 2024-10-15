<style>
  .sidebar-nav .nav-content a i {
    font-size: .9rem;
  }
  .nav-item .dropdown-menu {
    position: absolute;
    top: 100%;  /* Position below the trigger element */
    left: 0;
    z-index: 1050;  /* Make sure it appears on top */
    display: none;  /* Hide the dropdown initially */
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);  /* Add shadow for visibility */
}

/* Show dropdown when hovered or clicked */
.nav-item.dropdown:hover .dropdown-menu,
.nav-item .dropdown-menu.show {
    display: block;  /* Display on hover or when active */
}
</style>
<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

<ul class="sidebar-nav" id="sidebar-nav">

  <li class="nav-item">
      <i class="bi bi-grid"></i>
    </a>
  </li><!-- End Dashboard Nav -->

  <li class="nav-item">
    <a class="nav-link <?= $page != 'user/list' ? 'collapsed' : '' ?> nav-users" href="https://ramonianlostgems.com/admin/messages/reported_items.php">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 30 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-gem"><path d="M6 3h12l4 6-10 13L2 9Z"/><path d="M11 3 8 9l4 13 4-13-3-6"/><path d="M2 9h20"/></svg>
      <span>Reported Found Items</span>
      <?php 
      $message = $conn->query("SELECT * FROM `message_history` where `status` = 0")->num_rows;
      ?>
      <?php if($message > 0): ?>
        <span class="badge rounded-pill bg-danger text-light ms-4"><?= format_num($message) ?></span>
      <?php endif; ?>
    </a>
  </li>
  </li><!-- End Components Nav -->
  <li class="nav-item">
    <a class="nav-link <?= $page != 'user/list' ? 'collapsed' : '' ?> nav-users" href="https://ramonianlostgems.com/admin/missing_items/missing_tbl.php">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 30 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-gem"><path d="M6 3h12l4 6-10 13L2 9Z"/><path d="M11 3 8 9l4 13 4-13-3-6"/><path d="M2 9h20"/></svg>
      <span>Reported Missing Items</span>
      <?php 
      $message = $conn->query("SELECT * FROM `missing_items` where `status` = 0")->num_rows;
      ?>
      <?php if($message > 0): ?>
        <span class="badge rounded-pill bg-danger text-light ms-4"><?= format_num($message) ?></span>
      <?php endif; ?>
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $page != 'user/list' ? 'collapsed' : '' ?> nav-users" href="https://ramonianlostgems.com/admin/log_claims/claimed_items_table.php/">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 30 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-history"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg>
      <span>Claim History Logs</span>
      <?php 
      $message = $conn->query("SELECT * FROM `claim_history` where `status` = 1")->num_rows;
      ?>
      <?php if($message > 0): ?>
        <span class="badge rounded-pill bg-danger text-light ms-4"><?= format_num($message) ?></span>
      <?php endif; ?>
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $page != 'user/list' ? 'collapsed' : '' ?> nav-users" href="https://ramonianlostgems.com/admin/claimer/admin_view_claims.php/">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 30 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-question"><path d="M22 10.5V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12c0 1.1.9 2 2 2h12.5"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/><path d="M18 15.28c.2-.4.5-.8.9-1a2.1 2.1 0 0 1 2.6.4c.3.4.5.8.5 1.3 0 1.3-2 2-2 2"/><path d="M20 22v.01"/></svg>
      <span>Claim Request</span>
      <?php 
      $message = $conn->query("SELECT * FROM `claimer` where `status` = 1")->num_rows;
      ?>
      <?php if($message > 0): ?>
        <span class="badge rounded-pill bg-danger text-light ms-4"><?= format_num($message) ?></span>
      <?php endif; ?>
    </a>
  </li>
  <li class="nav-item dropdown"> <!-- Added dropdown class here -->
    <a class="nav-link <?= $page != 'user/list' ? 'collapsed' : '' ?> nav-users dropdown-toggle" href="#" id="deniedReportsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
    <i class="fa fa-exclamation-circle"></i>
      <span>Denied Item Reports</span>
      <?php 
      // Query to get the total count of denied items from both message_history and missing_items tables
      $denied_count_query = "
        SELECT SUM(denied_count) AS total_denied FROM (
          SELECT COUNT(*) AS denied_count FROM `message_history` WHERE `is_denied` = '1'
          UNION ALL
          SELECT COUNT(*) AS denied_count FROM `missing_items` WHERE `is_denied` = '1'
        ) as denied_items";

      $denied_count_result = $conn->query($denied_count_query)->fetch_assoc();
      $total_denied = $denied_count_result['total_denied'];
      ?>
      <?php if($total_denied > 0): ?>
        <span class="badge rounded-pill bg-danger text-light ms-4"><?= $total_denied ?></span>
      <?php endif; ?>
    </a>

    <!-- Dropdown Menu for sub-links -->
    <ul class="dropdown-menu" aria-labelledby="deniedReportsDropdown">
      <li>
        <a class="dropdown-item" href="https://ramonianlostgems.com/admin/messages/denied_items.php">Denied Found Items</a>
      </li>
      <li>
        <a class="dropdown-item" href="https://ramonianlostgems.com/admin/missing_items/denied_missing.php">Denied Missing Items</a>
      </li>
    </ul>
</li>

  <li class="nav-item">
    <a class="nav-link <?= $page != 'user/list' ? 'collapsed' : '' ?> nav-users" href="https://ramonianlostgems.com/admin/user_accounts/view_users.php">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 30 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users">
        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
        <circle cx="9" cy="7" r="4"/>
        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
      </svg>
      <span>Pending Student Accounts</span>

      <?php 
      // Fetch the count of pending accounts where status is set to 'pending' (status = 'pending')
      $pending_accounts = $conn->query("SELECT COUNT(*) AS count FROM `user_member` WHERE `status` = 'pending'")->fetch_assoc();
      ?>
      
      <!-- Check if there are any pending accounts -->
      <?php if($pending_accounts['count'] > 0): ?>
        <span class="badge rounded-pill bg-danger text-light ms-4">
          <?= $pending_accounts['count'] ?>  <!-- Display pending accounts count -->
        </span>
      <?php endif; ?>
    </a>
</li>

  <li class="nav-item">
    <a class="nav-link <?= $page != 'user/list' ? 'collapsed' : '' ?> nav-users" href="https://ramonianlostgems.com/admin/user_accounts/approved_users.php">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 30 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users">
        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
        <circle cx="9" cy="7" r="4"/>
        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
      </svg>
      <span>Approved Student Accounts</span>

      <?php 
      // Fetch the count of approved accounts where status is set to 'approved' (status = 1)
      $approved_accounts = $conn->query("SELECT COUNT(*) AS count FROM `user_member` WHERE `status` = 'approved'")->fetch_assoc();
      ?>
      
      <!-- Check if there are any approved accounts -->
      <?php if($approved_accounts['count'] > 0): ?>
        <span class="badge rounded-pill bg-danger text-light ms-4">
          <?= $approved_accounts['count'] ?>  <!-- Display approved accounts count -->
        </span>
      <?php endif; ?>
    </a>
</li>
<li class="nav-item">
    <a class="nav-link <?= $page != 'user/list' ? 'collapsed' : '' ?> nav-users" href="https://ramonianlostgems.com/admin/user_accounts/faculty_view.php">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 30 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users">
        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
        <circle cx="9" cy="7" r="4"/>
        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
      </svg>
      <span>Employee Accounts</span>

      <?php 
      // Fetch the count of approved accounts where status is set to 'approved' (status = 1)
      $approved_accounts = $conn->query("SELECT COUNT(*) AS count FROM `user_staff` WHERE `status` = 'pending'")->fetch_assoc();
      ?>
      
      <!-- Check if there are any approved accounts -->
      <?php if($approved_accounts['count'] > 0): ?>
        <span class="badge rounded-pill bg-danger text-light ms-4">
          <?= $approved_accounts['count'] ?>  <!-- Display approved accounts count -->
        </span>
      <?php endif; ?>
    </a>
</li>
   

  <?php if($_settings->userdata('type') == 1): ?>
  <li class="nav-heading">SSG Admin Maintenance</li>

  <li class="nav-item">
    <a class="nav-link <?= $page != 'user/list' ? 'collapsed' : '' ?> nav-users" href="<?= base_url."admin?page=user/list" ?>">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 30 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-cog"><path d="M2 21a8 8 0 0 1 10.434-7.62"/><circle cx="10" cy="8" r="5"/><circle cx="18" cy="18" r="3"/><path d="m19.5 14.3-.4.9"/><path d="m16.9 20.8-.4.9"/><path d="m21.7 19.5-.9-.4"/><path d="m15.2 16.9-.9-.4"/><path d="m21.7 16.5-.9.4"/><path d="m15.2 19.1-.9.4"/><path d="m19.5 21.7-.4-.9"/><path d="m16.9 15.2-.4-.9"/></svg>
      <span>Admin Users</span>
    </a>
  </li>
  <?php endif; ?>
</ul>

</aside><!-- End Sidebar-->


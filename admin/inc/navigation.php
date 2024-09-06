<style>
  .sidebar-nav .nav-content a i {
    font-size: .9rem;
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
    <a class="nav-link <?= !in_array($page, ['categories', 'categories/manage_category', 'categories/view_category']) ? 'collapsed' : '' ?>" data-bs-target="#categories-nav" data-bs-toggle="collapse" href="#" data-bs-collapse="<?= in_array($page, ['categories', 'categories/manage_category', 'categories/view_category']) ? 'true' : 'false' ?>">
      <i class="bi bi-menu-button-wide"></i><span>Categories</span><i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="categories-nav" class="nav-content collapse <?= in_array($page, ['categories', 'categories/manage_category', 'categories/view_category']) ? 'show' : '' ?> " data-bs-parent="#sidebar-nav">
      <li>
        <a href="<?= base_url.'admin/?page=categories/manage_category' ?>" class="<?= $page == 'categories/manage_category' ? 'active' : '' ?>">
          <i class="bi bi-plus-lg" style="font-size:.9rem"></i><span>Add New</span>
        </a>
      </li>
      <li>
        <a href="<?= base_url.'admin/?page=categories' ?>" class="<?= $page == 'categories' ? 'active' : '' ?>">
          <i class="bi bi-circle"></i><span>List</span>
        </a>
      </li>
    </ul>
  </li><!-- End Components Nav -->
  <li class="nav-item">
    <a class="nav-link <?= !in_array($page, ['items', 'items/manage_item', 'items/view_item']) ? 'collapsed' : '' ?>" data-bs-target="#items-nav" data-bs-toggle="collapse" href="#" data-bs-collapse="<?= in_array($page, ['items', 'items/manage_item', 'items/view_item']) ? 'true' : 'false' ?>">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 30 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-gem"><path d="M6 3h12l4 6-10 13L2 9Z"/><path d="M11 3 8 9l4 13 4-13-3-6"/><path d="M2 9h20"/></svg>
      <span>Items</span>
      <?php 
      $pitem = $conn->query("SELECT * FROM `item_list` where `status` = 0")->num_rows;
      ?>
      <?php if($pitem > 0): ?>
        <span class="badge rounded-pill bg-danger text-light ms-4"><?= format_num($pitem) ?></span>
      <?php endif; ?>
      
      <i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="items-nav" class="nav-content collapse <?= in_array($page, ['items', 'items/manage_item', 'items/view_item']) ? 'show' : '' ?> " data-bs-parent="#sidebar-nav">
      <li>
        <a href="<?= base_url.'admin/?page=items/manage_item' ?>" class="<?= $page == 'items/manage_item' ? 'active' : '' ?>">
          <i class="bi bi-plus-lg" style="font-size:.9rem"></i><span>Add New</span>
        </a>
      </li>
      <li>
        <a href="<?= base_url.'admin/?page=items' ?>" class="<?= $page == 'items' ? 'active' : '' ?>">
          <i class="bi bi-circle"></i><span>List</span>
        </a>
      </li>
    </ul>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $page != 'inquiries' ? 'collapsed' : '' ?> nav-users" href="<?= base_url."admin?page=inquiries" ?>">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 30 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mails"><rect width="16" height="13" x="6" y="4" rx="2"/><path d="m22 7-7.1 3.78c-.57.3-1.23.3-1.8 0L6 7"/><path d="M2 8v11c0 1.1.9 2 2 2h14"/></svg>
      <span>Messages</span>
      <?php 
      $message = $conn->query("SELECT * FROM `inquiry_list` where `status` = 0")->num_rows;
      ?>
      <?php if($message > 0): ?>
        <span class="badge rounded-pill bg-danger text-light ms-4"><?= format_num($message) ?></span>
      <?php endif; ?>
    </a>
  </li>
  <?php if($_settings->userdata('type') == 1): ?>
  <li class="nav-heading">SSG Admin Maintenance</li>

  <li class="nav-item">
    <a class="nav-link <?= $page != 'user/list' ? 'collapsed' : '' ?> nav-users" href="<?= base_url."admin?page=user/list" ?>">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 30 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-question"><path d="M22 10.5V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12c0 1.1.9 2 2 2h12.5"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/><path d="M18 15.28c.2-.4.5-.8.9-1a2.1 2.1 0 0 1 2.6.4c.3.4.5.8.5 1.3 0 1.3-2 2-2 2"/><path d="M20 22v.01"/></svg>
      <span>Admin Users</span>
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $page != 'user/list' ? 'collapsed' : '' ?> nav-users" href="http://localhost/lostgemramonian/admin/user_accounts/view_users.php">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 30 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      <span>User Accounts</span>
      <?php 
      $message = $conn->query("SELECT * FROM `user_member` where `status` = 0")->num_rows;
      ?>
      <?php if($message > 0): ?>
        <span class="badge rounded-pill bg-danger text-light ms-4"><?= format_num($message) ?></span>
      <?php endif; ?>
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $page != 'user/list' ? 'collapsed' : '' ?> nav-users" href="http://localhost/lostgemramonian/admin/activities/activity_log.php/">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 30 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-history"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg>
      <span>Activity Logs</span>
      <?php 
      $message = $conn->query("SELECT * FROM `claim_history` where `status` = 0")->num_rows;
      ?>
      <?php if($message > 0): ?>
        <span class="badge rounded-pill bg-danger text-light ms-4"><?= format_num($message) ?></span>
      <?php endif; ?>
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $page != 'user/list' ? 'collapsed' : '' ?> nav-users" href="http://localhost/lostgemramonian/admin/claims/claims_list.php">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 30 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail-question"><path d="M22 10.5V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12c0 1.1.9 2 2 2h12.5"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/><path d="M18 15.28c.2-.4.5-.8.9-1a2.1 2.1 0 0 1 2.6.4c.3.4.5.8.5 1.3 0 1.3-2 2-2 2"/><path d="M20 22v.01"/></svg>
      <span>Claim Request</span>
      <?php 
      $message = $conn->query("SELECT * FROM `claims` where `status` = 0")->num_rows;
      ?>
      <?php if($message > 0): ?>
        <span class="badge rounded-pill bg-danger text-light ms-4"><?= format_num($message) ?></span>
      <?php endif; ?>
    </a>
  </li>
  <?php endif; ?>

</ul>

</aside><!-- End Sidebar-->


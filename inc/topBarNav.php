<!-- ======= Header ======= -->
<style type="text/css">
    /* Styling for the logo and header */
    .logo img {
        width: 55px;
        height: 55px;
    }

    /* Aligning content inside the header */
    .container-lg {
        display: flex;
        justify-content: space-between; /* Push logo to the left, and sidebar toggle to the right */
        align-items: center;
        width: 100%;
    }

    /* Sidebar styling */
    #side-nav-bar {
        position: fixed;
        left: -250px; /* Initially hide the sidebar */
        top: 0;
        width: 250px;
        height: 100%;
        background-color: #2c3e50;
        color: #ecf0f1;
        transition: left 0.3s ease;
        z-index: 9999;
        overflow-y: auto;
        padding-top: 20px;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.5);
    }

    /* Sidebar links */
    #side-nav-bar ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
    }

    #side-nav-bar ul li {
        padding: 10px;
        border-bottom: 1px solid #34495e;
    }

    #side-nav-bar ul li a {
        text-decoration: none;
        color: #ecf0f1;
        display: block;
        font-size: 16px;
        transition: background-color 0.3s ease, padding-left 0.3s ease;
    }

    #side-nav-bar ul li a:hover {
        background-color: #34495e;
        padding-left: 20px;
    }

    /* Sidebar toggle button styling */
    #sidebar-toggle-button {
        background-color: #3498db;
        color: white;
        padding: 17px 30px;
        border: none;
        border-radius: 2px;
        cursor: pointer;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    }

    #sidebar-toggle-button:hover {
        background-color: #2980b9;
    }

    /* Responsive adjustments for smaller screens */
    @media (max-width: 768px) {
        #sidebar-toggle-button {
            display: block;
        }

        #side-nav-bar {
            width: 50%;
            left: -100%;
        }
    }

    /* Centering and aligning logo and button */
    .logo {
        margin-left: 0;
    }

    /* Push sidebar toggle button to the right */
    .navbar-toggler {
        margin-left: auto;
        display: flex;
        justify-content: flex-end;
    }
</style>

<header id="header" class="header fixed-top d-flex align-items-center">
    <!-- Sidebar -->
    <div id="side-nav-bar">
        <ul>
            <li><a href="https://ramonianlostgems.com/user_members/dashboard.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-user">
                    <circle cx="12" cy="12" r="10" />
                    <circle cx="12" cy="10" r="3" />
                    <path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662" />
                </svg> Profile</a></li>
            <li><a href="https://ramonianlostgems.com/send_message.php">Report Found Items</a></li>
            <li><a href="https://ramonianlostgems.com/send_missing.php">Report Missing Items</a></li>
            <li><a href="https://ramonianlostgems.com/itemss/items.php">Browse Items</a></li>
            <li style="position: absolute; bottom: 0;">
                <a href="https://ramonianlostgems.com/logout.php" class="btn btn-primary mx-2">Logout</a>
            </li>
        </ul>
    </div>

    <div class="container-lg d-flex justify-content-between px-4">
        <!-- Logo aligned to the left -->
        <div class="logo d-flex align-items-center">
            <a href="<?= base_url ?>">
                <img src="<?= validate_image($_settings->info('logo')) ?>" alt="System Logo">
            </a>
        </div>

        <!-- Sidebar Toggle Button on the right -->
        <button id="sidebar-toggle-button" class="navbar-toggler d-lg-none">
            ☰
        </button>
    </div>
</header>

<script type="text/javascript">
    document.getElementById('sidebar-toggle-button').addEventListener('click', function() {
        const sideNavBar = document.getElementById('side-nav-bar');

        if (sideNavBar.style.left === '0px' || sideNavBar.style.left === '') {
            sideNavBar.style.left = '-250px'; // Hide the sidebar
        } else {
            sideNavBar.style.left = '0'; // Show the sidebar
        }
    });
</script>

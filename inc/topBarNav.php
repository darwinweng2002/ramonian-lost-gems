<!-- ======= Header ======= -->
<style type="text/css">
    /* Initially hide dropdown menu */
    #navbar-menu {
        display: block;
        font-weight: bold;
        color: #012970;
    }

    /* Make dropdown menu visible and regular nav links hidden on smaller screens */
    @media (max-width: 512px) {
        /* Show toggle button on smaller screens */
        #navbar-toggler {
            display: block;
        }

        /* Hide regular nav links */
        .header-nav ul {
            display: none;
            width: 50%;
        }

        .header-nav ul li {
            font-size: 10px;
            margin-left: 10px;
        }

        .nav-text {
            display: none;
        }

        /* Show dropdown menu when toggled */
        #navbar-menu.show {
            display: none;
            flex-direction: column;
        }

        .header-nav ul li:hover {
            background-color: #F6B825;
            color: white;
            padding: 2px;
            border-radius: 4px;
        }

        .nav-item:hover .nav-link {
            background-color: #F6B825;
        }

        title {
            padding: 10px;
            background-color: #ccc;
        }

        /* Base styling for tooltip */
        a title {
            position: absolute;
            background-color: #333;
            color: #fff;
            padding: 5px 10px;
            border-radius: 4px;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
            z-index: 1000; /* Ensure tooltip appears above other elements */
        }

        /* When tooltip is visible */
        .title.show {
            opacity: 1;
            visibility: visible;
        }
    }

    /* Logo styling */
    .logo img {
        margin-top: 3px;
        width: 55px;
        height: 55px;
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

    /* Aligning content inside the header */
    .container-lg {
        display: flex;
        justify-content: space-between; /* Push logo to the left, and sidebar toggle to the right */
        align-items: center;
    }

    /* Responsive adjustments for sidebar */
    @media (max-width: 512px) {
        #side-nav-bar {
            width: 50%;
            left: -100%;
        }

        #sidebar-toggle-button {
            display: block;
        }
    }

    /* Hide the sidebar toggle button when the sidebar is open */
    #sidebar-toggle-button.hidden {
        display: none;
    }
</style>

<header id="header" class="header fixed-top d-flex align-items-center">
    <!-- Sidebar -->

    <!-- Sidebar Trigger Button aligned to the right -->
    <button id="sidebar-toggle-button" class="ml-auto">☰</button>

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
                <a href="https://ramonianlostgems.com/logout.php" class="btn btn-primary mx-2">
                    Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Logo aligned to the left -->
    <div class="container-lg d-flex justify-content-between px-4">
        <div class="d-flex align-items-center justify-content-between" style="margin-left: 0;">
            <a href="<?= base_url ?>" class="logo d-flex align-items-center">
                <img src="<?= validate_image($_settings->info('logo')) ?>" alt="System Logo">
            </a>
        </div>
    </div>
</header>

<script type="text/javascript">
    document.getElementById('navbar-toggler').addEventListener('click', function() {
        const navbarMenu = document.getElementById('navbar-menu');
        navbarMenu.classList.toggle('show');
    });

    document.getElementById('sidebar-toggle-button').addEventListener('click', function() {
        const sideNavBar = document.getElementById('side-nav-bar');

        if (sideNavBar.style.left === '0px' || sideNavBar.style.left === '') {
            sideNavBar.style.left = '-250px'; // Hide the sidebar
        } else {
            sideNavBar.style.left = '0'; // Show the sidebar
        }
    });
</script>

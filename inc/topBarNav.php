<!-- ======= Header ======= -->
<style type="text/css">
 /* Initially hide dropdown menu */
/* Initially hide dropdown menu */
#navbar-menu {
    display: block;
    font-weight: bold;
    color: #012970;
}
/* Make dropdown menu visible and regular nav links hidden on smaller screens */
@media (max-width: 800px) {
    /* Show toggle button on smaller screens */
    #navbar-toggler {
        display: block;
    }

    /* Hide regular nav links */
    .header-nav ul {
        display: none;
        width: auto;
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
        padding: 5px;
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
.logo img {
 
    margin-right: 0;
}
 .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {background-color: #f1f1f1}

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown-buttonm {
            background-color: #007bff;
            color: white;
            padding: 12px 16px;
            font-size: 16px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        .dropdown-button:hover {
            background-color: #0056b3;
        }


        
        /*-----*/
        /* Dropdown Container */
/* Dropdown Container */
.dropdown {
    position: relative;
}

/* Dropdown Button */
.dropdown-button {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: inherit;
    padding: 10px;
    background-color: transparent;
    border: none;
    cursor: pointer;
    border-radius: 4px;
}

.dropdown-button:hover {
    background-color: #F6B825; /* Background color on hover */
    color: white; /* Text color on hover */
}

/* Dropdown Content */
.dropdown-content {
    display: none;
    position: absolute;
    background-color: #f9f9f9;
    min-width: 160px;
    box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
    z-index: 1;
    border-radius: 4px;
    top: 100%; /* Position below the button */
    left: 0;
}

/* Dropdown Links */
.dropdown-content a {
    display: block;
    padding: 10px 16px;
    color: black;
    text-decoration: none;
    position: relative;
    overflow: hidden;
}

.dropdown-content a::before {
    content: "";
    position: absolute;
    width: 100%;
    height: 3px;
    bottom: 0;
    left: -100%;
    background-color: #007BFF; /* Color of the border-bottom animation */
    transition: transform 0.3s ease-in-out;
    transform: translateX(0);
}

.dropdown-content a:hover::before {
    transform: translateX(100%);
}


/* Show Dropdown Content on Hover */
.dropdown:hover .dropdown-content {
    display: block;
}

/* Sidebar styling */
#side-nav-bar {
    position: fixed;
    left: -250px; /* Initially hide the sidebar */
    top: 0;
    width: 250px;
    height: 100%;
    background-color: #2c3e50; /* Dark background for a professional look */
    color: #ecf0f1; /* Light text color */
    transition: left 0.3s ease; /* Smooth transition */
    z-index: 9999; /* Make sure it's above other elements */
    overflow-y: auto;
    padding-top: 20px;
    box-shadow: 2px 0 5px rgba(0,0,0,0.5); /* Add subtle shadow */
}

/* No hover effect for sidebar */
#side-nav-bar:hover {
    left: -250px; /* Ensure sidebar stays hidden when not toggled */
}

/* Sidebar links */
#side-nav-bar ul {
    list-style-type: none;
    padding: 0;
    margin: 0;
}

#side-nav-bar ul li {
    padding: 10px;
    border-bottom: 1px solid #34495e; /* Light border for separation */
}

#side-nav-bar ul li a {
    text-decoration: none;
    color: #ecf0f1; /* Match text color with the sidebar */
    display: block;
    font-size: 16px;
    transition: background-color 0.3s ease, padding-left 0.3s ease;
}

#side-nav-bar ul li a:hover {
    background-color: #34495e; /* Slightly lighter background on hover */
    padding-left: 20px; /* Indent the link on hover for a subtle effect */
}

/* Sidebar toggle button */
#sidebar-toggle-button {
    position: fixed;
    left: 0;
    top: 20px; /* Adjust to align with your design */
    background-color: #3498db; /* Professional color */
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    z-index: 10000; /* Make sure it's above other elements */
}

#sidebar-toggle-button:hover {
    background-color: #2980b9; /* Darker shade on hover */
}

.header-nav .nav-link {
    text-decoration: none;
}
/* Responsive adjustments for sidebar */
@media (max-width: 800px) {
    #side-nav-bar {
        width: 50%; /* Adjust width for small screens */
        left: -100%; /* Initially hide the sidebar */
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

<!-- Sidebar Trigger Button -->
    <button id="sidebar-toggle-button">☰</button>

    <!-- Sidebar -->
    <div id="side-nav-bar">
        <ul>
            <br>
            <br>
            <li><a href="http://localhost/lostgemramonian/user_members/dashboard.php"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-user"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="10" r="3"/><path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662"/></svg> Profile</a></li>
            <li><a href="<?= base_url.'?page=settings' ?>"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg> Settings</a></li>
            <li><a href="http://localhost/lostgemramonian/logout.php" class="btn btn-primary mx-2"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="30" viewBox="0 0 30 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>Logout</a></li>
        </ul>
    </div>

    <div class="container-lg d-flex justify-content-between px-4">
        <div class="d-flex align-items-center justify-content-between" style="margin-left: 0;">
            <a href="<?= base_url ?>" class="logo d-flex align-items-center">
                <img src="<?= validate_image($_settings->info('logo')) ?>" alt="System Logo">
                <span class="d-none d-lg-block"><?= $_settings->info('short_name') ?></span>
            </a>
        </div>

        <button class="navbar-toggler d-lg-none" id="navbar-toggler" type="button" aria-expanded="false">
            <span class="navbar-toggler-icon">☰</span>
        </button>

        <nav class="header-nav me-auto" id="navbar-menu">
            <ul class="d-flex align-items-center h-100">
                <li class="nav-item pe-3">
                    <a href="<?= base_url ?>" class="nav-link" title="Go to Home Page">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-home">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        <span class="nav-text">Home</span>
                    </a>
                </li>

                <li class="nav-item pe-3" class="active">
                    <a href="<?= base_url.'?page=items' ?>" id="home-link" class="nav-link" title="Lost and Found">
                        <svg title="This is a icon!" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-search">
                            <path d="M14 2v4a2 2 0 0 0 2 2h4"/>
                            <path d="M4.268 21a2 2 0 0 0 1.727 1H18a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v3"/>
                            <path d="m9 18-1.5-1.5"/>
                            <circle cx="5" cy="14" r="3"/>
                        </svg>
                        <span class="nav-text">Lost Items</span>
                    </a>
                </li>

                <li class="nav-item pe-3 dropdown">
                <a href="#" class="nav-link dropdown-button" title="Post an Item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-signpost">
                        <path d="M12 3v3"/>
                        <path d="M18.5 13h-13L2 9.5 5.5 6h13L22 9.5Z"/>
                        <path d="M12 13v8"/>
                    </svg>
                    <span class="nav-text">Post an Item</span>
                </a>
                <div class="dropdown-content" style="color: #000000;">
                <a href="<?= base_url.'?page=found' ?>" style="color: #000000; padding: 12px 16px; text-decoration: none; display: block;">Post Found Item</a>
                <a href="<?= base_url.'?page=missing' ?>" style="color: #000000; padding: 12px 16px; text-decoration: none; display: block;">Post Missing Item</a>
                </div>
                </li>


                <li class="nav-item pe-3">
                    <a href="<?= base_url.'?page=about' ?>" class="nav-link" title="About Page">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search-slash">
                            <path d="m13.5 8.5-5 5"/>
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.3-4.3"/>
                        </svg>
                        <span class="nav-text">About</span>
                    </a>
                </li>

                <li class="nav-item pe-3">
                    <a href="<?= base_url.'?page=contact' ?>" class="nav-link" title="Message">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mails">
                            <rect width="16" height="13" x="6" y="4" rx="2"/>
                            <path d="m22 7-7.1 3.78c-.57.3-1.23.3-1.8 0L6 7"/>
                            <path d="M2 8v11 c0 1.1.9 2 2 2h14"/>
                        </svg>
                        <span class="nav-text">Message</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="dropdown">
            <button class="dropdown-buttonm">Menu</button>
            <div class="dropdown-content">
                <a href="<?= base_url.'admin' ?>">Login</a>
                <a href="<?= base_url.'user_members/dashboard.php' ?>">Profile</a>
            </div>
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

document.getElementById('navbar-toggler').addEventListener('click', function() {
    const navbarMenu = document.getElementById('navbar-menu');
    navbarMenu.classList.toggle('show');
});


</script>

<!-- ======= Header ======= -->
<style type="text/css">
/* Styling for the logo and header */
.logo img {
        width: 55px; /* Logo size */
        height: 55px;
        object-fit: contain; /* Ensure the logo fits within its container */
        margin-top: 3px;
    }

.container-lg {
    display: flex;
    justify-content: space-between; /* Push logo to the left, and sidebar toggle to the right */
    align-items: center;
    width: 100%;
}

.header {
    height: 60px;
    background-color: #fff;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
    padding: 0;
}

/* Sidebar styling */
#side-nav-bar {
    position: fixed;
    right: -250px; /* Initially hide the sidebar */
    top: 0;
    width: 250px;
    height: 100%;
    background-color: #2c3e50;
    color: #ecf0f1;
    transition: right 0.3s ease;
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
    font-size: 14px;
    padding: 5px 10px;
    transition: background-color 0.3s ease, padding-left 0.3s ease;
}

#side-nav-bar ul li a:hover {
    background-color: #34495e;
    padding-left: 30px; /* Increase padding slightly on hover for a sliding effect */
}

/* Sidebar toggle button styling */
#sidebar-toggle-button {
    position: fixed;
    right: 40px; /* Keep the button on the right */
    top: 0; /* Adjust to align with your design */
    background-color: #3498db; /* Professional color */
    color: white;
    padding: 21.5px 30px;
    border: none;
    border-radius: 2px;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    z-index: 10000; /* Make sure it's above other elements */
}

#sidebar-toggle-button:hover {
    background-color: #2980b9;
}

/* Responsive adjustments */
@media (max-width: 1200px) {
    #sidebar-toggle-button {
        display: block;
    }

    #side-nav-bar {
        width: 250px;
        right: -250px; /* Hide the sidebar by default */
    }
    #side-nav-bar {
    position: fixed;
    right: -250px; /* Initially hide the sidebar */
    top: 0;
    width: 250px;
    height: 100%;
    background-color: #2c3e50;
    color: #ecf0f1;
    transition: right 0.3s ease;
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
    font-size: 14px;
    padding: 5px 10px;
    transition: background-color 0.3s ease, padding-left 0.3s ease;
}

#side-nav-bar ul li a:hover {
    background-color: #34495e;
    padding-left: 30px; /* Increase padding slightly on hover for a sliding effect */
}

/* Sidebar toggle button styling */
#sidebar-toggle-button {
    position: fixed;
    right: 40px; /* Keep the button on the right */
    top: 0; /* Adjust to align with your design */
    background-color: #3498db; /* Professional color */
    color: white;
    padding: 21.5px 30px;
    border: none;
    border-radius: 2px;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    z-index: 10000; /* Make sure it's above other elements */
}

#sidebar-toggle-button:hover {
    background-color: #2980b9;
}
}

@media (max-width: 1200px) {
    #sidebar-toggle-button {
        display: block;
    }

    #side-nav-bar {
        width: 250px;
        right: -250px; /* Hide the sidebar by default */
    }

    .main-content {
        padding-right: 0; /* No margin when sidebar is hidden */
    }
}
@media (min-width: 1201px) {
    #side-nav-bar {
        right: 0; /* Ensure sidebar is visible on larger screens */
    }

    #sidebar-toggle-button {
        display: block; /* Hide the toggle button on larger screens */
    }

    .main-content {
        padding-right: 270px; /* Ensure content isn't hidden behind sidebar */
    }
}
@media (max-width: 1024px) {
    #sidebar-toggle-button {
        display: block;
    }

    #side-nav-bar {
        right: -250px; /* Ensure the sidebar starts hidden */
    }

    .main-content {
        padding-right: 0; /* No margin when sidebar is hidden */
    }
}
</style>

<header id="header" class="header fixed-top d-flex align-items-center">
    <!-- Sidebar -->
    <div id="side-nav-bar">
        <br><br>
        <ul>
            <br><br>
            <?php
            // Check if the user is a staff member or a regular user
            if (isset($_SESSION['staff_id'])) {
                // If logged in as staff, show the staff profile link
                echo '<li><a href="https://ramonianlostgems.com/user_members/faculty_dashboard.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-user">
                            <circle cx="12" cy="12" r="10" />
                            <circle cx="12" cy="10" r="3" />
                            <path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662" />
                        </svg> Profile </a></li>';
            } elseif (isset($_SESSION['user_id'])) {
                // If logged in as a regular user, show the user profile link
                echo '<li><a href="https://ramonianlostgems.com/user_members/dashboard.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-user">
                            <circle cx="12" cy="12" r="10" />
                            <circle cx="12" cy="10" r="3" />
                            <path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662" />
                        </svg> Profile</a></li>';
            }
            ?>
            <!-- Other menu links remain the same -->
            <li><a href="https://ramonianlostgems.com/send_message.php"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-mouse-pointer"><path d="M12.034 12.681a.498.498 0 0 1 .647-.647l9 3.5a.5.5 0 0 1-.033.943l-3.444 1.068a1 1 0 0 0-.66.66l-1.067 3.443a.5.5 0 0 1-.943.033z"/><path d="M21 11V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h6"/></svg> Report Found Items</a></li>
            <li><a href="https://ramonianlostgems.com/send_missing.php"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-search"><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M4.268 21a2 2 0 0 0 1.727 1H18a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v3"/><path d="m9 18-1.5-1.5"/><circle cx="5" cy="14" r="3"/></svg> Report Missing Items</a></li>
            <li><a href="https://ramonianlostgems.com/itemss/items.php"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg> Browse  Items</a></li>
            <li><a href="https://ramonianlostgems.com/main.php"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"/></svg> About</a></li>
            <li style="position: absolute; bottom: 0;"><a href="#" id="logout-button" class="btn btn-primary mx-2"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="30" viewBox="0 0 30 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>Logout</a></li>
        </ul>
    </div>

    <div class="container-lg d-flex justify-content-between px-4">
        <!-- Logo aligned to the left -->
        <div class="logo d-flex align-items-center">
            
                <img src="<?= validate_image($_settings->info('logo')) ?>" alt="System Logo">
            </a>
        </div>

        <!-- Sidebar Toggle Button on the right -->
        <button id="sidebar-toggle-button" class="navbar-toggler d-lg-none">
            ☰
        </button>
    </div>
</header>

<!-- Include SweetAlert library -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    document.getElementById('sidebar-toggle-button').addEventListener('click', function() {
        const sideNavBar = document.getElementById('side-nav-bar');

        // Show or hide the sidebar
        if (sideNavBar.style.right === '0px') {
            sideNavBar.style.right = '-250px'; // Hide sidebar
        } else {
            sideNavBar.style.right = '0'; // Show sidebar
        }
    });

    // Add event listener for the logout button
    document.getElementById('logout-button').addEventListener('click', function(event) {
        event.preventDefault(); // Prevent the default behavior of the anchor tag

        // Trigger SweetAlert confirmation
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to log out.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, log out',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show SweetAlert loading spinner
                Swal.fire({
                    title: 'Logging out...',
                    allowOutsideClick: false, // Prevent user from clicking outside
                    didOpen: () => {
                        Swal.showLoading(); // Show the loader animation
                    }
                });

                // Simulate logout delay for demo purposes
                setTimeout(function() {
                    // Redirect to logout URL after delay
                    window.location.href = "https://ramonianlostgems.com/logout.php";
                }, 2000); // Adjust the delay as needed, here it is set to 2 seconds
            }
        });
    });
</script>
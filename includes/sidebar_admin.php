<aside>
    <div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
            <div class="sb-sidenav-menu">
                <div class="nav">
                    <!-- Navbar Brand with logo-->
                    <a class="navbar-brand ps-3" href="dashboard_admin.php?user_id=<?php echo $user_id; ?>">
                        <img src="img/lg2.png" alt="Logo"
                            style="height: 100px; width: auto; max-width: 100%; margin-left: 38px; ">
                    </a>
                    <img src="" alt="">
                    <div class="sb-sidenav-menu-heading"></div>
                    <a class="nav-link" href="dashboard_admin.php?user_id=<?php echo $user_id; ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                        Dashboard
                    </a>
                    <a class="nav-link" href="manage_users.php?user_id=<?php echo $user_id; ?>">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-users"></i></div>
                        Manage Users
                    </a>
                    <a class="nav-link" href="manage_members.php?user_id=<?php echo $user_id; ?>">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-users"></i></div>
                        Manage Members
                    </a>
                    <a class="nav-link" href="reports_admin.php?user_id=<?php echo $user_id; ?>">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-file-lines"></i></div>
                        Billing Reports
                    </a>
                    <a class="nav-link" href="payment_history_admin.php?user_id=<?php echo $user_id; ?>">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-file-lines"></i></div>
                        Payment History
                    </a>
                    <!-- <a class="nav-link" href="index.html">
                                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                    Activity Logs
                                </a> -->
                </div>
            </div>
            <div class="sb-sidenav-footer">
                <div class="small">Logged in as:</div>
                President
            </div>
        </nav>
    </div>
</aside>
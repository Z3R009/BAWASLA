<?php
include 'DBConnection.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // Handle the case when the user_id is not set, e.g., redirect to login
    header('Location: index.php');
    exit();
}

//Add users
function isUsernameAvailable($username, $connection)
{
    $sql = "SELECT COUNT(*) as count FROM users WHERE username = '$username'";
    $result = mysqli_query($connection, $sql);
    $row = mysqli_fetch_assoc($result);
    return ($row['count'] == 0);
}

if (isset($_POST['submit'])) {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_type = $_POST['user_type'];


    if (!isUsernameAvailable($username, $connection)) {

        echo "Username is not available. Please choose a different one.";
        exit;
    }


    $sql = "INSERT INTO users (user_id, username, password, user_type) VALUES (?, ?, ?, ?)";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("isss", $user_id, $username, $password, $user_type);

    if ($stmt->execute()) {
        header('Location: manage_users.php?user_id=<?php echo $user_id; ?>');
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Retrieve users

$user_types = ['President', 'Treasurer', 'Meter Reader'];
$user_types_str = "'" . implode("', '", $user_types) . "'";

$select = mysqli_query($connection, "SELECT * FROM users WHERE user_type IN ($user_types_str)");


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <!-- Link to your local Bootstrap CSS file -->
    <link href="start/css/style.min.css" rel="stylesheet" />
    <link href="start/css/styles.css" rel="stylesheet" />
    <script src="fontawesome-free-6.3.0-web/js/all.js"></script>
    <link href="img/lg2.png" rel="icon">

    <style>
        .action-dropdown {
            position: relative;
            display: inline-block;
        }

        .action-btn {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f1f1f1;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {
            background-color: #ddd;
        }

        .action-dropdown:hover .dropdown-content {
            display: block;
        }

        td:nth-child(3),
        th:nth-child(3) {
            width: 100px;
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <a class="navbar-brand ps-3"></a>
        <!-- Sidebar Toggle-->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i
                class="fas fa-bars"></i></button>
        <!-- Navbar Search-->
        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
            <div class="input-group">
            </div>
        </form>
        <!-- Navbar-->
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="manage_account_pres.php?user_id=<?php echo $user_id; ?>"><i
                                class="fa-solid fa-gear"></i><span style="margin-left: 20px; font-size: large; ">
                                Account Settings</span></a></li>
                    <li><a class="dropdown-item" href="#!"><i class="fa-solid fa-file"></i><span
                                style="margin-left: 20px; font-size: large; ">
                                Activity Logs</span></a></li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li><a class="dropdown-item" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i><span
                                style="margin-left: 20px; font-size: large; ">
                                Log Out</span></a></li>
                </ul>
            </li>
        </ul>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <!-- Navbar Brand with logo-->
                        <a class="navbar-brand ps-3" href="dashboard_admin.php?user_id=<?php echo $user_id; ?>">
                            <img src="img/lg2.png" alt="Logo"
                                style="height: 100px; width: auto; max-width: 100%; margin-left: 38px; ">
                            <!-- The height is increased to 80px for a larger logo -->
                        </a>
                        <div class="sb-sidenav-menu-heading"></div>
                        <a class="nav-link" href="dashboard_admin.php?user_id=<?php echo $user_id; ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </a>
                        <a class="nav-link" href="#">
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
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 d-flex justify-content-between align-items-center">
                        Manage Users
                        <button class="btn btn-primary ms-3" data-bs-toggle="modal" data-bs-target="#addUserModal"
                            type="button">
                            <i class="fas fa-plus"></i> Add User
                        </button>
                    </h1>

                    <!-- Modal for Add User Form -->
                    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addUserModalLabel">Add User
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="post" id="addUserForm">
                                        <div class="mb-3">
                                            <input type="hidden" id="item_id" name="user_id" value="<?php
                                            echo rand(100000000, 999999999);
                                            ?>" required autocomplete="off">
                                        </div>
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="username" name="username"
                                                placeholder="Enter username" required autocomplete="off">
                                        </div>
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" class="form-control" id="password" name="password"
                                                placeholder="Enter Password" required autocomplete="off">
                                        </div>
                                        <div class="mb-3">
                                            <label for="userType" class="form-label">User Type</label>
                                            <select class="form-select" id="user_type" name="user_type">
                                                <option selected disabled>Select User Type</option>
                                                <option value="President">President</option>
                                                <option value="Treasurer">Treasurer</option>
                                                <option value="Meter Reader">Meter Reader</option>
                                            </select>
                                        </div>
                                        <div class="modal-footer">
                                            <!-- <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Close</button> -->
                                            <button type="button" class="btn btn-secondary"
                                                onclick="clearForm()">Clear</button>
                                            <button type="submit" id="submit" name="submit"
                                                class="btn btn-primary">Save</button>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>


                    <div class="card mb-4">
                        <div class="card-body">
                            <table id="datatablesSimple">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>User Type</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($select)) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                                            <td><?php echo htmlspecialchars($row['user_type']); ?></td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-primary dropdown-toggle" type="button"
                                                        id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                        Action
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end" style="z-index: 1050;"
                                                        aria-labelledby="actionDropdown">
                                                        <li><a class="dropdown-item" href="javascript:void(0)"
                                                                onclick="editUser(<?php echo $row['user_id']; ?>, '<?php echo $row['username']; ?>', '<?php echo $row['password']; ?>', '<?php echo $row['user_type']; ?>')">
                                                                Edit
                                                            </a>
                                                        </li>
                                                        <li><a class="dropdown-item" href="javascript:void(0)"
                                                                onclick="deleteUser(<?php echo $row['user_id']; ?>)">Delete</a>
                                                        </li>

                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Modal for Edit User Form -->
                    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="editUserForm" method="post" action="update_user.php">
                                        <input type="hidden" id="edit_user_id" name="user_id">
                                        <div class="mb-3">
                                            <label for="edit_username" class="form-label">Username</label>
                                            <input type="text" class="form-control" name="username" id="edit_username"
                                                placeholder="Enter username" required>
                                        </div>
                                        <div class="mb-3">
                                            <input type="hidden" class="form-control" id="edit_password"
                                                placeholder="Enter Password" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label for="edit_userType" class="form-label">User Type</label>
                                            <select class="form-select" name="user_type" id="edit_userType">
                                                <option value="President">President</option>
                                                <option value="Treasurer">Treasurer</option>
                                                <option value="Meter Reader">Meter Reader</option>
                                                <option value="Member">Member</option>
                                            </select>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary" name="update_user"
                                                onclick="saveEdit()">Save
                                                changes</button>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </main>
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Your Website 2023</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms &amp; Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <!-- startbootstrap -->
    <script src="bootstrap-5.2.3/js/bootstrap.bundle.min.js"></script>
    <script src="start/js/scripts.js"></script>
    <script src="start/js/Chart.min.js"></script>
    <script src="start/assets/demo/chart-area-demo.js"></script>
    <script src="start/assets/demo/chart-bar-demo.js"></script>
    <script src="start/js/simple-datatables.min.js"></script>
    <script src="start/js/datatables-simple-demo.js"></script>

    <!-- function -->

    <!-- clear -->
    <script>
        // Function to clear form
        function clearForm() {
            document.getElementById('addUserForm').reset();
        }
    </script>

    //delete
    <script>
        function deleteUser(userId) {
            if (confirm("Are you sure you want to delete this user?")) {
                window.location.href = 'delete_user.php?user_id=' + userId + '&confirm=yes';
            }
        }
    </script>

    <script>
        // Function to handle the Edit button click and populate the modal with user data
        function editUser(userId, username, password, userType) {
            // Set the form values in the modal
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_password').value = password;

            // Pre-select the correct user type
            const userTypeSelect = document.getElementById('edit_userType');
            for (let i = 0; i < userTypeSelect.options.length; i++) {
                if (userTypeSelect.options[i].value === userType) {
                    userTypeSelect.options[i].selected = true;
                    break;
                }
            }

            // Show the modal
            var editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'));
            editUserModal.show();
        }

        // Function to save changes
        function saveEdit() {
            // Retrieve form values
            var userId = document.getElementById('edit_user_id').value;
            var username = document.getElementById('edit_username').value;
            var password = document.getElementById('edit_password').value;
            var userType = document.getElementById('edit_userType').value;

            // Example code for sending data to the server
            // You should implement the actual save functionality, e.g., using AJAX

            console.log('user_id:', user_id);
            console.log('username:', username);
            console.log('password:', password);
            console.log('user_type:', user_type);

            // Close the modal
            var editUserModal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'));
            editUserModal.hide();
        }
    </script>

</body>

</html>
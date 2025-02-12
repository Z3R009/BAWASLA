<?php
include 'DBConnection.php';

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // Redirect to login if user_id is not set
    header('Location: index.php');
    exit();
}

// Fetch total charges per purok (address)
$sql_charges = "SELECT m.address AS address, SUM(mr.current_charges) AS current_charges
                FROM members m
                JOIN meter_reading mr ON m.member_id = mr.member_id
                GROUP BY m.address";
$result_charges = mysqli_query($connection, $sql_charges);

$addresses = [];
$current_charges = [];

while ($row = mysqli_fetch_assoc($result_charges)) {
    $addresses[] = $row['address'];
    $current_charges[] = $row['current_charges'];
}

// Fetch total charges per month
$sql_monthly_charges = "
    SELECT MONTHNAME(reading_date) as month, 
           SUM(current_charges) as total_charges 
    FROM meter_reading 
    GROUP BY MONTH(reading_date)
    ORDER BY MONTH(reading_date)";
$result_monthly_charges = mysqli_query($connection, $sql_monthly_charges);

$months = [];
$total_charges = [];

while ($row = mysqli_fetch_assoc($result_monthly_charges)) {
    $months[] = $row['month'];
    $total_charges[] = $row['total_charges'];
}

// Fetch total members
$sql_members = "SELECT COUNT(*) as total_members FROM members";
$result_members = mysqli_query($connection, $sql_members);
$total_members = mysqli_fetch_assoc($result_members)['total_members'];

// Fetch total consumption (sum of current_charges) and count entries
$sql_avg_consumption = "SELECT SUM(current_charges) as total_consumption, 
                        AVG(current_reading) as avg_usage, 
                        COUNT(current_reading) as count_consumption 
                        FROM meter_reading";
$result_avg_consumption = mysqli_query($connection, $sql_avg_consumption);
$row = mysqli_fetch_assoc($result_avg_consumption);
$total_consumption = $row['total_consumption'];
$count_consumption = $row['count_consumption'];

// Calculate average consumption based on current_charges
$avg_consumption = $count_consumption > 0 ? $total_consumption / $count_consumption : 0;
$avg_usage = $row['avg_usage']; // Average usage from current_reading

// Fetch average charges per purok (address)
$sql_avg_charges = "
    SELECT m.address AS address, 
           AVG(mr.current_charges) AS avg_charges
    FROM members m
    JOIN meter_reading mr ON m.member_id = mr.member_id
    GROUP BY m.address";
$result_avg_charges = mysqli_query($connection, $sql_avg_charges);

$avg_charges = [];

while ($row = mysqli_fetch_assoc($result_avg_charges)) {
    $avg_charges[] = $row['avg_charges'];
}

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="start/css/style.min.css" rel="stylesheet" />
    <link href="start/css/styles.css" rel="stylesheet" />
    <script src="fontawesome-free-6.3.0-web/js/all.js"></script>
    <script src="start/js/Chart.min.js"></script>

    <style>
        .box {
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 20px;
            margin-top: 5px;

            border: 2px solid #000;
        }

        .row {
            display: flex;
            justify-content: space-between;
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
                    <li><a class="dropdown-item" href="manage_account_treas.php?user_id=<?php echo $user_id; ?>"><i
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
                        <a class="navbar-brand ps-3" href="dashboard_treasurer.php?user_id=<?php echo $user_id; ?>">
                            <img src="img/lg2.png" alt="Logo"
                                style="height: 100px; width: auto; max-width: 100%; margin-left: 38px; ">
                            <!-- The height is increased to 80px for a larger logo -->
                        </a>
                        <div class="sb-sidenav-menu-heading"></div>
                        <a class="nav-link" href="#">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </a>
                        <a class="nav-link" href="transaction_treas.php?user_id=<?php echo $user_id; ?>">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-money-bill-transfer"></i></div>
                            Manage Transaction
                        </a>
                        <a class="nav-link" href="invoice_treas.php?user_id=<?php echo $user_id; ?>">
                            <div class="sb-nav-link-icon"><i class="fa-regular fa-file"></i></div>
                            Manage Invoice
                        </a>
                        <a class="nav-link" href="pending_treas.php?user_id=<?php echo $user_id; ?>">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-clock"></i></div>
                            Pending Payment
                        </a>
                        <a class="nav-link" href="reports_treas.php?user_id=<?php echo $user_id; ?>">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-file-lines"></i></div>
                            Billing Reports
                        </a>
                        <!-- <a class="nav-link" href="index.html">
                                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                    Activity Logs
                                </a> -->
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Logged in as:</div>
                    Treasurer
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <!-- <h1 class="mt-4 d-flex">Dashboard</h1> -->
                    <ol class="breadcrumb mb-4">
                    </ol>
                    <div class="container-fluid px-4">

                        <!-- Card Section -->
                        <div class="row mb-4 justify-content-center">
                            <div class="col-lg-3">
                                <div class="card text-center bg-dark mb-4" style="height: 80px;">
                                    <div class="card-body text-white">
                                        <h5 class="card-title"><?php echo $total_members; ?></h5>
                                        <p class="card-text">Total Members</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="card text-center bg-dark mb-4" style="height: 80px;">
                                    <div class="card-body text-white">
                                        <h5 class="card-title"><?php echo number_format($avg_usage, 2); ?> m³</h5>
                                        <p class="card-text">Average Usage</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="card text-center bg-dark mb-4" style="height: 80px;">
                                    <div class="card-body text-white">
                                        <h5 class="card-title">&#8369; <?php echo number_format($avg_consumption, 2); ?>
                                        </h5>
                                        <p class="card-text">Average Bill</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="card text-center bg-dark mb-4" style="height: 80px;">
                                    <div class="card-body text-white">
                                        <h5 class="card-title">&#8369;
                                            <?php echo number_format($total_consumption, 2); ?>
                                        </h5>
                                        <p class="card-text">Total Charges</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="box">
                                        <h6>Total Charges per Purok</h6>
                                        <canvas id="chargesChart" style="width: 100%; height: 100px;"></canvas>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="box">
                                        <h6>Average Charges per Purok</h6>
                                        <canvas id="pieChart" style="width: 100%; height: 100px;"></canvas>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="box">
                                        <h6>Monthly Charges</h6>
                                        <canvas id="monthlyChargesChart" style="width: 1000px; height: auto;"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
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


                <!-- Chart.js -->
                <script src="gentella/Chart.js/dist/Chart.min.js"></script>

                <script>// Data passed from PHP to JavaScript
                    var addresses = <?php echo json_encode($addresses); ?>;
                    var currentCharges = <?php echo json_encode($current_charges); ?>;
                    var months = <?php echo json_encode($months); ?>;
                    var totalCharges = <?php echo json_encode($total_charges); ?>;
                    var avgCharges = <?php echo json_encode($avg_charges); ?>;

                    // Bar Chart: Total Charges per Purok
                    var ctx1 = document.getElementById('chargesChart').getContext('2d');
                    new Chart(ctx1, {
                        type: 'bar',
                        data: {
                            labels: addresses,
                            datasets: [{
                                label: 'Total Charges per Purok',
                                data: currentCharges,
                                backgroundColor: [
                                    'rgba(75, 192, 192, 0.6)',
                                    'rgba(153, 102, 255, 0.6)',
                                    'rgba(255, 159, 64, 0.6)',
                                    'rgba(54, 162, 235, 0.6)',
                                    'rgba(255, 99, 132, 0.6)',
                                    'rgba(201, 203, 207, 0.6)'
                                ],
                                borderWidth: 1,
                                label: 'Total Charges per Purok'
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: false // This removes the legend from the chart
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Total Charges (PHP)'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Address'
                                    }
                                }
                            }
                        }
                    });

                    // Line Chart: Monthly Charges
                    var ctx2 = document.getElementById('monthlyChargesChart').getContext('2d');
                    new Chart(ctx2, {
                        type: 'line',
                        data: {
                            labels: months,
                            datasets: [{
                                label: 'Total Charges',
                                data: totalCharges,
                                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            },
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: false
                                }, tooltip: {
                                    enabled: true // Enables tooltips, if desired
                                }
                            }
                        }
                    });

                    // Pie Chart: Average Charges per Purok
                    var ctx3 = document.getElementById('pieChart').getContext('2d');
                    new Chart(ctx3, {
                        type: 'pie',
                        data: {
                            labels: addresses,
                            datasets: [{
                                label: 'Average Charges per Purok',
                                data: avgCharges,
                                backgroundColor: [
                                    'rgba(75, 192, 192, 0.6)',
                                    'rgba(153, 102, 255, 0.6)',
                                    'rgba(255, 159, 64, 0.6)',
                                    'rgba(54, 162, 235, 0.6)',
                                    'rgba(255, 99, 132, 0.6)',
                                    'rgba(201, 203, 207, 0.6)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            return `₱${context.raw.toFixed(2)}`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                </script>

</body>

</html>
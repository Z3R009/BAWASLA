<!-- dfhdhfhdf -->
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

// retrieve/if done reading

$select_isDone = mysqli_query($connection, "
    SELECT 
        members.member_id, 
        CONCAT(members.last_name, ', ', members.first_name, ' ', members.middle_name) AS fullname, 
        isDone
    FROM 
        members
");

// retrieve status/paid not paid

$select_status = mysqli_query($connection, "
    SELECT 
        members.member_id, 
        CONCAT(members.last_name, ', ', members.first_name, ' ', members.middle_name) AS fullname,
        meter_reading.status
    FROM 
        meter_reading
    JOIN 
        members ON meter_reading.member_id = members.member_id
");

// Get the selected month from the URL or default to null
$selected_month = isset($_GET['month']) ? $_GET['month'] : null;

// Fetch total charges per purok (address)
$sql_charges = "SELECT m.address AS address, SUM(mr.current_charges) AS current_charges
                FROM members m
                JOIN meter_reading mr ON m.member_id = mr.member_id";

if ($selected_month) {
    $sql_charges .= " WHERE MONTHNAME(mr.reading_date) = ?";
}
$sql_charges .= " GROUP BY m.address";

$stmt_charges = $connection->prepare($sql_charges);

if ($selected_month) {
    $stmt_charges->bind_param("s", $selected_month);
    $stmt_charges->execute();
    $result_charges = $stmt_charges->get_result();
} else {
    $result_charges = $connection->query($sql_charges);
}

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

if ($selected_month) {
    $sql_avg_consumption .= " WHERE MONTHNAME(reading_date) = ?";
}

$stmt_avg_consumption = $connection->prepare($sql_avg_consumption);

if ($selected_month) {
    $stmt_avg_consumption->bind_param("s", $selected_month);
    $stmt_avg_consumption->execute();
    $result_avg_consumption = $stmt_avg_consumption->get_result();
} else {
    $result_avg_consumption = $connection->query($sql_avg_consumption);
}

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
    JOIN meter_reading mr ON m.member_id = mr.member_id";

if ($selected_month) {
    $sql_avg_charges .= " WHERE MONTHNAME(mr.reading_date) = ?";
}
$sql_avg_charges .= " GROUP BY m.address";

$stmt_avg_charges = $connection->prepare($sql_avg_charges);

if ($selected_month) {
    $stmt_avg_charges->bind_param("s", $selected_month);
    $stmt_avg_charges->execute();
    $result_avg_charges = $stmt_avg_charges->get_result();
} else {
    $result_avg_charges = $connection->query($sql_avg_charges);
}

$avg_charges = [];
while ($row = mysqli_fetch_assoc($result_avg_charges)) {
    $avg_charges[] = $row['avg_charges'];
}

// Fetch the count of members by payment method
$sql_payment_methods_count = "
SELECT payment_method, COUNT(*) AS member_count
FROM history";

if ($selected_month) {
    $sql_payment_methods_count .= " WHERE MONTHNAME(payment_date) = ?";
}
$sql_payment_methods_count .= " GROUP BY payment_method";

$stmt_payment_methods_count = $connection->prepare($sql_payment_methods_count);

if ($selected_month) {
    $stmt_payment_methods_count->bind_param("s", $selected_month);
    $stmt_payment_methods_count->execute();
    $result_payment_methods_count = $stmt_payment_methods_count->get_result();
} else {
    $result_payment_methods_count = $connection->query($sql_payment_methods_count);
}

$payment_methods = [];
$member_counts = [];
while ($row = mysqli_fetch_assoc($result_payment_methods_count)) {
    $payment_methods[] = $row['payment_method'];
    $member_counts[] = $row['member_count'];
}

// Fetch total members who paid using G-Cash or Walk-in by Purok
$sql_payment_methods_count = "
SELECT m.address AS address, 
       SUM(CASE WHEN t.payment_method = 'G-Cash' THEN 1 ELSE 0 END) AS gcash_count,
       SUM(CASE WHEN t.payment_method = 'Walk-in' THEN 1 ELSE 0 END) AS walkin_count
FROM members m
JOIN history t ON m.member_id = t.member_id";

if ($selected_month) {
    $sql_payment_methods_count .= " WHERE MONTHNAME(t.payment_date) = ?";
}

$sql_payment_methods_count .= " GROUP BY m.address";

$stmt_payment_methods_count = $connection->prepare($sql_payment_methods_count);

if ($selected_month) {
    $stmt_payment_methods_count->bind_param("s", $selected_month);
    $stmt_payment_methods_count->execute();
    $result_payment_methods_count = $stmt_payment_methods_count->get_result();
} else {
    $result_payment_methods_count = $connection->query($sql_payment_methods_count);
}

$gcash_counts = [];
$walkin_counts = [];

while ($row = mysqli_fetch_assoc($result_payment_methods_count)) {
    $gcash_counts[] = $row['gcash_count'];
    $walkin_counts[] = $row['walkin_count'];
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
    <link href="img/lg2.png" rel="icon">

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

        #month-select {
            width: 100%;
            padding: 8px;
            border: 2px solid black;
            border-radius: 5px;
            background-color: white;
            font-size: 16px;
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand with logo-->
        <a class="navbar-brand ps-3" href="#">

        </a>
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
        <?php include "Includes/sidebar_admin.php"; ?>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">

                    <!-- <h1 class="mt-4 d-flex">Dashboard</h1> -->
                    <ol class="breadcrumb mb-4">
                    </ol>
                    <div class="container-fluid px-4">

                        <!-- Card Section -->
                        <div class="row mb-4 justify-content-center">
                            <div class="col-lg-2">
                                <div class="card text-center bg-dark mb-4" style="height: 80px;">
                                    <div class="card-body text-white">
                                        <h5 class="card-title"><?php echo $total_members; ?></h5>
                                        <p class="card-text">Total Members</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="card text-center bg-dark mb-4" style="height: 80px;">
                                    <div class="card-body text-white">
                                        <h5 class="card-title"><?php echo number_format($avg_usage, 2); ?> m³</h5>
                                        <p class="card-text">Average Usage</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="card text-center bg-dark mb-4" style="height: 80px;">
                                    <div class="card-body text-white">
                                        <h5 class="card-title">&#8369; <?php echo number_format($avg_consumption, 2); ?>
                                        </h5>
                                        <p class="card-text">Average Bill</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="card text-center bg-dark mb-4" style="height: 80px;">
                                    <div class="card-body text-white">
                                        <h5 class="card-title">&#8369;
                                            <?php echo number_format($total_consumption, 2); ?>
                                        </h5>
                                        <p class="card-text">Total Charges</p>
                                    </div>
                                </div>
                            </div>
                            <!-- ComboBox for Month -->
                            <div class="col-lg-2">
                                <select id="month-select">
                                    <option value="select_month" disabled selected>Select Month</option>
                                    <option value="January" <?= $selected_month == 'January' ? 'selected' : '' ?>>January
                                    </option>
                                    <option value="February" <?= $selected_month == 'February' ? 'selected' : '' ?>>
                                        February</option>
                                    <option value="March" <?= $selected_month == 'March' ? 'selected' : '' ?>>March
                                    </option>
                                    <option value="April" <?= $selected_month == 'April' ? 'selected' : '' ?>>April
                                    </option>
                                    <option value="May" <?= $selected_month == 'May' ? 'selected' : '' ?>>May</option>
                                    <option value="June" <?= $selected_month == 'June' ? 'selected' : '' ?>>June</option>
                                    <option value="July" <?= $selected_month == 'July' ? 'selected' : '' ?>>July</option>
                                    <option value="August" <?= $selected_month == 'August' ? 'selected' : '' ?>>August
                                    </option>
                                    <option value="September" <?= $selected_month == 'September' ? 'selected' : '' ?>>
                                        September</option>
                                    <option value="October" <?= $selected_month == 'October' ? 'selected' : '' ?>>October
                                    </option>
                                    <option value="November" <?= $selected_month == 'November' ? 'selected' : '' ?>>
                                        November</option>
                                    <option value="December" <?= $selected_month == 'December' ? 'selected' : '' ?>>
                                        December</option>
                                    <!-- <option value="" <?= !$selected_month ? 'selected' : '' ?>>All
                                        Months</option> -->
                                </select>
                            </div>
                            <!-- ComboBox for Address -->
                            <!-- <div class="col-lg-2">
                                <select id="address-select" class="form-select">
                                    <option selected disabled>Select Purok</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                </select>
                            </div> -->

                            <div class="container-fluid px-4">
                                <div class="row">
                                    <div class="col-lg-8">
                                        <div class="box">
                                            <h6>Total Charges per Purok</h6>
                                            <canvas id="chargesChart" style="width: 100%; height: 200px;"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="box">
                                            <h6>Payment Method Used</h6>
                                            <canvas id="paymentMethodChart"
                                                style="width: 100%; height: 200px;"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="container-fluid px-4">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="box">
                                            <h6>Monthly Charges</h6>
                                            <canvas id="monthlyChargesChart"
                                                style="width: 1000px; height: 200px;"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="container-fluid px-4">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="box">
                                            <h6>Payment Methods by Purok (G-Cash vs Walk-in)</h6>
                                            <canvas id="paymentChart" style="width: 100%; height: 300px;"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- <div class="card mb-4">
                        <div class="card-body">
                            <table id="datatablesSimple">
                                <thead>
                                    <tr>
                                        <th>Fullname</th>
                                        <th>Done Reading</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($select_isDone)) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                            <td><?php echo htmlspecialchars($row['isDone']); ?></td>

                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div> -->

                    <div class="card mb-4">
                        <div class="card-body">
                            <table id="datatablesSimple">
                                <thead>
                                    <tr>
                                        <th>Fullname</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($select_status)) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
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

                <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>



                <script>
                    // Data passed from PHP to JavaScript
                    var addresses = <?php echo json_encode($addresses); ?>;
                    var currentCharges = <?php echo json_encode($current_charges); ?>;
                    var months = <?php echo json_encode($months); ?>;
                    var totalCharges = <?php echo json_encode($total_charges); ?>;
                    var avgCharges = <?php echo json_encode($avg_charges); ?>;

                    var paymentMethods = <?php echo json_encode($payment_methods); ?>;
                    var memberCounts = <?php echo json_encode($member_counts); ?>;
                    var gcashCounts = <?php echo json_encode($gcash_counts); ?>;
                    var walkinCounts = <?php echo json_encode($walkin_counts); ?>;

                    // Initialize Charts
                    var ctx1 = document.getElementById('chargesChart').getContext('2d');
                    var chargesChart = new Chart(ctx1, {
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
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: true,
                                    labels: {
                                        generateLabels: function (chart) {
                                            var originalLabels = Chart.defaults.plugins.legend.labels.generateLabels(chart);
                                            var customLabels = originalLabels.map(function (label, index) {
                                                // Update the text for each legend entry
                                                label.text = 'Purok ' + (index + 1) + ' (Charges)';
                                                label.fillStyle = chart.data.datasets[0].backgroundColor[index]; // Use corresponding color
                                                return label;
                                            });
                                            return customLabels;
                                        },

                                    }
                                }
                            }
                        }
                    });

                    var ctx2 = document.getElementById('paymentMethodChart').getContext('2d');
                    var paymentMethodChart = new Chart(ctx2, {
                        type: 'pie',
                        data: {
                            labels: paymentMethods,
                            datasets: [{
                                data: memberCounts,
                                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'],
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: true
                                }
                            }
                        }
                    });

                    var ctx3 = document.getElementById('monthlyChargesChart').getContext('2d');

                    var monthlyChargesChart = new Chart(ctx3, {
                        type: 'line',
                        data: {
                            labels: months,
                            datasets: [{
                                label: 'Total Charges per Month',
                                data: totalCharges,
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 2,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: true
                                }
                            }
                        },
                        plugins: [{
                            beforeDraw: (chart) => {
                                let ctx = chart.ctx;
                                let chartArea = chart.chartArea;
                                if (!chartArea) return;

                                // Create gradient based on actual chart size
                                let gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                                gradient.addColorStop(0, 'rgba(75, 192, 192, 1)');
                                gradient.addColorStop(1, 'rgba(75, 192, 192, 0.2)');

                                // Apply gradient to dataset
                                chart.data.datasets[0].backgroundColor = gradient;
                            }
                        }]
                    });



                    // Stacked Bar Chart for Total Charges per Purok
                    var ctxPayment = document.getElementById('paymentChart').getContext('2d');
                    var paymentChart = new Chart(ctxPayment, {
                        type: 'bar',
                        data: {
                            labels: addresses,
                            datasets: [{
                                label: 'G-Cash',
                                data: gcashCounts,
                                backgroundColor: 'rgba(54, 162, 235, 0.6)', // Blue
                            },
                            {
                                label: 'Walk-in',
                                data: walkinCounts,
                                backgroundColor: 'rgba(255, 99, 132, 0.6)', // Red
                            }
                            ]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });



                    // Function to filter data based on the selected month
                    function filterDataByMonth(selectedMonth) {
                        // Ensure proper filtering by finding the indices that match the selected month
                        var filteredIndices = months.map((month, index) => month === selectedMonth ? index : -1).filter(index => index !== -1);

                        // Filter data for the bar chart (addresses and charges)
                        var filteredAddresses = filteredIndices.map(index => addresses[index]);
                        var filteredCharges = filteredIndices.map(index => currentCharges[index]);

                        // Filter data for the line chart (total charges by month)
                        var filteredTotalCharges = months.map((month, index) => month === selectedMonth ? totalCharges[index] : 0);

                        // Aggregate data for the pie chart
                        var filteredPaymentCounts = paymentMethods.map((method, methodIndex) => {
                            return filteredIndices.reduce((sum, dataIndex) => sum + (methodIndex === dataIndex ? memberCounts[dataIndex] : 0), 0);
                        });

                        // Update bar chart
                        chargesChart.data.labels = filteredAddresses;
                        chargesChart.data.datasets[0].data = filteredCharges;
                        chargesChart.update();

                        // Update line chart
                        monthlyChargesChart.data.datasets[0].data = filteredTotalCharges;
                        monthlyChargesChart.update();

                        // Update pie chart
                        paymentMethodChart.data.datasets[0].data = filteredPaymentCounts;
                        paymentMethodChart.update();
                    }

                    // Event listener for the month dropdown
                    document.getElementById('month-select').addEventListener('change', function () {
                        var selectedMonth = this.value;
                        if (selectedMonth) {
                            filterDataByMonth(selectedMonth);
                        }
                    });
                </script>

                <script>
                    document.getElementById("month-select").addEventListener("change", function () {
                        var selectedMonth = this.value;
                        window.location.href = "dashboard_admin.php?month=" + selectedMonth;
                    });
                </script>

</body>

</html>
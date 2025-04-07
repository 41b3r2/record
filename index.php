<?php
session_start(); 
include('connector.php');

if (!isset($_SESSION['ad_id'])) {
    header("Location: pages-login.php");
    exit();
}

$fname = $_SESSION['fname'];
$lname = $_SESSION['lname'];
$email_user = $_SESSION['email'];

$fullname = $fname." ".$lname;

// Fetch truck count and types
$query = "SELECT COUNT(*) as total FROM truck";
$stmt = $conn->prepare($query);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$truck_count = $row['total'];

$type_query = "SELECT w_id, wheel_type FROM type";
$type_stmt = $conn->prepare($type_query);
$type_stmt->execute();
$types = $type_stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare counts and names arrays
$counts = [];
$names = [];
foreach ($types as $type) {
    $counts[$type['w_id']] = 0;
    $names[$type['w_id']] = $type['wheel_type'];
}

$count_query = "
    SELECT wheel_name, COUNT(*) as total 
    FROM truck 
    GROUP BY wheel_name
";
$count_stmt = $conn->prepare($count_query);
$count_stmt->execute();
$truck_counts = $count_stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($truck_counts as $row) {
    $counts[$row['wheel_name']] = $row['total'];
}

$query = "SELECT * FROM admin";
$stmt = $conn->prepare($query);
$stmt->execute();
$email = $stmt->fetchAll(PDO::FETCH_ASSOC); 
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>PCL - Dashboard</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/icon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">

</head>
<body>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center">
        <img src="assets/img/pcl_logo.png" alt="">
        <span class="d-none d-lg-block">Pick Count Log.</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">
        <li class="nav-item dropdown pe-3">
          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
          <img src="<?php echo $_SESSION['profile'] ?? 'default.jpg'; ?>" alt="Profile" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">
          <span class="d-none d-md-block dropdown-toggle ps-2"><?php echo $fullname; ?></span>
          </a><!-- End Profile Iamge Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?php echo $fullname; ?></h6>
              <span><?php echo $email_user; ?></span>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="users-profile.php">
                <i class="bi bi-person"></i>
                <span>My Profile</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="users-profile.php">
                <i class="bi bi-gear"></i>
                <span>Account Settings</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="logout.php">
                <i class="ri-logout-box-line"></i>
                <span>Sign Out</span>
              </a>
            </li>

          </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link " href="index.php">
          <i class="bi bi-grid"></i>
          <span>Dashboard</span>
        </a>
      </li><!-- End Dashboard Nav -->

      <li class="nav-item">
          <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
              <i class="ri ri-truck-line"></i><span>Trucks</span><i class="bi bi-chevron-down ms-auto"></i>
          </a>
          <ul id="components-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
              <?php
              // Loop through each type and create an individual list item for each wheel_type
              foreach ($types as $type) {
                  echo '<li>';
// In the sidebar section
echo '<a href="truck-details.php?wheel_type=' . $type['wheel_type'] . '&w_id=' . $type['w_id'] . '">';
                  echo '<i class="bi bi-circle"></i><span>' . $type['wheel_type'] . ' (' . $counts[$type['w_id']] . ' trucks)</span>';
                  echo '</a>';
                  echo '</li>';
              }
              ?>
          </ul>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="change-oil-record1.php">
          <i class="ri-oil-line"></i><span>Changed Oil (Record 1)</span></a></li>

        <li class="nav-item">
        <a class="nav-link collapsed" href="change-oil-record2.php">
          <i class="ri-oil-line"></i><span>Changed Oil (Record 2)</span></a></li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="users-profile.php">
          <i class="bi bi-people"></i>
          <span>Drivers</span>
        </a>
      </li>

      <li class="nav-heading">Pages</li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="users-profile.php">
          <i class="bi bi-person"></i>
          <span>Profile</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="pages-register.php">
          <i class="bi bi-people"></i>
          <span>Add Admin</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="logout.php">
          <i class="ri-logout-box-line"></i>
          <span>Sign out</span>
        </a>
      </li>
    </ul>

  </aside><!-- End Sidebar-->

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Dashboard</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">
        <div class="col-lg-8">
          <div class="row">


            <div class="col-xxl-4 col-md-6">
              <div class="card info-card trucks-card">
                <div class="card-body">
                  <h5 class="card-title">Trucks <span>| Total</span></h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="ri ri-truck-line"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo $truck_count; ?></h6>
                      <a href="#" class="text-success small pt-1 fw-bold">View</a> <span class="text-muted small pt-2 ps-1">Record</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>


            <div class="col-xxl-4 col-md-6">
              <div class="card info-card a-card">
                <div class="card-body">
                  <h5 class="card-title"><?php echo $names[1]; ?> <span>| Total</span></h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="ri ri-truck-line"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo $counts[1]; ?></h6>
                      <a href="#" class="text-success small pt-1 fw-bold">View</a> <span class="text-muted small pt-2 ps-1">Record</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>


            <div class="col-xxl-4 col-xl-6">
              <div class="card info-card b-card">
                <div class="card-body">
                  <h5 class="card-title"><?php echo $names[2]; ?> <span>| Total</span></h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="ri ri-truck-line"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo $counts[2]; ?></h6>
                      <a href="#" class="text-success small pt-1 fw-bold">View</a> <span class="text-muted small pt-2 ps-1">Record</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>


            <div class="col-xxl-4 col-md-6">
              <div class="card info-card c-card">
                <div class="card-body">
                  <h5 class="card-title"><?php echo $names[3]; ?> <span>| Total</span></span></h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="ri ri-truck-line"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo $counts[3]; ?></h6>
                      <a href="#" class="text-success small pt-1 fw-bold">View</a> <span class="text-muted small pt-2 ps-1">Record</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xxl-4 col-md-6">
              <div class="card info-card d-card">
                <div class="card-body">
                  <h5 class="card-title"><?php echo $names[4]; ?> <span>| Total</span></span></h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="ri ri-truck-line"></i>
                    </div>
                    <div class="ps-3">
                    <h6><?php echo $counts[4]; ?></h6>
                    <a href="#" class="text-success small pt-1 fw-bold">View</a> <span class="text-muted small pt-2 ps-1">Record</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xxl-4 col-xl-6">
              <div class="card info-card e-card">
                <div class="card-body">
                <h5 class="card-title"><?php echo $names[5]; ?> <span>| Total</span></span></h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="ri ri-truck-line"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo $counts[5]; ?></h6>
                      <a href="#" class="text-success small pt-1 fw-bold">View</a> <span class="text-muted small pt-2 ps-1">Record</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-xxl-4 col-xl-6">
              <div class="card info-card f-card">
                <div class="card-body">
                <h5 class="card-title"><?php echo $names[6]; ?> <span>| Total</span></span></h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="ri ri-truck-line"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo $counts[6]; ?></h6>
                      <a href="#" class="text-success small pt-1 fw-bold">View</a> <span class="text-muted small pt-2 ps-1">Record</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>


            <div class="col-xxl-4 col-xl-6">
              <div class="card info-card g-card">
                <div class="card-body">
                <h5 class="card-title"><?php echo $names[7]; ?> <span>| Total</span></span></h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="ri ri-truck-line"></i>
                    </div>
                    <div class="ps-3">
                      <h6><?php echo $counts[7]; ?></h6>
                      <a href="#" class="text-success small pt-1 fw-bold">View</a> <span class="text-muted small pt-2 ps-1">Record</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            












            <!-- admin -->
            <div class="col-12">
    <div class="card top-selling overflow-auto">
        <div class="card-body pb-0">
            <h5 class="card-title">Admin<span>| <a href="#">View Record</a></span></h5>

            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($email as $index => $data) {
                        echo "<tr>";
                        echo "<td>" . ($index + 1) . "</td>"; 
                        echo "<td>" . $data['fname'] . " " . $data['lname'] . "</td>"; 
                        echo "<td>" . $data['email'] . "</td>"; 
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



            <!-- End admin -->
          </div>
        </div>
        <!-- End Left side columns -->

        

            <div class="card" style="flex: 1;">
              <div class="card-body pb-0">
                <h5 class="card-title">Truck Status <span>| Record</span></h5>
                <p>The Bar Chart shows the breakdown of truck models as follows:</p>
                <div id="trafficChart2" style="min-height: 300px; width: 100%;" class="echart"></div>
                <script src="https://cdn.jsdelivr.net/npm/echarts@5.0.0/dist/echarts.min.js"></script>
                <script>
                  document.addEventListener("DOMContentLoaded", () => {
                    var myChart = echarts.init(document.getElementById('trafficChart2'));
                    var option = {
                      tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                          type: 'shadow'
                        }
                      },
                      legend: {
                        top: '5%',
                        left: 'center'
                      },
                      xAxis: {
                        type: 'category',
                        data: [
                          '<?php echo $names[1]; ?>',
                          '<?php echo $names[2]; ?>',
                          '<?php echo $names[3]; ?>',
                          '<?php echo $names[4]; ?>',
                          '<?php echo $names[5]; ?>',
                          '<?php echo $names[6]; ?>',
                          '<?php echo $names[7]; ?>'
                        ], 
                        axisLabel: {
                          rotate: 45, 
                          fontSize: 12,
                          interval: 0, 
                        }
                      },
                      yAxis: {
                        type: 'value'
                      },
                      series: [{
                        name: 'Trucks',
                        type: 'bar',
                        data: [
                          <?php echo $counts[1]; ?>,
                          <?php echo $counts[2]; ?>,
                          <?php echo $counts[3]; ?>,
                          <?php echo $counts[4]; ?>,
                          <?php echo $counts[5]; ?>,
                          <?php echo $counts[6]; ?>,
                          <?php echo $counts[7]; ?>
                        ], 
                        emphasis: {
                          focus: 'series'
                        },
                        itemStyle: {
                          color: '#3498db'
                        },
                        label: {
                          show: true,
                          position: 'top', 
                          fontSize: 12 
                        }
                      }]
                    };
                    myChart.setOption(option);
                  });
                </script>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>Producers Connection Logistics Inc</span></strong>. All Rights Reserved
    </div>
  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>
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
$about = $_SESSION['about'];
$phone = $_SESSION['phone'];
$profile = $_SESSION['profile'];

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

  <title>PCL - Add Admin</title>
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

  <!-- =======================================================
  * Template Name: NiceAdmin
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Updated: Apr 20 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
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
                  echo '<a href="truck-details.php?wheel_type=' . $type['wheel_type'] . '">';
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
      <h1>Profile</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.html">Home</a></li>
          <li class="breadcrumb-item">Users</li>
          <li class="breadcrumb-item active">Profile</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section profile">
      <div class="row">
        <div class="col-xl-12">

          <div class="card">
            <div class="card-body pt-3">
              <div class="tab-content pt-2">

              
                  <form action="add_account.php" method="POST" enctype="multipart/form-data">
                  <div class="row mb-3">
    <label for="profileImage" class="col-md-4 col-lg-3 col-form-label">Profile Image</label>
    <div class="col-md-8 col-lg-9">
        <div class="pt-2">
            <input type="file" name="profileImage" id="profileImageInput" style="display: none;" accept="image/*" onchange="updateFileName()">
            <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('profileImageInput').click();">
                <i class="bi bi-upload"></i> Insert Profile Image
            </button>
            <span id="fileName" style="margin-left: 10px; color: #555;"></span> <!-- Display the filename here -->
        </div>
    </div>
</div>

<script>
    function updateFileName() {
        var input = document.getElementById('profileImageInput');
        var fileName = input.files.length > 0 ? input.files[0].name : ''; // Get the name of the selected file
        document.getElementById('fileName').textContent = fileName; // Display the filename
    }
</script>


                        <div class="row mb-3">
                            <label for="fname" class="col-md-4 col-lg-3 col-form-label">First Name</label>
                            <div class="col-md-8 col-lg-9">
                                <input name="fname" type="text" class="form-control" id="fname" value="" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="lname" class="col-md-4 col-lg-3 col-form-label">Last Name</label>
                            <div class="col-md-8 col-lg-9">
                                <input name="lname" type="text" class="form-control" id="lname" value="" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="about" class="col-md-4 col-lg-3 col-form-label">About</label>
                            <div class="col-md-8 col-lg-9">
                                <textarea name="about" class="form-control" id="about" style="height: 100px" required></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="phone" class="col-md-4 col-lg-3 col-form-label">Phone</label>
                            <div class="col-md-8 col-lg-9">
                                <input name="phone" type="text" class="form-control" id="phone" value="" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-lg-3 col-form-label">Email</label>
                            <div class="col-md-8 col-lg-9">
                                <input name="email" type="email" class="form-control" id="email" value="" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-lg-3 col-form-label">Password</label>
                            <div class="col-md-8 col-lg-9">
                                <input name="password" type="password" class="form-control" id="password" value="" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="confirm_password" class="col-md-4 col-lg-3 col-form-label">Confirm Password</label>
                            <div class="col-md-8 col-lg-9">
                                <input name="confirm_password" type="password" class="form-control" id="confirm_password" value="" required>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>



                </div>

            
</form>

              </div><!-- End Bordered Tabs -->

            </div>
          </div>

        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
  <div class="copyright">&copy; Copyright <strong><span>Producers Connection Logistics Inc</span></strong>. All Rights Reserved</div>
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
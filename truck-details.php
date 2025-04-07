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

$query = "SELECT COUNT(*) as total FROM truck";
$stmt = $conn->prepare($query);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$truck_count = $row['total'];

$type_query = "SELECT w_id, wheel_type FROM type";
$type_stmt = $conn->prepare($type_query);
$type_stmt->execute();
$types = $type_stmt->fetchAll(PDO::FETCH_ASSOC);

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

$highlight_trck_id = isset($_GET['trck_id']) ? $_GET['trck_id'] : null;
$highlight_week_no = isset($_GET['week_no']) ? $_GET['week_no'] : null;

$wheel_type = isset($_GET['wheel_type']) ? $_GET['wheel_type'] : '';

if ($wheel_type) {
    $query = "
        SELECT 
            t.trck_id, 
            t.wheel_name, 
            t.trck_plate, 
            wt.wheel_type
        FROM truck t
        JOIN type wt ON t.wheel_name = wt.w_id
        WHERE wt.wheel_type = :wheel_type
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['wheel_type' => $wheel_type]);
    $trucks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $trucks = [];
}

$weekQuery = "SELECT DISTINCT week_no FROM odometer ORDER BY week_no ASC";
$weekStmt = $pdo->prepare($weekQuery);
$weekStmt->execute();
$weeks = $weekStmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>PCL - Trucks Weekly PMS</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <link href="assets/img/icon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <link href="assets/css/style.css" rel="stylesheet">
  <link href="assets/css/style1.css" rel="stylesheet">
    
</head>
<body>
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

  </aside>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Weekly Truck Mileage Report</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item active">Wheel Type: <?php echo htmlspecialchars($wheel_type); ?></li>
        </ol>
      </nav>
    </div>

    <section class="section dashboard">
    <div class="row">
        <div class="col-lg-12">
            <div class="row">
                <div class="container mt-4">
                    <div class="controls-container">
                        <button class="btn btn-success mb-3" onclick="addWeekColumn()">Add Week Column</button>
                        <button class="btn btn-warning mb-3" onclick="$editWeekColumn()">Edit Week Column</button>

                        
                        <div class="search-filter-wrapper">
                            <!-- Search container -->
                            <div class="search-container">
                                <input type="text" 
                                    id="tableSearch" 
                                    class="form-control" 
                                    placeholder="Search for truck ID, wheel name, or plate number...">
                            </div>
                            <!-- Week filter -->
                            <div class="column-filter">
                                <select class="form-select" id="weekFilter">
                                    <option value="all">All Weeks</option>
                                    <option value="1-10">Week 1-10</option>
                                    <option value="11-20">Week 11-20</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-container">
                        <div class="table-responsive">
                            <div style="overflow-x: auto; width: 100%;">
                                <table class="table table-bordered" id="truckTable" style="table-layout: fixed; width: 100%;">
                                    <thead>
                                        <tr>
                                            <th class="fixed-col first-col">#</th>
                                            <th class="fixed-col second-col">Wheel Name (Type)</th>
                                            <th class="fixed-col third-col">Truck Plate</th>
                                            <?php foreach ($weeks as $week): ?>
                                                <th style="width: 200px;">
                                                    Week <?php echo htmlspecialchars($week); ?>
                                                    <div class="small text-muted">Odo / Date</div>
                                                </th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($trucks)): ?>
                                            <?php foreach ($trucks as $truck): ?>
                                                <tr class="hover-highlight" 
                                                    data-search-id="<?php echo strtolower(htmlspecialchars($truck['trck_id'])); ?>"
                                                    data-search-wheel="<?php echo strtolower(htmlspecialchars($truck['wheel_name'])); ?>"
                                                    data-search-plate="<?php echo strtolower(htmlspecialchars($truck['trck_plate'])); ?>">
                                                    <td class="fixed-col first-col">
                                                        <?php echo htmlspecialchars($truck['trck_id']); ?>
                                                    </td>
                                                    <td class="fixed-col second-col">
                                                        <?php echo htmlspecialchars($truck['wheel_name']) . ' (' . 
                                                            htmlspecialchars($truck['wheel_type']) . ')'; ?>
                                                    </td>
                                                    <td class="fixed-col third-col">
                                                        <?php echo htmlspecialchars($truck['trck_plate']); ?>
                                                    </td>
                                                    <?php foreach ($weeks as $week): ?>
                                                        <?php 
                                                            $odoQuery = "SELECT odometer, record_date FROM odometer WHERE trck_id = ? AND week_no = ?";
                                                            $odoStmt = $pdo->prepare($odoQuery);
                                                            $odoStmt->execute([$truck['trck_id'], $week]);
                                                            $result = $odoStmt->fetch(PDO::FETCH_ASSOC);
                                                            $odometer = $result['odometer'] ?? null;
                                                            $record_date = $result['record_date'] ?? null;
                                                            $formatted_date = $record_date ? date('F d, Y', strtotime($record_date)) : '-';
                                                        ?>
                                                        <td class="clickable-cell" 
                                                            onclick="openModal('<?php echo $truck['trck_id']; ?>', 
                                                                            '<?php echo $week; ?>', 
                                                                            '<?php echo $truck['trck_plate']; ?>', 
                                                                            '<?php echo $truck['wheel_type']; ?>')"
                                                            data-trck-id="<?php echo $truck['trck_id']; ?>"
                                                            data-week="<?php echo $week; ?>">
                                                            <?php echo $odometer ? htmlspecialchars(number_format($odometer, 0, '.', ',')) : '-'; ?><br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($formatted_date); ?></small>
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="<?php echo 3 + count($weeks); ?>" class="text-center">
                                                    No trucks found for this wheel type.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



<script>
document.getElementById('tableSearch').addEventListener('keyup', function() {
    let searchText = this.value.toLowerCase();
    let rows = document.querySelectorAll('#truckTable tbody tr');
    
    rows.forEach(row => {
        let searchId = row.getAttribute('data-search-id') || '';
        let searchWheel = row.getAttribute('data-search-wheel') || '';
        let searchPlate = row.getAttribute('data-search-plate') || '';
        
        if (searchId.includes(searchText) || 
            searchWheel.includes(searchText) || 
            searchPlate.includes(searchText)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

document.getElementById('weekFilter').addEventListener('change', function() {
    let value = this.value;
    let table = document.getElementById('truckTable');
    let headers = table.querySelectorAll('thead th');
    let rows = table.querySelectorAll('tbody tr');
    
    // Skip the first 3 columns (ID, Wheel Name, Truck Plate)
    let startCol = 3;
    
    headers.forEach((header, index) => {
        if (index >= startCol) {
            let weekNum = parseInt(header.textContent.match(/\d+/)[0]);
            let show = value === 'all' ||
                      (value === '1-10' && weekNum >= 1 && weekNum <= 10) ||
                      (value === '11-20' && weekNum >= 11 && weekNum <= 20);
                      
            header.style.display = show ? '' : 'none';
            
            rows.forEach(row => {
                if (row.cells[index]) {
                    row.cells[index].style.display = show ? '' : 'none';
                }
            });
        }
    });
});
</script>
  </main>
    <!-- Modal -->
    <div class="modal fade" id="addOdometerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Odometer Reading</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="current_trck_id">
                    <div class="mb-3">
                        <label class="form-label">Week Number</label>
                        <input type="text" class="form-control" id="week_no" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Truck Plate</label>
                        <input type="text" class="form-control" id="trck_plate" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Wheel Type</label>
                        <input type="text" class="form-control" id="wheel_type" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Odometer Reading</label>
                        <input type="number" class="form-control" id="odometer" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveOdometerBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>






<!-- ======= Script ======= -->
<script>
document.getElementById('weekFilter').addEventListener('change', function() {
    const selectedRange = this.value;
    const table = document.querySelector('.table');
    const headerCells = table.querySelectorAll('thead th');
    const dataCells = table.querySelectorAll('tbody tr');
    const weekStartIndex = 3;
    
    headerCells.forEach((cell, index) => {
        if (index >= weekStartIndex) {
            const weekText = cell.textContent;
            const weekNumber = parseInt(weekText.replace('Week ', ''));
            
            if (selectedRange === 'all') {
                cell.style.display = '';
            } else if (selectedRange === '1-10') {
                cell.style.display = (weekNumber >= 1 && weekNumber <= 10) ? '' : 'none';
            } else if (selectedRange === '11-20') {
                cell.style.display = (weekNumber >= 11 && weekNumber <= 20) ? '' : 'none';
            }
        }
    });
    
    dataCells.forEach(row => {
        const cells = row.querySelectorAll('td');
        cells.forEach((cell, index) => {
            if (index >= weekStartIndex) {
                const weekNumber = parseInt(headerCells[index].textContent.replace('Week ', ''));
                
                if (selectedRange === 'all') {
                    cell.style.display = '';
                } else if (selectedRange === '1-10') {
                    cell.style.display = (weekNumber >= 1 && weekNumber <= 10) ? '' : 'none';
                } else if (selectedRange === '11-20') {
                    cell.style.display = (weekNumber >= 11 && weekNumber <= 20) ? '' : 'none';
                }
            }
        });
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function openModal(trck_id, week_no, trck_plate, wheel_type) {
        document.getElementById('current_trck_id').value = trck_id;
        document.getElementById('week_no').value = week_no;
        document.getElementById('trck_plate').value = trck_plate;
        document.getElementById('wheel_type').value = wheel_type;
        
        document.getElementById('saveOdometerBtn').dataset.trckId = trck_id;
        document.getElementById('saveOdometerBtn').dataset.weekNo = week_no;

        var modal = new bootstrap.Modal(document.getElementById('addOdometerModal'));
        modal.show();
    }

    function addWeekColumn() {
        var newWeek = prompt("Enter a new week number:");
        if (!newWeek) {alert("Please enter a week number.");return;}

        newWeek = parseInt(newWeek);
        if (isNaN(newWeek) || newWeek <= 0) {alert("Invalid week number. Please enter a valid number.");return;}

        fetch('add_week.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json',},
            body: JSON.stringify({week_no: newWeek})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {alert("Week " + newWeek + " added successfully!");location.reload();} 
            else {alert('Error adding week: ' + (data.message || 'Unknown error'));}
        })
        .catch(error => {
            console.error('Error:', error);alert('Failed to connect to server.');
        });
    }
    
    function $editWeekColumn() {
    var oldWeekNo = prompt("Enter the existing week number to edit:");
    var newWeekNo = prompt("Enter the new week number:");

    if (!oldWeekNo || !newWeekNo) {
        alert("Please enter both old and new week numbers.");
        return;
    }

    oldWeekNo = parseInt(oldWeekNo);
    newWeekNo = parseInt(newWeekNo);

    if (isNaN(oldWeekNo) || isNaN(newWeekNo) || newWeekNo <= 0) {
        alert("Invalid week number. Please enter valid numbers.");
        return;
    }

    fetch('edit_week.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({old_week_no: oldWeekNo, new_week_no: newWeekNo})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Week number updated successfully!");
            location.reload();
        } else {
            alert("Error updating week number: " + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to connect to server.');
    });
}


    document.getElementById('saveOdometerBtn').addEventListener('click', function() {
    let trck_id = this.dataset.trckId;
    let week_no = this.dataset.weekNo;
    let odometer = document.getElementById('odometer').value;

    if (odometer === '') {
        alert('Please enter an odometer reading.');
        return;
    }

    fetch('update_odometer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ trck_id, week_no, odometer })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI
            let cell = document.querySelector(`td[data-trck-id="${trck_id}"][data-week="${week_no}"]`);
            if (cell) { cell.textContent = odometer; }

            alert('Odometer updated successfully.');

            // Hide modal
            var modal = bootstrap.Modal.getInstance(document.getElementById('addOdometerModal'));
            modal.hide();

            // Refresh the page after a short delay
            setTimeout(() => { location.reload(); }, 500); 
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
});

</script>

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">&copy; Copyright <strong><span>Producers Connection Logistics Inc</span></strong>. All Rights Reserved</div>
  </footer>
  <!-- End Footer -->

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
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
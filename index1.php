<?php
session_start(); 

if (!isset($_SESSION['ad_id'])) {
    header("Location: pages-login.php");
    exit();
}

$fname = $_SESSION['fname'];
$lname = $_SESSION['lname'];
$email = $_SESSION['email'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome, <?php echo $fname; ?>!</title>
</head>
<body>
    <h1>Welcome, <?php echo $fname . ' ' . $lname; ?>!</h1>
    <p>Email: <?php echo $email; ?></p>
    
    <a href="logout.php">Logout</a>
</body>
</html>
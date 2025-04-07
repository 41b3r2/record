<?php
require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $trck_id = $_POST['trck_id'];
    $week_no = $_POST['week_no'];
    $odometer = $_POST['odometer'];

    $insertQuery = "
        INSERT INTO odometer (trck_id, week_no, odometer) 
        VALUES (:trck_id, :week_no, :odometer) 
        ON DUPLICATE KEY UPDATE odometer = :odometer
    ";
    $stmt = $pdo->prepare($insertQuery);
    $stmt->execute([
        'trck_id' => $trck_id, 
        'week_no' => $week_no, 
        'odometer' => $odometer
    ]);

    header("Location: truck-details.php?wheel_type=" . urlencode($_GET['wheel_type']));
    exit();
}
?>

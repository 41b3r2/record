<?php
require 'connector.php';

header('Content-Type: application/json');

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!isset($data['trck_id']) || !isset($data['week_no']) || !isset($data['odometer'])) {
        throw new Exception('Missing required data');
    }

    $trck_id = $data['trck_id'];
    $week_no = $data['week_no'];
    $odometer = $data['odometer'];

    // Check if record already exists
    $checkQuery = "SELECT COUNT(*) FROM odometer WHERE trck_id = ? AND week_no = ?";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute([$trck_id, $week_no]);
    $exists = $checkStmt->fetchColumn();

    if ($exists) {
        // Update existing record with new odometer and current date
        $query = "UPDATE odometer SET odometer = ?, record_date = CURRENT_DATE() WHERE trck_id = ? AND week_no = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$odometer, $trck_id, $week_no]);
    } else {
        // Insert new record with odometer and current date
        $query = "INSERT INTO odometer (trck_id, week_no, odometer, record_date) VALUES (?, ?, ?, CURRENT_DATE())";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$trck_id, $week_no, $odometer]);
    }

    echo json_encode([ 'success' => true, 'message' => 'Odometer updated successfully' ]);

} catch (Exception $e) {
    echo json_encode([ 'success' => false, 'message' => $e->getMessage() ]);
}
?>

<?php
session_start();
include 'db.php';
include 'auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
$checkIns = isset($_POST['check_in']) ? $_POST['check_in'] : [];
$checkOuts = isset($_POST['check_out']) ? $_POST['check_out'] : [];

try {
    // Start transaction
    mysqli_begin_transaction($conn);

    // First, delete any existing records for this date
    $stmt = mysqli_prepare($conn, "DELETE FROM attendance_logs WHERE date = ?");
    mysqli_stmt_bind_param($stmt, "s", $date);
    mysqli_stmt_execute($stmt);

    // Prepare insert statement
    $stmt = mysqli_prepare($conn, "INSERT INTO attendance_logs (employee_id, date, check_in, check_out, status) VALUES (?, ?, ?, ?, ?)");

    // For each employee
    foreach ($checkIns as $employeeId => $checked) {
        $checkIn = isset($checkIns[$employeeId]) ? '08:00:00' : null;
        $checkOut = isset($checkOuts[$employeeId]) ? '17:00:00' : null;
        
        // Determine status
        if ($checkIn && $checkOut) {
            $status = 'Present';
        } elseif ($checkIn) {
            $status = 'Half Day';
        } else {
            $status = 'Absent';
        }

        // Insert the record
        mysqli_stmt_bind_param($stmt, "issss", $employeeId, $date, $checkIn, $checkOut, $status);
        mysqli_stmt_execute($stmt);
    }

    // Commit transaction
    mysqli_commit($conn);
    
    echo json_encode(['success' => true, 'message' => 'Attendance saved successfully']);

} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => 'Error saving attendance: ' . $e->getMessage()]);
}

mysqli_close($conn);
?>

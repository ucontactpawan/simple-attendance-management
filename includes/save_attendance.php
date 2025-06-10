<?php
session_start();
include 'includes/db.php';
include 'includes/auth.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set headers
header('Content-Type: application/json');

// Debug logging
error_log("Raw POST data: " . file_get_contents('php://input'));
error_log("POST variables: " . print_r($_POST, true));

// Validate input
if (!isset($_POST['date'])) {
    error_log("Missing date");
    echo json_encode(['status' => 'error', 'message' => 'Date is required']);
    exit;
}

if (!isset($_POST['attendance'])) {
    error_log("Missing attendance data");
    echo json_encode(['status' => 'error', 'message' => 'Attendance data is required']);
    exit;
}

try {
    // Parse the attendance data
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $attendance = json_decode($_POST['attendance'], true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON decode error: " . json_last_error_msg());
    }

    if (!is_array($attendance)) {
        throw new Exception("Invalid attendance data format - expected array");
    }

    // Start transaction
    mysqli_begin_transaction($conn);

    foreach ($attendance as $entry) {
        $employee_id = (int)$entry['employee_id'];
        $check_in = $entry['check_in'] ? "'" . mysqli_real_escape_string($conn, $entry['check_in']) . "'" : "NULL";
        $check_out = $entry['check_out'] ? "'" . mysqli_real_escape_string($conn, $entry['check_out']) . "'" : "NULL";
        
        // Calculate late time (in minutes)
        $late_time = 0;
        if ($entry['check_in']) {
            $in_time = strtotime($entry['check_in']);
            $standard_time = strtotime('09:30:00');
            if ($in_time > $standard_time) {
                $late_time = round(($in_time - $standard_time) / 60);
            }
        }

        // Calculate total time (in minutes)
        $total_time = 0;
        if ($entry['check_in'] && $entry['check_out']) {
            $total_time = round((strtotime($entry['check_out']) - strtotime($entry['check_in'])) / 60);
        }

        // Check if record exists
        $check_sql = "SELECT id FROM attendance_logs WHERE employee_id = ? AND date = ?";
        $stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($stmt, "is", $employee_id, $date);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            // Update existing record
            $sql = "UPDATE attendance_logs SET 
                   in_time = $check_in, 
                   out_time = $check_out,
                   late_time = $late_time,
                   total_time = $total_time
                   WHERE employee_id = $employee_id AND date = '$date'";
        } else {
            // Insert new record
            $sql = "INSERT INTO attendance_logs 
                   (employee_id, date, in_time, out_time, late_time, total_time) 
                   VALUES 
                   ($employee_id, '$date', $check_in, $check_out, $late_time, $total_time)";
        }

        if (!mysqli_query($conn, $sql)) {
            throw new Exception("Database error: " . mysqli_error($conn));
        }
    }

    // Commit transaction
    mysqli_commit($conn);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Attendance saved successfully'
    ]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    error_log("Attendance save error: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'debug' => [
            'post_data' => $_POST,
            'json_error' => json_last_error_msg()
        ]
    ]);
}

mysqli_close($conn);
?>

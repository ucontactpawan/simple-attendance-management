<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';



// Set headers
header('Content-Type: application/json');



// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['date'])) {
    error_log("Missing date");
    echo json_encode(['status' => 'error', 'message' => 'Date is required']);
    exit;
}

if (!isset($input['attendance']) || !is_array($input['attendance'])) {
    error_log("Missing or invalid attendance data");
    echo json_encode(['status' => 'error', 'message' => 'Attendance data is required']);
    exit;
}

try {
    // Parse input data
    $date = mysqli_real_escape_string($conn, $input['date']);
    $attendance = $input['attendance'];

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON format: ' . json_last_error_msg());
    }

    if (!is_array($attendance)) {
        throw new Exception('Invalid attendance data structure');
    }

    // Log the parsed data for debugging
    error_log('Parsed attendance data: ' . print_r($attendance, true));

    // Start transaction
    mysqli_begin_transaction($conn);

    foreach ($attendance as $entry) {      
        $employee_id = (int)$entry['employee_id'];
        $in_time = $entry['in_time'] ? "'" . mysqli_real_escape_string($conn, $entry['in_time']) . "'" : "NULL";
        $out_time = $entry['out_time'] ? "'" . mysqli_real_escape_string($conn, $entry['out_time']) . "'" : "NULL";
        $comments = isset($entry['comments']) ? "'" . mysqli_real_escape_string($conn, $entry['comments']) . "'" : "NULL";
        
        // Calculate late time (in minutes)
        $late_time = 0;
        if ($entry['in_time']) {
            $time_in = strtotime($entry['in_time']);
            $standard_time = strtotime('09:30:00');
            if ($time_in > $standard_time) {
                $late_time = round(($time_in - $standard_time) / 60);
            }
        }        // Calculate total time (in minutes)
        $total_time = 0;
        if ($entry['in_time'] && $entry['out_time']) {
            $total_time = round((strtotime($entry['out_time']) - strtotime($entry['in_time'])) / 60);
        }

        // Check if record exists
        $check_sql = "SELECT id FROM attendance WHERE employee_id = ? AND date = ?";
        $stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($stmt, "is", $employee_id, $date);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {            
            $sql = "UPDATE attendance SET 
                   in_time = $in_time, 
                   out_time = $out_time,
                   comments = $comments,
                   status = '1'
                   WHERE employee_id = $employee_id AND date = '$date'";
        } else {
            // Insert new record
            $sql = "INSERT INTO attendance 
                   (employee_id, date, in_time, out_time, comments, status) 
                   VALUES 
                   ($employee_id, '$date', $in_time, $out_time, $comments, '1')";
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

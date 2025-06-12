<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON format: ' . json_last_error_msg()]);
    exit;
}

// Validate input
if (!isset($input['date'])) {
    echo json_encode(['status' => 'error', 'message' => 'Date is required']);
    exit;
}

if (!isset($input['attendance']) || !is_array($input['attendance'])) {
    echo json_encode(['status' => 'error', 'message' => 'Attendance data is required']);
    exit;
}

try {
    // Start transaction
    mysqli_begin_transaction($conn);

    foreach ($input['attendance'] as $entry) {
        $employee_id = (int)$entry['employee_id'];
        $date = mysqli_real_escape_string($conn, $input['date']);
        $in_time = $entry['in_time'] ? mysqli_real_escape_string($conn, $entry['in_time']) : null;
        $out_time = $entry['out_time'] ? mysqli_real_escape_string($conn, $entry['out_time']) : null;
        $comments = isset($entry['comments']) ? mysqli_real_escape_string($conn, $entry['comments']) : '';        // Check if record exists for this employee
        $check_sql = "SELECT id FROM attendance WHERE employee_id = ?";
        $stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($stmt, "i", $employee_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            // Update existing record
            $row = $result->fetch_assoc();
            $attendance_id = $row['id'];
            
            $update_sql = "UPDATE attendance SET 
                          in_time = ?, 
                          out_time = ?,
                          comments = ?,
                          status = '1'
                          WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($stmt, "sssi", $in_time, $out_time, $comments, $attendance_id);
            mysqli_stmt_execute($stmt);
        } else {            // Insert new record
            $insert_sql = "INSERT INTO attendance 
                         (employee_id, in_time, out_time, comments, status) 
                         VALUES (?, ?, ?, ?, '1')";
            $stmt = mysqli_prepare($conn, $insert_sql);
            mysqli_stmt_bind_param($stmt, "isss", $employee_id, $in_time, $out_time, $comments);
            mysqli_stmt_execute($stmt);
            $attendance_id = mysqli_insert_id($conn);
        }        // Handle IN time record - only insert if it doesn't exist
            if ($in_time) {
                // Check if IN record exists for this attendance
                $check_in_sql = "SELECT id FROM attendance_history 
                                WHERE attendance_id = ? AND employee_id = ? AND action = 'IN'";
                $stmt = mysqli_prepare($conn, $check_in_sql);
                mysqli_stmt_bind_param($stmt, "ii", $attendance_id, $employee_id);
                mysqli_stmt_execute($stmt);
                $in_result = mysqli_stmt_get_result($stmt);

                // Only insert IN record if it doesn't exist
                if (mysqli_num_rows($in_result) == 0) {
                    $history_sql = "INSERT INTO attendance_history 
                                  (attendance_id, employee_id, action, date_time, comments) 
                                  VALUES (?, ?, 'IN', ?, ?)";
                    $stmt = mysqli_prepare($conn, $history_sql);
                    mysqli_stmt_bind_param($stmt, "iiss", $attendance_id, $employee_id, $in_time, $comments);
                    mysqli_stmt_execute($stmt);
                }
            }
            
            // Handle OUT time record - check existing and update if needed
            if ($out_time) {
                // Check if OUT record exists for this attendance and if it's different
                $check_out_sql = "SELECT id, date_time FROM attendance_history 
                                WHERE attendance_id = ? AND employee_id = ? AND action = 'OUT' 
                                ORDER BY created_at DESC LIMIT 1";
                $stmt = mysqli_prepare($conn, $check_out_sql);
                mysqli_stmt_bind_param($stmt, "ii", $attendance_id, $employee_id);
                mysqli_stmt_execute($stmt);
                $out_result = mysqli_stmt_get_result($stmt);
                
                if ($out_row = mysqli_fetch_assoc($out_result)) {
                    // Only insert new record if time has changed
                    if ($out_row['date_time'] !== $out_time) {
                        $history_sql = "INSERT INTO attendance_history 
                                      (attendance_id, employee_id, action, date_time, comments) 
                                      VALUES (?, ?, 'OUT', ?, ?)";
                        $stmt = mysqli_prepare($conn, $history_sql);
                        mysqli_stmt_bind_param($stmt, "iiss", $attendance_id, $employee_id, $out_time, $comments);
                        mysqli_stmt_execute($stmt);
                    }
                } else {
                    // First time OUT record
                    $history_sql = "INSERT INTO attendance_history 
                                  (attendance_id, employee_id, action, date_time, comments) 
                                  VALUES (?, ?, 'OUT', ?, ?)";
                    $stmt = mysqli_prepare($conn, $history_sql);
                    mysqli_stmt_bind_param($stmt, "iiss", $attendance_id, $employee_id, $out_time, $comments);
                    mysqli_stmt_execute($stmt);
                }
            }
    }

    // Commit transaction
    mysqli_commit($conn);
    echo json_encode(['status' => 'success', 'message' => 'Attendance saved successfully']);

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

mysqli_close($conn);
?>



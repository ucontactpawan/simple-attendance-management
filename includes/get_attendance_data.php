<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();

try {
    require_once __DIR__ . '/db.php';
    require_once __DIR__ . '/auth.php';

    // Ensure clean output
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json');

    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    $type = isset($_GET['type']) ? $_GET['type'] : 'all';    // Get employees who don't have attendance
    $query = "SELECT 
        e.id as employee_id, 
        e.name as employee_name,
        COALESCE(a.in_time, '') as in_time,
        COALESCE(a.out_time, '') as out_time,
        COALESCE(a.comments, '') as comments
    FROM employees e 
    LEFT JOIN attendance a ON e.id = a.employee_id
    WHERE e.status = '1'";    // Add specific employee filter for single mode
    if ($type === 'single' && isset($_GET['employee_id'])) {
        $query .= " AND e.id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $_GET['employee_id']);
    } else {
        $stmt = $conn->prepare($query);
    }

    if (!$stmt->execute()) {
        throw new Exception("Failed to execute query: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'employee_id' => $row['employee_id'],
            'employee_name' => $row['employee_name'],
            'in_time' => $row['in_time'],
            'out_time' => $row['out_time'],
            'comments' => $row['comments']
        ];
    }

    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);

} catch (Exception $e) {
    error_log("Error in get_attendance_data.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
exit();

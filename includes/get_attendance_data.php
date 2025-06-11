<?php
<<<<<<< HEAD
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
    $type = isset($_GET['type']) ? $_GET['type'] : 'all';

    // Get employees who don't have attendance for the selected date
    $query = "SELECT 
        e.id as employee_id, 
        e.name as employee_name,
        COALESCE(a.in_time, '') as in_time,
        COALESCE(a.out_time, '') as out_time,
        COALESCE(a.comments, '') as comments
    FROM employees e 
    LEFT JOIN attendance a ON e.id = a.employee_id AND a.date = ?
    WHERE e.status = '1' AND a.id IS NULL";

    // Add specific employee filter for single mode
    if ($type === 'single' && isset($_GET['employee_id'])) {
        $query .= " AND e.id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $date, $_GET['employee_id']);
    } else {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $date);
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
=======
session_start();
include 'db.php';
include 'auth.php';

header('Content-Type: application/json');

try {
    $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    $type = isset($_GET['type']) ? $_GET['type'] : '';

    $query = "SELECT a.*, e.name as employee_name 
              FROM attendance a 
              LEFT JOIN employees e ON a.employee_id = e.id 
              WHERE DATE(a.date) = ?";

    $params = [$date];
    $types = "s";

    if ($type && $type !== 'all') {
        $query .= " AND e.position = ?";
        $params[] = $type;
        $types .= "s";
    }

    $stmt = mysqli_prepare($conn, $query);
    
    if ($stmt === false) {
        throw new Exception('Error preparing statement: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, $types, ...$params);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error executing statement: ' . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'id' => $row['id'],
            'employee_name' => htmlspecialchars($row['employee_name']),
            'in_time' => $row['in_time'],
            'out_time' => $row['out_time'],
            'status' => $row['status'],
            'comments' => htmlspecialchars($row['comments'] ?? '')
        ];
    }    echo json_encode(['status' => 'success', 'data' => $data]);

} catch (Exception $e) {
>>>>>>> 84b16f637eeeb84293c21d5fc67f822c09b4048f
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
<<<<<<< HEAD
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
exit();
=======
}
exit(); // Make sure nothing else is output
>>>>>>> 84b16f637eeeb84293c21d5fc67f822c09b4048f

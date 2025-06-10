<?php
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
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
exit(); // Make sure nothing else is output
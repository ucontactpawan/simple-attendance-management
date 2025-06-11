<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

header('Content-Type: application/json');

try {
    $query = "SELECT id, name FROM employees WHERE status = '1' ORDER BY name ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $employees = [];
    while ($row = $result->fetch_assoc()) {
        $employees[] = [
            'id' => $row['id'],
            'name' => $row['name']
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $employees
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();


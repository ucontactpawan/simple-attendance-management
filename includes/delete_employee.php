<?php  

include 'db.php';

if(isset($_POST['id'])){
    $id = intval($_POST['id']);    // Delete related attendance_logs first
    $conn->query("DELETE FROM attendance_logs WHERE employee_id = $id");

    $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        echo json_encode(['status' => 'success', 'message' => 'Employee deleted successfully']);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to delete employee: ' . $stmt->error
        ]);
    }
    $stmt->close();
    $conn->close();
}else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
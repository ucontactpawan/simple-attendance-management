<?php 

include '../includes/db.php';

if($_SERVER['REQUEST_METHOD']=== 'GET'){
    $employeeId = $_GET['employee_id'] ?? null;
    $date = $_GET['date'] ?? null;

    try{
        $query = "SELECT 
        ah.*, e.name as employee_name
        FROM attendance_history ah
        JOIN employees e ON ah.employee_id = e.id 
        WHERE 1=1";

        $params = [];
        $types = "";
        if($employeeId){
            $query .= "AND ah.employee_id = ?";
            $params[] = $employeeId;
            $types .= "i";
        }
        if($date){
            $query .= "AND DATE (ah.date_time) = ?";
            $params[] = $date;
            $types = "i";
        }

        $query .= "ORDER BY ah.date_time DESC";

        $stmt = $conn->prepare($query);
        if(!empty($params)){
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $history = [];
        while($row = $result->fetch_assoc()){
            $history[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data' => $history
        ]);
    }catch(Exception $e){
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
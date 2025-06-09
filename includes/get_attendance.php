<?php
include 'db.php';

if(isset($_GET['date'])) {
    $date = $_GET['date'];
    
    // Prepare query to get attendance data with employee names
    $query = "SELECT 
        a.id,
        e.name as employee_name,
        a.date,
        a.check_in,
        a.check_out,
        CASE
            WHEN a.check_in IS NULL AND a.check_out IS NULL THEN 'absent'
            WHEN TIME(a.check_in) > '09:00:00' THEN 'late'
            ELSE 'present'
        END as status
    FROM employees e
    LEFT JOIN attendance_logs a ON e.id = a.employee_id
    WHERE DATE(a.date) = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $date);
    
    if($stmt->execute()) {
        $result = $stmt->get_result();
        $attendance_data = array();
        
        while($row = $result->fetch_assoc()) {
            $attendance_data[] = array(
                'id' => $row['id'],
                'employee_name' => $row['employee_name'],
                'date' => $row['date'],
                'check_in' => $row['check_in'],
                'check_out' => $row['check_out'],
                'status' => $row['status']
            );
        }
        
        echo json_encode($attendance_data);
    } else {
        echo json_encode(['error' => 'Failed to fetch attendance data']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['error' => 'Date parameter is required']);
}

$conn->close();
?>
<?php  

include 'db.php';

if(isset($_GET['id'])){
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM employees WHERE id = $id");
    if($result && $row = $result ->fetch_assoc()){
        echo json_encode($row);
    }else {
        echo json_encode([]);
        
    }

}else{
    echo json_encode([]);
}
<?php  

include 'db.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $email = $_POST['email'];
    $position = $_POST['position'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];

    // now checking for  duplicate email , except for the current employee
    $stmt = $conn->prepare("SELECT id FROM employees WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows > 0){
        echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
    } else {
        $stmt->close();        // Update employee details
        $update_query = "UPDATE employees SET name = ?, email = ?, position = ?, contact = ?, address = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("sssssi", $name, $email, $position, $contact, $address, $id);

        if($update_stmt->execute()){
            echo json_encode(['status' => 'success', 'message' => 'Employee updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update employee']);
        }
        $update_stmt->close();
        $conn->close();
    }
}
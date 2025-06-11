<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Set content type to JSON
header("Content-Type: application/json");

// Include database connection
require_once "db.php";

try {
    // Check request method
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Invalid request method");
    }    // Validate required fields
    $required_fields = ["name", "email", "position", "contact", "address"];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Sanitize input
    $name = mysqli_real_escape_string($conn, $_POST["name"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $position = mysqli_real_escape_string($conn, $_POST["position"]);
    $contact = mysqli_real_escape_string($conn, $_POST["contact"]);
    $address = mysqli_real_escape_string($conn, $_POST["address"]);// Check if email already exists
    $check_email = "SELECT id FROM employees WHERE email = ?";
    $check_stmt = mysqli_prepare($conn, $check_email);
    mysqli_stmt_bind_param($check_stmt, "s", $email);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        throw new Exception("Email already exists");
    }
    mysqli_stmt_close($check_stmt);    // Prepare the insert query
    $insert_query = "INSERT INTO employees (name, email, position, contact, address) 
                    VALUES (?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $position, $contact, $address);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error saving employee: " . mysqli_error($conn));
    }

    $employee_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

        // Return success response
    echo json_encode(array(
        'status' => 'success',
        'message' => 'Employee added successfully',
        'employee_id' => $employee_id
    ));

} catch (Exception $e) {
    // Return error response
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
} finally {
    // Close database connection
    if (isset($conn)) {
        mysqli_close($conn);
    }
}
?>

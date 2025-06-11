<?php   
// Database connection file

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance_portal";

$conn = new mysqli($servername, $username, $password, $dbname);

if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

?>
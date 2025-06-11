<?php
session_start();
include('includes/db.php');

if (isset($_POST['signup'])) {
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $position = 'employee'; // Default postion for new employees

    //Now check if email is already registered
    $check_query = "SELECT * FROM employees WHERE email = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($check_result) > 0){
        $error = "Email already registered. Please use a different email.";
    }else {

        // now inserting the new employee
        $insert_query = "INSERT INTO employees (name, email,password,position) VALUES (?,?,?,?)";  
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $password, $position);
        
        if(mysqli_stmt_execute($stmt)){
            $_SESSION['user_id'] = mysqli_insert_id($conn);
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role']= $position;
            header("Location: dashboard.php");
            exit();
        }else{
            $error = "Failed to register. Please try again.";
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>register page</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
</head>

<body>
    <div class="container">
        <h1 class="form-title">Register</h1>
        <form method="POST" action="">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="name" id="name" placeholder="Name" required>
            </div>
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="email" placeholder="Email" required>
            </div>
            <div class="input-group password">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="Password" required>
                <i id="eye" class="fa fa-eye"></i>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="confirm_password" placeholder="confirm password">
            </div>
            <input type="submit" class="custom-btn" value="Sign Up" name="signup">
        </form>
        <p class="or">
            ----------or--------
        </p>
        <div class="icons">
            <i class="fab fa-google"></i>
            <i class="fab fa-facebook"></i>
        </div>
        <div class="links">
            <p>Don't have account yet?</p>
            <a href="register.php">Sign Up</a>
        </div>
    </div>
    <script src="js/script.js"></script>
</body>

</html>
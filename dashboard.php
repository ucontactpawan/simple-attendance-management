<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include('includes/db.php');

?>
<!-- Dashboard page -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <!-- Add Bootstrap CSS first -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Base styles -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Component styles -->
    <link rel="stylesheet" href="css/navbar.css">
    <!-- Page specific styles -->
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
    <?php include('includes/navbar.php'); ?>
    <!-- implementing sidebar html -->
    <?php include('includes/sidebar.php'); ?>

    <div class="main-content">
        <div class="header">
            <h2>Dashboard</h2>
            <p>Welcome to Simple Attendance Management System</p>
        </div>

        <div class="card-container">
            <div class="card">
                <div class="card-icon"><i class="fa fa-user fa-3x"></i></div>
                <p class="card-label">TOTAL EMPLOYEES</p>
                <h3 id="totalEmployees">7</h3>
            </div>
            <div class="card">
                <div class="card-icon"><i class="fa fa-check-square fa-3x"></i></div>
                <p class="card-label">ON TIME TODAY</p>
                <div class="circle-progress">
                    <svg width="60" height="60">
                        <circle cx="30" cy="30" r="25" stroke="#009688" stroke-width="5" fill="none" />
                        <circle cx="30" cy="30" r="25" stroke="#e0e0e0" stroke-width="5" fill="none" stroke-dasharray="157" stroke-dashoffset="40" />
                    </svg>
                </div>
                <h3 id="onTimeToday">4</h3>
            </div>
            <div class="card">
                <div class="card-icon"><i class="fa fa-exclamation-triangle fa-3x"></i></div>
                <p class="card-label">LATE TODAY</p>
                <div class="circle-progress">
                    <svg width="60" height="60">
                        <circle cx="30" cy="30" r="25" stroke="#009688" stroke-width="5" fill="none" />
                        <circle cx="30" cy="30" r="25" stroke="#e0e0e0" stroke-width="5" fill="none" stroke-dasharray="157" stroke-dashoffset="100" />
                    </svg>
                </div>
                <h3 id="lateToday">3</h3>
            </div>            <div class="card">
                <div class="card-icon"><i class="fa fa-clock fa-3x"></i></div>
                <p class="card-label">ON TIME PERCENTAGE</p>
                <div class="circle-progress">
                    <svg width="60" height="60">
                        <circle cx="30" cy="30" r="25" stroke="#009688" stroke-width="5" fill="none" />
                        <circle cx="30" cy="30" r="25" stroke="#e0e0e0" stroke-width="5" fill="none" stroke-dasharray="157" stroke-dashoffset="67" />
                    </svg>
                </div>
                <h3 id="onTimePercent">57.1%</h3>
            </div>
        </div>
    </div> <?php include('includes/footer.php'); ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>

</html>
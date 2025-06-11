<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <h2>PORTAL</h2>    <ul>
        <li><a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php' ? 'active' : ''); ?>">Dashboard</a></li>
        <li><a href="employee.php" class="<?php echo ($current_page == 'employee.php' ? 'active' : ''); ?>">Employees</a></li>
        <li><a href="attendance_sheet.php" class="<?php echo ($current_page == 'attendance_sheet.php' ? 'active' : ''); ?>">Attendance Sheet</a></li>
        <li><a href="attendance_logs.php" class="<?php echo ($current_page == 'attendance_logs.php' ? 'active' : ''); ?>">Attendance Logs</a></li>
    </ul>
</div>
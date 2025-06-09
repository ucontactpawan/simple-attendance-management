<?php
session_start();
include 'includes/db.php';
include 'includes/auth.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get filter values
$employee_id = isset($_GET['employee_id']) ? $_GET['employee_id'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01'); // First day of current month
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d'); // Current date
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Build query
$query = "SELECT a.*, e.name as employee_name 
          FROM attendance_logs a 
          JOIN employees e ON a.employee_id = e.id 
          WHERE 1=1";

$params = [];
$types = "";

if ($employee_id) {
    $query .= " AND a.employee_id = ?";
    $params[] = $employee_id;
    $types .= "i";
}

if ($date_from) {
    $query .= " AND a.date >= ?";
    $params[] = $date_from;
    $types .= "s";
}

if ($date_to) {
    $query .= " AND a.date <= ?";
    $params[] = $date_to;
    $types .= "s";
}

$query .= " ORDER BY a.date DESC, a.check_in ASC";

// Prepare and execute statement
$stmt = mysqli_prepare($conn, $query);
if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get all employees for filter dropdown
$employees_query = "SELECT id, name FROM employees ORDER BY name";
$employees_result = mysqli_query($conn, $employees_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Logs</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Base styles -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Component styles -->
    <link rel="stylesheet" href="css/navbar.css">
    <!-- Attendance Logs specific styles -->
    <link rel="stylesheet" href="css/attendance_logs.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="logs-container">
            <div class="logs-header">
                <h2 class="logs-title">Attendance Logs</h2>
                <button class="logs-btn-filter" id="exportLogs">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>

            <form id="logsFilterForm" class="logs-filters">
                <div class="logs-filter-group">
                    <label for="employeeFilter">Employee</label>
                    <select id="employeeFilter" class="logs-filter-control">
                        <option value="">All Employees</option>
                        <?php while ($emp = mysqli_fetch_assoc($employees_result)) { ?>
                            <option value="<?php echo $emp['id']; ?>" <?php echo ($employee_id == $emp['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($emp['name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="logs-filter-group">
                    <label for="dateFrom">Date From</label>
                    <input type="date" id="dateFrom" class="logs-filter-control logs-date-input"
                        value="<?php echo $date_from; ?>">
                </div>
                <div class="logs-filter-group">
                    <label for="dateTo">Date To</label>
                    <input type="date" id="dateTo" class="logs-filter-control logs-date-input"
                        value="<?php echo $date_to; ?>">
                </div>
                <div class="logs-filter-group">
                    <label for="statusFilter">Status</label>
                    <select id="statusFilter" class="logs-filter-control">
                        <option value="">All Status</option>
                        <option value="ontime" <?php echo ($status == 'ontime') ? 'selected' : ''; ?>>On Time</option>
                        <option value="late" <?php echo ($status == 'late') ? 'selected' : ''; ?>>Late</option>
                        <option value="absent" <?php echo ($status == 'absent') ? 'selected' : ''; ?>>Absent</option>
                    </select>
                </div>
                <button type="submit" class="logs-btn-filter">Apply Filters</button>
                <button type="button" id="clearFilters" class="logs-btn-filter" style="background-color: #6b7280;">Clear</button>
            </form>

            <table class="logs-table">
                <thead>
                    <tr>
                        <th>Date</th>                        <th>Employee</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                            <td><?php echo htmlspecialchars($row['employee_name']); ?></td>
                            <td><?php echo $row['check_in'] ? date('h:i A', strtotime($row['check_in'])) : '-'; ?></td>
                            <td><?php echo $row['check_out'] ? date('h:i A', strtotime($row['check_out'])) : '-'; ?></td>
                            <td>
                                <div data-entry-time="<?php echo $row['check_in']; ?>">
                                    <?php 
                                    if ($row['check_in']) {
                                        $checkInTime = new DateTime($row['check_in']);
                                        $standardTime = new DateTime('09:30:00');
                                        if ($checkInTime > $standardTime) {
                                            echo '<span class="badge bg-warning">Late</span>';
                                        } else {
                                            echo '<span class="badge bg-success">On Time</span>';
                                        }
                                    } else {
                                        echo '<span class="badge bg-danger">Absent</span>';
                                    }
                                    ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/attendance_logs.js"></script>
</body>

</html>
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
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01'); 
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-t'); // Last day of current month
$status = isset($_GET['status']) ? $_GET['status'] : '';

// Query to get attendance logs with employee names
$query = "SELECT 
            a.*, 
            e.name as employee_name,
            CASE
                WHEN a.status = '0' THEN 'Absent'
                WHEN a.status = '1' THEN 'Present'
            END as status,
            CASE
                WHEN a.in_time IS NOT NULL AND a.out_time IS NOT NULL 
                THEN TIMESTAMPDIFF(MINUTE, a.in_time, a.out_time)
                ELSE NULL
            END as total_minutes
          FROM attendance a 
          JOIN employees e ON a.employee_id = e.id 
          WHERE 1=1";

$params = array();
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

$query .= " ORDER BY a.date DESC, e.name ASC";

// Add error checking after prepare statement
$stmt = mysqli_prepare($conn, $query);
if ($stmt === false) {
    die('Prepare failed: ' . mysqli_error($conn));
}

if (!empty($params)) {
    if (!mysqli_stmt_bind_param($stmt, $types, ...$params)) {
        die('Binding parameters failed: ' . mysqli_stmt_error($stmt));
    }
}

if (!mysqli_stmt_execute($stmt)) {
    die('Execute failed: ' . mysqli_stmt_error($stmt));
}

$result = mysqli_stmt_get_result($stmt);
if ($result === false) {
    die('Getting result set failed: ' . mysqli_stmt_error($stmt));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Logs</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/attendance_logs.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="logs-container">
            <div class="filter-header">
                <div class="filter-group">
                    <label>Select Month</label>
                    <select class="form-select" id="monthFilter">
                        <?php 
                        $months = [
                            '01' => 'January', '02' => 'February', '03' => 'March',
                            '04' => 'April', '05' => 'May', '06' => 'June',
                            '07' => 'July', '08' => 'August', '09' => 'September',
                            '10' => 'October', '11' => 'November', '12' => 'December'
                        ];
                        foreach ($months as $value => $label) {
                            $selected = $value == date('m') ? 'selected' : '';
                            echo "<option value='$value' $selected>$label</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Select Year</label>
                    <select class="form-select" id="yearFilter">
                        <?php 
                        $currentYear = date('Y');
                        for($i = $currentYear - 2; $i <= $currentYear + 2; $i++) {
                            $selected = $i == $currentYear ? 'selected' : '';
                            echo "<option value='$i' $selected>$i</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Select Employee</label>
                    <select class="form-select" id="employeeFilter">
                        <option value="">All Employees</option>
                        <?php
                        $emp_query = "SELECT id, name FROM employees ORDER BY name ASC";
                        $emp_result = mysqli_query($conn, $emp_query);
                        while ($emp = mysqli_fetch_assoc($emp_result)) {
                            $selected = $emp['id'] == $employee_id ? 'selected' : '';
                            echo "<option value='{$emp['id']}' $selected>" . 
                                 htmlspecialchars($emp['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="total-hours">
                Total Working Hours <span id="totalWorkingHours">0Hr 0Min</span>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>In Time</th>
                            <th>Out Time</th>
                            <th>Late Time</th>
                            <th>Total Hours</th>
                            <th>Status</th>
                            <th>Comments</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { 
                        $totalMinutes = $row['total_minutes'] ?? 0;
                        $hours = floor($totalMinutes / 60);
                        $minutes = $totalMinutes % 60;
                        
                        $lateMinutes = 0;
                        if ($row['in_time']) {
                            $inTime = strtotime($row['in_time']);
                            $standardTime = strtotime('09:30:00');
                            if ($inTime > $standardTime) {
                                $lateMinutes = round(($inTime - $standardTime) / 60);
                            }
                        }
                    ?>
                        <tr>
                            <td><?php echo date('D, d M Y', strtotime($row['date'])); ?></td>
                            <td><?php echo htmlspecialchars($row['employee_name']); ?></td>
                            <td><?php echo $row['in_time'] ? date('h:i A', strtotime($row['in_time'])) : '-'; ?></td>
                            <td><?php echo $row['out_time'] ? date('h:i A', strtotime($row['out_time'])) : '-'; ?></td>
                            <td><?php echo $lateMinutes > 0 ? $lateMinutes . 'm' : '-'; ?></td>
                            <td class="total-time"><?php
                            if($totalMinutes > 0){
                                $hours = floor($totalMinutes / 60);
                                $minutes = $totalMinutes % 60;
                                printf('%02dh %02dm', $hours, $minutes);
                            }else{
                                echo '-';
                            } ?></td>
                            <td>
                                <?php 
                                $statusClass = match($row['status']) {
                                    'On Time' => 'bg-success',
                                    'Late' => 'bg-warning',
                                    'Absent' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                                echo "<span class='badge {$statusClass}'>{$row['status']}</span>";
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['comments'] ?? '-'); ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-btn" 
                                        title="Edit" 
                                        data-id="<?php echo $row['id']; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/attendance_logs.js"></script>
</body>
</html>
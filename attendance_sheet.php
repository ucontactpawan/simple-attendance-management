<?php
session_start();
include 'includes/db.php';
include 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Sheet</title>
    <!-- Bootstrap link -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Font awesome link -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- styles -->
    <link rel="stylesheet" href="css/style.css">
    <!-- nav bar style link -->
    <link rel="stylesheet" href="css/navbar.css">
    <!-- sidebar style link -->
    <link rel="stylesheet" href="css/sidebar.css">
    <!-- attendance sheet style link -->
    <link href="css/attendance_sheet.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <div class="att-sheet-main">
        <div class="att-sheet-container">
            <div class="att-sheet-header">
                <h2 class="att-sheet-title">Attendance Sheet</h2>
                <div class="att-sheet-controls">
                    <input type="date" class="att-sheet-date-picker" id="attendanceDate" value="<?php echo date('Y-m-d'); ?>">
                    <button class="att-sheet-btn-refresh" id="refreshBtn">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                    <button class="att-sheet-btn-submit" id="submitAttendance">
                        <i class="fas fa-save"></i> Submit Attendance
                    </button>
                </div>
            </div>

            <div class="att-sheet-table-container">
                <form id="attendanceForm">
                    <table class="att-sheet-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Position</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="attendanceTableBody">
                        <?php
                            // Fetch employees from database
                            $query = "SELECT * FROM employees ORDER BY name ASC";
                            $result = mysqli_query($conn, $query);
                            
                            // Add error checking
                            if (!$result) {
                                die("Query failed: " . mysqli_error($conn));
                            }

                            while ($row = mysqli_fetch_assoc($result)) {
                                echo '<tr data-employee-id="' . $row['id'] . '">';
                                echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['position'] ?? 'N/A') . '</td>'; // Changed from position_name to position
                                echo '<td class="checkbox-cell">
                                        <input type="checkbox" name="check_in[' . $row['id'] . ']" class="check-in" />
                                      </td>';
                                echo '<td class="checkbox-cell">
                                        <input type="checkbox" name="check_out[' . $row['id'] . ']" class="check-out" />
                                      </td>';
                                echo '<td class="status-cell">Not Set</td>';
                                echo '</tr>';
                            }
                        ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Success</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Attendance has been saved successfully!
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- script tag link -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/attendance_sheet.js"></script>
</body>
</html>
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
            <!-- Header Section -->
            <div class="header">
                <h3 class="att-sheet-title">Add Attendance</h3>
                <div class="d-flex align-items-center justify-content-between mt-3">
                    <div class="date-container">
                        <div class="input-group">
                            <input type="date" class="form-control" id="attendanceDate" value="<?php echo date('Y-m-d'); ?>"
                                onchange="loadAttendanceData()">
                        </div>
                    </div>

                    <div class="user-type-controls">
                        <label class="form-label me-2">User Type:</label>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="radio" name="attendanceType" id="singleAttendance"
                                value="single" onchange="loadAttendanceData()">
                            <label class="form-check-label" for="singleAttendance">Single</label>
                        </div>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="radio" name="attendanceType" id="multipleAttendance"
                                value="multiple" onchange="loadAttendanceData()">
                            <label class="form-check-label" for="multipleAttendance">Multiple</label>
                        </div>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="radio" name="attendanceType" id="allAttendance"
                                value="all" onchange="loadAttendanceData()">
                            <label class="form-check-label" for="allAttendance">All</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Container -->
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Employee</th>
                                <th>In Time</th>
                                <th>Out Time</th>
                                <th>Status</th>
                                <th>Comments</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="attendanceTableBody">
                            <!-- Table content will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
<!-- 
    Success Modal -->
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
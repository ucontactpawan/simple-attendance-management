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
        <!-- Control Box -->
        <div class="att-control-box">
            <div class="att-control-header">
                <h3 class="att-control-title">Add Attendance</h3>
            </div>
                <div class="att-control-body">
                    <div class="att-filters-wrap">
                        <!-- Date Section -->
                        <div class="att-filter-group">
                            <label for="attendanceDate" class="att-filter-label">Date</label>
                            <div class="att-input-wrap date-input-container">
                                <input type="hidden" id="attendanceDateISO" value="<?php echo date('Y-m-d'); ?>">
                                <input type="date" class="form-control att-date-input" id="attendanceDate"
                                    value="<?php echo date('Y-m-d'); ?>"
                                    max="<?php echo date('Y-m-d'); ?>"
                                    onchange="handleDateChange(this)">
                            </div>
                        </div>

                        <!-- User Type Section -->
                        <div class="att-filter-group">
                            <label class="att-filter-label">User Type</label>
                            <div class="att-radio-wrap">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="attendanceType"
                                        id="singleAttendance" value="single" checked>
                                    <label class="form-check-label" for="singleAttendance">Single</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="attendanceType"
                                        id="allAttendance" value="all">
                                    <label class="form-check-label" for="allAttendance">All</label>
                                </div>
                            </div>
                        </div>

                        <!-- Apply Button -->
                        <div class="att-filter-group att-button-wrap">
                            <button type="button" class="btn btn-primary att-apply-btn-sm" id="applyFilters">
                                <i class="fas fa-check"></i> Apply
                            </button>
                        </div>
                    </div>
                </div>
            </div>            <!-- Table Container -->            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">Sl no</th>
                                <th scope="col">Employee Name</th>
                                <th scope="col">In Time</th>
                                <th scope="col">Out Time</th>
                                <th scope="col">Comments</th>
                            </tr>
                        </thead>
                        <tbody id="attendanceTableBody">
                            <!-- Table content will be populated by JavaScript -->
                        </tbody>
                    </table>            
                 </div>
                <div class="save-button-container">
                    <button type="button" class="btn btn-primary btn-save-attendance" id="saveBtnContainer">
                        <i class="fas fa-save"></i> Save
                    </button>
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



    <!-- script tag link -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/attendance_sheet.js"></script>
</body>

</html>
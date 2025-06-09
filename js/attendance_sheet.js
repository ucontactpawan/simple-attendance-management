$(document).ready(function () {
  // initializing current date
  const today = new Date().toISOString().split("T")[0];
  $("#attendanceDate").val(today);
  loadAttendance(today);

  //refresh button click handler
  $(".att-sheet-btn-refresh").click(function () {
    const selectedDate = $("#attendanceDate").val();
    loadAttendance(selectedDate);
  });

  //date change event handler
  $("#attendanceDate").change(function () {
    const selectedDate = $(this).val();
    loadAttendance(selectedDate);
  });

  // Checkbox change handler
  $(".check-in, .check-out").change(function () {
    updateRowStatus($(this).closest("tr"));
  });

  // Submit attendance handler
  $("#submitAttendance").click(function () {
    submitAttendance();
  });
});

function loadAttendance(date) {
  //ajax call to load attendance data for the selected date
  $.ajax({
    url: "includes/get_attendance.php",
    type: "GET",
    data: { date: date },
    dataType: "json",
    success: function (response) {
      if (response.success) {
        response.data.forEach(function (record) {
          const row = $(`tr[data-employee-id="${record.employee_id}"]`);
          if (record.check_in) {
            row.find(".check-in").prop("checked", true);
          }
          if (record.check_out) {
            row.find(".check-out").prop("checked", true);
          }
          updateRowStatus(row);
        });
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading attendance:", error);
    },
  });
}

function updateRowStatus(row) {
  const checkIn = row.find(".check-in").prop("checked");
  const checkOut = row.find(".check-out").prop("checked");
  const statusCell = row.find(".status-cell");

  statusCell.removeClass("status-present status-absent status-half-day");

  if (checkIn && checkOut) {
    statusCell.text("Present").addClass("status-present");
  } else if (checkIn || checkOut) {
    statusCell.text("Half Day").addClass("status-half-day");
  } else {
    statusCell.text("Absent").addClass("status-absent");
  }
}

function submitAttendance() {
  const date = $("#attendanceDate").val();
  const formData = new FormData();
  formData.append("date", date);

  // Collect check-in/out data
  $(".att-sheet-table tbody tr").each(function () {
    const employeeId = $(this).data("employee-id");
    formData.append(
      `check_in[${employeeId}]`,
      $(this).find(".check-in").prop("checked")
    );
    formData.append(
      `check_out[${employeeId}]`,
      $(this).find(".check-out").prop("checked")
    );
  });

  // Submit to server
  $.ajax({
    url: "includes/save_attendance.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "json",
    success: function (response) {
      if (response.success) {
        // Show success modal
        $("#successModal").modal("show");
        // Refresh the attendance data
        loadAttendance();
      } else {
        alert("Error: " + response.message);
      }
    },
    error: function (xhr, status, error) {
      console.error("Error saving attendance:", error);
      alert("Error saving attendance. Please try again.");
    },
  });
}

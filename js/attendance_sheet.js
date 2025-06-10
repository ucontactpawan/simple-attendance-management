$(document).ready(function () {
  // initializing current date
  const today = new Date().toISOString().split("T")[0];
  $("#attendanceDate").val(today);
  loadAttendance(today);


  //date change event handler
  $("#attendanceDate").change(function () {
    const selectedDate = $(this).val();
    loadAttendance(selectedDate);
  });

  // Checkbox change handler
  $(".check-in, .check-out").change(function () {
    const row = $(this).closest("tr");
    updateRowStatus(row);
  });

  // Submit attendance handler
  $("#submitAttendance").click(function (e) {
    e.preventDefault();

    let attendanceData = [];
    $("#attendanceTableBody tr").each(function () {
      const row = $(this);
      const employeeId = row.data("employee-id");
      const checkIn = row.find(".check-in").prop("checked");
      const checkOut = row.find(".check-out").prop("checked");
      const inTime = row.find(".in-time").val();
      const outTime = row.find(".out-time").val();

      attendanceData.push({
        employee_id: employeeId,
        check_in: checkIn,
        check_out: checkOut,
        in_time: inTime,
        out_time: outTime
      });
    });

    $.ajax({
      url: "includes/save_attendance.php",
      type: "POST",
      data: {
        date: $("#attendanceDate").val(),
        attendance: JSON.stringify(attendanceData)
      },
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          $("#successModal").modal("show");
        } else {
          alert("Error: " + (response.message || "Unknown error occurred"));
        }
      },
      error: function (xhr, status, error) {
        alert("Error saving attendance: " + error);
      }
    });
  });

  function updateRowStatus(row) {
    const checkIn = row.find(".check-in").prop("checked");
    const checkOut = row.find(".check-out").prop("checked");
    const statusCell = row.find(".status-cell .badge");

    if (checkIn && checkOut) {
      statusCell.removeClass().addClass("badge bg-success").text("Present");
    } else if (checkIn) {
      statusCell.removeClass().addClass("badge bg-primary").text("Checked In");
    } else if (checkOut) {
      statusCell.removeClass().addClass("badge bg-warning").text("Checked Out");
    } else {
      statusCell.removeClass().addClass("badge bg-secondary").text("Not Set");
    }
  }
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

function showLoader() {
    const loader = document.createElement('div');
    loader.className = 'loader-container';
    loader.innerHTML = `
        <div class="loader"></div>
        <div class="loader-text">One moment please...</div>
    `;
    document.body.appendChild(loader);
}

function hideLoader() {
    const loader = document.querySelector('.loader-container');
    if (loader) {
        loader.remove();
    }
}

function loadAttendanceData() {
    const date = document.getElementById('attendanceDate').value;
    const selectedType = document.querySelector('input[name="attendanceType"]:checked');
    const type = selectedType ? selectedType.value : '';

    showLoader(); 

    fetch(`includes/get_attendance_data.php?date=${date}&type=${type}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(response => {
            const tbody = document.getElementById('attendanceTableBody');
            tbody.innerHTML = '';
            
            if (response.status === 'error') {
                throw new Error(response.message || 'Unknown error occurred');
            }

            const data = response.data || [];
            
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">No attendance records found</td></tr>';
                return;
            }
            
            data.forEach(row => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${formatDate(date)}</td>
                    <td>${row.employee_name}</td>
                    <td>${row.in_time || '-'}</td>
                    <td>${row.out_time || '-'}</td>
                    <td><span class="badge ${getStatusBadgeClass(row.status)}">${row.status === '1' ? 'Present' : 'Absent'}</span></td>
                    <td>${row.comments || '-'}</td>
                    <td>
                        <button class="btn btn-sm btn-primary edit-btn" data-id="${row.id}">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Error loading attendance data: ${error.message}</td></tr>`;
        })
        .finally(() => {
            hideLoader(); // Hide loader when done
        });
}

function formatDate(dateString) {
    const options = { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

function getStatusBadgeClass(status) {
    return status === '1' ? 'bg-success' : 'bg-secondary';
}

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Load initial data without any type selected
    loadAttendanceData();
    
    // Add event listeners
    document.querySelectorAll('input[name="attendanceType"]').forEach(radio => {
        radio.addEventListener('change', loadAttendanceData);
    });
    
    document.getElementById('attendanceDate').addEventListener('change', loadAttendanceData);
});

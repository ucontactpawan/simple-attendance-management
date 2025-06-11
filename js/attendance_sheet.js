$(document).ready(function () {
  // initializing current date
  const today = new Date().toISOString().split("T")[0];
  $("#attendanceDate").val(today);
<<<<<<< HEAD
  loadAttendanceData();
=======
  loadAttendance(today);
>>>>>>> 84b16f637eeeb84293c21d5fc67f822c09b4048f


  //date change event handler
  $("#attendanceDate").change(function () {
<<<<<<< HEAD
    loadAttendanceData();
  });

  // Attendance type change handler
  $('input[name="attendanceType"]').change(function() {
    loadAttendanceData();
=======
    const selectedDate = $(this).val();
    loadAttendance(selectedDate);
  });

  // Checkbox change handler
  $(".check-in, .check-out").change(function () {
    const row = $(this).closest("tr");
    updateRowStatus(row);
>>>>>>> 84b16f637eeeb84293c21d5fc67f822c09b4048f
  });

  // Submit attendance handler
  $("#submitAttendance").click(function (e) {
    e.preventDefault();
<<<<<<< HEAD
    saveAttendance();
  });
  
=======

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

>>>>>>> 84b16f637eeeb84293c21d5fc67f822c09b4048f
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
<<<<<<< HEAD
    const type = selectedType ? selectedType.value : 'single';
    
    localStorage.setItem('attendanceType', type);
    const tbody = document.getElementById('attendanceTableBody');
    
    // Show save button
    const saveBtnContainer = document.getElementById('saveBtnContainer');
    if (saveBtnContainer) {
        saveBtnContainer.style.display = 'block';
        const saveBtn = saveBtnContainer.querySelector('.btn-save-attendance');
        if (saveBtn) {
            saveBtn.innerHTML = '<i class="fas fa-save"></i> ' + (type === 'single' ? 'Save' : 'Save All');
            saveBtn.onclick = saveAttendance;
        }
    }    showLoader();

    // Load available employees for single mode
    if (type === 'single') {
        fetch(`includes/get_available_employees.php?date=${date}`)
            .then(response => response.json())
            .then(response => {
                if (response.status === 'success') {
                    availableEmployees = response.data;
                    const tbody = document.getElementById('attendanceTableBody');
                    tbody.innerHTML = '';

                    if (availableEmployees.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="text-center">No employees available for attendance</td></tr>';
                        hideLoader();
                        return;
                    }

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>1</td>
                        <td>
                            <select class="form-select form-select-sm single-employee-select">
                                <option value="">Select Employee</option>
                                ${availableEmployees.map(emp => `
                                    <option value="${emp.id}">${emp.name}</option>
                                `).join('')}
                            </select>
                        </td>
                        <td>
                            <input type="time" class="form-control form-control-sm" name="in_time">
                        </td>
                        <td>
                            <input type="time" class="form-control form-control-sm" name="out_time">
                        </td>
                        <td>
                            <textarea class="form-control form-control-sm" rows="2" name="comments" 
                                placeholder="Add comments..."></textarea>
                        </td>
                    `;
                    
                    tbody.appendChild(tr);
                    
                    // Set up event listener for employee selection
                    const employeeSelect = tr.querySelector('.single-employee-select');
                    employeeSelect.addEventListener('change', function() {
                        if (this.value) {
                            tr.setAttribute('data-employee-id', this.value);
                        }
                    });

                    hideLoader();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading employees: ' + error.message);
                hideLoader();
            });
        return;
    }    // For all mode, load unsaved attendance data
    fetch(`includes/get_attendance_data.php?date=${date}&type=${type}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
=======
    const type = selectedType ? selectedType.value : '';

    showLoader(); 

    fetch(`includes/get_attendance_data.php?date=${date}&type=${type}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
>>>>>>> 84b16f637eeeb84293c21d5fc67f822c09b4048f
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
            
<<<<<<< HEAD
            if (type === 'single') {
                // Create a new row for single employee selection
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>1</td>
                    <td>
                        <select class="form-select form-select-sm single-employee-select">
                            <option value="">Select Employee</option>
                            ${availableEmployees.map(emp => `
                                <option value="${emp.id}">${emp.name}</option>
                            `).join('')}
                        </select>
                    </td>
                    <td>
                        <input type="time" class="form-control form-control-sm" name="in_time">
                    </td>
                    <td>
                        <input type="time" class="form-control form-control-sm" name="out_time">
                    </td>
                    <td>
                        <textarea class="form-control form-control-sm" rows="2" name="comments" 
                            placeholder="Add comments..."></textarea>
                    </td>
                `;
                
                tbody.appendChild(tr);

                // Set up event listener for employee selection
                const employeeSelect = tr.querySelector('.single-employee-select');
                employeeSelect.addEventListener('change', function() {
                    const selectedId = this.value;
                    if (selectedId) {
                        tr.setAttribute('data-employee-id', selectedId);
                        
                        // Find existing data for this employee if any
                        const employeeData = data.find(d => d.employee_id === selectedId);
                        if (employeeData) {
                            tr.querySelector('[name="in_time"]').value = employeeData.in_time || '';
                            tr.querySelector('[name="out_time"]').value = employeeData.out_time || '';
                            tr.querySelector('[name="comments"]').value = employeeData.comments || '';
                        } else {
                            // Clear the fields if no data exists
                            tr.querySelector('[name="in_time"]').value = '';
                            tr.querySelector('[name="out_time"]').value = '';
                            tr.querySelector('[name="comments"]').value = '';
                        }
                    }
                });

                return;
            }
            
            // Handle all mode
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">No employees found</td></tr>';
                return;
            }
              // For all mode, show all employees without attendance
            data.forEach((row, index) => {
                const tr = document.createElement('tr');
                tr.setAttribute('data-employee-id', row.employee_id);
                
                tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${row.employee_name || 'N/A'}</td>
                    <td>
                        <input type="time" class="form-control form-control-sm" name="in_time">
                    </td>
                    <td>
                        <input type="time" class="form-control form-control-sm" name="out_time">
                    </td>
                    <td>
                        <textarea class="form-control form-control-sm" 
                            rows="2" 
                            name="comments"
                            placeholder="Add comments..."></textarea>
=======
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
>>>>>>> 84b16f637eeeb84293c21d5fc67f822c09b4048f
                    </td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(error => {
            console.error('Error:', error);
<<<<<<< HEAD
            alert('Error loading attendance data: ' + error.message);
        })
        .finally(() => {
            hideLoader();
        });
}

// Global variables for tracking employees
let availableEmployees = [];

function loadAvailableEmployees() {
    const date = document.getElementById('attendanceDate').value;
    
    return fetch(`includes/get_available_employees.php?date=${date}&_=${new Date().getTime()}`)
        .then(response => response.json())
        .then(response => {
            if (response.status === 'success') {
                availableEmployees = response.data;
                return availableEmployees;
            } else {
                throw new Error(response.message || 'Failed to load employees');
            }
        });
}

function createEmployeeDropdown() {
    const select = document.createElement('select');
    select.className = 'form-select form-select-sm employee-select';
    select.innerHTML = '<option value="" disabled selected hidden></option>';
    
    // Add available employees
    availableEmployees.forEach(emp => {
        select.innerHTML += `<option value="${emp.id}">${emp.name}</option>`;
    });
    
    return select;
}

// Function to create single employee row with dropdown
function createSingleEmployeeRow() {
    const tr = document.createElement('tr');
    const employeeSelect = createEmployeeDropdown();
    employeeSelect.classList.add('single-employee-select');
    
    tr.innerHTML = `
        <td>1</td>
        <td></td>
        <td>
            <input type="time" class="form-control form-control-sm" name="in_time">
        </td>
        <td>
            <input type="time" class="form-control form-control-sm" name="out_time">
        </td>
        <td>
            <textarea class="form-control form-control-sm" rows="2" name="comments" placeholder="Add comments..."></textarea>
        </td>
    `;
    
    // Insert the dropdown into the second cell
    tr.cells[1].appendChild(employeeSelect);
    
    // Handle employee selection
    employeeSelect.addEventListener('change', function() {
        const selectedId = this.value;
        if (selectedId) {
            tr.setAttribute('data-employee-id', selectedId);
        }
    });
    
    return tr;
}

// Save attendance function
function saveAttendance() {
    const tbody = document.getElementById('attendanceTableBody');
    const rows = tbody.getElementsByTagName('tr');
    const attendanceData = [];
    let hasError = false;

    Array.from(rows).forEach(row => {
        const employeeSelect = row.querySelector('.single-employee-select');
        const employeeId = employeeSelect ? employeeSelect.value : row.getAttribute('data-employee-id');

        if (employeeSelect && !employeeId) {
            hasError = true;
            employeeSelect.classList.add('is-invalid');
            return;
        }

        attendanceData.push({
            employee_id: employeeId,
            in_time: row.querySelector('[name="in_time"]').value,
            out_time: row.querySelector('[name="out_time"]').value,
            comments: row.querySelector('[name="comments"]').value
        });
    });

    if (hasError) {
        alert('Please select an employee');
        return;
    }

    if (attendanceData.length === 0) {
        alert('No attendance data to save');
        return;
    }

    const requestData = {
        date: document.getElementById('attendanceDate').value,
        attendance: attendanceData
    };
    
    console.log('Sending data:', requestData); // Debug log
    
    showLoader();
    fetch('includes/save_attendance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(response => {
        if (response.status === 'success') {
            const type = document.querySelector('input[name="attendanceType"]:checked').value;
            
            if (type === 'single') {
                // Clear the form data
                const tbody = document.getElementById('attendanceTableBody');
                tbody.innerHTML = '';
                
                // Reload available employees and create new row
                loadAvailableEmployees().then(() => {
                    if (availableEmployees.length > 0) {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>1</td>
                            <td>
                                <select class="form-select form-select-sm single-employee-select">
                                    <option value="">Select Employee</option>
                                    ${availableEmployees.map(emp => `
                                        <option value="${emp.id}">${emp.name}</option>
                                    `).join('')}
                                </select>
                            </td>
                            <td>
                                <input type="time" class="form-control form-control-sm" name="in_time">
                            </td>
                            <td>
                                <input type="time" class="form-control form-control-sm" name="out_time">
                            </td>
                            <td>
                                <textarea class="form-control form-control-sm" rows="2" name="comments" 
                                    placeholder="Add comments..."></textarea>
                            </td>
                        `;
                        
                        tbody.appendChild(tr);
                        
                        // Set up event listener for employee selection
                        const employeeSelect = tr.querySelector('.single-employee-select');
                        employeeSelect.addEventListener('change', function() {
                            if (this.value) {
                                tr.setAttribute('data-employee-id', this.value);
                            }
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="5" class="text-center">No more employees available for attendance</td></tr>';
                    }
                });
            } else {
                loadAttendanceData(); // In all mode, just reload the data
            }

            alert('Attendance saved successfully');
        } else {
            throw new Error(response.message || 'Failed to save attendance');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving attendance: ' + error.message);
    })
    .finally(() => {
        hideLoader();
    });
}
=======
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
>>>>>>> 84b16f637eeeb84293c21d5fc67f822c09b4048f

$(document).ready(function () {
  // Load saved user type preference
  const savedType = localStorage.getItem('attendanceType');
  if (savedType) {
    document.querySelector(`input[name="attendanceType"][value="${savedType}"]`).checked = true;
  }

  // initializing current date
  const today = new Date().toISOString().split("T")[0];
  $("#attendanceDate").val(today);
  handleDateChange(document.getElementById('attendanceDate')); // Just update the date format display
  
  // Initialize with empty state message
  const tbody = document.getElementById('attendanceTableBody');
  tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Select date and type, then click Apply to load attendance data</td></tr>';
  
  // If single type is selected (default or from localStorage), load employee dropdown
  const selectedType = document.querySelector('input[name="attendanceType"]:checked');
  if (selectedType && selectedType.value === 'single') {
    loadAttendanceData();
  }

  // Handle user type change
  $('input[name="attendanceType"]').change(function() {
    const type = $(this).val();
    if (type === 'single') {
      loadAttendanceData();
    } else {
      // For 'all' type, reset to initial state until Apply is clicked
      tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Click Apply to load attendance data</td></tr>';
    }
  });
  
  // Apply filters button click handler - only needed for 'all' type now
  $("#applyFilters").click(function() {
    const type = document.querySelector('input[name="attendanceType"]:checked').value;
    if (type === 'all') {
      loadAttendanceData();
    }
  });

  // Date change handler - format the display date
  $("#attendanceDate").change(function () {
    handleDateChange(this);
    // Automatically reload data for single type
    const type = document.querySelector('input[name="attendanceType"]:checked').value;
    if (type === 'single') {
      loadAttendanceData();
    }
  });

  // Save button click handler
  $(document).on('click', '#saveBtnContainer', function(e) {
    e.preventDefault();
    saveAttendance();
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
    // Use the hidden ISO date input for backend processing
    const date = document.getElementById('attendanceDateISO').value;
    const selectedType = document.querySelector('input[name="attendanceType"]:checked');
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
                    </td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(error => {
            console.error('Error:', error);
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

// Update the document ready function to properly bind the save button click event
$(document).ready(function () {
  // Load saved user type preference
  const savedType = localStorage.getItem('attendanceType');
  if (savedType) {
    document.querySelector(`input[name="attendanceType"][value="${savedType}"]`).checked = true;
  }

  // initializing current date
  const today = new Date().toISOString().split("T")[0];
  $("#attendanceDate").val(today);
  handleDateChange(document.getElementById('attendanceDate')); // Just update the date format display
  
  // Initialize with empty state message
  const tbody = document.getElementById('attendanceTableBody');
  tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Select date and type, then click Apply to load attendance data</td></tr>';
  
  // If single type is selected (default or from localStorage), load employee dropdown
  const selectedType = document.querySelector('input[name="attendanceType"]:checked');
  if (selectedType && selectedType.value === 'single') {
    loadAttendanceData();
  }

  // Handle user type change
  $('input[name="attendanceType"]').change(function() {
    const type = $(this).val();
    if (type === 'single') {
      loadAttendanceData();
    } else {
      // For 'all' type, reset to initial state until Apply is clicked
      tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Click Apply to load attendance data</td></tr>';
    }
  });
  
  // Apply filters button click handler - only needed for 'all' type now
  $("#applyFilters").click(function() {
    const type = document.querySelector('input[name="attendanceType"]:checked').value;
    if (type === 'all') {
      loadAttendanceData();
    }
  });

  // Date change handler - format the display date
  $("#attendanceDate").change(function () {
    handleDateChange(this);
    // Automatically reload data for single type
    const type = document.querySelector('input[name="attendanceType"]:checked').value;
    if (type === 'single') {
      loadAttendanceData();
    }
  });

  // Save button click handler
  $(document).on('click', '#saveBtnContainer', function(e) {
    e.preventDefault();
    saveAttendance();
  });
});

// Save attendance function
function saveAttendance() {
    const tbody = document.getElementById('attendanceTableBody');
    const rows = tbody.getElementsByTagName('tr');
    const date = document.getElementById('attendanceDate').value;

    const attendance = [];

    for (const row of rows) {
        const employeeId = row.getAttribute('data-employee-id');
        if (!employeeId) continue;

        const inTime = row.querySelector('input[type="time"][name="in_time"]')?.value || null;
        const outTime = row.querySelector('input[type="time"][name="out_time"]')?.value || null;
        const comments = row.querySelector('textarea[name="comments"]')?.value || '';

        if (inTime || outTime) {
            attendance.push({
                employee_id: parseInt(employeeId),
                in_time: inTime,
                out_time: outTime,
                comments: comments
            });
        }
    }

    if (attendance.length === 0) {
        alert('Please enter attendance data before saving.');
        return;
    }

    // Show loading state
    const saveBtn = document.querySelector('.btn-save-attendance');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    saveBtn.disabled = true;

    // Send data to server
    fetch('includes/save_attendance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            date: date,
            attendance: attendance
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' || data.success === true) {
            // Show success message
            $('#successModal').modal('show');
            // Reload attendance data
            loadAttendanceData();
        } else {
            throw new Error(data.message || 'Failed to save attendance');
        }
    })
    .catch(error => {
        console.error('Save error:', error);
        alert('Error saving attendance: ' + error.message);
    })
    .finally(() => {
        // Reset button state
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

// Format date as "12 June 2025"
function formatDate(date) {
  const d = new Date(date);
  const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
  return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
}

function handleDateChange(input) {
  if (!input) return;
  const dateValue = input.value;
  
  // Update the hidden ISO date
  const isoInput = document.getElementById('attendanceDateISO');
  if (isoInput) {
    isoInput.value = dateValue;
  }
  
  // Format the visible date
  const formattedDate = formatDate(dateValue);
  $(input).attr('title', formattedDate); // Show formatted date on hover
  
  // Create or update the formatted date display
  let dateDisplay = input.parentElement.querySelector('.formatted-date');
  if (!dateDisplay) {
    dateDisplay = document.createElement('div');
    dateDisplay.className = 'formatted-date';
    input.parentElement.appendChild(dateDisplay);
  }
  dateDisplay.textContent = formattedDate;
}

// Add this to your document.ready function
$(document).ready(function() {
    // Initialize date
    const initialDate = $('#attendanceDate').val();
    handleDateChange(document.getElementById('attendanceDate'));
});

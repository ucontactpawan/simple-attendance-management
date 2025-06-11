document.addEventListener("DOMContentLoaded", function () {
  // Initialize month and year filters with current date
  const today = new Date();
  $("#monthFilter").val(String(today.getMonth() + 1).padStart(2, "0"));
  $("#yearFilter").val(today.getFullYear());

  // Add filter and export buttons
  $(".filter-header").append(`
        <div class="filter-controls">
            <button class="btn btn-primary filter-btn">
                <i class="fas fa-filter"></i> Apply Filter
            </button>
            <button class="btn btn-secondary" id="clearFilters">
                <i class="fas fa-times"></i> Clear
            </button>
            <button class="btn btn-success" id="exportLogs">
                <i class="fas fa-file-export"></i> Export
            </button>
        </div>
    `);

  // Handle filter button click
  $(".filter-btn").click(function (e) {
    e.preventDefault();
    const month = $("#monthFilter").val();
    const year = $("#yearFilter").val();
    const employee = $("#employeeFilter").val();

    // Create date range for the selected month
    const startDate = `${year}-${month}-01`;
    const endDate = new Date(year, month, 0).toISOString().split("T")[0];

    // Build query string
    const params = new URLSearchParams({
      date_from: startDate,
      date_to: endDate,
    });

    if (employee) {
      params.append("employee_id", employee);
    }

    // Redirect with filters
    window.location.href = `attendance_logs.php?${params.toString()}`;
  });

  // Handle clear filters
  $("#clearFilters").click(function () {
    window.location.href = "attendance_logs.php";
  });

  // Calculate total working hours
  let totalMinutes = 0;
  $("tbody tr").each(function () {
    const totalTime = $(this).find("td:eq(5)").text();
    if (totalTime !== "-") {
      const [hours, minutes] = totalTime.split("h ");
      totalMinutes += parseInt(hours) * 60 + parseInt(minutes);
    }
  });

  const totalHours = Math.floor(totalMinutes / 60);
  const remainingMinutes = totalMinutes % 60;
  $("#totalWorkingHours").text(`${totalHours}Hr ${remainingMinutes}Min`);

  // Export functionality
  $("#exportLogs").click(function () {
    const data = [];
    const headers = [];

    // Get headers
    $("table thead th").each(function () {
      headers.push($(this).text().trim());
    });

    // Get data
    $("table tbody tr").each(function () {
      const row = {};
      $(this)
        .find("td")
        .each(function (i) {
          // Skip the picture column and action column
          if (i !== 1 && i !== 8) {
            row[headers[i]] = $(this).text().trim();
          }
        });
      data.push(row);
    });

    // Convert to CSV
    let csv = headers.filter((h, i) => i !== 1 && i !== 8).join(",") + "\n";
    data.forEach((row) => {
      csv += Object.values(row).join(",") + "\n";
    });

    // Create download link
    const blob = new Blob([csv], { type: "text/csv" });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.style.display = "none";
    a.href = url;
    a.download = "attendance_logs.csv";

    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    a.remove();
  });

  // Function to calculate and display attendance status
  function calculateStatus(entry_time) {
    const entry = new Date(`2000-01-01 ${entry_time}`);
    const standardTime = new Date(`2000-01-01 09:30:00`);

    if (entry <= standardTime) {
      return '<span class="badge bg-success">On Time</span>';
    } else {
      return '<span class="badge bg-warning">Late</span>';
    }
  }

  // Apply status badges
  document.querySelectorAll("[data-entry-time]").forEach((element) => {
    const entryTime = element.dataset.entryTime;

    if (entryTime) {
      element.innerHTML = calculateStatus(entryTime);
    } else {
      element.innerHTML = '<span class="badge bg-danger">Absent</span>';
    }
  });
});

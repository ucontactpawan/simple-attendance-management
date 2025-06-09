document.addEventListener("DOMContentLoaded", function () {
  // Initialize datepicker for date inputs if using one
  if ($.fn.datepicker) {
    $(".logs-date-input").datepicker({
      format: "yyyy-mm-dd",
      autoclose: true,
    });
  }

  // Handle filter form submission
  document
    .getElementById("logsFilterForm")
    .addEventListener("submit", function (e) {
      e.preventDefault();

      const formData = {
        employee_id: document.getElementById("employeeFilter").value,
        date_from: document.getElementById("dateFrom").value,
        date_to: document.getElementById("dateTo").value,
        status: document.getElementById("statusFilter").value,
      };

      // Add query parameters to URL
      const params = new URLSearchParams(formData);
      const newUrl = `${window.location.pathname}?${params.toString()}`;
      window.location.href = newUrl;
    });

  // Handle clear filters
  document
    .getElementById("clearFilters")
    .addEventListener("click", function () {
      window.location.href = window.location.pathname;
    });
  // Export functionality - temporarily disabled
  document.getElementById("exportLogs").addEventListener("click", function () {
    alert("Export functionality will be implemented soon.");
  });
  // Function to calculate and display attendance status
  function calculateStatus(entry_time) {
    const entry = new Date(`2000-01-01 ${entry_time}`);
    const standardTime = new Date(`2000-01-01 09:30:00`);

    if (entry <= standardTime) {
      return '<span class="logs-status logs-status-ontime">On Time</span>';
    } else {
      return '<span class="logs-status logs-status-late">Late</span>';
    }
  }

  // Apply status badges if they exist
  document.querySelectorAll("[data-entry-time]").forEach((element) => {
    const entryTime = element.dataset.entryTime;

    if (entryTime) {
      element.innerHTML = calculateStatus(entryTime);
    } else {
      element.innerHTML =
        '<span class="logs-status logs-status-absent">Absent</span>';
    }
  });
});

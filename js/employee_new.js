function showAddModal() {
  $("#addEmployeeModal").modal("show");
}

$(document).ready(function () {
  // Add Employee button click
  $(".add-employee-btn").click(function () {
    showAddModal();
  }); // Form submission
  $("#addEmployeeForm").on("submit", function (e) {
    e.preventDefault();

    // Get employee id if editing
    var employeeId = $("#addEmployeeModal").data("employee-id") || "";    var formData = {
      name: $("#name").val().trim(),
      email: $("#email").val().trim(),
      position: $("#position").val().trim(),
      contact: $("#contact").val().trim(),
      address: $("#address").val().trim(),
    };

    // If editing, add id to formData
    if (employeeId) {
      formData.id = employeeId;
    }

    // Choose correct URL
    var url = employeeId ? "includes/update_employee.php" : "includes/save_employee.php";

    $.ajax({
      url: url,
      type: "POST",
      data: formData,
      dataType: "json",
      beforeSend: function () {
        // Disable submit button
        $(".employee-btn-save").prop("disabled", true);
      },
      success: function (response) {
        if (response.status === "success" || response.success) {
          alert(response.message);
          $("#addEmployeeModal").modal("hide");
          $("#addEmployeeForm")[0].reset();
          $("#addEmployeeModal").removeData("employee-id");
          location.reload();
        } else {
          alert("Error: " + (response.message || "Unknown error occurred"));
        }
      },
      error: function (xhr, status, error) {
        console.error("Error details:", {
          error: error,
          status: status,
          response: xhr.responseText,
        });
        try {
          const response = JSON.parse(xhr.responseText);
          alert("Error: " + (response.message || "Unknown error occurred"));
        } catch (e) {
          alert("Error occurred while saving employee. Please try again.");
        }
      },
      complete: function () {
        // Re-enable submit button
        $(".employee-btn-save").prop("disabled", false);
      },
    });
  });

  // Modal close buttons
  $(".btn-close, .employee-btn-cancel").click(function () {
    $("#addEmployeeModal").modal("hide");
    $("#addEmployeeForm")[0].reset();
  });

  // Search functionality
  $("#searchEmployee").on("keyup", function () {
    var value = $(this).val().toLowerCase();
    $("#employeeTable tbody tr").filter(function () {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
  });
});

// Show Edit Modal
function showEditModal(employeeId){
  alert('Edit Employee ID: ' + employeeId);
  // fetch employee data using ajax
  $.ajax({
    url: 'includes/get_employee.php',
    type: 'GET',
    data: {id: employeeId},
    dataType: 'json',    success: function(employee){
      // update modal fields with employee data
      if(employee && employee.id){
        $("#addEmployeeModal #name").val(employee.name);
        $("#addEmployeeModal #email").val(employee.email);
        $("#addEmployeeModal #position").val(employee.position);
        $("#addEmployeeModal #contact").val(employee.contact);
        $("#addEmployeeModal #address").val(employee.address);

        $("#addEmployeeModal").data('employee-id',employee.id);
        //change modal title and button
        $("#addEmployeeModalLabel").text("Edit Employee");
        $(".employee-btn-save").text("Update Employee");
        $('#addEmployeeModal').modal("show");
      }else{
        alert("Employee not found");
      }
    },
    error: function(){
      alert("Error fetching employee data");
    }
  })
}

function deleteEmployee(employeeId){
  if(confirm('Are you sure you want to delete this employee?')){
   $.ajax({
    url: 'includes/delete_employee.php',
    type: 'POST',
    data: {id: employeeId},
    success: function(response){
      try{
        var result = JSON.parse(response);
        if(result.status === 'success'){
          alert('Employee deleted successfully.');
          location.reload();
        }else{
          alert('Error deleting employee.');
        }
      } catch(e){
        alert('Unexpected error: ' + response);
      }    },
    error: function(){
      alert('AJAX error occurred');
    }
   }) ;
  }
}

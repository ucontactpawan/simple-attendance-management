<?php
// Function to check if user has employee management permissions
function canManageEmployees()
{
    // Allow access to any logged-in user
    return isset($_SESSION['user_id']);
}

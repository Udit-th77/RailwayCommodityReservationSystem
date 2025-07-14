<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to employee login page
header("Location: employee_login.php");
exit();
?>

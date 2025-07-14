<?php
$host = "localhost";
$user = "root"; // Default XAMPP MySQL user
$pass = ""; // No password in XAMPP by default
$dbname = "freightx_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

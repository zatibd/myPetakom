<?php
// config.php
$host = "localhost";
$user = "root";
$password = ""; // Replace with your database password if needed
$db = "mypetakom";

// Create connection
$conn = mysqli_connect($host, $user, $password, $db);

// Check connection
if ($conn === false) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
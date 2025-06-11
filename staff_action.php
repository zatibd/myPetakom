<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "mypetakom");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Restrict access to only logged-in staff
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Staff (Administrator)', 'Staff (Petakom Advisor)'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$user_sql = "SELECT * FROM user WHERE user_id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("s", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Fetch staff role from staff table
$staff_role = "Not Available";
$staff_sql = "SELECT staff_role FROM staff WHERE staff_id = ?";
$staff_stmt = $conn->prepare($staff_sql);
$staff_stmt->bind_param("s", $user_id);
$staff_stmt->execute();
$staff_result = $staff_stmt->get_result();

if ($staff_result->num_rows === 1) {
    $row = $staff_result->fetch_assoc();
    $staff_role = $row['staff_role'];
}
?>

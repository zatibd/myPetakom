<?php
session_start();
$conn = new mysqli("localhost", "root", "", "mypetakom");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Staff (Administrator)') {
    $event_id = $conn->real_escape_string($_POST['event_id']);
    $status = $conn->real_escape_string($_POST['status']);

    if (!in_array($status, ['Approved', 'Rejected'])) {
        echo "<script>alert('Invalid status selected.'); window.location.href='merit_application_admin.php';</script>";
        exit();
    }

    $update = $conn->query("UPDATE event SET merit_status = '$status' WHERE event_id = '$event_id'");

    if ($update) {
        echo "<script>alert('Updated Successfully.'); window.location.href='merit_application_admin.php';</script>";
    } else {
        echo "<script>alert('Database Error.'); window.location.href='merit_application_admin.php';</script>";
    }
} else {
    header("Location: login.php");
    exit();
}
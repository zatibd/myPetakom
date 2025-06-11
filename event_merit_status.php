<?php
session_start();
$conn = new mysqli("localhost", "root", "", "mypetakom");

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Staff (Petakom Advisor)') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'];

    // Update merit status to Pending
    $update = $conn->query("UPDATE event SET merit_status = 'Pending' WHERE event_id = '$event_id'");

    if ($update) {
        echo "<script>alert('Merit application sent.'); window.location.href='apply_merit.php';</script>";
    } else {
        echo "<script>alert('Error applying merit.'); window.location.href='apply_merit.php';</script>";
    }
} else {
    header("Location: apply_merit.php");
    exit();
}
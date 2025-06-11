<?php
include 'config.php';
$event_id = $_GET['event_id'];

$conn->query("DELETE FROM committee WHERE event_id = '$event_id'");
echo "<script>alert('Committee Deleted.'); window.location.href='assign_event.php';</script>";
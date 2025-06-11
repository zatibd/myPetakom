<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Staff (Petakom Advisor)') {
    header("Location: login.php");
    exit();
}

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$database = "mypetakom";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect form data
$event_id = $_POST['event_id'];
$slot_date = $_POST['slot_date'];
$slot_time = $_POST['slot_time'];
$datetime = $slot_date . ' ' . $slot_time;

// Validate event ID exists
$checkEventSql = "SELECT event_id FROM event WHERE event_id = ?";
$stmt = $conn->prepare($checkEventSql);
$stmt->bind_param("s", $event_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    echo "<script>alert('Error: Event ID does not exist. Please enter a valid Event ID.'); window.history.back();</script>";
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();

// Get latitude & longitude and validate
$latitude = isset($_POST['latitude']) ? floatval($_POST['latitude']) : null;
$longitude = isset($_POST['longitude']) ? floatval($_POST['longitude']) : null;

if (!is_numeric($latitude) || !is_numeric($longitude) ||
    $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
    echo "<script>alert('Invalid geolocation values. Check latitude and longitude range.'); window.history.back();</script>";
    $conn->close();
    exit();
}

// Auto-generate attendanceslot_id with format AS0001
$result = $conn->query("SELECT MAX(CAST(SUBSTRING(attendanceslot_id, 3) AS UNSIGNED)) AS max_id_num FROM attendance_slot");
if ($result && $row = $result->fetch_assoc()) {
    $last_num = $row['max_id_num'];
    if ($last_num === null) {
        $new_num = 1;
    } else {
        $new_num = $last_num + 1;
    }
} else {
    $new_num = 1;
}
$slot_id = "AS" . str_pad($new_num, 4, "0", STR_PAD_LEFT);

// Insert into attendance_slot table
$insertSql = "INSERT INTO attendance_slot (
    attendanceslot_id, event_id, slot_geolocation, slot_time
) VALUES (?, ?, POINT(?, ?), ?)";

$stmt = $conn->prepare($insertSql);
if (!$stmt) {
    echo "<script>alert('Prepare statement failed: " . $conn->error . "'); window.history.back();</script>";
    $conn->close();
    exit();
}

$stmt->bind_param(
    "ssdds",
    $slot_id,
    $event_id,
    $latitude,
    $longitude,
    $datetime
);

if ($stmt->execute()) {
    echo "<script>alert('Attendance slot created successfully!'); window.location.href='attendaceSlot.php';</script>";
} else {
    echo "<script>alert('Error inserting attendance slot: " . $stmt->error . "'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
?>






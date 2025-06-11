<?php
session_start();
include 'config.php';

//Delete event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete']) && isset($_POST['delete_id'])) {
    // Validate session
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Staff (Petakom Advisor)') {
		header("Location: login.php");
		exit();
	}

    $event_id = mysqli_real_escape_string($conn, $_POST['delete_id']);
    $delete_sql = "DELETE FROM event WHERE event_id = '$event_id'";

    if (mysqli_query($conn, $delete_sql)) {
        header("Location: event_view.php?deleted=success");
        exit();
    } else {
        echo "Error deleting event: " . mysqli_error($conn);
        exit();
    }
}

//Create new event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {

    // Validate session
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $staff_id = $_SESSION['user_id'];
    $event_id = uniqid("E");
    $title = $_POST['title'] ?? '';
    $event_date = $_POST['event_date'] ?? '';
    $status = 'Active'; // Hardcoded to "Active"
    $location = $_POST['location'] ?? '';
    $description = $_POST['description'] ?? '';
	$level = $_POST['level'] ?? '';

    // Upload and read approval letter as binary
    if (isset($_FILES['approval_letter']) && $_FILES['approval_letter']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['approval_letter']['tmp_name'];
        $approval_letter_content = file_get_contents($file_tmp); // Read binary data
    }

    // --- Geolocation ---
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $geolocation = "POINT($longitude $latitude)";
	$merit_status = "Not Applied";
    
    // Handle approval letter (PDF) as binary
    $approval_letter_content = null;
    if (isset($_FILES['approval_letter']) && $_FILES['approval_letter']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['approval_letter']['tmp_name'];
        $approval_letter_content = file_get_contents($file_tmp);
    }

    // --- SQL insert ---
    $sql = "INSERT INTO event (
		event_id, staff_id, event_title, event_date,
		event_status, event_qr, event_geolocation,
		event_description, event_approval, event_level,
		merit_status
	) VALUES (?, ?, ?, ?, ?, '', ST_GeomFromText(?), ?, ?, ?, ?)";


    $stmt = mysqli_prepare($conn, $sql);
	mysqli_stmt_bind_param($stmt, "sssssssbss", $event_id, $staff_id, $title, $event_date, $status, $geolocation, $description, $approval_letter_content, $level, $merit_status);

    // Send long data (for BLOB) if not null
    if ($approval_letter_content !== null) {
        mysqli_stmt_send_long_data($stmt, 7, $approval_letter_content);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['event_saved'] = true;
        header("Location: event_view.php");
        exit();
    } else {
        echo "Database Error: " . mysqli_error($conn);
    }
}
?>

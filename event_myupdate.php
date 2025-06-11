<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Staff (Petakom Advisor)') {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "mypetakom");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $event_id = $_POST['event_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $date = $_POST['event_date'];
    $location_raw = $_POST['location']; // e.g., "POINT(3.123 101.123)"
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    // Parse geolocation
    $location_clean = str_replace(['POINT(', ')'], '', $location_raw);
    list($lat, $lng) = explode(' ', $location_clean);
    $geolocation_sql = "ST_GeomFromText('POINT($lat $lng)')";

    // Check if a new approval letter is uploaded
    $approval_letter = null;
    $update_letter = "";
    if (isset($_FILES['approval_letter']) && $_FILES['approval_letter']['error'] === UPLOAD_ERR_OK) {
        $filename = time() . "_" . basename($_FILES['approval_letter']['name']);
        $target = "uploads/" . $filename;
        if (move_uploaded_file($_FILES['approval_letter']['tmp_name'], $target)) {
            $approval_letter = $filename;
            $update_letter = ", event_approval = '$approval_letter'";
        }
    }

    // Check if the event date has changed and update the event status to "Postponed"
    $status_update = "";
    $old_date_query = "SELECT event_date FROM event WHERE event_id = '$event_id'";
    $result = mysqli_query($conn, $old_date_query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if ($row['event_date'] != $date) {
            $status_update = ", event_status = 'Postponed'"; // Change status to "Postponed"
        }
    }

    // Update query
    $query = "
        UPDATE event 
        SET event_title = '$title', 
            event_date = '$date', 
            event_description = '$description',
            event_geolocation = $geolocation_sql
            $status_update
            $update_letter
        WHERE event_id = '$event_id'
    ";

    if (mysqli_query($conn, $query)) {
        header("Location: event_details.php?id=" . urlencode($event_id));
        exit();
    } else {
        echo "Error updating event: " . mysqli_error($conn);
    }
} else {
    echo "Invalid request.";
}

mysqli_close($conn);
?>

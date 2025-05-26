<?php
session_start();
if ($_SESSION['user_type'] !== 'student') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "mypetakom");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$claim_id = $_POST['claim_id'] ?? '';
$student_id = $_POST['student_id'] ?? '';
$event_title = $_POST['event_title'] ?? '';
$merit_description = $_POST['merit_description'] ?? '';

if (empty($claim_id) || empty($student_id) || empty($event_title) || empty($merit_description)) {
    die("All fields are required.");
}

// Get event_id
$stmt = $conn->prepare("SELECT event_id FROM event WHERE event_title = ?");
$stmt->bind_param("s", $event_title);
$stmt->execute();
$stmt->bind_result($event_id);
$stmt->fetch();
$stmt->close();

if (empty($event_id)) {
    die("Event not found.");
}

// Get merit_id
$stmt = $conn->prepare("SELECT merit_id FROM merit WHERE merit_description = ?");
$stmt->bind_param("s", $merit_description);
$stmt->execute();
$stmt->bind_result($merit_id);
$stmt->fetch();
$stmt->close();

if (empty($merit_id)) {
    die("Merit role not found.");
}

// Check for uploaded file
if (isset($_FILES['letter_upload']) && $_FILES['letter_upload']['error'] === 0) {
    $fileData = file_get_contents($_FILES['letter_upload']['tmp_name']);
    $stmt = $conn->prepare("UPDATE meritclaim SET event_id = ?, merit_id = ?, letter_upload = ? WHERE claim_id = ?");
    $stmt->bind_param("ssss", $event_id, $merit_id, $fileData, $claim_id);
} else {
    $stmt = $conn->prepare("UPDATE meritclaim SET event_id = ?, merit_id = ? WHERE claim_id = ?");
    $stmt->bind_param("sss", $event_id, $merit_id, $claim_id);
}

if ($stmt->execute()) {
    echo "<script>alert('Merit claim updated successfully.'); window.location.href='displayMerit.php';</script>";
} else {
    echo "Error updating merit claim: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
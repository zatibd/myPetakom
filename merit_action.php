<?php
session_start();

// Check if logged in as Student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$studentid = $_SESSION['user_id'] ?? '';
if (empty($studentid)) {
    die("❌ Student ID missing from session.");
}

// Get submitted form data
$eventId = $_POST["event_id"] ?? '';
$role = $_POST["merit_description"] ?? '';
$letterFile = $_FILES["letter_upload"] ?? null;

// DB connection
$conn = new mysqli("localhost", "root", "", "mypetakom");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validate student
$stmt = $conn->prepare("SELECT student_id FROM student WHERE student_id = ?");
$stmt->bind_param("s", $studentid);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("❌ Invalid Student ID.");
}
$stmt->close();

// Get event level
$stmt = $conn->prepare("SELECT event_level FROM event WHERE event_id = ?");
$stmt->bind_param("s", $eventId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $eventLevel = $row['event_level'];
} else {
    die("❌ Event not found.");
}
$stmt->close();

// Combine role and event level to form merit_description
$meritDescription = "$role in $eventLevel Level";

// Find existing merit_id from merit table
$stmt = $conn->prepare("SELECT merit_id FROM merit WHERE merit_description = ?");
$stmt->bind_param("s", $meritDescription);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $meritId = $row['merit_id'];
} else {
    die("❌ Merit role for event level not found.");
}
$stmt->close();

// Handle file upload
if ($letterFile && $letterFile['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $letterFile['tmp_name'];
    $fileContent = file_get_contents($fileTmpPath);
} else {
    die("❌ File upload failed.");
}

// Generate claim ID and insert into meritclaim table
$claimId = uniqid("CLM");
$claimStatus = 'In Progress';

$stmt = $conn->prepare("INSERT INTO meritclaim 
    (claim_id, student_id, event_id, merit_id, letter_upload, claimStatus) 
    VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $claimId, $studentid, $eventId, $meritId, $fileContent, $claimStatus);
$stmt->send_long_data(4, $fileContent);

if ($stmt->execute()) {
    echo "<script>alert('✅ Merit application submitted successfully.'); window.location.href='displayMerit.php';</script>";
} else {
    echo "❌ Error submitting application: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

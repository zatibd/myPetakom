<?php
// BCS2243 WEB ENGINEERING
// Student ID: CB23040
// Student Name: Noora Hanim binti Azmi
// Section: 1B
// Lecturer name: Dr Noorlin binti Mohd Ali

$studentid = $_POST["student_id"];
$eventTitle = $_POST["event_title"];
$role = $_POST["merit_description"];
$letterFile = $_FILES["letter_upload"];

// Step 1: DB Connection
$conn = new mysqli("localhost", "root", "", "mypetakom");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 2: Validate student
$stmt = $conn->prepare("SELECT student_id FROM student WHERE student_id = ?");
$stmt->bind_param("s", $studentid);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("❌ Invalid Student ID.");
}

// Step 3: Get event_id
$stmt = $conn->prepare("SELECT event_id FROM event WHERE event_title = ?");
$stmt->bind_param("s", $eventTitle);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("❌ Event not found.");
}
$eventId = $result->fetch_assoc()["event_id"];

// Step 4: Get merit_id
$stmt = $conn->prepare("SELECT merit_id FROM merit WHERE merit_description = ?");
$stmt->bind_param("s", $role);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("❌ Role not found.");
}
$meritId = $result->fetch_assoc()["merit_id"];

// Step 5: Read uploaded letter file
if ($letterFile['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $letterFile['tmp_name'];
    $fileContent = file_get_contents($fileTmpPath);
} else {
    die("❌ File upload failed.");
}

// Step 6: Generate unique claim_id
$claimId = uniqid("CLM");

// ✅ Step 7: Set default status
$claimStatus = 'In Progress';

// ✅ Step 8: Insert into meritclaim with status
$stmt = $conn->prepare("INSERT INTO meritclaim 
    (claim_id, student_id, event_id, merit_id, letter_upload, claimStatus) 
    VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $claimId, $studentid, $eventId, $meritId, $fileContent, $claimStatus);
$stmt->send_long_data(4, $fileContent); // For BLOB

if ($stmt->execute()) {
    echo "<script>alert(' Merit application submitted successfully.'); window.location.href='meritApplication.php';</script>";
} else {
    echo "Error:  Submission failed: " . $stmt->error;
}

$conn->close();
?>

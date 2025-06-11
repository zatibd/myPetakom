<?php
session_start();

// Make sure the user is a logged-in student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

// Check that a claim ID was provided
if (!isset($_GET['claim_id']) || empty($_GET['claim_id'])) {
    die("Invalid request: claim ID is missing.");
}

$claim_id = $_GET['claim_id'];
$student_id = $_SESSION['student_id'] ?? '';

$conn = new mysqli("localhost", "root", "", "mypetakom");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Delete the claim
$stmt = $conn->prepare("DELETE FROM meritclaim WHERE claim_id = ?");
$stmt->bind_param("s", $claim_id);

if ($stmt->execute()) {
    echo "<script>alert('Merit claim deleted successfully.'); window.location.href='displayMerit.php';</script>";
} else {
    echo "Error: Could not delete merit claim. " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

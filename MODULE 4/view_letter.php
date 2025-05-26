<?php
if (!isset($_GET['claim_id'])) {
    die("❌ No claim ID provided.");
}

$claimId = $_GET['claim_id'];

$conn = new mysqli("localhost", "root", "", "mypetakom");
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT letter_upload FROM meritclaim WHERE claim_id = ?");
$stmt->bind_param("s", $claimId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die("❌ File not found.");
}

$stmt->bind_result($fileData);
$stmt->fetch();
$stmt->close();
$conn->close();

// Try to detect the file type from content
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_buffer($finfo, $fileData);
finfo_close($finfo);

// Set headers
header("Content-Type: $mimeType");
header("Content-Disposition: inline; filename=\"supporting_letter." . pathinfo($mimeType, PATHINFO_EXTENSION) . "\"");
echo $fileData;
?>

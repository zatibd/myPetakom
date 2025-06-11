<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "mypetakom");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

require_once "phpqrcode/qrlib.php";

$qrDir = 'qrcodes';
if (!file_exists($qrDir)) {
    mkdir($qrDir, 0777, true);
}

$qrLink = "http://10.66.42.171/BCS2243/mypetakom-1/mypetakom/qr_result.php?sid=" . urlencode($student_id);
$qrImagePath = $qrDir . '/qr_' . $student_id . '.png';
QRcode::png($qrLink, $qrImagePath, QR_ECLEVEL_H, 4);

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student QR Code</title>
    <link rel="stylesheet" href="STYLE4/merit_qr.css">
</head>
<body>
    <div class="container">
        <h2>Your Merit QR Code</h2>

        <p>Scan the QR code below to view and print your merit information.</p>

        <div class="qr-container">
            <img src="<?= htmlspecialchars($qrImagePath) ?>" alt="QR Code" />
            <div class="button-group">
                <a href="<?= htmlspecialchars($qrImagePath) ?>" download="MeritQR_<?= htmlspecialchars($student_id) ?>.png" class="btn">Download QR</a>
                <button onclick="window.print()" class="btn">Print QR</button>
            </div>
        </div>
    </div>
</body>
</html>

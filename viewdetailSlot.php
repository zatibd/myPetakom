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

$slot_id = isset($_GET['slot_id']) ? intval($_GET['slot_id']) : 0;

$stmt = $conn->prepare("SELECT 
    attendance_slot.attendanceslot_id,
    attendance_slot.event_id,
    attendance_slot.slot_time,
    ST_X(attendance_slot.slot_geolocation) AS latitude,
    ST_Y(attendance_slot.slot_geolocation) AS longitude,
    event.event_title,
    event.event_date
    FROM attendance_slot
    JOIN event ON attendance_slot.event_id = event.event_id
    WHERE attendanceslot_id = ?
    LIMIT 1");
$stmt->bind_param("i", $slot_id);
$stmt->execute();
$result = $stmt->get_result();
$slot = $result->fetch_assoc();

$qrURL = "";
if ($slot) {
    // Gunakan IP sebenar server anda untuk hasilkan URL QR
    $baseUrl = "http://10.66.41.227/BCS2243/mypetakom/scan_slot.php";
    $qrData = $baseUrl . "?slot_id=" . $slot['attendanceslot_id'];
    $qrURL = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qrData);
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Slot Details</title>
    <link rel="stylesheet" href="STYLE1/staff_style.css">
    <style>
        .slot-details {
            background: #fff;
            padding: 30px;
            max-width: 700px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .slot-details h2 {
            color: #2b4d71;
        }
        .slot-details p {
            line-height: 1.6;
        }
        .qr-code {
            text-align: center;
            margin-top: 20px;
        }
        .qr-code img {
            width: 200px;
            height: 200px;
        }
        .btn {
            background-color: #2b4d71;
            color: white;
            padding: 10px 20px;
            margin: 10px 5px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>
<body>
<div class="slot-details">
    <a href="attendaceSlot.php?event_id=<?= htmlspecialchars($slot['event_id'] ?? 0) ?>" class="btn">Back</a>
    <h2>Event Attendance Slot Details</h2>

    <?php if ($slot): ?>
        <p><strong>Event:</strong> <?= htmlspecialchars($slot['event_title']) ?></p>
        <p><strong>Date:</strong> <?= htmlspecialchars($slot['event_date']) ?></p>
        <p><strong>Time:</strong> <?= htmlspecialchars(date("H:i:s", strtotime($slot['slot_time']))) ?></p>
        <p><strong>Geolocation:</strong> <?= number_format($slot['latitude'], 6) ?>, <?= number_format($slot['longitude'], 6) ?></p>

        <div class="qr-code">
            <img src="<?= htmlspecialchars($qrURL) ?>" alt="QR Code">
            <div>
                <a href="<?= htmlspecialchars($qrURL) ?>" download="AttendanceSlot_<?= $slot['attendanceslot_id'] ?>.png" class="btn">Download QR</a>
                <a href="<?= htmlspecialchars($qrURL) ?>" target="_blank" class="btn">Print QR</a>
            </div>
        </div>
    <?php else: ?>
        <p>Attendance slot not found.</p>
    <?php endif; ?>
</div>
</body>
</html>

<?php mysqli_close($conn); ?>








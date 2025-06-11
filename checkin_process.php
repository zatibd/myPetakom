<?php
// Sambung ke pangkalan data
$conn = mysqli_connect("localhost", "root", "", "mypetakom");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Step 1: Dapatkan QRCode daripada URL
$QRCode = $_GET['attendanceslot_qr'] ?? '';

if (empty($QRCode)) {
    die("QR code is missing.");
}

// Step 2: Cari attendanceslot_id dan slot_geolocation daripada jadual attendance_slot
$getSlot = mysqli_query($conn, "SELECT attendanceslot_id, slot_geolocation FROM attendance_slot WHERE attendanceslot_qr='$QRCode'");
if (!$getSlot || mysqli_num_rows($getSlot) == 0) {
    die("Slot not found for QRCode.");
}
$slot = mysqli_fetch_assoc($getSlot);
$attendanceslot_id = $slot['attendanceslot_id'];
$expectedGeo = $slot['slot_geolocation'];

// Step 3: Dapatkan input dari borang
$student_id = $_POST['student_id'] ?? '';
$user_password = $_POST['user_password'] ?? '';
$checkin_time = date("H:i:s");

// Semak input tidak kosong
if (empty($student_id) || empty($user_password)) {
    echo "<script>alert('Please enter both Student ID and Password.'); window.location='scan_slot.php';</script>";
    exit();
}

// Step 4: Sahkan maklumat pelajar dalam jadual user
$userCheck = mysqli_query($conn, "SELECT * FROM user WHERE student_id='$student_id' AND user_password='$user_password'");
if (!$userCheck || mysqli_num_rows($userCheck) == 0) {
    echo "<script>alert('Invalid Student ID or Password'); window.location='scan_slot.php';</script>";
    exit();
}

// Step 5: Simulasi lokasi — di sini anda boleh letak sistem sebenar jika perlu
$actualGeo = $expectedGeo; // Simulasi
$location_verification = ($actualGeo === $expectedGeo) ? 1 : 0;

// Step 6: Masukkan rekod ke dalam jadual attendance
$query = "INSERT INTO attendance (attendanceslot_id, student_id, checkin_time, geolocation, location_verification)
          VALUES ('$attendanceslot_id', '$student_id', '$checkin_time', '$actualGeo', '$location_verification')";

if (mysqli_query($conn, $query)) {
    echo "<script>alert('Check-in recorded. Awaiting approval.'); window.location='scan_slot.php';</script>";
} else {
    echo "Database error: " . mysqli_error($conn);
}

// Tutup sambungan
mysqli_close($conn);
?>


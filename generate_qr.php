<?php
session_start();
include 'config.php';
require_once 'LIB/qr/phpqrcode.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Staff (Petakom Advisor)') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "Event ID not provided.";
    exit();
}

$event_id = $_GET['id'];

// Fetch event details
$result = mysqli_query($conn, "SELECT event_title, event_date, ST_AsText(event_geolocation) AS geo, event_description FROM event WHERE event_id = '$event_id'");
if (mysqli_num_rows($result) === 0) {
    echo "Event not found.";
    exit();
}

$event = mysqli_fetch_assoc($result);

// Extract lat/lon from POINT string
$location_name = "Unknown location";

if (preg_match('/POINT\(([-0-9\.]+) ([-0-9\.]+)\)/', $event['geo'], $matches)) {
    $lon = $matches[1];
    $lat = $matches[2];

    // Reverse geocoding with OpenStreetMap (Nominatim)
    $geo_url = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=$lat&lon=$lon";
    $opts = [
        "http" => [
            "header" => "User-Agent: MyPetakomQR/1.0\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    $geo_json = @file_get_contents($geo_url, false, $context);

    if ($geo_json !== false) {
    $geo_data = json_decode($geo_json, true);

		// ✅ Use full address directly from display_name
		if (isset($geo_data['display_name'])) {
			$location_name = $geo_data['display_name'];
		}
	} else {
        $location_name = "Unable to fetch location";
    }
}

// Prepare QR content as a URL pointing to student event detail page
$qrData = "https://localhost/BCS2243/mypetakom-1/mypetakom-1/mypetakom/student_event_qr_display.php?event_id=" . urlencode($event_id);


// Save QR to file
$qrFilename = "QR_" . $event_id . ".png";
$qrPath = "QR_IMAGES/" . $qrFilename;
QRcode::png($qrData, $qrPath, QR_ECLEVEL_L, 5);

// Convert QR image to base64 and store it in DB
$imageData = file_get_contents($qrPath);
$base64 = base64_encode($imageData);

$update = $conn->prepare("UPDATE event SET event_qr = ? WHERE event_id = ?");
$update->bind_param("ss", $base64, $event_id);
$update->execute();

// Redirect to view the generated QR
header("Location: view_qr.php?id=" . urlencode($event_id));
exit();
?>

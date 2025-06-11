<?php
session_start();
$conn = new mysqli("localhost", "root", "", "mypetakom");

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['event_id'])) {
    die("Event ID not provided.");
}

$event_id = $conn->real_escape_string($_GET['event_id']);

// Fetch event
$query = "
    SELECT event_title, event_date, event_description, event_qr, ST_AsText(event_geolocation) AS geo
    FROM event
    WHERE event_id = '$event_id'
";
$result = $conn->query($query);

if (!$result || $result->num_rows === 0) {
    die("Event not found.");
}

$event = $result->fetch_assoc();

// Default values
$lat = $lng = null;
$location_name = "Unknown location";

// Extract coordinates from 'POINT(lon lat)'
if (preg_match('/POINT\(([-0-9\.]+) ([-0-9\.]+)\)/', $event['geo'], $matches)) {
    $lng = $matches[1];
    $lat = $matches[2];

    // Reverse geocode using OpenStreetMap API
    $geo_url = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=$lat&lon=$lng";

    $opts = [
        "http" => [
            "header" => "User-Agent: MyPetakomApp/1.0\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    $geo_json = @file_get_contents($geo_url, false, $context);

    if ($geo_json !== false) {
        $geo_data = json_decode($geo_json, true);
        if (isset($geo_data['display_name'])) {
            $location_name = $geo_data['display_name'];
        }
    } else {
        $location_name = "Unable to fetch location";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>View Events - MyPetakom</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
</head>
<body>

	<div class="content">
		<div class="card shadow p-4">
		  <h2 class="text-center mb-4"><b><?= htmlspecialchars($event['event_title']) ?> Details</b></h2>
		  <ul class="list-group list-group-flush">
		    <li class="list-group-item"><strong>Title:</strong> <?= htmlspecialchars($event['event_title']) ?></li>
			<li class="list-group-item"><strong>Date:</strong> <?= htmlspecialchars($event['event_date']) ?></li>
			<li class="list-group-item"><strong>Location:</strong> <?= htmlspecialchars($location_name) ?></li>
			<li class="list-group-item"><strong>Description:</strong> <br><?= nl2br(htmlspecialchars($event['event_description'])) ?></li>
		  </ul>
		</div>
	</div>



</body>
</html>


  
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
  <link rel="stylesheet" href="STYLE1/student_style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .qr-box img {
      max-width: 300px;
      height: auto;
    }
  </style>
</head>
<body>
<!-- Sidebar -->
  <div class="sidebar">
    <a href="student_dashboard.php">
    <img src="IMAGES/LogoPetakom.png" alt="PETAKOM Logo" />
	</a>

    <div class="search-box">
      <input type="text" placeholder="SEARCH" />
      <button>🔍</button>
    </div>

    <div class="menu">
      <div class="menu-title" onclick="toggleMenu('home')">HOME</div>


      <div class="menu-title" onclick="toggleMenu('event')">EVENT</div>
      <div class="dropdown-content" id="event">
        <a href="student_view_event.php">View Event</a>
      </div>

      <div class="menu-title" onclick="toggleMenu('attendance')">ATTENDANCE</div>
      <div class="dropdown-content" id="attendance">
        <a href="#">Key In Attendance</a>
        <a href="#">View Attendance</a>
      </div>

      <div class="menu-title" onclick="toggleMenu('merit')">MERIT</div>
      <div class="dropdown-content" id="merit">
        <a href="meritApplication.php">Merit Application</a>
        <a href="#">Merit Summary</a>
      </div>
    </div>
  </div>

  <!-- Topbar -->
  <div class="topbar">
    <div class="dropdown">
      <div class="profile-wrapper">
        <div class="profile-circle">N.</div>
        <span class="dropdown-icon">▼</span>
      </div>
      <div class="dropdown-content-top">
        <a href="student_profile.php">Profile</a>
        <a href="#">Calendar</a>
        <a href="#">Report</a>
        <a href="logout.php">Log Out</a>
      </div>
    </div>
  </div>
  
   <!-- Main Content -->
   <div class="content">
		<div class="card shadow p-4">
		<a href="student_view_event.php">
			<button type="button">Back</button>
		</a><br>

		<h1 class="text-center mb-3"><?= htmlspecialchars($event['event_title']) ?> Details</h1>
		<p class="text-center mb-4">Scan the QR code to get the event details!</p>

		<?php if (!empty($event['event_qr'])): ?>
		  <div class="d-flex justify-content-center">
			<div class="qr-box border rounded p-3 bg-light">
			  <img src="data:image/png;base64,<?= $event['event_qr'] ?>" alt="QR Code" class="img-fluid">
			</div>
		  </div>
		<?php else: ?>
		  <div class="text-center">
			<p><em>QR Code not generated yet.</em></p>
		  </div>
		<?php endif; ?>

	  </div>
	</div>

	

	<!-- Footer -->
		<div class="footer">
		@MyPetakom 2024/2025
		</div>

	<script>
	function toggleMenu(id) {
		var content = document.getElementById(id);
		content.style.display = content.style.display === "block" ? "none" : "block";
	}

	</script>

</body>
</html>


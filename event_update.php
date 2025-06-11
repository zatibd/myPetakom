<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Staff (Petakom Advisor)') {
    header("Location: login.php");
    exit();
}

$link = mysqli_connect("localhost", "root", "", "mypetakom");
if (!$link) {
    die("Connection failed: " . mysqli_connect_error());
}

$event = null;
$event_id = '';
$title = '';
$date = '';
$geolocation = '';
$description = '';
$letter = '';
$lat = 3.0738;
$lng = 101.5183;

if (isset($_GET['id'])) {
    $event_id = $_GET['id'];

    $query = "SELECT * FROM event WHERE event_id = '$event_id'";
    $result = mysqli_query($link, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $title = $row["event_title"];
        $status = $row["event_status"];
        $date = $row["event_date"];
        $description = $row["event_description"];
        $letter = $row["event_approval"];

        $geo_query = "SELECT ST_AsText(event_geolocation) AS geo FROM event WHERE event_id = '$event_id'";
        $geo_result = mysqli_query($link, $geo_query);
        $geo_row = mysqli_fetch_assoc($geo_result);
        $geolocation = $geo_row ? $geo_row['geo'] : "";

        if (!empty($geolocation) && preg_match('/POINT\(([-0-9\.]+) ([-0-9\.]+)\)/', $geolocation, $matches)) {
            $lng = $matches[1];
            $lat = $matches[2];
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Event - MyPetakom</title>
    <link rel="stylesheet" href="STYLE1/staff_style.css">
	<link rel="stylesheet" href="STYLE2/event_update_style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map { height: 300px; width: 100%; margin-bottom: 10px; }
    </style>
</head>
<body>

	<!-- Sidebar -->
	<div class="sidebar">
		<a href="staff_dashboard.php">
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
				<a href="event_registration.php">New Event</a>
				<a href="event_view.php">View Event</a>
				<a href="#">Assign Committee</a>
				<a href="#">Apply Merit</a>
			</div>
			<div class="menu-title" onclick="toggleMenu('attendance')">ATTENDANCE</div>
		<div class="dropdown-content" id="attendance">
		  <a href="attendaceSlot.php">View Attendance</a>
		</div>
		    <div class="menu-title" onclick="toggleMenu('merit')">MERIT</div>
    <div class="dropdown-content" id="merit">
      <a href="changeStatus.php">Update Missing Merit Status</a>
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
				<a href="#">Profile</a>
				<a href="#">Calendar</a>
				<a href="#">Report</a>
				<a href="logout.php">Log Out</a>
			</div>
		</div>
	</div>

	<!-- Main Content -->
	<div class="content">
		<h1>Update Event</h1>

		<form action="event_myupdate.php" method="POST" enctype="multipart/form-data">
			<input type="hidden" name="event_id" value="<?= htmlspecialchars($event_id) ?>">

			<table>
				<tr>
					<td><b>Event Title</b></td>
					<td><input type="text" name="title" size="60" value="<?= htmlspecialchars($title) ?>" required></td>
				</tr>
				<tr>
					<td><b>Date</b></td>
					<td><input type="date" name="event_date" value="<?= htmlspecialchars($date) ?>" required></td>
				</tr>
				<tr>
					<td><b>Search Location</b></td>
					<td colspan="2">
						<input type="text" id="locationInput" placeholder="Enter location name" size="36">
						<button type="button" onclick="searchLocation()">Find</button>
					</td>
				</tr>
				<tr>
					<td><b>Geolocation</b></td>
					<td><input type="text" name="location" id="geoDisplay" value="<?= htmlspecialchars($geolocation) ?>" readonly required></td>
				</tr>
				<tr>
					<td><b>Event Location</b></td>
					<td colspan="2">
						<div id="map"></div>
						<input type="hidden" name="latitude" id="latitude" value="<?= htmlspecialchars($lat) ?>" required>
						<input type="hidden" name="longitude" id="longitude" value="<?= htmlspecialchars($lng) ?>" required>
					</td>
				</tr>
				<tr>
					<td><b>Event Description</b></td>
					<td>
						<textarea name="description" rows="4" cols="36" required><?= htmlspecialchars($description) ?></textarea>
					</td>
				</tr>
				<tr>
					<td><b>Upload Approval Letter</b></td>
					<td>
						<?php if (!empty($letter)): ?>
							<div id="currentFileSection">
								<p>Current File: <a href="download_approval.php?id=<?= urlencode($event_id) ?>" target="_blank">View PDF</a></p>
								<label>
									<input type="checkbox" id="removeFileCheckbox" name="remove_file" onchange="toggleFileView()"> Remove current file
								</label>
							</div>
							<div id="uploadField" style="display: none; margin-top: 8px;">
								<input type="file" name="approval_letter" accept="application/pdf">
							</div>
						<?php else: ?>
							<input type="file" name="approval_letter" accept="application/pdf">
						<?php endif; ?>
					</td>
				</tr>
			</table>

			<br>
			<div class="button-group">
				<input type="submit" name="update" value="Update">
				<a href="event_details.php?id=<?= urlencode($event_id) ?>">
					<button type="button">Cancel</button>
				</a><br>
			</div>
		</form>
	</div>

	<!-- Footer -->
	<div class="footer">@MyPetakom 2024/2025</div>

	<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
	<script>
	function toggleMenu(id) {
		const content = document.getElementById(id);
		content.style.display = content.style.display === "block" ? "none" : "block";
	}

	function toggleFileView() {
		const checkbox = document.getElementById('removeFileCheckbox');
		const currentFileSection = document.getElementById('currentFileSection');
		const uploadField = document.getElementById('uploadField');

		if (checkbox.checked) {
			currentFileSection.style.display = 'none';
			uploadField.style.display = 'block';
		}
	}

	// -------- Map Initialization --------
	var lat = parseFloat("<?= $lat ?>");
	var lng = parseFloat("<?= $lng ?>");

	var map = L.map('map').setView([lat, lng], 15);
	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		attribution: '&copy; OpenStreetMap contributors'
	}).addTo(map);

	var marker = L.marker([lat, lng]).addTo(map);
	updateGeoDisplay(lat, lng);

	map.on('click', function (e) {
		var lat = e.latlng.lat.toFixed(6);
		var lng = e.latlng.lng.toFixed(6);
		setMarker(lat, lng);
	});

	function setMarker(lat, lng) {
		if (marker) map.removeLayer(marker);
		marker = L.marker([lat, lng]).addTo(map);
		document.getElementById('latitude').value = lat;
		document.getElementById('longitude').value = lng;
		updateGeoDisplay(lat, lng);
	}

	function updateGeoDisplay(lat, lng) {
		document.getElementById('geoDisplay').value = `POINT(${lng} ${lat})`;
	}

	// -------- Location Search --------
	function searchLocation() {
		var location = document.getElementById('locationInput').value;
		if (!location) return;

		fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(location)}`)
			.then(res => res.json())
			.then(data => {
				if (data.length > 0) {
					var lat = parseFloat(data[0].lat).toFixed(6);
					var lng = parseFloat(data[0].lon).toFixed(6);
					map.setView([lat, lng], 16);
					setMarker(lat, lng);
				} else {
					alert("Location not found.");
				}
			})
			.catch(err => {
				console.error(err);
				alert("Failed to fetch location.");
			});
	}
	</script>

</body>
</html>

<?php mysqli_close($link); ?>

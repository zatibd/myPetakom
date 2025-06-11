<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Staff (Petakom Advisor)') {
    header("Location: login.php");
    exit();
}
include 'config.php';

// Show success message once
if (isset($_SESSION['event_saved'])) {
    echo "<script>alert('Event Saved Successfully!');</script>";
    unset($_SESSION['event_saved']);
}

// Handle fetching events
$events = mysqli_query($conn, "SELECT * FROM event ORDER BY event_date DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Registration - MyPetakom</title>
    <link rel="stylesheet" href="STYLE1/staff_style.css">
	<link rel="stylesheet" href="STYLE2/event_registration_style.css">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

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
			<a href="assign_event.php">Assign Committee</a>
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
		  <a href="student_profile.php">Profile</a>
		  <a href="#">Calendar</a>
		  <a href="#">Report</a>
		  <a href="logout.php">Log Out</a>
		</div>
	  </div>
	</div>

	<!-- Main Content -->
	<div class="content">
	  <h1>Event Registration</h1>

	  <!-- Create Form -->
	  <form action="event_process.php" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="id" value="">
		<table>
		  <tr>
			<td><b>Event Title</b></td>
			<td><input type="text" name="title" size="36" required></td>
		  </tr>
		  <tr>
			<td><b>Date</b></td>
			<td><input type="date" name="event_date" required></td>
		  </tr>
		  <tr>
			<td><b>Search Location</b></td>
			<td colspan="2">
			  <input type="text" id="locationInput" placeholder="Enter location name" size="36">
			  <button type="button" onclick="searchLocation()">Find</button>
			</td>
		  </tr>
		  <tr>
			<td>
				<b>Event Location</b>
			</td>
			<td colspan="2">
			  <div id="map"></div>
			  <p>Click the map or use the search to set location.</p>
			  <input type="hidden" name="latitude" id="latitude" required>
			  <input type="hidden" name="longitude" id="longitude" required>
			</td>
		  </tr>
		  <tr>
			<td><b>Event Level</b></td>
			<td><select name = "level" required>
				<option selected = "selected">UMPSA</option>
				<option>District</option>
				<option>State</option>
				<option>National</option>
				<option>International</option>
			</select>
			</td>
		  </tr>
		  <tr>
			<td><b>Event Description</b></td>
			<td><textarea name="description" rows="4" cols="36" required></textarea></td>
		  </tr>
		  <tr>
			<td><b>Upload Approval Letter</b></td>
			<td><input type="file" name="approval_letter" accept="application/pdf"></td>
		  </tr>

		  <input type="hidden" name="status" value="Pending">
		</table>
		<br>
		<button type="submit" name="create">Save Event</button>
	  </form>
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

	// Initialize the map
	var map = L.map('map').setView([3.0738, 101.5183], 13); // Default center
	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
	  attribution: '&copy; OpenStreetMap contributors'
	}).addTo(map);

	var marker;

	function setMarker(lat, lng) {
	  if (marker) {
		map.removeLayer(marker);
	  }
	  marker = L.marker([lat, lng]).addTo(map);
	  document.getElementById('latitude').value = lat;
	  document.getElementById('longitude').value = lng;
	}

	// On map click
	map.on('click', function (e) {
	  var lat = e.latlng.lat.toFixed(6);
	  var lng = e.latlng.lng.toFixed(6);
	  setMarker(lat, lng);
	});

	// Location search function
	function searchLocation() {
	  var location = document.getElementById('locationInput').value;
	  if (!location) return;

	  fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(location)}`)
		.then(response => response.json())
		.then(data => {
		  if (data && data.length > 0) {
			var lat = parseFloat(data[0].lat).toFixed(6);
			var lng = parseFloat(data[0].lon).toFixed(6);
			map.setView([lat, lng], 15);
			setMarker(lat, lng);
		  } else {
			alert("Location not found.");
		  }
		})
		.catch(error => {
		  console.error("Error:", error);
		  alert("Failed to fetch location.");
		});
	}
	</script>

</body>
</html>

<?php
session_start();
if ($_SESSION['user_type'] !== 'staff') {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$database = "mypetakom";
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'] ?? '';
if (!$id) {
    die("Missing attendance slot ID.");
}

// Handle POST update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'] ?? '';
    $slot_date = $_POST['slot_date'] ?? '';
    $slot_time = $_POST['slot_time'] ?? '';
    $latitude = $_POST['latitude'] ?? '';
    $longitude = $_POST['longitude'] ?? '';

    if (!$event_id || !$slot_date || !$slot_time || !$latitude || !$longitude) {
        die("All fields are required.");
    }

    if (!is_numeric($latitude) || !is_numeric($longitude)) {
        die("Latitude and Longitude must be numeric.");
    }

    $datetime = $slot_date . ' ' . $slot_time;

    // Check event exists
    $stmt = $conn->prepare("SELECT event_id FROM event WHERE event_id = ?");
    $stmt->bind_param("s", $event_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 0) {
        die("Event ID does not exist.");
    }
    $stmt->close();

    // Update slot
    $stmt = $conn->prepare("UPDATE attendance_slot SET event_id=?, slot_time=?, slot_geolocation=POINT(?, ?) WHERE attendanceslot_id=?");
    $stmt->bind_param("ssdds", $event_id, $datetime, $latitude, $longitude, $id);
    if ($stmt->execute()) {
        echo "<script>alert('Attendance slot updated successfully.'); window.location.href='attendaceSlot.php';</script>";
        exit();
    } else {
        die("Update failed: " . $stmt->error);
    }
}

// Get current data
$stmt = $conn->prepare("SELECT attendanceslot_id, event_id, slot_time, AsText(slot_geolocation) AS location FROM attendance_slot WHERE attendanceslot_id=?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if (!$data) {
    die("Slot not found.");
}

// Parse date/time/location
$date = date('Y-m-d', strtotime($data['slot_time']));
$time = date('H:i', strtotime($data['slot_time']));
preg_match('/POINT\(([-0-9\.]+) ([-0-9\.]+)\)/', $data['location'], $matches);
$latitude = $matches[1] ?? '2.9272';
$longitude = $matches[2] ?? '101.6412';
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Update Attendance Slot</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

  <!-- Styles -->
  <link rel="stylesheet" href="STYLE/staff_style.css" />
  <link rel="stylesheet" href="STYLE/attendance_slot.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <style>
    #map { height: 300px; width: 100%; margin-bottom: 20px; }
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <img src="IMAGES/LogoPetakom.png" alt="PETAKOM Logo" />
  <div class="search-box">
    <input type="text" placeholder="SEARCH" />
    <button>🔍</button>
  </div>
  <div class="menu">
    <div class="menu-title" onclick="toggleMenu('home')">HOME</div>
    <div class="menu-title" onclick="toggleMenu('event')">EVENT</div>
    <div class="dropdown-content" id="event">
      <a href="#">New Event</a>
      <a href="#">View Event</a>
      <a href="#">Assign Committee</a>
      <a href="#">Apply Merit</a>
    </div>
    <div class="menu-title" onclick="toggleMenu('attendance')">ATTENDANCE</div>
    <div class="dropdown-content" id="attendance">
      <a href="attendaceSlot.php">View Attendance</a>
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
  <button type="button" onclick="window.location.href='attendaceSlot.php'">Back</button><br><br>
  <h1>Update Attendance Slot</h1>

  <form action="" method="POST" enctype="multipart/form-data" style="max-width: 500px;">
    <label for="event_id">Event ID:</label><br>
    <input type="text" id="event_id" name="event_id" required value="<?= htmlspecialchars($data['event_id']) ?>"><br><br>

    <label for="slot_date">Date:</label><br>
    <input type="date" id="slot_date" name="slot_date" required value="<?= $date ?>"><br><br>

    <label for="slot_time">Time:</label><br>
    <input type="time" id="slot_time" name="slot_time" required value="<?= $time ?>"><br><br>

    <label for="location_name">Search Location Name:</label><br>
    <input type="text" id="location_name" placeholder="e.g. Universiti Putra Malaysia"><br><br>
    <button type="button" onclick="searchLocation()">Search Location</button><br><br>

    <label>Pick Location on Map:</label><br>
    <div id="map"></div>

    <label for="latitude">Latitude:</label><br>
    <input type="text" id="latitude" name="latitude" readonly required value="<?= $latitude ?>"><br><br>

    <label for="longitude">Longitude:</label><br>
    <input type="text" id="longitude" name="longitude" readonly required value="<?= $longitude ?>"><br><br>

    <p style="color: green;">QR Code will be regenerated automatically.</p>
    <button type="submit">Update</button><br><br>
  </form>
</div>

<!-- Footer -->
<div class="footer">@MyPetakom 2024/2025</div>

<!-- Scripts -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
function toggleMenu(id) {
  var content = document.getElementById(id);
  content.style.display = content.style.display === "block" ? "none" : "block";
}

// Initialize map
var map = L.map('map').setView([<?= $latitude ?>, <?= $longitude ?>], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 19,
  attribution: '© OpenStreetMap'
}).addTo(map);

var marker = L.marker([<?= $latitude ?>, <?= $longitude ?>]).addTo(map)
             .bindPopup("Selected Location:<br><?= $latitude ?>, <?= $longitude ?>").openPopup();

map.on('click', function(e) {
  var lat = e.latlng.lat.toFixed(6);
  var lng = e.latlng.lng.toFixed(6);
  setCoordinates(lat, lng);
});

function setCoordinates(lat, lng) {
  document.getElementById('latitude').value = lat;
  document.getElementById('longitude').value = lng;
  if (marker) map.removeLayer(marker);
  marker = L.marker([lat, lng]).addTo(map)
           .bindPopup("Selected Location:<br>" + lat + ", " + lng).openPopup();
}

function searchLocation() {
  var name = document.getElementById('location_name').value;
  if (!name) {
    alert("Please enter a location name.");
    return;
  }

  fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(name)}`)
    .then(response => response.json())
    .then(data => {
      if (data.length > 0) {
        var lat = parseFloat(data[0].lat).toFixed(6);
        var lon = parseFloat(data[0].lon).toFixed(6);
        map.setView([lat, lon], 16);
        setCoordinates(lat, lon);
      } else {
        alert("Location not found.");
      }
    })
    .catch(() => {
      alert("Failed to search location.");
    });
}
</script>
</body>
</html>

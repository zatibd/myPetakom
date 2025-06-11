<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Staff (Petakom Advisor)') {
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
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Update Attendance Slot</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="STYLE3/attendance_slot.css" />
 
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
  <button class="back-btn" onclick="window.location.href='attendaceSlot.php'">Back</button>
  <h1>Update Attendance Slot</h1>

  <form action="" method="POST" enctype="multipart/form-data">
    <label for="event_id">Event ID:</label>
    <input type="text" id="event_id" name="event_id" required value="<?= htmlspecialchars($data['event_id']) ?>">

    <label for="slot_date">Date:</label>
    <input type="date" id="slot_date" name="slot_date" required value="<?= htmlspecialchars($date) ?>">

    <label for="slot_time">Time:</label>
    <input type="time" id="slot_time" name="slot_time" required value="<?= htmlspecialchars($time) ?>">

    <label for="location_name">Search Location Name:</label>
    <input type="text" id="location_name" name="location_name" placeholder="e.g. Universiti Malaysia Pahang">

    <button type="button" id="searchBtn" onclick="searchLocation()">Search Location</button>

    <label>Pick Location on Map:</label>
    <div id="map"></div>

    <label for="latitude">Latitude:</label>
    <input type="text" id="latitude" name="latitude" readonly required value="<?= htmlspecialchars($latitude) ?>">

    <label for="longitude">Longitude:</label>
    <input type="text" id="longitude" name="longitude" readonly required value="<?= htmlspecialchars($longitude) ?>">

    <button type="submit" class="submit-btn">Update</button>
  </form>
</div>

<div class="footer">
    @MyPetakom 2024/2025
  </div>


<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  // Toggle sidebar menus
  function toggleMenu(menuId) {
    const elem = document.getElementById(menuId);
    if (elem.style.display === "block") {
      elem.style.display = "none";
    } else {
      elem.style.display = "block";
    }
  }

  // Initialize map with existing coordinates
  const initialLat = <?= json_encode(floatval($latitude)) ?>;
  const initialLng = <?= json_encode(floatval($longitude)) ?>;

  const map = L.map('map').setView([initialLat, initialLng], 15);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: 'Map data © <a href="https://openstreetmap.org">OpenStreetMap</a> contributors'
  }).addTo(map);

  const marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);

  // Update input fields when marker dragged
  marker.on('dragend', function(e) {
    const latlng = marker.getLatLng();
    document.getElementById('latitude').value = latlng.lat.toFixed(6);
    document.getElementById('longitude').value = latlng.lng.toFixed(6);
  });

  // Search location by name
  function searchLocation() {
    const locationName = document.getElementById('location_name').value.trim();
    if (!locationName) {
      alert("Please enter a location name to search.");
      return;
    }
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(locationName)}`;

    fetch(url)
      .then(response => response.json())
      .then(data => {
        if (data && data.length > 0) {
          const lat = parseFloat(data[0].lat);
          const lon = parseFloat(data[0].lon);
          map.setView([lat, lon], 15);
          marker.setLatLng([lat, lon]);
          document.getElementById('latitude').value = lat.toFixed(6);
          document.getElementById('longitude').value = lon.toFixed(6);
        } else {
          alert('Location not found.');
        }
      })
      .catch(err => {
        alert('Error searching location.');
        console.error(err);
      });
  }
</script>
</body>
</html>


<?php
session_start();
if ($_SESSION['user_type'] !== 'staff') {
    header("Location: login.php");
    exit();
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Create Attendance Slot</title>
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
  <button class="back-btn" onclick="window.location.href='attendaceSlot.php'">Back</button>
  <h1>Create Attendance Slot</h1>

  <form action="create_attendance_slot_process.php" method="POST" enctype="multipart/form-data">
    <label for="event_id">Event ID:</label>
    <input type="text" id="event_id" name="event_id" required>

    <label for="slot_date">Date:</label>
    <input type="date" id="slot_date" name="slot_date" required>

    <label for="slot_time">Time:</label>
    <input type="time" id="slot_time" name="slot_time" required>

    <label for="location_name">Search Location Name:</label>
    <input type="text" id="location_name" name="location_name" placeholder="e.g. Universiti Malaysia Pahang">
   <button type="button" id="searchBtn" onclick="searchLocation()">Search Location</button>


    <label>Pick Location on Map:</label>
    <div id="map"></div>

    <label for="latitude">Latitude:</label>
    <input type="text" id="latitude" name="latitude" readonly required>

    <label for="longitude">Longitude:</label>
    <input type="text" id="longitude" name="longitude" readonly required>

    <button type="submit" class="submit-btn">Submit</button>
  </form>
</div>

<!-- Footer -->
<div class="footer">@MyPetakom 2024/2025</div>

<!-- Scripts -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  function toggleMenu(id) {
    var element = document.getElementById(id);
    element.style.display = element.style.display === "block" ? "none" : "block";
  }

  const map = L.map('map').setView([3.546654, 103.427395], 15);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18,
  }).addTo(map);

  const marker = L.marker([3.546654, 103.427395], { draggable: true }).addTo(map);
  document.getElementById('latitude').value = marker.getLatLng().lat.toFixed(6);
  document.getElementById('longitude').value = marker.getLatLng().lng.toFixed(6);

  marker.on('dragend', function (e) {
    const lat = marker.getLatLng().lat.toFixed(6);
    const lng = marker.getLatLng().lng.toFixed(6);
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;
  });

function searchLocation() {
  const location = document.getElementById("location_name").value;
  console.log("Searching for:", location);
  
  fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(location)}`)
    .then(response => response.json())
    .then(data => {
      console.log("Search results:", data);
      if (data && data.length > 0) {
        const lat = parseFloat(data[0].lat);
        const lon = parseFloat(data[0].lon);
        map.setView([lat, lon], 16);
        marker.setLatLng([lat, lon]);
        document.getElementById('latitude').value = lat.toFixed(6);
        document.getElementById('longitude').value = lon.toFixed(6);
      } else {
        alert("Location not found!");
      }
    })
    .catch(error => {
      console.error("Error searching location:", error);
      alert("Failed to search location.");
    });
}

</script>
</body>
</html>



  



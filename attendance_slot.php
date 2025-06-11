<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Staff (Petakom Advisor)') {
    header("Location: login.php");
    exit();
}

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$database = "mypetakom";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch events that do NOT have attendance slots yet
$sql = "
    SELECT event_id, event_title, event_date 
    FROM event 
    WHERE event_id NOT IN (SELECT event_id FROM attendance_slot)
    ORDER BY event_title ASC
";
$result = $conn->query($sql);

$events = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Create Attendance Slot</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="STYLE3/attendance_slot.css" />
  <style>
    select#event_id,
    input[type="date"],
    input[type="time"],
    input[type="text"] {
      width: 100%;
      max-width: 600px;
      padding: 8px 12px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-sizing: border-box;
      margin-bottom: 15px;
    }

    label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }

    .content {
      max-width: 650px;
      margin: 20px auto;
      padding: 0 15px;
    }
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
  <h1>Create Attendance Slot</h1>

  <form id="attendanceForm" action="create_attendance_slot_process.php" method="POST" enctype="multipart/form-data">

    <label for="event_id">Select Event:</label>
    <select id="event_id" name="event_id" required>
      <option value="" disabled selected>-- Select Event --</option>
      <?php
      foreach ($events as $event) {
          echo '<option value="' . htmlspecialchars($event['event_id']) . '">' 
               . htmlspecialchars($event['event_title']) . ' (ID: ' . htmlspecialchars($event['event_id']) . ')</option>';
      }
      ?>
    </select>

    <label for="slot_date">Date:</label>
    <input type="date" id="slot_date" name="slot_date" required>

    <label for="slot_time">Time:</label>
    <input type="time" id="slot_time" name="slot_time" required>

    <label for="location_name">Search Location Name:</label>
    <input type="text" id="location_name" name="location_name" placeholder="e.g. Universiti Malaysia Pahang" />
    <button type="button" id="searchBtn" onclick="searchLocation()">Search Location</button>

    <label>Pick Location on Map:</label>
    <div id="map" style="height: 300px;"></div>

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

  // Build eventDates object without trailing comma
  const eventDates = {
    <?php
      $last_key = array_key_last($events);
      foreach ($events as $key => $event) {
          echo '"' . $event['event_id'] . '": "' . $event['event_date'] . '"';
          if ($key !== $last_key) echo ",";
      }
    ?>
  };

  // Update date input on event select change
  document.getElementById('event_id').addEventListener('change', function() {
    const selectedEventId = this.value;
    const dateInput = document.getElementById('slot_date');

    console.log("Selected Event ID:", selectedEventId); // Debug: see selected id

    if (selectedEventId in eventDates) {
      dateInput.value = eventDates[selectedEventId];
    } else {
      dateInput.value = '';
    }
  });

  // Leaflet map setup
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

  // Location search with Nominatim
  function searchLocation() {
    const location = document.getElementById("location_name").value.trim();
    if (!location) {
      alert("Please enter a location to search.");
      return;
    }

    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(location)}`)
      .then(response => response.json())
      .then(data => {
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

<?php
$conn->close();
?>




<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Staff (Petakom Advisor)') {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "mypetakom");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT 
            attendanceslot_id, 
            event_id, 
            slot_time, 
            ST_X(slot_geolocation) AS latitude, 
            ST_Y(slot_geolocation) AS longitude, 
            attendanceslot_qr AS qr_code_file
        FROM attendance_slot
        ORDER BY event_id, slot_time";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
  <title>View Attendance Slots</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="STYLE3/attendance.css" />
  <style>
    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #222;
      font-weight: 700;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    h3 {
      margin-top: 30px;
      color: #2b4d71;
      transition: background-color 0.3s ease;
    }
    .highlight {
      background-color: #ffff99;
      padding: 4px;
      border-radius: 4px;
    }
    .attendance-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    .attendance-table th, .attendance-table td {
      border: 1px solid #ccc;
      padding: 10px;
      text-align: center;
    }
    .attendance-table th {
      background-color: #f0f0f0;
      font-weight: bold;
    }
    .attendance-table img {
      width: 100px;
    }
    .back-btn {
      background-color: #2b4d71;
      color: white;
      padding: 8px 16px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    .top-controls {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    .top-controls input[type="text"] {
      padding: 8px;
      width: 200px;
      border-radius: 6px;
      border: 1px solid #ccc;
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
<div class="attendance-slot-container">
  <!-- Top controls: Back + Search -->
  <div class="top-controls">
    <button class="back-btn" onclick="window.location.href='attendaceSlot.php'">Back</button>
    <input type="text" id="searchInput" placeholder="🔍Search Event ID" onkeyup="highlightEvent()" />
  </div>
   <h2>Submitted Attendance Slots</h2>

  <?php
  if (mysqli_num_rows($result) > 0) {
      $event_slots = [];

      // Group slots by event_id
      while ($row = mysqli_fetch_assoc($result)) {
          $event_slots[$row['event_id']][] = $row;
      }

      foreach ($event_slots as $event_id => $slots) {
          echo "<h3 class='event-heading'>Event ID: " . htmlspecialchars($event_id) . "</h3>";
          echo '<table class="attendance-table">
                  <thead>
                    <tr>
                      <th>Slot ID</th>
                      <th>Time</th>
                      <th>Location (Lat, Long)</th>
                      <th>QR Code</th>
                    </tr>
                  </thead>
                  <tbody>';

          foreach ($slots as $slot) {
              echo "<tr>
                      <td>" . htmlspecialchars($slot['attendanceslot_id']) . "</td>
                      <td>" . htmlspecialchars(date("d M Y, H:i", strtotime($slot['slot_time']))) . "</td>
                      <td>" . number_format($slot['latitude'], 6) . ', ' . number_format($slot['longitude'], 6) . "</td>
                      <td>";
              if (!empty($slot['qr_code_file'])) {
                  echo '<img src="uploads/' . htmlspecialchars($slot['qr_code_file']) . '" alt="QR Code">';
              } else {
                  echo '<em>No QR</em>';
              }
              echo "</td></tr>";
          }

          echo '</tbody></table><br>';
      }
  } else {
      echo "<p>No attendance slots found.</p>";
  }
  ?>

</div>

<!-- Footer -->
<div class="footer">
  @MyPetakom 2024/2025
</div>

<script>
function toggleMenu(id) {
  const content = document.getElementById(id);
  content.style.display = content.style.display === 'block' ? 'none' : 'block';
}

function highlightEvent() {
  const input = document.getElementById("searchInput").value.toLowerCase();
  const headings = document.querySelectorAll(".event-heading");

  headings.forEach(h => {
    const eventText = h.textContent.toLowerCase();
    if (input && eventText.includes(input)) {
      h.classList.add("highlight");
    } else {
      h.classList.remove("highlight");
    }
  });
}
</script>

</body>
</html>

<?php mysqli_close($conn); ?>









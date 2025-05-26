<?php
session_start();
if ($_SESSION['user_type'] !== 'staff') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MyPetakom - Attendance Slot</title>
  <link rel="stylesheet" href="STYLE/staff_style.css" />
  <link rel="stylesheet" href="STYLE/attendance.css" />
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
<div class="attendance-slot-container">

  <!-- + Create Button -->
  <div class="top-bar">
    <a href="attendance_slot.php" class="create-btn">+ Create</a>
  </div>

  <!-- Existing Attendance Slot List -->
  <div class="slot-list">
    <h3>Existing Attendance Slots</h3>
    <ul>
      <?php
      $link = mysqli_connect("localhost", "root", "", "mypetakom");

      if (!$link) {
        echo "<div class='error-box'>Database connection failed: " . mysqli_connect_error() . "</div>";
      } else {
        $query = "SELECT attendanceslot_id, event_id, slot_time FROM attendance_slot";
        $result = mysqli_query($link, $query);
        $count = 1;

        if (mysqli_num_rows($result) > 0) {
          while ($row = mysqli_fetch_assoc($result)) {
            $id = $row['attendanceslot_id'];
            $eventId = $row['event_id'];
            $slotTime = date("d M Y, H:i", strtotime($row['slot_time']));

            echo "<li>
                    <span class='slot-number'>{$count}.</span>
                    <a href='viewSlot.php?id={$id}' class='slot-title'>Event ID: {$eventId} - {$slotTime}</a>
                    <a href='updateSlot.php?id={$id}' class='edit-btn'>Update</a>
                    <a href='deleteSlot.php?id={$id}' class='delete-btn'>Delete</a>
                  </li>";
            $count++;
          }
        } else {
          echo "<li><em>No attendance slots found.</em></li>";
        }
        mysqli_close($link);
      }
      ?>
    </ul>
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

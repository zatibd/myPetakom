<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Staff (Petakom Advisor)') {
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
  <link rel="stylesheet" href="STYLE3/attendance.css" />
  <style>
    .view-btn {
      padding: 4px 10px;
      background-color: #2ecc71;
      color: white;
      border-radius: 4px;
      text-decoration: none;
      margin: 0 5px;
      font-size: 14px;
      transition: background-color 0.3s ease;
    }
    .view-btn:hover {
      background-color: #27ae60;
    }

    .edit-btn {
      padding: 4px 10px;
      background-color: #3498db;
      color: white;
      border-radius: 4px;
      text-decoration: none;
      margin: 0 5px;
      font-size: 14px;
      transition: background-color 0.3s ease;
    }
    .edit-btn:hover {
      background-color: #2980b9;
    }

    .delete-btn {
      padding: 4px 10px;
      background-color: #e74c3c;
      color: white;
      border-radius: 4px;
      text-decoration: none;
      margin: 0 5px;
      font-size: 14px;
      transition: background-color 0.3s ease;
    }
    .delete-btn:hover {
      background-color: #c0392b;
    }

    .attendance-btn {
      padding: 4px 10px;
      background-color: orange;
      color: white;
      border-radius: 4px;
      text-decoration: none;
      margin: 0 5px;
      font-size: 14px;
      transition: background-color 0.3s ease;
    }
    .attendance-btn:hover {
      background-color: darkorange;
    }

    .slot-number {
      font-weight: bold;
      margin-right: 8px;
    }

    .slot-title {
      font-weight: normal;
      color: #333;
      text-decoration: none;
    }

    .slot-title:hover {
      text-decoration: underline;
    }

    .slot-list h4 {
      margin-top: 30px;
      color: #2c3e50;
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

  <!-- + Create Button -->
  <div class="top-bar">
    <a href="attendance_slot.php" class="create-btn">+ Create</a>
  </div>

  <!-- Existing Attendance Slot List -->
  <div class="slot-list">
    <h3>Existing Attendance Slots</h3>
    <?php
    $link = mysqli_connect("localhost", "root", "", "mypetakom");

    if (!$link) {
      echo "<div class='error-box'>Database connection failed: " . mysqli_connect_error() . "</div>";
    } else {
      $query = "
        SELECT 
          a.attendanceslot_id, 
          a.event_id, 
          e.event_title, 
          a.slot_time 
        FROM attendance_slot a
        LEFT JOIN event e ON a.event_id = e.event_id
        ORDER BY a.event_id ASC, a.slot_time ASC
      ";

      $result = mysqli_query($link, $query);
      $currentEventId = null;
      $count = 1;

      if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
          $id = $row['attendanceslot_id'];
          $eventId = $row['event_id'];
          $eventTitle = $row['event_title'] ?? "Unknown Event";
          $slotTime = date("d M Y, H:i", strtotime($row['slot_time']));

          if ($currentEventId !== $eventId) {
            if ($currentEventId !== null) {
              echo "</ul>";
            }
            echo "<h4>Event: {$eventTitle} (ID: {$eventId})</h4>";
            echo "<ul>";
            $currentEventId = $eventId;
            $count = 1;
          }

          echo "<li>
                  <span class='slot-number'>{$count}.</span>
                  <span class='slot-title'>{$slotTime}</span>
                  <a href='viewdetailSlot.php?id={$id}' class='view-btn'>View Details</a>
                  <a href='attendanceList.php?id={$id}' class='attendance-btn'>Attendance</a>
                  <a href='updateSlot.php?id={$id}' class='edit-btn'>Update</a>
                  <a href='deleteSlot.php?id={$id}' class='delete-btn' onclick=\"return confirm('Are you sure want to delete this slot?');\">Delete</a>
                </li>";
          $count++;
        }
        echo "</ul>";
      } else {
        echo "<p><em>No attendance slots found.</em></p>";
      }

      mysqli_close($link);
    }
    ?>
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




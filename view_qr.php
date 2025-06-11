<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Staff (Petakom Advisor)') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "No event ID given.";
    exit();
}

$event_id = $_GET['id'];
$result = mysqli_query($conn, "SELECT event_title, event_qr FROM event WHERE event_id = '$event_id'");
if (!$result || mysqli_num_rows($result) === 0) {
    echo "Event not found or QR not generated.";
    exit();
}

$data = mysqli_fetch_assoc($result);
?>

<head>
  <title>QR Code - <?= htmlspecialchars($data['event_title']) ?></title>
  <link rel="stylesheet" href="STYLE1/staff_style.css">
  <link rel="stylesheet" href="STYLE2/view_qr_style.css">

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
		<a href="apply_merit.php">Apply Merit</a>
		
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
	<div class="center">
		
        <h2>QR Code for <?= htmlspecialchars($data['event_title']) ?></h2>
        <div class="qr-box">
            <img src="data:image/png;base64,<?= $data['event_qr'] ?>" alt="QR Code">
        </div>
        <br><br>
        <a href="event_details.php?id=<?= $event_id ?>"><button>Back to Event</button></a>
    </div>
	<br>
	
	
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
   
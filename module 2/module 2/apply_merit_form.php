<?php
session_start();
$conn = new mysqli("localhost", "root", "", "mypetakom");

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Staff (Petakom Advisor)') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['event_id'])) {
    header("Location: apply_merit.php");
    exit();
}

$event_id = $_GET['event_id'];

// Get event details
$event = $conn->query("SELECT * FROM event WHERE event_id = '$event_id'")->fetch_assoc();

// Fetch geolocation (WKT format)
$geoRes = $conn->query("SELECT ST_AsText(event_geolocation) AS geo FROM event WHERE event_id = '$event_id'");
$geoRow = $geoRes->fetch_assoc();
$geolocation = $geoRow ? $geoRow['geo'] : "Not available";

$event_title = $event['event_title'];
$event_date = $event['event_date'];
$event_level = $event['event_level'];
$event_description = $event['event_description'];
$approval_letter = $event['event_approval']; 


// Get committee members
$committee = $conn->query("
    SELECT 
        user.user_name,
        user.user_id AS student_id,
        committee.committee_role,
        event.event_level,
        merit.merit_score
    FROM committee
    JOIN member ON committee.member_id = member.member_id
    JOIN user ON member.student_id = user.user_id
    JOIN event ON committee.event_id = event.event_id
    LEFT JOIN merit 
      ON merit.merit_description = CONCAT(committee.committee_role, ' in ', event.event_level, ' Level')
    WHERE committee.event_id = '$event_id'
");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Apply Merit - MyPetakom</title>
  <link rel="stylesheet" href="STYLE1/staff_style.css">
  <link rel="stylesheet" href="STYLE2/merit_form_style.css">

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
	<div class="content container">
	<a href="apply_merit.php">
			<button type="button">Back</button>
		</a><br>
	
		<h2 class="center">Apply Merit for <?= htmlspecialchars($event_title) ?></h2>
		
		<h3>Event Details</h3>
		<table>
			<tr><th>Event ID</th><td><?= htmlspecialchars($event_id) ?></td></tr>
			<tr><th>Title</th><td><?= htmlspecialchars($event_title) ?></td></tr>
			<tr><th>Date</th><td><?= htmlspecialchars($event_date) ?></td></tr>
			<tr><th>Geolocation</th><td><?= htmlspecialchars($geolocation) ?></td></tr>
			<tr><th>Level</th><td><?= htmlspecialchars($event_level) ?></td></tr>
			<tr><th>Description</th><td><?= htmlspecialchars($event_description) ?></td></tr>
			<tr>
				<th>Approval Letter</th>
				<td>
					<?php if (!empty($event['event_approval'])): ?>
                    <a href="download_approval.php?id=<?= urlencode($event_id) ?>" target="_blank">Download Approval Letter (PDF)</a>
                <?php else: ?>
                    Not uploaded
                <?php endif; ?>
				</td>
			</tr>
		</table>
		<br>

		<h3>Committee Members</h3>
		<table>
			<tr>
				<th>Name</th>
				<th>Student ID</th>
				<th>Role</th>
				<th>Event Level</th>
				<th>Merit Score</th>
			</tr>
			<?php while ($row = $committee->fetch_assoc()): ?>
				<tr>
					<td><?= htmlspecialchars($row['user_name']) ?></td>
					<td><?= htmlspecialchars($row['student_id']) ?></td>
					<td><?= htmlspecialchars($row['committee_role']) ?></td>
					<td><?= htmlspecialchars($row['event_level']) ?></td>
					<td><?= htmlspecialchars($row['merit_score'] ?? 'Not Matched') ?></td>
				</tr>
			<?php endwhile; ?>
		</table>

		<br class="center">
		<form method="post" action="event_merit_status.php" onsubmit="return confirm('Confirmed details?');">
			<input type="hidden" name="event_id" value="<?= $event_id ?>">
			<div class="center">
				<button type="submit" class="btn-apply">Apply</button>
			</div>
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

	</script>

</body>
</html>
   
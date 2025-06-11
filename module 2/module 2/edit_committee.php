<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Staff (Petakom Advisor)') {
    header("Location: login.php");
    exit();
}
include 'config.php';

if (!isset($_GET['event_id'])) {
    header("Location: assign_event.php");
    exit;
}

$event_id = $_GET['event_id'];
$event_result = $conn->query("SELECT event_title FROM event WHERE event_id = '$event_id'");
$event_title = ($event_result && $event_result->num_rows > 0) ? $event_result->fetch_assoc()['event_title'] : "Unknown Event";

// Fetch existing committee members
$query = "
SELECT member.member_id, member.student_id, user.user_name, committee.committee_role 
FROM committee 
JOIN member ON committee.member_id = member.member_id 
JOIN user ON member.student_id = user.user_id 
WHERE committee.event_id = '$event_id'
";
$result = $conn->query($query);
$committee_members = [];
while ($row = $result->fetch_assoc()) {
    $committee_members[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Committee - MyPetakom</title>
  <link rel="stylesheet" href="STYLE1/staff_style.css">
  <style>
    .committee-member { margin-bottom: 20px; }
    .committee-member input, .committee-member select { margin-right: 10px; }
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
        <a href="#">Profile</a>
        <a href="#">Calendar</a>
        <a href="#">Report</a>
        <a href="logout.php">Log Out</a>
      </div>
    </div>
  </div>
  
   <!-- Main Content -->
	<div class="content">
	  <h2>Edit Committee for <?= htmlspecialchars($event_title) ?></h2>

	  <form action="save_edited_committee.php" method="post">
		<input type="hidden" name="event_id" value="<?= htmlspecialchars($event_id) ?>">
		
		<div id="committee-container">
		  <?php foreach ($committee_members as $index => $member): ?>
			<div class="committee-member">
			  <label>Student ID:</label>
			  <input type="text" name="student_ids[]" value="<?= htmlspecialchars($member['student_id']) ?>" required>
			  <label>Position:</label>
			  <select name="positions[]" required>
				<option <?= $member['committee_role'] == 'Main Committee' ? 'selected' : '' ?>>Main Committee</option>
				<option <?= $member['committee_role'] == 'Committee' ? 'selected' : '' ?>>Committee</option>
				<option <?= $member['committee_role'] == 'Participant' ? 'selected' : '' ?>>Participant</option>
			  </select>
			</div>
		  <?php endforeach; ?>
		</div>
		<br>
		<button type="submit">Save Changes</button>
		<a href="assign_event.php"><button type="button">Cancel</button></a>
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

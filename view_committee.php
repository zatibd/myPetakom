<?php
include 'config.php';
$event_id = $_GET['event_id'];
$event_result = $conn->query("SELECT event_title FROM event WHERE event_id = '$event_id'");
$event_title = ($event_result && $event_result->num_rows > 0) ? $event_result->fetch_assoc()['event_title'] : "Unknown Event";

$result = $conn->query("
  SELECT user.user_name, user.user_id AS student_id, committee.committee_role 
  FROM committee 
  JOIN member ON committee.member_id = member.member_id 
  JOIN user ON member.student_id = user.user_id 
  WHERE committee.event_id = '$event_id'
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Assigned Committee - MyPetakom</title>
    <link rel="stylesheet" href="STYLE1/staff_style.css">
	<link rel="stylesheet" href="STYLE2/add_committee_style.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
		<a href="assign_event.php" class="btn btn-secondary mb-4">Back</a>
		
		<h2 class="mb-4"><b>Committee Members for <?= htmlspecialchars($event_title) ?></b></h2>
		  <div class="table-responsive">
			<table class="table table-bordered table-hover align-middle text-center">
			  <thead class="table-dark">
				<tr>
				  <th>Student Name</th>
				  <th>Student ID</th>
				  <th>Position</th>
				</tr>
			  </thead>
			  <tbody>
				<?php while ($row = $result->fetch_assoc()): ?>
				  <tr>
					<td><?= htmlspecialchars($row['user_name']) ?></td>
					<td><?= htmlspecialchars($row['student_id']) ?></td>
					<td><?= htmlspecialchars($row['committee_role']) ?></td>
				  </tr>
				<?php endwhile; ?>
			  </tbody>
			</table>
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
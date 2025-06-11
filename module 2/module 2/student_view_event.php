<?php
session_start();
$conn = new mysqli("localhost", "root", "", "mypetakom");

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

//Get member_id from student_id
$get_member = $conn->query("SELECT member_id FROM member WHERE student_id = '$student_id'");
if ($get_member->num_rows === 0) {
    die("You are not registered as a member.");
}
$member_id = $get_member->fetch_assoc()['member_id'];

//Get events and roles from committee table
$query = "
SELECT event.event_id, event.event_title, event.event_date, committee.committee_role 
FROM committee 
JOIN event ON committee.event_id = event.event_id 
WHERE committee.member_id = '$member_id'
ORDER BY event.event_date DESC
";
$result = $conn->query($query);
?>


<!DOCTYPE html>
<html>
<head>
  <title>View Events - MyPetakom</title>
  <link rel="stylesheet" href="STYLE1/student_style.css">
  
</head>
<body>
<!-- Sidebar -->
  <div class="sidebar">
    <a href="student_dashboard.php">
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
        <a href="student_view_event.php">View Event</a>
      </div>

      <div class="menu-title" onclick="toggleMenu('attendance')">ATTENDANCE</div>
      <div class="dropdown-content" id="attendance">
        <a href="#">Key In Attendance</a>
        <a href="#">View Attendance</a>
      </div>

      <div class="menu-title" onclick="toggleMenu('merit')">MERIT</div>
      <div class="dropdown-content" id="merit">
        <a href="meritApplication.php">Merit Application</a>
        <a href="#">Merit Summary</a>
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
        <a href="student_profile.php">Profile</a>
        <a href="#">Calendar</a>
        <a href="#">Report</a>
        <a href="logout.php">Log Out</a>
      </div>
    </div>
  </div>
  
   <!-- Main Content -->
	<div class="content">
		<h2>My Committee Events</h2>
		<?php if ($result->num_rows > 0): ?>
		<table border="1" cellpadding="10">
			<tr>
				<th>Event Title</th>
				<th>Role</th>
				<th>Action</th>
			</tr>
			<?php while ($row = $result->fetch_assoc()): ?>
			<tr>
				<td><?= htmlspecialchars($row['event_title']) ?></td>
				<td><?= htmlspecialchars($row['committee_role']) ?></td>
				<td>
					<a href="student_event_details.php?event_id=<?= $row['event_id'] ?>">
						<button>View Details</button>
					</a>
				</td>
			</tr>
			<?php endwhile; ?>
		</table>
		<?php else: ?>
		<p>You are not assigned to any events yet.</p>
		<?php endif; ?>
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

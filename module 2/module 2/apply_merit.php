<?php
session_start();

// Restrict access to only logged-in advisors
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Staff (Petakom Advisor)') {
    header("Location: login.php");
    exit();
}

// DB Connection
$conn = new mysqli("localhost", "root", "", "mypetakom");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$advisor_id = $_SESSION['user_id'];

// Fetch events created by this advisor
$query = "SELECT * FROM event WHERE staff_id = '$advisor_id' ORDER BY event_date DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Apply Merit - MyPetakom</title>
  <link rel="stylesheet" href="STYLE1/staff_style.css">
  <link rel="stylesheet" href="STYLE2/apply_merit_style.css">

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
   <div class="content">
		<h1 class="centered">Apply Merit for My Events</h1>
		<table>
			<tr>
				<th>Event Title</th>
				<th>Date</th>
				<th>Status</th>
				<th>Action</th>
			</tr>

			<?php if ($result->num_rows > 0): ?>
				<?php while ($row = $result->fetch_assoc()):
					$event_id = $row['event_id'];
					$event_title = htmlspecialchars($row['event_title']);
					$event_date = htmlspecialchars($row['event_date']);
					$status = $row['merit_status'] ?? 'Not Applied';
				?>
				<tr>
					<td><?= $event_title ?></td>
					<td><?= $event_date ?></td>
					<td><?= $status ?></td>
					<td>
						<?php if ($status === 'Not Applied'): ?>
							<a href="apply_merit_form.php?event_id=<?= $event_id ?>">
								<button>Apply</button>
							</a>
						<?php else: ?>
							<a href="view_merit_application.php?event_id=<?= $event_id ?>" target="_blank">
								<button>View Details</button>
							</a>
						<?php endif; ?>
					</td>
				</tr>
				<?php endwhile; ?>
			<?php else: ?>
				<tr><td colspan="4" class="centered">No events found.</td></tr>
			<?php endif; ?>
		</table>
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
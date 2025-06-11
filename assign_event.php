<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Staff (Petakom Advisor)') {
    header("Location: login.php");
    exit();
}
include 'config.php'; // Points to DB connection

// Handle fetching events
$events = mysqli_query($conn, "SELECT * FROM event ORDER BY event_date DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Committee - MyPetakom</title>
    <link rel="stylesheet" href="STYLE1/staff_style.css">
	<!-- Bootstrap 5 CDN -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
	  <h1 class="text-center mb-4"><b>Assign Committee</b></h1>
	  
	  <div class="table-responsive">
		<table class="table table-bordered table-hover align-middle">
		  <thead class="table-danger">
			<tr>
			  <th scope="col">Event</th>
			  <th scope="col">Date</th>
			  <th scope="col">Actions</th>
			</tr>
		  </thead>
		  <tbody>
			<?php
			mysqli_data_seek($events, 0); // Reset pointer
			while ($row = mysqli_fetch_assoc($events)) {
				$event_id = $row['event_id'];
				$committee_check = $conn->query("SELECT COUNT(*) as total FROM committee WHERE event_id = '$event_id'");
				$has_committee = $committee_check->fetch_assoc()['total'] > 0;
			?>
			<tr>
			  <td><?= htmlspecialchars($row['event_title']) ?></td>
			  <td><?= htmlspecialchars($row['event_date']) ?></td>
			  <td>
				<?php if (!$has_committee): ?>
				  <form action="add_committee.php" method="get" class="d-inline">
					<input type="hidden" name="event_id" value="<?= htmlspecialchars($event_id) ?>">
					<button type="submit" class="btn btn-sm btn-success">Assign</button>
				  </form>
				<?php else: ?>
				  <a href="view_committee.php?event_id=<?= $event_id ?>" class="btn btn-sm btn-primary">View</a>
				  <a href="edit_committee.php?event_id=<?= $event_id ?>" class="btn btn-sm btn-warning">Edit</a>
				  <a href="delete_committee.php?event_id=<?= $event_id ?>"
					 class="btn btn-sm btn-danger"
					 onclick="return confirm('Are you sure you want to delete the committee for this event?');">
					Delete
				  </a>
				<?php endif; ?>
			  </td>
			</tr>
			<?php } ?>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
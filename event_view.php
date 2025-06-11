<?php
session_start();
//Message shows only once after a redirect
if (isset($_SESSION['event_saved'])) {
    echo "<script>alert('Event Saved Successfully!');</script>";
    unset($_SESSION['event_saved']); // Only show once
}
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Staff (Petakom Advisor)') {
    header("Location: login.php");
    exit();
}
include 'config.php'; // Points to DB connection

// Handle fetching events
$search_title = isset($_GET['search_title']) ? mysqli_real_escape_string($conn, $_GET['search_title']) : '';
$search_status = isset($_GET['search_status']) ? mysqli_real_escape_string($conn, $_GET['search_status']) : '';

$sql = "SELECT * FROM event WHERE 1=1";

if (!empty($search_title)) {
    $sql .= " AND event_title LIKE '%$search_title%'";
}
if (!empty($search_status)) {
    $sql .= " AND event_status LIKE '%$search_status%'";
}

$sql .= " ORDER BY event_date DESC";
$events = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Events - MyPetakom</title>
    <link rel="stylesheet" href="STYLE1/staff_style.css">
	<link rel="stylesheet" href="STYLE2/event_view_style.css">
	<!-- ✅ Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
		<h1 class="text-center mb-4">My Events</h1>

		<form method="GET" class="row g-3 mb-4">
			<div class="col-md-6">
				<input type="text" name="search_title" class="form-control" placeholder="Search by Title" value="<?= htmlspecialchars($search_title) ?>">
			</div>
			<div class="col-md-4">
				<select name="search_status" class="form-select">
					<option value="">All</option>
					<option value="Active" <?= $search_status == 'Active' ? 'selected' : '' ?>>Active</option>
					<option value="Inactive" <?= $search_status == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
					<option value="Completed" <?= $search_status == 'Completed' ? 'selected' : '' ?>>Completed</option>
				</select>
			</div>
			<div class="col-md-2">
				<button type="submit" class="btn btn-primary w-100">Search</button>
			</div>
		</form>

		<div class="table-responsive">
			<table class="table table-bordered table-striped align-middle">
				<thead class="table-dark">
					<tr>
						<th>Title</th>
						<th>Status</th>
						<th class="text-center">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php while($row = mysqli_fetch_assoc($events)) { ?>
					<tr>
						<td><?= htmlspecialchars($row['event_title']) ?></td>
						<td><?= htmlspecialchars($row['event_status']) ?></td>
						<td class="text-center">
							<form action="event_details.php" method="GET" class="d-inline">
								<input type="hidden" name="id" value="<?= htmlspecialchars($row['event_id']) ?>">
								<button type="submit" class="btn btn-sm btn-info">View Details</button>
							</form>
							<form action="event_process.php" method="POST" class="d-inline" onsubmit="return confirm('Delete this event?')">
								<input type="hidden" name="delete_id" value="<?= htmlspecialchars($row['event_id']) ?>">
								<button type="submit" name="delete" class="btn btn-sm btn-danger">Delete</button>
							</form>
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
  
  <!-- Bootstrap JS  -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    function toggleMenu(id) {
      var content = document.getElementById(id);
      content.style.display = content.style.display === "block" ? "none" : "block";
    }
  </script>
	
    
</body>
</html>

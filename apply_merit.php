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

// Handle fetching events
$search_title = isset($_GET['search_title']) ? mysqli_real_escape_string($conn, $_GET['search_title']) : '';
$search_status = isset($_GET['search_status']) ? mysqli_real_escape_string($conn, $_GET['search_status']) : '';

$sql = "SELECT * FROM event WHERE staff_id = '$advisor_id'";

if (!empty($search_title)) {
    $sql .= " AND event_title LIKE '%$search_title%'";
}
if (!empty($search_status)) {
    $sql .= " AND merit_status LIKE '%$search_status%'"; // Adjusted to match your actual merit status
}

$sql .= " ORDER BY event_date DESC";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>
<head>
  <title>Apply Merit - MyPetakom</title>
  <link rel="stylesheet" href="STYLE1/staff_style.css">
  <link rel="stylesheet" href="STYLE2/apply_merit_style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
		<h2 class="mb-4"><b>Apply Merit for My Events</b></h2>

	  <form method="GET" class="row g-3 mb-4">
		<div class="col-md-5">
		  <input type="text" name="search_title" class="form-control" placeholder="Search by Title" value="<?= htmlspecialchars($search_title) ?>">
		</div>
		<div class="col-md-4">
		  <select name="search_status" class="form-select">
			<option value="">All</option>
			<option value="Not Applied" <?= $search_status == 'Not Applied' ? 'selected' : '' ?>>Not Applied</option>
			<option value="Pending" <?= $search_status == 'Pending' ? 'selected' : '' ?>>Pending</option>
			<option value="Approved" <?= $search_status == 'Approved' ? 'selected' : '' ?>>Approved</option>
			<option value="Rejected" <?= $search_status == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
		  </select>
		</div>
		<div class="col-md-3">
		  <button type="submit" class="btn btn-primary w-100">Search</button>
		</div>
	  </form>

	  <div class="table-responsive">
		<table class="table table-bordered table-hover align-middle text-center">
		  <thead class="table-dark">
			<tr>
			  <th>Event Title</th>
			  <th>Date</th>
			  <th>Status</th>
			  <th>Action</th>
			</tr>
		  </thead>
		  <tbody>
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
				<td>
				  <?php
					$badge_class = match($status) {
					  'Approved' => 'success',
					  'Pending' => 'warning',
					  'Rejected' => 'danger',
					  default => 'secondary'
					};
				  ?>
				  <span class="badge bg-<?= $badge_class ?>"><?= $status ?></span>
				</td>
				<td>
				  <?php if ($status === 'Not Applied'): ?>
					<a href="apply_merit_form.php?event_id=<?= $event_id ?>" class="btn btn-sm btn-outline-primary">Apply</a>
				  <?php else: ?>
					<a href="view_merit_application.php?event_id=<?= $event_id ?>" target="_blank" class="btn btn-sm btn-outline-info">View Details</a>
				  <?php endif; ?>
				</td>
			  </tr>
			  <?php endwhile; ?>
			<?php else: ?>
			  <tr>
				<td colspan="4" class="text-center">No events found.</td>
			  </tr>
			<?php endif; ?>
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
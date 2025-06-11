<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "mypetakom");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Restrict access to only logged-in students
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Staff (Administrator)') {
    header("Location: login.php");
    exit();
}

// Fetch all events with pending merit status
$events = $conn->query("SELECT * FROM event WHERE merit_status = 'Pending' ORDER BY event_date DESC");
?>

<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Merit Application - MyPetakom</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="STYLE1/administrator_style.css" />
</head>
<body>

  <!-- Sidebar -->
<div class="sidebar">
  <a href="administrator_dashboard.php">
    <img src="IMAGES/LogoPetakom.png" alt="PETAKOM Logo" />
	</a>

  <div class="search-box">
    <input type="text" placeholder="SEARCH" />
    <button>🔍</button>
  </div>

  <div class="menu">
    <div class="menu-title" onclick="toggleMenu('home')">HOME</div>

    <div class="menu-title" onclick="toggleMenu('membership')">MEMBERSHIP</div>
    <div class="dropdown-content" id="membership">
      <a href="#">Verification Status</a>
      <a href="member.php">View Member</a> <!-- Updated link -->
    </div>

    <div class="menu-title" onclick="toggleMenu('event')">EVENT</div>
    <div class="dropdown-content" id="event">
      <a href="#">Attendance Records</a>
    </div>

    <div class="menu-title" onclick="toggleMenu('merit')">MERIT</div>
    <div class="dropdown-content" id="merit">
      <a href="#">Merit Claim</a>
      <a href="merit_application_admin.php">Merit Application</a>
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
        <a href="administrator_profile.php">Profile</a>
        <a href="#">Calendar</a>
        <a href="#">Report</a>
        <a href="logout.php">Log Out</a>
      </div>
    </div>
  </div>

	<!-- Main Content -->
	<div class="content">
		<h2 class="text-center mb-4"><b>Merit Applications</b></h2>

		<div class="table-responsive">
		  <table class="table table-bordered table-hover align-middle">
			<thead class="table-light text-center">
			  <tr>
				<th>Event Title</th>
				<th>Event ID</th>
				<th>Event Date</th>
				<th>View Details</th>
				<th>Change Status</th>
			  </tr>
			</thead>
			<tbody>
			  <?php if ($events->num_rows > 0): ?>
				<?php while ($row = $events->fetch_assoc()): ?>
				  <tr class="text-center">
					<td><?= htmlspecialchars($row['event_title']) ?></td>
					<td><?= htmlspecialchars($row['event_id']) ?></td>
					<td><?= htmlspecialchars($row['event_date']) ?></td>
					<td>
					  <a href="view_merit_application.php?event_id=<?= $row['event_id'] ?>" class="btn btn-info btn-sm" target="_blank">View Details</a>
					</td>
					<td>
					  <form method="post" action="update_merit_status.php" onsubmit="return confirm('Confirm status update?');" class="d-flex flex-column gap-2">
						<input type="hidden" name="event_id" value="<?= $row['event_id'] ?>">
						<select name="status" class="form-select form-select-sm" required>
						  <option value="" disabled selected>-- Select --</option>
						  <option value="Approved">Approve</option>
						  <option value="Rejected">Reject</option>
						</select>
						<button type="submit" class="btn btn-success btn-sm">Update</button>
					  </form>
					</td>
				  </tr>
				<?php endwhile; ?>
			  <?php else: ?>
				<tr>
				  <td colspan="5" class="text-center text-muted">No pending merit applications.</td>
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
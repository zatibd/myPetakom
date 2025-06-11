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

// Handle fetching events
$search_title = isset($_GET['search_title']) ? mysqli_real_escape_string($conn, $_GET['search_title']) : '';
$search_status = isset($_GET['search_status']) ? mysqli_real_escape_string($conn, $_GET['search_status']) : '';

//Get events and roles from committee table
$query = "
SELECT event.event_id, event.event_title, event.event_date, committee.committee_role 
FROM committee 
JOIN event ON committee.event_id = event.event_id 
WHERE committee.member_id = '$member_id'
";

if (!empty($search_title)) {
    $query .= " AND event.event_title LIKE '%$search_title%'";
}
if (!empty($search_status)) {
    $query .= " AND committee.committee_role LIKE '%$search_status%'";
}

$query .= " ORDER BY event.event_date DESC";

$result = $conn->query($query);
?>


<!DOCTYPE html>
<html>
<head>
  <title>View Events - MyPetakom</title>
  <link rel="stylesheet" href="STYLE1/student_style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
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
		<div class="card shadow p-4">
		<h2 class="mb-4 text-center"><b>Joined Events</b></h2>
		
		<!-- Search Bar -->
	    <form method="GET" class="row g-3 mb-4">
			<div class="col-md-6">
				<input type="text" name="search_title" class="form-control" placeholder="Search by Title" value="<?= htmlspecialchars($search_title) ?>">
			</div>
			<div class="col-md-4">
				<select name="search_status" class="form-select">
					<option value="">All</option>
					<option value="Main" <?= $search_status == 'Main' ? 'selected' : '' ?>>Main Committee</option>
					<option value="Committee" <?= $search_status == 'Committee' ? 'selected' : '' ?>>Committee</option>
					<option value="Participant" <?= $search_status == 'Participant' ? 'selected' : '' ?>>Participant</option>
				</select>
			</div>
			<div class="col-md-2">
				<button type="submit" class="btn btn-primary w-100">Search</button>
			</div>
		</form>

		<?php if ($result->num_rows > 0): ?>
		  <div class="table-responsive">
			<table class="table table-bordered table-striped align-middle text-center">
			  <thead class="table-dark">
				<tr>
				  <th>Event Title</th>
				  <th>Role</th>
				  <th>Action</th>
				</tr>
			  </thead>
			  <tbody>
				<?php while ($row = $result->fetch_assoc()): ?>
				  <tr>
					<td><?= htmlspecialchars($row['event_title']) ?></td>
					<td><?= htmlspecialchars($row['committee_role']) ?></td>
					<td>
					  <a href="student_event_details.php?event_id=<?= $row['event_id'] ?>" class="btn btn-primary btn-sm">
						View Details
					  </a>
					</td>
				  </tr>
				<?php endwhile; ?>
			  </tbody>
			</table>
		  </div>
		<?php else: ?>
		  <div class="alert alert-warning text-center">You are not assigned to any events yet.</div>
		<?php endif; ?>
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

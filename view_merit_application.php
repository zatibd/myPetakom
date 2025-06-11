<?php
session_start();
$conn = new mysqli("localhost", "root", "", "mypetakom");

// Ensure logged in (either advisor or admin)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Staff (Petakom Advisor)', 'Staff (Administrator)'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['event_id'])) {
    echo "Invalid event.";
    exit();
}

$event_id = $conn->real_escape_string($_GET['event_id']);

// Fetch event details
$event_query = $conn->query("SELECT *, ST_AsText(event_geolocation) AS geo FROM event WHERE event_id = '$event_id'");
if (!$event_query || $event_query->num_rows === 0) {
    echo "Event not found.";
    exit();
}
$event = $event_query->fetch_assoc();

$event_title = $event['event_title'];
$event_date = $event['event_date'];
$event_level = $event['event_level'];
$event_description = $event['event_description'];
$approval_letter = $event['event_approval'];
$geolocation = $event['geo'] ?? 'Not available';
$merit_status = $event['merit_status'];

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
  <title>View Merit Application - MyPetakom</title>
  <link rel="stylesheet" href="STYLE2/view_merit_application.css">
  <link rel="stylesheet" href="STYLE2/merit_form_style.css">

</head>
<body>
  
	<!-- Main Content -->
	<div class="content">
		
		<h1 class="center">Merit Application Details for <?= htmlspecialchars($event_title) ?></h1>

		<h3>Event Details</h3>
		<table>
			<tr><th>Event ID</th><td><?= htmlspecialchars($event_id) ?></td></tr>
			<tr><th>Title</th><td><?= htmlspecialchars($event_title) ?></td></tr>
			<tr><th>Date</th><td><?= htmlspecialchars($event_date) ?></td></tr>
			<tr><th>Geolocation</th><td><?= htmlspecialchars($geolocation) ?></td></tr>
			<tr><th>Level</th><td><?= htmlspecialchars($event_level) ?></td></tr>
			<tr><th>Description</th><td><?= htmlspecialchars($event_description) ?></td></tr>
			<tr><th>Merit Status</th><td><?= htmlspecialchars($merit_status) ?></td></tr>
			<tr>
				<th>Approval Letter</th>
				<td>
					<?php if (!empty($approval_letter)): ?>
						<a href="view_letter.php?claim_id=<?= urlencode($row['claim_id']) ?>" target="_blank">View File</a>
					<?php else: ?>
						Not uploaded
					<?php endif; ?>
				</td>
			</tr>
		</table>

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
		

	</div>
	
	<!-- Footer -->
		<div class="footer">
		@MyPetakom 2024/2025
		</div>


</body>
</html>
<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Staff (Petakom Advisor)') {
    header("Location: login.php");
    exit();
}
include 'config.php'; // Points to DB connection

// Handle fetching events
$events = mysqli_query($conn, "SELECT * FROM event ORDER BY event_date DESC");

if (!isset($_GET['event_id'])) {
    header("Location: select_event.php");
    exit;
}

$event_id = $_GET['event_id'];
$event_result = $conn->query("SELECT event_title FROM event WHERE event_id = '$event_id'");
$event_title = ($event_result && $event_result->num_rows > 0) ? $event_result->fetch_assoc()['event_title'] : "Unknown Event";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Committee - MyPetakom</title>
    <link rel="stylesheet" href="STYLE1/staff_style.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<style>
	.name-display {
	  margin-left: 10px;
	  color: green;
	  font-weight: bold;
	}
	.error-display {
	  margin-left: 10px;
	  color: red;
	  font-weight: bold;
	}
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
  <a href="assign_event.php">
		<button type="button">Back</button>
	</a><br>
	<h2 class="mb-4 text-center">Assign Committee for <?= htmlspecialchars($event_title) ?></h2>

	  <form action="save_committee.php" method="post">
		<input type="hidden" name="event_id" value="<?= htmlspecialchars($event_id) ?>">

		<div id="committee-container" class="row gy-3">
		  <div class="committee-member col-md-12 border p-3 rounded shadow-sm">
			<div class="mb-3">
			  <label class="form-label">Student ID:</label>
			  <input type="text" name="student_ids[]" class="form-control student-id" required onblur="fetchName(this)">
			  <div class="name-display small mt-1"></div>
			</div>
			<div class="mb-3">
			  <label class="form-label">Position:</label>
			  <select name="positions[]" class="form-select" required>
				<option>Main Committee</option>
				<option>Committee</option>
				<option>Participant</option>
			  </select>
			</div>
			<button type="button" class="btn btn-danger btn-sm" onclick="removeMember(this)">Delete</button>
		  </div>
		</div>

		<div class="mt-4">
		  <button type="button" class="btn btn-primary" onclick="addMember()">Add Another Member</button>
		</div>

		<div class="mt-4">
		  <button type="submit" class="btn btn-success">Add Committee</button>
		</div>
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

function addMember() {
  const container = document.getElementById('committee-container');
  const member = document.createElement('div');
  member.className = "committee-member col-md-12 border p-3 rounded shadow-sm mt-3";
  member.innerHTML = `
    <div class="mb-3">
      <label class="form-label">Student ID:</label>
      <input type="text" name="student_ids[]" class="form-control student-id" required onblur="fetchName(this)">
      <div class="name-display small mt-1"></div>
    </div>
    <div class="mb-3">
      <label class="form-label">Position:</label>
      <select name="positions[]" class="form-select" required>
        <option>Main Committee</option>
        <option>Committee</option>
        <option>Participant</option>
      </select>
    </div>
    <button type="button" class="btn btn-danger btn-sm" onclick="removeMember(this)">Delete</button>
  `;
  container.appendChild(member);
}

function removeMember(button) {
  const memberDiv = button.closest('.committee-member');
  memberDiv.remove();
}

function fetchName(inputElement) {
  const studentId = inputElement.value.trim();
  const nameDisplay = inputElement.nextElementSibling;

  if (studentId === "") {
    nameDisplay.innerHTML = "";
    return;
  }

  $.ajax({
    url: "get_student_name.php",
    method: "POST",
    data: { student_id: studentId },
    success: function(response) {
      const result = JSON.parse(response);
      if (result.success) {
        nameDisplay.className = "name-display text-success small mt-1";
        nameDisplay.innerText = result.name;
      } else {
        nameDisplay.className = "name-display text-danger small mt-1";
        nameDisplay.innerText = "No student exists under this ID";
      }
    }
  });
}
</script>

</body>
</html>
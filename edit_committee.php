<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Staff (Petakom Advisor)') {
    header("Location: login.php");
    exit();
}
include 'config.php';

if (!isset($_GET['event_id'])) {
    header("Location: assign_event.php");
    exit;
}

$event_id = $_GET['event_id'];
$event_result = $conn->query("SELECT event_title FROM event WHERE event_id = '$event_id'");
$event_title = ($event_result && $event_result->num_rows > 0) ? $event_result->fetch_assoc()['event_title'] : "Unknown Event";

// Fetch existing committee members
$query = "
SELECT member.member_id, member.student_id, user.user_name, committee.committee_role 
FROM committee 
JOIN member ON committee.member_id = member.member_id 
JOIN user ON member.student_id = user.user_id 
WHERE committee.event_id = '$event_id'
";
$result = $conn->query($query);
$committee_members = [];
while ($row = $result->fetch_assoc()) {
    $committee_members[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Committee - MyPetakom</title>
  <link rel="stylesheet" href="STYLE1/staff_style.css">
  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    .committee-member { margin-bottom: 20px; }
    .name-display { font-size: 0.875rem; }
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
  <h1><b>Edit Committee for <?= htmlspecialchars($event_title) ?></b></h1>
  <br>

  <form action="save_committee.php" method="post">
    <input type="hidden" name="event_id" value="<?= htmlspecialchars($event_id) ?>">

    <div id="committee-container">
      <?php foreach ($committee_members as $index => $member): ?>
        <div class="committee-member border rounded p-4 shadow-sm">
          <div class="mb-3">
            <label class="form-label">Student ID:</label>
            <input type="text" name="student_ids[]" value="<?= htmlspecialchars($member['student_id']) ?>" class="form-control student-id" required onblur="fetchName(this)">
            <div class="name-display text-muted mt-1"><?= htmlspecialchars($member['user_name']) ?></div>
          </div>
          <div class="mb-3">
            <label class="form-label">Position:</label>
            <select name="positions[]" class="form-select" required>
              <option <?= $member['committee_role'] == 'Main Committee' ? 'selected' : '' ?>>Main Committee</option>
              <option <?= $member['committee_role'] == 'Committee' ? 'selected' : '' ?>>Committee</option>
              <option <?= $member['committee_role'] == 'Participant' ? 'selected' : '' ?>>Participant</option>
            </select>
          </div>
          <button type="button" class="btn btn-danger btn-sm" onclick="removeMember(this)">Delete</button>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="mt-4">
      <button type="button" class="btn btn-primary" onclick="addMember()">Add Another Member</button>
    </div>

    <div class="mt-4">
      <button type="submit" class="btn btn-success">Save Changes</button>
      <a href="assign_event.php" class="btn btn-secondary">Cancel</a>
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

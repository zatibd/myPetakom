<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Staff (Petakom Advisor)') {
    header("Location: login.php");
    exit();
}

include 'config.php'; // Ensure this sets $conn

// Check if event ID is provided
if (isset($_GET['id'])) {
    $event_id = $_GET['id'];

    // Fetch main event details
    $query = "SELECT * FROM event WHERE event_id = '$event_id'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $event = mysqli_fetch_assoc($result);

        // Fetch geolocation (WKT format)
        $geoRes = mysqli_query($conn, "SELECT ST_AsText(event_geolocation) AS geo FROM event WHERE event_id = '$event_id'");
        $geoRow = mysqli_fetch_assoc($geoRes);
        $geolocation = $geoRow ? $geoRow['geo'] : "Not available";

    } else {
        echo "Event not found.";
        exit();
    }
} else {
    echo "No event ID provided.";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Details - MyPetakom</title>
    <link rel="stylesheet" href="STYLE1/staff_style.css">
	<link rel="stylesheet" href="STYLE2/event_details_style.css">
	<!-- Bootstrap 5 -->
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
	<a href="event_view.php">
		<button type="button">Back</button>
	</a><br>
    
    <!-- Details -->
	<div class="card shadow">
        <div class="card-header text-white" style="background-color: #880808;">
            <h3 class="mb-0">Event Details</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th width="30%">Event ID</th>
                    <td><?= htmlspecialchars($event['event_id']) ?></td>
                </tr>
                <tr>
                    <th>Event Title</th>
                    <td><?= htmlspecialchars($event['event_title']) ?></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td><?= htmlspecialchars($event['event_status']) ?></td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td><?= htmlspecialchars($event['event_date']) ?></td>
                </tr>
                <tr>
                    <th>Geolocation</th>
                    <td><?= htmlspecialchars($geolocation) ?></td>
                </tr>
                <tr>
                    <th>Event Level</th>
                    <td><?= htmlspecialchars($event['event_level']) ?></td>
                </tr>
                <tr>
                    <th>Event Description</th>
                    <td><?= nl2br(htmlspecialchars($event['event_description'])) ?></td>
                </tr>
                <tr>
                    <th>Approval Letter</th>
                    <td>
                        <?php if (!empty($event['event_approval'])): ?>
                            <a href="view_letter.php?claim_id=<?= urlencode($row['claim_id']) ?>" target="_blank">View File</a>
                        <?php else: ?>
                            Not uploaded
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
	
	<br><br>
	<div class="button-group">
		<form action="event_update.php" method="GET">
			<input type="hidden" name="id" value="<?= $event['event_id'] ?>">
			<button type="submit">Edit Event</button>
		</form>    
		<form action="generate_qr.php" method="GET">
			<input type="hidden" name="id" value="<?= $event['event_id'] ?>">
			<button type="submit">Generate QR Code</button>
		</form>
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

<?php mysqli_close($conn); ?>

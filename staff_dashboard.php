<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "mypetakom");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Restrict access to only logged-in students
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Staff (Petakom Advisor)') {
    header("Location: login.php");
    exit();
}

// Get student data from database
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM user WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    // Student not found in database
    session_destroy();
    header("Location: login.php");
    exit();
}

$user = $result->fetch_assoc();

// Get total number of events organized by the staff
$total_events_query = "SELECT COUNT(*) AS total FROM event WHERE staff_id = ?";
$stmt_total = $conn->prepare($total_events_query);
$stmt_total->bind_param("s", $user_id);
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_events = $result_total->fetch_assoc()['total'];

// Get events grouped by month for bar chart
$chart_query = "
    SELECT MONTH(event_date) AS month, COUNT(*) AS count 
    FROM event 
    WHERE staff_id = ? 
    GROUP BY MONTH(event_date)
    ORDER BY MONTH(event_date)";
$stmt_chart = $conn->prepare($chart_query);
$stmt_chart->bind_param("s", $user_id);
$stmt_chart->execute();
$result_chart = $stmt_chart->get_result();

$month_counts = array_fill(1, 12, 0); // default 0 for each month

while ($row = $result_chart->fetch_assoc()) {
    $month_counts[(int)$row['month']] = (int)$row['count'];
}

$merit_query = "
    SELECT merit_status, COUNT(*) AS count 
    FROM event 
    WHERE staff_id = ?
    GROUP BY merit_status";
$stmt_merit = $conn->prepare($merit_query);
$stmt_merit->bind_param("s", $user_id);
$stmt_merit->execute();
$result_merit = $stmt_merit->get_result();

$merit_counts = [
    'Approved' => 0,
    'Pending' => 0,
    'Rejected' => 0,
    'Not Applied' => 0
];

while ($row = $result_merit->fetch_assoc()) {
    $status = $row['merit_status'];
    if (array_key_exists($status, $merit_counts)) {
        $merit_counts[$status] = (int)$row['count'];
    }
}

$level_query = "SELECT event_level, COUNT(*) AS total FROM event WHERE staff_id = ? GROUP BY event_level";
$stmt_level = $conn->prepare($level_query);
$stmt_level->bind_param("s", $user_id);
$stmt_level->execute();
$result_level = $stmt_level->get_result();

$event_levels = [];
while ($row = $result_level->fetch_assoc()) {
    $event_levels[$row['event_level']] = $row['total'];
}

$upcoming_query = "
SELECT 
    e.event_id,
    e.event_title,
    e.event_date,
    u.user_name AS committee_member_name
FROM event e
JOIN committee c ON e.event_id = c.event_id
JOIN member m ON c.member_id = m.member_id
JOIN user u ON m.student_id = u.user_id
WHERE e.staff_id = ?
  AND e.event_date >= CURDATE()
ORDER BY e.event_date ASC
LIMIT 3";

$stmt_upcoming = $conn->prepare($upcoming_query);
$stmt_upcoming->bind_param("s", $user_id);
$stmt_upcoming->execute();
$result_upcoming = $stmt_upcoming->get_result();

$upcoming_events = [];
while ($row = $result_upcoming->fetch_assoc()) {
    $event_id = $row['event_id'];
    if (!isset($upcoming_events[$event_id])) {
        $upcoming_events[$event_id] = [
            'title' => $row['event_title'],
            'date' => $row['event_date'],
            'members' => []
        ];
    }
    $upcoming_events[$event_id]['members'][] = $row['committee_member_name'];
}



?>

<!DOCTYPE html>
<html>
<head>

  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MyPetakom Dashboard</title>
  <link rel="stylesheet" href="STYLE1/staff_style.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .dashboard-card {
      border-radius: 1rem;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      padding: 2rem;
    }
    .hover-img {
      transition: all 0.3s ease-in-out;
    }
    .hover-img:hover {
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
      transform: scale(1.03);
    }
    .profile-circle {
      background-color: #007bff;
      color: white;
      border-radius: 50%;
      padding: 10px 15px;
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
        <a href="staff_profile.php">Profile</a>
        <a href="#">Calendar</a>
        <a href="#">Report</a>
        <a href="logout.php">Log Out</a>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
		<h1><b>Welcome, <?= htmlspecialchars($user['user_name']) ?></b></h1>
	</div>

	<div class="row g-4 mb-4">
		<div class="col-md-4">
		  <div class="dashboard-card bg-light text-center">
			<h5>Total Events Handled</h5>
			<p class="display-4 fw-bold text-primary"><?= $total_events ?></p>
		  </div>
		  
		  <br>
			<div class="card mt-4 p-3" style="max-width: 600px; margin: auto;">
			  <h5 class="text-center">Merit Application Status</h5>
			  <canvas id="meritPieChart"></canvas>
			</div>
		</div>
		<div class="col-md-8">
			<!-- Events by Level -->
		<div class="dashboard-card bg-light mb-4">
			<h5 class="text-center">Total Events by Level</h5>
			<ul class="list-group">
				<?php foreach ($event_levels as $level => $count): ?>
					<li class="list-group-item d-flex justify-content-between">
						<?= htmlspecialchars($level) ?>
						<span class="badge bg-primary"><?= $count ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		  <div class="dashboard-card bg-light">
			<h5 class="text-center mb-3">Events by Month</h5>
			<canvas id="eventBarChart" height="100"></canvas>
		  </div>
		</div>
		<!-- Upcoming Events with Committee Members -->
		<div class="dashboard-card bg-light">
			<h5 class="text-center"><b>Upcoming Events & Committee Members</b></h5>
			<hr>
			<?php foreach ($upcoming_events as $event): ?>
				<div class="mb-3">
					<strong><?= htmlspecialchars($event['title']) ?> (<?= $event['date'] ?>)</strong>
					<ul>
						<?php foreach ($event['members'] as $member): ?>
							<li><?= htmlspecialchars($member) ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endforeach; ?>
		</div>
	</div>


	<div class="row g-4 mb-4">
		<div class="col-md-12">
		  <div class="dashboard-card bg-light">
			<h3 class="mb-3">What's New</h3>
			<div class="row">
			  <div class="col-md-6 text-center">
				<h6>Larian Amal: Zombie Run</h6>
				<img src="IMAGES/program1.jpeg" class="img-fluid hover-img rounded" alt="Zombie Run">
			  </div>
			  <div class="col-md-6 text-center">
				<h6>Pendidikan Kesihatan Sosial</h6>
				<img src="IMAGES/program2.jpeg" class="img-fluid hover-img rounded" alt="Kesihatan Sosial">
			  </div>
			</div>
		  </div>
		</div>
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
	const ctx = document.getElementById('eventBarChart').getContext('2d');
    const eventBarChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                     'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Events',
                data: <?= json_encode(array_values($month_counts)) ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    precision: 0
                }
            }
        }
    });
	
	const meritCtx = document.getElementById('meritPieChart').getContext('2d');
	const meritPieChart = new Chart(meritCtx, {
		type: 'pie',
		data: {
			labels: ['Approved', 'Pending', 'Rejected', 'Not Applied'],
			datasets: [{
				label: 'Merit Applications',
				data: <?= json_encode(array_values($merit_counts)) ?>,
				backgroundColor: [
					'rgba(40, 167, 69, 0.6)',    // Green - Approved
					'rgba(255, 193, 7, 0.6)',    // Yellow - Pending
					'rgba(220, 53, 69, 0.6)',    // Red - Rejected
					'rgba(108, 117, 125, 0.6)'   // Grey - Not Applied
				],
				borderColor: [
					'rgba(40, 167, 69, 1)',
					'rgba(255, 193, 7, 1)',
					'rgba(220, 53, 69, 1)',
					'rgba(108, 117, 125, 1)'
				],
				borderWidth: 1
			}]
		},
		options: {
			responsive: true
		}
	});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "mypetakom");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Restrict access to only logged-in students
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get student profile info
$query = "
    SELECT u.user_name
    FROM user u
    JOIN student s ON u.user_id = s.student_id
    WHERE u.user_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$user = $result->fetch_assoc();

// Get merit by event level
$meritQuery = "
    SELECT e.event_level, SUM(m.merit_score) AS total_merit
    FROM committee c
    JOIN member mb ON c.member_id = mb.member_id
    JOIN event e ON c.event_id = e.event_id
    JOIN merit m ON m.merit_description = CONCAT(c.committee_role, ' in ', e.event_level, ' Level')
    WHERE e.merit_status = 'Approved' AND mb.student_id = ?
    GROUP BY e.event_level
";
$meritStmt = $conn->prepare($meritQuery);
$meritStmt->bind_param("s", $user_id);
$meritStmt->execute();
$meritResult = $meritStmt->get_result();

$levelMerit = [];
while ($row = $meritResult->fetch_assoc()) {
    $levelMerit[$row['event_level']] = $row['total_merit'];
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MyPetakom Dashboard</title>
  <link rel="stylesheet" href="STYLE4/student_style.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <img src="IMAGES/LogoPetakom.png" alt="PETAKOM Logo" />

    <div class="search-box">
      <input type="text" placeholder="SEARCH" />
      <button>🔍</button>
    </div>

    <div class="menu">
      <div class="menu-title" onclick="toggleMenu('home')">HOME</div>
      <div class="menu-title" onclick="toggleMenu('event')">EVENT</div>
      <div class="dropdown-content" id="event">
        <a href="#">View Event</a>
      </div>

      <div class="menu-title" onclick="toggleMenu('attendance')">ATTENDANCE</div>
      <div class="dropdown-content" id="attendance">
        <a href="#">Key In Attendance</a>
        <a href="#">View Attendance</a>
      </div>

      <div class="menu-title" onclick="toggleMenu('merit')">MERIT</div>
      <div class="dropdown-content" id="merit">
        <a href="meritApplication.php">Merit Application</a>
        <a href="meritAwarded.php">Merit Summary</a>
      </div>
    </div>
  </div>

  <!-- Topbar -->
  <div class="topbar">
    <div class="dropdown">
      <div class="profile-wrapper">
        <div class="profile-circle">
          <?= strtoupper(substr($user['user_name'], 0, 1)) ?>.
        </div>
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
  <div class="main-content">
    <h2>WELCOME, <?= htmlspecialchars($user['user_name']) ?>!</h2>

    <div class="dashboard-flex">
		<div class="poster-row">
			<div class="poster-box">
				<img src="IMAGES/poster1.jpg" alt="Poster 1" />
			<div class="poster-caption">HACKATHON</div>
			</div>
				<div class="poster-box">
					<img src="IMAGES/poster2.jpg" alt="Poster 2" />
				<div class="poster-caption">LARIAN AMAL 2025</div>
			</div>
		<div class="poster-box">
			<img src="IMAGES/poster3.jpg" alt="Poster 3" />
		<div class="poster-caption">TEMASYA OLAHRAGA UMPSA 2025</div>
		</div>
	</div>

      <div class="dashboard-chart small-chart">
        <canvas id="meritChart"></canvas>
      </div>

      <div class="table-container small-table">
        <table>
          <thead>
            <tr>
              <th>Event Level</th>
              <th>Total Merit Score</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($levelMerit as $level => $total): ?>
              <tr>
                <td><?= htmlspecialchars($level) ?></td>
                <td><?= htmlspecialchars($total) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <div class="footer">
    @MyPetakom 2024/2025
  </div>

  <script>
    const ctx = document.getElementById('meritChart').getContext('2d');
    const meritChart = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: <?= json_encode(array_keys($levelMerit)) ?>,
        datasets: [{
          label: 'Merit Score',
          data: <?= json_encode(array_values($levelMerit)) ?>,
          backgroundColor: [
            'rgba(255, 99, 132, 0.6)',
            'rgba(54, 162, 235, 0.6)',
            'rgba(255, 206, 86, 0.6)',
            'rgba(75, 192, 192, 0.6)',
            'rgba(153, 102, 255, 0.6)',
            'rgba(255, 159, 64, 0.6)'
          ],
          borderColor: '#fff',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'bottom'
          },
          title: {
            display: true,
            text: 'Cumulative Merit Score by Event Level'
          }
        }
      }
    });
	
	function toggleMenu(id) {
    var content = document.getElementById(id);
    content.style.display = content.style.display === "block" ? "none" : "block";
  }
  </script>
</body>
</html>

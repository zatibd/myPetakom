<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mypetakom";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Total membership applications (students who signed up)
$total_sql = "SELECT COUNT(*) AS total_applications FROM user WHERE LOWER(user_role) = 'student'";
$total_result = $conn->query($total_sql);
$total_applications = $total_result->fetch_assoc()['total_applications'];

// Membership status counts
$approved_sql = "SELECT COUNT(*) AS total_approved FROM member WHERE member_status = 'approved'";
$approved_result = $conn->query($approved_sql);
$approved_count = $approved_result->fetch_assoc()['total_approved'];

$rejected_sql = "SELECT COUNT(*) AS total_rejected FROM member WHERE member_status = 'rejected'";
$rejected_result = $conn->query($rejected_sql);
$rejected_count = $rejected_result->fetch_assoc()['total_rejected'];

$pending_sql = "SELECT COUNT(*) AS total_pending FROM member WHERE member_status IS NULL OR member_status = ''";
$pending_result = $conn->query($pending_sql);
$pending_count = $pending_result->fetch_assoc()['total_pending'];

// Student program distribution
$program_sql = "SELECT student_program, COUNT(*) AS total FROM student GROUP BY student_program";
$program_result = $conn->query($program_sql);

$program_labels = [];
$program_data = [];
while ($row = $program_result->fetch_assoc()) {
    $program_labels[] = $row['student_program'];
    $program_data[] = $row['total'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>MyPetakom Dashboard - Admin Graph</title>
<link rel="stylesheet" href="STYLE1/admin_dashboard.css" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
  body {
    font-family: 'Segoe UI', sans-serif;
    background: #f4f6f8;
  }
  .content {
    margin-left: 220px;
    padding: 30px;
    background: #fff;
    min-height: 100vh;
  }
  h2 {
    color: #333;
    margin-bottom: 20px;
  }
  .stats-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 30px;
  }
  .card {
    flex: 1 1 200px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    padding: 20px;
    text-align: center;
  }
  .card strong {
    color: #555;
    font-size: 14px;
    margin-bottom: 8px;
    display: block;
  }
  .count {
    font-size: 24px;
    color: #222;
    font-weight: 600;
  }
  .charts-container {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
  }
  .chart-card {
    flex: 1 1 45%;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    padding: 20px;
  }
  .chart-card h3 {
    font-size: 16px;
    margin-bottom: 10px;
    color: #444;
    text-align: center;
  }
  .chart-container {
    width: 100%;
    height: 300px; /* Set uniform height for charts */
  }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <img src="IMAGES/LogoPetakom.png" alt="PETAKOM Logo" />
  <div class="menu">
    <a class="menu-title" href="admin_graph.php">HOME</a>
    <div class="menu-title" onclick="toggleMenu('membership')">MEMBERSHIP</div>
    <div class="dropdown-content" id="membership">
      <a href="member_verification.php">Verification Status</a>
      <a href="view_member.php">View Member</a>
    </div>
    <div class="menu-title" onclick="toggleMenu('event')">EVENT</div>
    <div class="dropdown-content" id="event">
      <a href="#">Attendance Records</a>
    </div>
    <div class="menu-title" onclick="toggleMenu('merit')">MERIT</div>
    <div class="dropdown-content" id="merit">
      <a href="#">Merit Claim</a>
      <a href="#">Merit Application</a>
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
      <a href="calendar.php">Calendar</a>
      <a href="#">Report</a>
      <a href="logout.php">Log Out</a>
    </div>
  </div>
</div>

<!-- Main Content -->
<div class="content">
  <h2>Membership Status Overview</h2>

  <div class="stats-container">
    <div class="card"><strong>Total Applications</strong><p class="count"><?php echo $total_applications; ?></p></div>
    <div class="card"><strong>Approved Members</strong><p class="count"><?php echo $approved_count; ?></p></div>
    <div class="card"><strong>Rejected Members</strong><p class="count"><?php echo $rejected_count; ?></p></div>
    <div class="card"><strong>Pending Members</strong><p class="count"><?php echo $pending_count; ?></p></div>
  </div>

  <div class="charts-container">
    <div class="chart-card">
      <h3>Membership Status Distribution</h3>
      <div class="chart-container">
        <canvas id="pieChart"></canvas>
      </div>
    </div>
    <div class="chart-card">
      <h3>Student Program Distribution</h3>
      <div class="chart-container">
        <canvas id="barChart"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Footer -->
<div class="footer">@MyPetakom 2024/2025</div>

<script>
  function toggleMenu(id) {
    const content = document.getElementById(id);
    content.style.display = content.style.display === "block" ? "none" : "block";
  }

  // Pie Chart
  const pieChart = new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: {
      labels: ['Approved', 'Rejected', 'Pending'],
      datasets: [{
        data: [<?php echo $approved_count; ?>, <?php echo $rejected_count; ?>, <?php echo $pending_count; ?>],
        backgroundColor: ['#4CAF50', '#DC3545', '#FFC107'],
        borderWidth: 1
      }]
    },
    options: { responsive: true }
  });

  // Bar Chart
  const barChart = new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($program_labels); ?>,
      datasets: [{
        label: 'Number of Students',
        data: <?php echo json_encode($program_data); ?>,
        backgroundColor: '#3498db'
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true }
      }
    }
  });
</script>

</body>
</html>

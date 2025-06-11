<?php
session_start();
$conn = new mysqli("localhost", "root", "", "mypetakom");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Staff (Administrator)') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM user WHERE user_id = ?";
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

$eventTitles = [];
$eventQuery = "SELECT DISTINCT event_title FROM event ORDER BY event_title";
$eventResult = $conn->query($eventQuery);
if ($eventResult) {
    while ($row = $eventResult->fetch_assoc()) {
        $eventTitles[] = $row['event_title'];
    }
}

$selectedEvent = $_GET['event_title'] ?? '';

if ($selectedEvent && in_array($selectedEvent, $eventTitles)) {
    $summaryQuery = "
        SELECT e.event_title,
               SUM(CASE WHEN a.location_verification = 'Verified' THEN 1 ELSE 0 END) AS verified_count,
               SUM(CASE WHEN a.location_verification = 'Not Verified' THEN 1 ELSE 0 END) AS unverified_count
        FROM attendance a
        JOIN attendance_slot slot ON a.attendanceslot_id = slot.attendanceslot_id
        JOIN event e ON slot.event_id = e.event_id
        WHERE e.event_title = ?
        GROUP BY e.event_title
        ORDER BY e.event_title
    ";
    $stmt2 = $conn->prepare($summaryQuery);
    $stmt2->bind_param("s", $selectedEvent);
    $stmt2->execute();
    $summaryResult = $stmt2->get_result();
} else {
    $summaryQuery = "
        SELECT e.event_title,
               SUM(CASE WHEN a.location_verification = 'Verified' THEN 1 ELSE 0 END) AS verified_count,
               SUM(CASE WHEN a.location_verification = 'Not Verified' THEN 1 ELSE 0 END) AS unverified_count
        FROM attendance a
        JOIN attendance_slot slot ON a.attendanceslot_id = slot.attendanceslot_id
        JOIN event e ON slot.event_id = e.event_id
        GROUP BY e.event_title
        ORDER BY e.event_title
    ";
    $summaryResult = $conn->query($summaryQuery);
}

$eventSummary = [];
$totalVerified = 0;
$totalUnverified = 0;

if ($summaryResult) {
    while ($row = $summaryResult->fetch_assoc()) {
        $eventSummary[] = $row;
        $totalVerified += (int)$row['verified_count'];
        $totalUnverified += (int)$row['unverified_count'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MyPetakom Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .sidebar {
      height: 100vh;
      background-color: #F2EB95;
      padding: 20px;
      position: fixed;
      width: 220px;
    }
    .topbar {
      margin-left: 220px;
      height: 80px;
      background-color: #F2EB95;
      display: flex;
      justify-content: flex-end;
      align-items: center;
      padding: 0 30px;
    }
    .content {
      margin-left: 220px;
      padding: 30px;
      background-color: #ffffff;
      min-height: 90vh;
    }
    .profile-circle {
      width: 40px;
      height: 40px;
      background-color: #e7eaee;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      font-weight: bold;
      color: #333;
    }
    .dropdown-menu a {
      font-size: 14px;
    }
    .footer {
      text-align: center;
      padding: 10px;
      background-color: #f0f0f0;
      font-weight: bold;
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar d-flex flex-column align-items-center">
  <img src="IMAGES/LogoPetakom.png" alt="PETAKOM Logo" class="mb-4" width="120">
  <input type="text" class="form-control mb-3" placeholder="Search...">
  
<div class="w-100">
    <div class="fw-bold mb-2">HOME</div>

    <div class="fw-bold" data-bs-toggle="collapse" data-bs-target="#membershipMenu" style="cursor:pointer;">MEMBERSHIP</div>
    <div class="collapse ms-3" id="membershipMenu">
      <a href="#" class="d-block text-decoration-none text-dark">Verification Status</a>
      <a href="member.php" class="d-block text-decoration-none text-dark">View Member</a>
    </div>

    <div class="fw-bold mt-3" data-bs-toggle="collapse" data-bs-target="#eventMenu" style="cursor:pointer;">EVENT</div>
    <div class="collapse ms-3" id="eventMenu">
      <a href="#" class="d-block text-decoration-none text-dark">Attendance Records</a>
    </div>

    <div class="fw-bold mt-3" data-bs-toggle="collapse" data-bs-target="#meritMenu" style="cursor:pointer;">MERIT</div>
    <div class="collapse ms-3" id="meritMenu">
      <a href="#" class="d-block text-decoration-none text-dark">Merit Claim</a>
      <a href="#" class="d-block text-decoration-none text-dark">Merit Application</a>
    </div>
  </div>
</div>

<!-- Topbar -->
<div class="topbar">
  <div class="dropdown">
    <button class="btn btn-light d-flex align-items-center" data-bs-toggle="dropdown">
      <div class="profile-circle me-2">N</div>
      ▼
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
      <li><a class="dropdown-item" href="administrator_profile.php">Profile</a></li>
      <li><a class="dropdown-item" href="#">Calendar</a></li>
      <li><a class="dropdown-item" href="#">Report</a></li>
      <li><a class="dropdown-item" href="logout.php">Log Out</a></li>
    </ul>
  </div>
</div>

<!-- Main Content -->
<div class="content">
  <form method="get" class="mb-4">
    <label for="event_title" class="form-label">Select Event:</label>
    <select name="event_title" id="event_title" class="form-select" onchange="this.form.submit()">
      <option value="">-- All Events --</option>
      <?php foreach ($eventTitles as $title): ?>
        <option value="<?= htmlspecialchars($title); ?>" <?= ($selectedEvent === $title) ? 'selected' : ''; ?>>
          <?= htmlspecialchars($title); ?>
        </option>
      <?php endforeach; ?>
    </select>
  </form>

  <h2 class="text-center mb-4">Event Attendance Verification Summary</h2>
  <table class="table table-bordered text-center">
    <thead class="table-light">
      <tr>
        <th>Event Title</th>
        <th>Verified Students</th>
        <th>Unverified Students</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($eventSummary)): ?>
        <?php foreach ($eventSummary as $event): ?>
          <tr>
            <td><?= htmlspecialchars($event['event_title']); ?></td>
            <td><?= (int)$event['verified_count']; ?></td>
            <td><?= (int)$event['unverified_count']; ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="3">No event attendance data found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="row mt-5">
    <div class="col-md-8">
      <h5 class="text-center">Verified vs Unverified Students Per Event</h5>
      <canvas id="barChart"></canvas>
    </div>
    <div class="col-md-4">
      <h5 class="text-center">Total Verified vs Unverified</h5>
      <canvas id="pieChart"></canvas>
    </div>
  </div>
</div>

<!-- Footer -->
<div class="footer mt-4">
  @MyPetakom 2024/2025
</div>

<!-- Scripts -->
<script>
  const eventLabels = <?= json_encode(array_column($eventSummary, 'event_title')); ?>;
  const verifiedCounts = <?= json_encode(array_map('intval', array_column($eventSummary, 'verified_count'))); ?>;
  const unverifiedCounts = <?= json_encode(array_map('intval', array_column($eventSummary, 'unverified_count'))); ?>;
  const totalVerified = <?= $totalVerified; ?>;
  const totalUnverified = <?= $totalUnverified; ?>;
 new Chart(document.getElementById('barChart').getContext('2d'), {
    type: 'bar',
    data: {
      labels: eventLabels,
      datasets: [
        {
          label: 'Verified',
          data: verifiedCounts,
          backgroundColor: 'rgba(54, 162, 235, 0.7)'
        },
        {
          label: 'Unverified',
          data: unverifiedCounts,
          backgroundColor: 'rgba(255, 99, 132, 0.7)'
        }
      ]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true }
      }
    }
  });

  new Chart(document.getElementById('pieChart').getContext('2d'), {
    type: 'pie',
    data: {
      labels: ['Verified', 'Unverified'],
      datasets: [{
        data: [totalVerified, totalUnverified],
        backgroundColor: [
          'rgba(54, 162, 235, 0.7)',
          'rgba(255, 99, 132, 0.7)'
        ]
      }]
    },
    options: {
      responsive: true
    }
  });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
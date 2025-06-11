<?php
session_start();

// Check if logged in as Student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

// Get student ID from session
$student_id = $_SESSION['user_id'] ?? '';
if (empty($student_id)) {
    die("❌ Student ID missing from session.");
}

// Database connection
$conn = new mysqli("localhost", "root", "", "mypetakom");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Filtering options
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? 'All';

// Build SQL
$sql = "SELECT 
            meritclaim.claim_id, 
            meritclaim.claimStatus, 
            meritclaim.student_id, 
            meritclaim.event_id, 
            event.event_title, 
            event.event_level, 
            merit.merit_description,
            merit.merit_score
        FROM meritclaim
        JOIN event ON meritclaim.event_id = event.event_id
        JOIN merit ON meritclaim.merit_id = merit.merit_id
        WHERE meritclaim.student_id = ?";

$params = [$student_id];
$types = "s";

if (!empty($search)) {
    $sql .= " AND event.event_title LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}

if ($status !== 'All') {
    $sql .= " AND meritclaim.claimStatus = ?";
    $params[] = $status;
    $types .= "s";
}

$sql .= " ORDER BY meritclaim.claim_id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Display Missing Application Merit</title>
    <link rel="stylesheet" href="STYLE4/displayMerit.css">
</head>
<body>

<div class="sidebar">
  <img src="IMAGES/LogoPetakom.png" alt="PETAKOM Logo" />
  <div class="search-box">
    <input type="text" placeholder="SEARCH" />
    <button>🔍</button>
  </div>

  <div class="menu-title" onclick="toggleMenu('home')">HOME</div>
  <div class="dropdown-content" id="home">
    <a href="student_dashboard.php">Dashboard</a>
  </div>

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

<main class="main-content">
    <div class="top-controls">
        <a href="student_dashboard.php" class="back-btn">BACK</a>
        <form method="get">
            <input type="text" name="search" placeholder="Search For Event Title" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit">🔍</button>
        </form>

        <div class="filters">
            <a href="?status=All" class="<?= (!isset($_GET['status']) || $_GET['status'] == 'All') ? 'active' : '' ?>">All</a>
            <a href="?status=In Progress" class="<?= ($_GET['status'] ?? '') == 'In Progress' ? 'active' : '' ?>">In Progress</a>
            <a href="?status=Submitted" class="<?= ($_GET['status'] ?? '') == 'Submitted' ? 'active' : '' ?>">Submitted</a>
        </div>

        <a href="meritApplication.php" class="create-btn">+ Create</a>
    </div>

    <h2>MISSING APPLICATION MERIT</h2>

    <div class="container">
        <table>
            <tr class="header-row">
                <th>Student ID</th>
                <th>Claim ID</th>
                <th>Event ID</th>
                <th>Event Title</th>
                <th>Role</th>
                <th>Merit Score</th>
                <th>Claim Status</th>
                <th>Upload Letter</th>
                <th>Actions</th>
            </tr>

            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['student_id']) ?></td>
                        <td><?= htmlspecialchars($row['claim_id']) ?></td>
                        <td><?= htmlspecialchars($row['event_id']) ?></td>
                        <td><?= htmlspecialchars($row['event_title']) ?></td>
                        <td><?= htmlspecialchars($row['merit_description']) ?></td>
                        <td><?= htmlspecialchars($row['merit_score']) ?></td>
                        <td>
                            <?php if ($row['claimStatus'] === 'Submitted'): ?>
                                <span class="status resolved">Submitted</span>
                            <?php else: ?>
                                <span class="status in-progress">In Progress</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="view_letter.php?claim_id=<?= urlencode($row['claim_id']) ?>" target="_blank">View File</a>
                        </td>
                        <td>
                            <?php if ($row['claimStatus'] === 'In Progress'): ?>
                                <a href="updateMerit.php?claim_id=<?= urlencode($row['claim_id']) ?>" class="update-btn">Update</a>
                                <a href="deleteMerit.php?claim_id=<?= urlencode($row['claim_id']) ?>" class="delete-btn" onclick="return confirm('Delete this merit claim?');">Delete</a>
                            <?php else: ?>
                                <em>-</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="9">No merit claims found.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</main>

<div class="pagination">
    <span class="active">1</span>
    <a href="#">2</a>
    <a href="#">&gt;</a>
</div>

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

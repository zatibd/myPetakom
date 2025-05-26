<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
    header("Location: login.php");
    exit();
}

$link = mysqli_connect("localhost", "root", "", "mypetakom") or die(mysqli_connect_error());

$filter = '';
if (isset($_GET['status']) && $_GET['status'] !== 'All') {
    $status = mysqli_real_escape_string($link, $_GET['status']);
    $filter .= " AND mc.claimStatus = '$status'";
}

$search = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = mysqli_real_escape_string($link, $_GET['search']);
    $search .= " AND e.event_title LIKE '%$searchTerm%'";
}

$query = "
    SELECT 
        s.student_id, 
        mc.claim_id, 
        mc.letter_upload, 
        mc.claimStatus, 
        e.event_id, 
        e.event_title, 
        e.event_level,            
        m.merit_description       
    FROM meritclaim mc
    JOIN student s ON mc.student_id = s.student_id
    JOIN event e ON mc.event_id = e.event_id
    JOIN merit m ON mc.merit_id = m.merit_id
    WHERE 1=1 $filter $search
    ORDER BY mc.claim_id DESC
";

$result = mysqli_query($link, $query);

function calculateScore($level, $role) {
    $scores = [
        'International' => ['Main Committee' => 100, 'Committee' => 70, 'Participant' => 50],
        'National'      => ['Main Committee' => 80,  'Committee' => 50, 'Participant' => 40],
        'State'         => ['Main Committee' => 60,  'Committee' => 40, 'Participant' => 30],
        'District'      => ['Main Committee' => 40,  'Committee' => 30, 'Participant' => 15],
        'UMPSA'         => ['Main Committee' => 30,  'Committee' => 20, 'Participant' => 5],
    ];
    return $scores[$level][$role] ?? 0;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Display Missing Application Merit</title>
    <link rel="stylesheet" href="STYLE/displayMerit.css">
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
    <a href="#">Merit Summary</a>
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
        <form method="get" style="display: flex; align-items: center;">
            <input type="text" name="search" placeholder="Search For Event Title" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button class="search-btn" type="submit">🔍</button>
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
                <th>Merit Score</th>
                <th>Claim Status</th>
                <th>Upload Letter</th>
                <th>Actions</th>
            </tr>

            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['student_id']) ?></td>
                        <td><?= htmlspecialchars($row['claim_id']) ?></td>
                        <td><?= htmlspecialchars($row['event_id']) ?></td>
                        <td><?= htmlspecialchars($row['event_title']) ?></td>
                        <td><?= calculateScore($row['event_level'], $row['merit_description']) ?></td>
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
								<a href="deleteMerit.php?claim_id=<?= urlencode($row['claim_id']) ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this merit claim?');">Delete</a>
							<?php else: ?>
								<em>-</em>
							<?php endif; ?>
						</td>

                    </tr>
                <?php } ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No merit claims found.</td>
                </tr>
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

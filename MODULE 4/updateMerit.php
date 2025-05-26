<?php
session_start();
if ($_SESSION['user_type'] !== 'student') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "mypetakom");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$claim_id = $_GET['claim_id'] ?? '';
if (empty($claim_id)) {
    die("No claim ID provided.");
}

$stmt = $conn->prepare("SELECT mc.student_id, e.event_title, m.merit_description FROM meritclaim mc JOIN event e ON mc.event_id = e.event_id JOIN merit m ON mc.merit_id = m.merit_id WHERE mc.claim_id = ?");
$stmt->bind_param("s", $claim_id);
$stmt->execute();
$stmt->bind_result($student_id, $event_title, $merit_description);
$stmt->fetch();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Update Merit Application</title>
  <link rel="stylesheet" href="STYLE/meritApplication.css" />
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
  <header class="header">
    <a href="displayMerit.php" class="back-btn">BACK</a>
    <h1>UPDATE MERIT APPLICATION</h1>
    <a href="displayMerit.php" class="view-application">View Application</a>
  </header>

  <section class="form-section">
    <form action="updateMerit_action.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="claim_id" value="<?= htmlspecialchars($claim_id) ?>">

      <label for="student_id">Student ID:</label>
      <input type="text" id="student_id" name="student_id" value="<?= htmlspecialchars($student_id) ?>" readonly>

      <label for="event_title">Event Title:</label>
      <input type="text" id="event_title" name="event_title" value="<?= htmlspecialchars($event_title) ?>" required>

      <label for="merit_description">Role:</label>
      <select id="merit_description" name="merit_description" required>
        <option value="Main Committee" <?= $merit_description == 'Main Committee' ? 'selected' : '' ?>>Main Committee</option>
        <option value="Committee" <?= $merit_description == 'Committee' ? 'selected' : '' ?>>Committee</option>
        <option value="Participant" <?= $merit_description == 'Participant' ? 'selected' : '' ?>>Participant</option>
      </select>

      <label for="letter_upload">Upload Supporting Letter:</label>
      <input type="file" id="letter_upload" name="letter_upload" accept=".pdf,.jpg,.png">

      <input type="submit" value="SAVE">
    </form>
  </section>
</main>

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

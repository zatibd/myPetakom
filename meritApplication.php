<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];  // Assuming this is IC or login ID

// Connect to DB
$conn = new mysqli("localhost", "root", "", "mypetakom");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get student_id based on user_id
$stmt = $conn->prepare("SELECT student_id FROM student WHERE student_id = ?");
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();
$student_id = "";
if ($row = $result->fetch_assoc()) {
    $student_id = $row['student_id'];
} else {
    die("Student ID not found.");
}

// Fetch event list
$eventOptions = "";
$res = $conn->query("SELECT event_id, event_title FROM event WHERE event_status = 'Active' ORDER BY event_title ASC");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $eid = htmlspecialchars($row['event_id']);
        $etitle = htmlspecialchars($row['event_title']);
        $eventOptions .= "<option value=\"$eid\">$etitle</option>";
    }
} else {
    $eventOptions = "<option disabled>No approved events available</option>";
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MyPetakom Merit Application</title>
  <link rel="stylesheet" href="STYLE4/meritApplication.css" />
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
  <header class="header">
    <a href="student_dashboard.php" class="back-btn">BACK</a>
    <h1>MISSING MERIT APPLICATION</h1>
    <a href="displayMerit.php" class="view-application">View Application</a>
  </header>

  <section class="form-section">
  
  <label for="student_id">Student ID:</label>
    <input type="text" id="student_id" name="student_id" value="<?= htmlspecialchars($student_id) ?>" readonly>

    <form action="merit_action.php" method="post" enctype="multipart/form-data">
      <label for="event_id">Event Title:</label>
      <select name="event_id" required>
          <option value="">-- Select Event --</option>
          <?= $eventOptions ?>
      </select>

      <label for="merit_description">Role:</label>
      <select id="merit_description" name="merit_description" required>
        <option value="Main Committee">Main Committee</option>
        <option value="Committee">Committee</option>
        <option value="Participant">Participant</option>
      </select>

      <label for="letter_upload">Upload Supporting Letter:</label>
      <input type="file" id="letter_upload" name="letter_upload" accept=".pdf,.jpg,.png" required>

      <input type="submit" value="Submit">
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
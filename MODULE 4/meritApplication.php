<?php
session_start();
if ($_SESSION['user_type'] !== 'student') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MyPetakom Merit Application</title>
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
    <a href="student_dashboard.php" class="back-btn">BACK</a>
    <h1>MISSING MERIT APPLICATION</h1>
    <a href="displayMerit.php" class="view-application">View Application</a>
  </header>

  <section class="form-section">
    <form action="merit_action.php" method="post" enctype="multipart/form-data">
      <label for="student_id">Student ID:</label>
      <input type="text" id="student_id" name="student_id" required>

      <label for="event_title">Event Title:</label>
      <input type="text" id="event_title" name="event_title" required>

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
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
?>

<!DOCTYPE html>
<html>
<head>

  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MyPetakom Dashboard</title>
  <link rel="stylesheet" href="STYLE/staff_style.css" />
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
        <a href="#">New Event</a>
		<a href="#">View Event</a>
		<a href="#">Assign Committee</a>
		<a href="#">Apply Merit</a>
		
      </div>

      <div class="menu-title" onclick="toggleMenu('attendance')">ATTENDANCE</div>
      <div class="dropdown-content" id="attendance">
        <a href="#">View Attendance</a>
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
    <h1>Welcome, Staff Member!</h1>
    <p>Staff dashboard content here.</p>
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

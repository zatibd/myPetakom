<?php include("staff_action.php"); ?>
<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MyPetakom Dashboard</title>
  <link rel="stylesheet" href="STYLE/staff_syle.css" />
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
      <div class="profile-circle">
        <?php echo strtoupper(substr($user['user_name'], 0, 1)); ?>.
      </div>
      <span class="dropdown-icon">▼</span>
    </div>
    <div class="dropdown-content-top">
      <a href="">Profile</a>
      <a href="#">Calendar</a>
      <a href="#">Report</a>
      <a href="logout.php">Log Out</a>
    </div>
  </div>
</div>

<div class="content">

  <div class="profile-data">
    <h3>Profile Details</h3>
    <div class="field">
      <label for="name">Name:</label>
      <span><?php echo htmlspecialchars($user['user_name']); ?></span>
    </div>
    <div class="field">
      <label for="staff_id">Staff ID:</label>
      <span><?php echo htmlspecialchars($user['user_id']); ?></span>
    </div>
    <div class="field">
      <label for="email">Email:</label>
      <span><?php echo htmlspecialchars($user['user_email']); ?></span>
    </div>
    <div class="field">
      <label for="phone">Phone Number:</label>
      <span><?php echo htmlspecialchars($user['user_phone']); ?></span>
    </div>
    <div class="field">
      <label for="password">Password:</label>
      <span>**********</span>
    </div>
    <div class="field">
      <label for="role">Staff Role:</label>
      <span><?php echo htmlspecialchars($staff_role); ?></span>
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
</script>

</body>
</html>

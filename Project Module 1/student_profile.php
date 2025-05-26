<?php include("student_action.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MyPetakom Dashboard</title>
  <link rel="stylesheet" href="STYLE/student_profile.css">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <img src="IMAGES/LogoPetakom.png" alt="PETAKOM Logo">

  <div class="search-box">
    <input type="text" placeholder="SEARCH">
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
      <a href="#">Merit Application</a>
      <a href="#">Merit Summary</a>
    </div>
  </div>
</div>

<!-- Topbar -->
<div class="topbar">
  <div class="dropdown">
    <div class="profile-wrapper">
      <div class="profile-circle"><?php echo strtoupper(substr($user['user_name'], 0, 1)) ?>.</div>
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

<!-- Main Content -->
<div class="content">

  <div class="profile-data">
    <h3>Student Profile Details</h3>
    <div class="field">
      <label for="name">Name:</label>
      <span><?php echo htmlspecialchars($user['user_name']); ?></span>
    </div>
    <div class="field">
      <label for="student_id">Student ID:</label>
      <span><?php echo htmlspecialchars($user['user_id']); ?></span>
    </div>
    <div class="field">
      <label for="email">Email:</label>
      <span><?php echo htmlspecialchars($user['user_email']); ?></span>
    </div>
    <div class="field">
      <label for="program">Program:</label>
      <span><?php echo htmlspecialchars($program); ?></span>
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
      <label for="role">Role:</label>
      <span><?php echo htmlspecialchars($user['user_role']); ?></span>
    </div>

      <!-- Student Card Upload Section -->
      <div class="field">
        <label for="student_card">Upload Student Card:</label>
        <input type="file" name="student_card" accept="image/*">
      </div>
    </div>

    <!-- Save Button at the Bottom -->
    <div class="save-button">
      <button type="submit" name="save_profile">Save</button>
    </div>
    
  </form>
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

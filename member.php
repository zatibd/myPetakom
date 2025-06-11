<?php
// Fetch user data
$link = mysqli_connect("localhost", "root", "") or die(mysqli_connect_error());
mysqli_select_db($link, "mypetakom") or die(mysqli_error($link));
$query = "SELECT * FROM user";
$result = mysqli_query($link, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>MyPetakom Dashboard</title>
    <link rel="stylesheet" href="STYLE1/member_style.css" />
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
      <a class="menu-title" href="admin_graph.php">HOME</a>

      <div class="menu-title" onclick="toggleMenu('membership')">MEMBERSHIP</div>
      <div class="dropdown-content" id="membership">
        <a href="member_verification.php">Verification Status</a>
        <a href="view_member.php">View Member</a>
      </div>

      <div class="menu-title" onclick="toggleMenu('event')">EVENT</div>
      <div class="dropdown-content" id="event">
        <a href="event_dashboard.php">Attendance Records</a>
      </div>
      
      <div class="menu-title" onclick="toggleMenu('merit')">MERIT</div>
      <div class="dropdown-content" id="merit">
        <a href="#">Merit Claim</a>
        <a href="merit_application_admin.php">Merit Application</a>
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
        <a href="#">Calendar</a>
        <a href="#">Report</a>
        <a href="logout.php">Log Out</a>
      </div>
    </div>
  </div>
  
  <!-- Main Content -->
  <div class="content">

   

    <h2>Profile Management</h2>
    <table>
	 <div style="margin-bottom: 10px;">
      <a href="registration.php" style="display: inline-block; padding: 8px 12px; background-color: #007BFF; color: white; text-decoration: none; border-radius: 4px;">+ Add User</a>
    </div>
      <thead>
          <tr>
              <th>User ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Actions</th>
          </tr>
      </thead>
      <tbody>
          <?php
              if (mysqli_num_rows($result) > 0) {
                  while ($row = mysqli_fetch_assoc($result)) {
                      echo "<tr>
                          <td>" . $row['user_id'] . "</td>
                          <td>" . $row['user_name'] . "</td>
                          <td>" . $row['user_email'] . "</td>
                          <td>" . $row['user_role'] . "</td>
                          <td>
                              <a href='manage_user.php?id=" . $row['user_id'] . "'>Edit</a> | 
                              <a href='member_action.php?action=delete&id=" . $row['user_id'] . "' onclick='return confirm(\"Are you sure you want to delete this user?\");'>Delete</a>
                          </td>
                      </tr>";
                  }
              } else {
                  echo "<tr><td colspan='5'>No users found</td></tr>";
              }
          ?>
      </tbody>
    </table>
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

<?php
mysqli_close($link);
?>

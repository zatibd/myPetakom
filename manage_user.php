<?php
// Connect to the database
$link = mysqli_connect("localhost", "root", "") or die(mysqli_connect_error());
mysqli_select_db($link, "mypetakom") or die(mysqli_error($link));

// Get the user ID from the URL
if (!isset($_GET['id'])) {
    die("Missing user ID.");
}

$user_id = $_GET['id'];

// Fetch the user data based on user_id
$query = "SELECT * FROM user WHERE user_id = '$user_id'";
$result = mysqli_query($link, $query);

// Check if the user exists
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
} else {
    die("User not found.");
}

// Handle the form submission for updating the user data
if (isset($_POST['save'])) {
    $user_email = $_POST['user_email'];
    $user_phone = $_POST['user_phone'];

    $update_query = "UPDATE user SET user_email = '$user_email', user_phone = '$user_phone' WHERE user_id = '$user_id'";
    
    if (mysqli_query($link, $update_query)) {
        echo "<script type='text/javascript'> alert('Profile updated successfully.'); window.location='view_member.php'; </script>";
    } else {
        echo "Error updating record: " . mysqli_error($link);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit User - MyPetakom Dashboard</title>
  <link rel="stylesheet" href="STYLE1/admin_dashboard.css" />
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
        <a href="calendar.php">Calendar</a>
        <a href="#">Report</a>
        <a href="logout.php">Log Out</a>
      </div>
    </div>
  </div>
  
  <!-- Main Content -->
  <div class="content">
    <h2>Edit User Profile</h2>

    <!-- Profile Data Box -->
    <div class="profile-data">

      <!-- Edit Form -->
      <form method="POST" action="manage_user.php?id=<?php echo $user_id; ?>">

        <div class="field">
          <label for="user_email">Email:</label>
          <input type="email" name="user_email" value="<?php echo htmlspecialchars($row['user_email']); ?>" required style="flex:1; padding:8px;">
        </div>

        <div class="field">
          <label for="user_phone">Phone Number:</label>
          <input type="text" name="user_phone" value="<?php echo htmlspecialchars($row['user_phone']); ?>" required style="flex:1; padding:8px;">
        </div>

        <div class="field" style="justify-content: center;">
          <button type="submit" name="save" style="padding:10px 20px; background-color:#4CAF50; color:white; border:none; border-radius:4px; cursor:pointer;">Save</button>
          <a href="view_member.php" style="padding:10px 20px; background-color:#ccc; color:black; text-decoration:none; border-radius:4px; margin-left:10px;">Cancel</a>
        </div>

      </form>

    </div> <!-- End profile-data -->

  </div>

  <!-- Footer -->
  <div class="footer">
    @MyPetakom 2024/2025
  </div>

<script>
  function toggleMenu(id) {
    const content = document.getElementById(id);
    content.style.display = content.style.display === "block" ? "none" : "block";
  }
</script>

</body>
</html>

<?php
mysqli_close($link);
?>

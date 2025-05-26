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
    // Get the updated data from the form
    $user_name = $_POST['user_name'];
    $user_email = $_POST['user_email'];
    $user_role = $_POST['user_role'];

    // Update the user data in the database
    $update_query = "UPDATE user SET user_name = '$user_name', user_email = '$user_email', user_role = '$user_role' WHERE user_id = '$user_id'";
    
    if (mysqli_query($link, $update_query)) {
        echo "<script type='text/javascript'> alert('Profile updated successfully.'); window.location='member.php'; </script>";
    } else {
        echo "Error updating record: " . mysqli_error($link);
    }
}
?>

<!DOCTYPE html>
<html>
<head>

  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MyPetakom Dashboard</title>
  <link rel="stylesheet" href="STYLE/administrator_style.css" />
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

      <div class="menu-title" onclick="toggleMenu('event')">MEMBERSHIP</div>
      <div class="dropdown-content" id="event">
        <a href="#">Verification Status</a>
		<a href="#">View Member</a>
		
      </div>

      <div class="menu-title" onclick="toggleMenu('attendance')">EVENT</div>
      <div class="dropdown-content" id="attendance">
        <a href="#">Attendance Records</a>
      </div>
	  
	  <div class="menu-title" onclick="toggleMenu('event')">MERIT</div>
      <div class="dropdown-content" id="event">
        <a href="#">Merit Claim</a>
		<a href="#">Merit Application</a>
		
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
        <a href="#">Profile</a>
        <a href="#">Calendar</a>
        <a href="#">Report</a>
        <a href="logout.php">Log Out</a>
      </div>
    </div>
  </div>
  
  <!-- Main Content -->
  <div class="content">
    <h2>Edit User Profile</h2>
    
    <!-- Edit Form -->
    <form method="POST" action="manage_user.php?id=<?php echo $user_id; ?>">
        <div class="field">
            <label for="user_name">Name:</label>
            <input type="text" name="user_name" value="<?php echo htmlspecialchars($row['user_name']); ?>" required>
        </div>
        <div class="field">
            <label for="user_email">Email:</label>
            <input type="email" name="user_email" value="<?php echo htmlspecialchars($row['user_email']); ?>" required>
        </div>
        <div class="field">
            <label for="user_role">Role:</label>
            <input type="text" name="user_role" value="<?php echo htmlspecialchars($row['user_role']); ?>" required>
        </div>

        <!-- Submit button -->
        <div class="field">
            <button type="submit" name="save">Save</button>
        </div>
    </form>
  </div>

  <!-- Footer -->
  <div class="footer">
    @MyPetakom 2024/2025
  </div>

</body>
</html>

<?php
mysqli_close($link);
?>

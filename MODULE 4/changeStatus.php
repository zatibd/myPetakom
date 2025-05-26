<?php
session_start();

if ($_SESSION['user_type'] !== 'staff') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "mypetakom");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_id'], $_POST['new_status'])) {
    $claimId = $_POST['claim_id'];
    $newStatus = $_POST['new_status'];

    $stmt = $conn->prepare("UPDATE meritclaim SET claimStatus = ? WHERE claim_id = ?");
    $stmt->bind_param("ss", $newStatus, $claimId);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('✅ Status updated successfully.'); window.location.href='changeStatus.php';</script>";
}

// Fetch data
$query = "
    SELECT 
        mc.claim_id, 
        s.student_id, 
        e.event_title, 
        mc.claimStatus AS status
    FROM meritclaim mc
    JOIN student s ON mc.student_id = s.student_id
    JOIN event e ON mc.event_id = e.event_id
    ORDER BY mc.claim_id DESC
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Update Merit Status</title>
  <link rel="stylesheet" href="STYLE/changeStatus.css" />
</head>
<body>
  <div class="sidebar">
    <img src="IMAGES/LogoPetakom.png" alt="PETAKOM Logo" />
    <div class="search-box">
      <input type="text" placeholder="SEARCH" />
      <button>🔍</button>
    </div>
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
      <a href="attendaceSlot.php">View Attendance</a>
    </div>
    <div class="menu-title" onclick="toggleMenu('merit')">MERIT</div>
    <div class="dropdown-content" id="merit">
      <a href="changeStatus.php">Update Missing Merit Status</a>
    </div>
  </div>

  <div class="main-wrapper">
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
      <div class="content-wrapper">
		<button class="back-btn" onclick="window.location.href='staff_dashboard.php'">BACK</button>
	 
        <h2>Change Merit Claim Status</h2>
		
        <table>
          <tr>
            <th>Claim ID</th>
            <th>Student ID</th>
            <th>Event Title</th>
            <th>Current Status</th>
            <th>Change Status</th>
          </tr>
          <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['claim_id']) ?></td>
            <td><?= htmlspecialchars($row['student_id']) ?></td>
            <td><?= htmlspecialchars($row['event_title']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td>
              <form method="POST">
                <input type="hidden" name="claim_id" value="<?= $row['claim_id'] ?>">
                <select name="new_status" required>
                  <option value="In Progress" <?= $row['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                  <option value="Submitted" <?= $row['status'] === 'Submitted' ? 'selected' : '' ?>>Submitted</option>
                </select>
                <button type="submit">Update</button>
              </form>
            </td>
          </tr>
          <?php endwhile; ?>
        </table>
      </div>
    </main>

    <div class="footer">@MyPetakom 2024/2025</div>
  </div>

<script>
function toggleMenu(id) {
  const content = document.getElementById(id);
  content.style.display = content.style.display === "block" ? "none" : "block";
}
</script>
</body>
</html>

<?php
session_start();

// Only allow PETAKOM Advisors
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Staff (Petakom Advisor)') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "mypetakom");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process status update and insert into committee/member if approved
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_id'], $_POST['new_status'])) {
    $claimId = $_POST['claim_id'];
    $newStatus = $_POST['new_status'];

    // 1. Update claim status
    $stmt = $conn->prepare("UPDATE meritclaim SET claimStatus = ? WHERE claim_id = ?");
    $stmt->bind_param("ss", $newStatus, $claimId);
    $stmt->execute();
    $stmt->close();

    // 2. If status is "Submitted", mirror into committee system
    if ($newStatus === 'Submitted') {
        $fetch = $conn->prepare("
            SELECT mc.student_id, mc.event_id, m.merit_description
            FROM meritclaim mc
            JOIN merit m ON mc.merit_id = m.merit_id
            WHERE mc.claim_id = ?
        ");
        $fetch->bind_param("s", $claimId);
        $fetch->execute();
        $result = $fetch->get_result();

        if ($data = $result->fetch_assoc()) {
            $student_id = $data['student_id'];
            $event_id = $data['event_id'];
            $role = explode(' in ', $data['merit_description'])[0];

            $checkMember = $conn->prepare("SELECT member_id FROM member WHERE student_id = ?");
            $checkMember->bind_param("s", $student_id);
            $checkMember->execute();
            $memberResult = $checkMember->get_result();

            if ($member = $memberResult->fetch_assoc()) {
                $member_id = $member['member_id'];
            } else {
                $insertMember = $conn->prepare("INSERT INTO member (student_id) VALUES (?)");
                $insertMember->bind_param("s", $student_id);
                $insertMember->execute();
                $member_id = $insertMember->insert_id;
                $insertMember->close();
            }

            $checkCommittee = $conn->prepare("SELECT 1 FROM committee WHERE member_id = ? AND event_id = ?");
            $checkCommittee->bind_param("is", $member_id, $event_id);
            $checkCommittee->execute();
            $exists = $checkCommittee->get_result()->num_rows > 0;
            $checkCommittee->close();

            if (!$exists) {
                $insertCommittee = $conn->prepare("INSERT INTO committee (member_id, event_id, committee_role) VALUES (?, ?, ?)");
                $insertCommittee->bind_param("iss", $member_id, $event_id, $role);
                $insertCommittee->execute();
                $insertCommittee->close();
            }
        }

        $fetch->close();
    }

    echo "<script>alert('✅ Status updated successfully.'); window.location.href='changeStatus.php';</script>";
}

// Fetch all claims for display (including letter)
$query = "
    SELECT 
        mc.claim_id, 
        s.student_id, 
        e.event_title, 
        mc.letter_upload,
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
  <link rel="stylesheet" href="STYLE4/changeStatus.css" />
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
      <a href="event_registration.php">New Event</a>
      <a href="event_view.php">View Event</a>
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
            <th>Letter</th>
            <th>Current Status</th>
            <th>Change Status</th>
          </tr>
          <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['claim_id']) ?></td>
            <td><?= htmlspecialchars($row['student_id']) ?></td>
            <td><?= htmlspecialchars($row['event_title']) ?></td>
            <td>
                <a href="view_letter.php?claim_id=<?= urlencode($row['claim_id']) ?>" target="_blank">View File</a>
            </td>
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

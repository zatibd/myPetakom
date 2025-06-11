<?php
// Database connection
$link = mysqli_connect("localhost", "root", "", "mypetakom") or die(mysqli_connect_error());

// Query to fetch student and their card data
$query = "
    SELECT u.user_id, u.user_name, m.member_id, m.member_approval, m.student_card
    FROM user u
    JOIN member m ON u.user_id = m.student_id
    WHERE LOWER(u.user_role) = 'student'
";

$result = mysqli_query($link, $query) or die("Query error: " . mysqli_error($link));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Membership Approval - MyPetakom Dashboard</title>
<link rel="stylesheet" href="STYLE1/admin.css" />
</head>
<body>

<div class="sidebar">
  <img src="IMAGES/LogoPetakom.png" alt="PETAKOM Logo" />
  <div class="menu">
    <div class="menu-title" onclick="window.location.href='admin_graph.php'">HOME</div>
    <div class="menu-title" onclick="toggleMenu('membership')">MEMBERSHIP</div>
    <div class="dropdown-content" id="membership">
      <a href="member_verification.php">Verification Status</a>
      <a href="view_member.php">View Member</a>
    </div>
    <div class="menu-title" onclick="toggleMenu('event')">EVENT</div>
    <div class="dropdown-content" id="event">
      <a href="#">Attendance Records</a>
    </div>
    <div class="menu-title" onclick="toggleMenu('merit')">MERIT</div>
    <div class="dropdown-content" id="merit">
      <a href="#">Merit Claim</a>
      <a href="#">Merit Application</a>
    </div>
  </div>
</div>

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

<div class="content">
  <div class="h1text">
    <h1>MEMBERSHIP MANAGEMENT</h1>
  </div>

  <div class="container2">
    <input type="text" id="userSearch" placeholder="Search by name">

    <table id="membershipTable">
      <thead>
        <tr>
          <th>STUDENT ID</th>
          <th>NAME</th>
          <th>STATUS</th>
          <th>ACTIONS</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr id="row-<?= $row['member_id'] ?>">
            <td><?= htmlspecialchars($row["user_id"]) ?></td>
            <td><?= htmlspecialchars($row["user_name"]) ?></td>
            <td id="status-<?= $row['member_id'] ?>">
              <?php
                if ($row["member_approval"] === null) {
                  echo "Pending";
                } else {
                  echo ucfirst(htmlspecialchars($row["member_approval"]));
                }
              ?>
            </td>
            <td>
              <button class="action-btn accept" onclick="updateStatus(<?= $row['member_id'] ?>, 'accept')">&#10004;</button>
              <button class="action-btn reject" onclick="updateStatus(<?= $row['member_id'] ?>, 'reject')">&#10008;</button>
              <button class="action-btn view-card" onclick="showCard('<?= base64_encode($row['student_card']) ?>')">🪪 View Card</button>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="footer">@MyPetakom 2024/2025</div>

<div id="cardModal" class="card-modal">
  <div class="card-modal-content">
    <span class="close-modal" onclick="document.getElementById('cardModal').style.display='none'">✖️</span>
    <h3>Student Card Preview</h3>
    <img id="cardImage" src="" alt="Student Card">
  </div>
</div>

<script>
function toggleMenu(id) {
  const content = document.getElementById(id);
  content.style.display = content.style.display === "block" ? "none" : "block";
}
function updateStatus(memberID, action) {
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "update_status_membership.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  xhr.onload = function() {
    if (xhr.status === 200) {
      const response = xhr.responseText.trim();
      const statusCell = document.getElementById("status-" + memberID);
      if (response === "approved" || response === "rejected") {
        statusCell.innerText = response.charAt(0).toUpperCase() + response.slice(1);
      } else {
        alert("Unexpected response: " + response);
      }
    } else {
      alert("Failed to update status: " + xhr.responseText);
    }
  };

  xhr.send("memberID=" + memberID + "&action=" + action);
}

function showCard(base64Image) {
  const modal = document.getElementById("cardModal");
  const img = document.getElementById("cardImage");
  img.src = "data:image/jpeg;base64," + base64Image;
  modal.style.display = "flex";
}

document.getElementById("userSearch").addEventListener("keyup", function () {
  const search = this.value.toLowerCase();
  const rows = document.querySelectorAll("#membershipTable tbody tr");
  rows.forEach(row => {
    const text = row.innerText.toLowerCase();
    row.style.display = text.includes(search) ? "" : "none";
  });
});
</script>

<?php mysqli_close($link); ?>
</body>
</html>
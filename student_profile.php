<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "mypetakom");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Restrict access to only logged-in students
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$user_sql = "SELECT * FROM user WHERE user_id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("s", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Fetch program from student table
$program = "Not Available";
$student_sql = "SELECT student_program FROM student WHERE student_id = ?";
$student_stmt = $conn->prepare($student_sql);
$student_stmt->bind_param("s", $user_id);
$student_stmt->execute();
$student_result = $student_stmt->get_result();

if ($student_result->num_rows === 1) {
    $program_row = $student_result->fetch_assoc();
    $program = $program_row['student_program'];
}

// Fetch student card image
$card_query = "SELECT student_card FROM member WHERE student_id = ?";
$card_stmt = $conn->prepare($card_query);
$card_stmt->bind_param("s", $user_id);
$card_stmt->execute();
$card_result = $card_stmt->get_result();
$student_card_blob = null;

if ($card_result->num_rows > 0) {
    $row = $card_result->fetch_assoc();
    if ($row['student_card']) {
        $student_card_blob = base64_encode($row['student_card']);
    }
}

// Fetch membership status
$membership_status = null;
$status_query = "SELECT member_status FROM member WHERE student_id = ?";
$status_stmt = $conn->prepare($status_query);
$status_stmt->bind_param("s", $user_id);
$status_stmt->execute();
$status_result = $status_stmt->get_result();

if ($status_result->num_rows > 0) {
    $status_row = $status_result->fetch_assoc();
    $membership_status = $status_row['member_status'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>MyPetakom Dashboard</title>
<link rel="stylesheet" href="STYLE1/student.css" />
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
    <div class="dropdown-content" id="event" style="display:none;">
      <a href="#">View Event</a>
    </div>
    <div class="menu-title" onclick="toggleMenu('attendance')">ATTENDANCE</div>
    <div class="dropdown-content" id="attendance" style="display:none;">
      <a href="#">Key In Attendance</a>
      <a href="#">View Attendance</a>
    </div>
    <div class="menu-title" onclick="toggleMenu('merit')">MERIT</div>
    <div class="dropdown-content" id="merit" style="display:none;">
      <a href="#">Merit Application</a>
      <a href="#">Merit Summary</a>
    </div>
  </div>
</div>

<!-- Topbar -->
<div class="topbar">
  <div class="dropdown">
    <div class="profile-wrapper" onclick="toggleDropdown()">
      <div class="profile-circle"><?php echo strtoupper(substr($user['user_name'], 0, 1)); ?>.</div>
      <span class="dropdown-icon">▼</span>
    </div>
    <div class="dropdown-content-top" id="topDropdown" style="display:none;">
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

    <?php if (isset($_SESSION['message'])): ?>
      <div style="color: green; margin-bottom: 10px;">
        <?php 
          echo htmlspecialchars($_SESSION['message']);
          unset($_SESSION['message']); 
        ?>
      </div>
    <?php endif; ?>
	
	<div class="field"><label>Name:</label><span><?php echo htmlspecialchars($user['user_name']); ?></span></div>
    <div class="field"><label>Student ID:</label><span><?php echo htmlspecialchars($user['user_id']); ?></span></div>
    <div class="field"><label>Email:</label><span><?php echo htmlspecialchars($user['user_email']); ?></span></div>
    <div class="field"><label>Program:</label><span><?php echo htmlspecialchars($program); ?></span></div>
    <div class="field"><label>Phone Number:</label><span><?php echo htmlspecialchars($user['user_phone']); ?></span></div>
    <div class="field"><label>Password:</label><span>**********</span></div>
    <div class="field"><label>Role:</label><span><?php echo htmlspecialchars($user['user_role']); ?></span></div>
    <div class="field"><label>Membership Status:</label>
      <span>
        <?php
          if ($membership_status === 'approved') {
            echo 'Active';
          } elseif ($membership_status === 'rejected') {
            echo 'Rejected - Please contact administrator';
          } else {
            echo 'Pending';
          }
        ?>
      </span>
    </div>

    <!-- Upload form -->
    <form action="student_profile_action.php" method="POST" enctype="multipart/form-data">
      <div class="field">
        <label for="student_card">Upload Student Card:</label>
        <input type="file" name="student_card" accept="image/*" id="studentCardInput" required />
      </div>

      <div class="field">
        <label>Preview Student Card:</label><br />
        <img id="studentCardPreview" src="" alt="Student Card Preview" style="max-width: 300px; display:none;" />
      </div>

      <div class="button-container">
        <button type="button" id="displayProfileBtn">Display User Details</button>
        <button type="submit" name="student_profile_action">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal -->
<div id="profileModal">
  <div>
    <span id="closeModal">&times;</span>
    <div class="profile-data">
      <h3>Student Profile Details</h3>
      <div class="field"><label>Name:</label><span><?php echo htmlspecialchars($user['user_name']); ?></span></div>
      <div class="field"><label>Student ID:</label><span><?php echo htmlspecialchars($user['user_id']); ?></span></div>
      <div class="field"><label>Email:</label><span><?php echo htmlspecialchars($user['user_email']); ?></span></div>
      <div class="field"><label>Program:</label><span><?php echo htmlspecialchars($program); ?></span></div>
      <div class="field"><label>Phone Number:</label><span><?php echo htmlspecialchars($user['user_phone']); ?></span></div>
      <div class="field"><label>Role:</label><span><?php echo htmlspecialchars($user['user_role']); ?></span></div>
      <div class="field"><label>Membership Status:</label>
        <span>
          <?php
            if ($membership_status === 'approved') {
              echo 'Active';
            } elseif ($membership_status === 'rejected') {
              echo 'Rejected - Please contact administrator';
            } else {
              echo 'Pending';
            }
          ?>
        </span>
      </div>
      <?php if ($student_card_blob): ?>
      <div class="field">
        <label>Uploaded Student Card:</label><br />
        <img src="data:image/jpeg;base64,<?php echo $student_card_blob; ?>" style="max-width: 300px;" />
      </div>
      <?php else: ?>
      <div class="field" style="color: red;">
        <label>Uploaded Student Card:</label><br />
        <span>No card uploaded.</span>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Footer -->
<div class="footer">@MyPetakom 2024/2025</div>

<script>
function toggleMenu(id) {
  var content = document.getElementById(id);
  content.style.display = content.style.display === "block" ? "none" : "block";
}

function toggleDropdown() {
  var dropdown = document.getElementById('topDropdown');
  dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}

window.onclick = function(event) {
  var dropdown = document.getElementById('topDropdown');
  if (!event.target.closest('.profile-wrapper') && dropdown.style.display === 'block') {
    dropdown.style.display = 'none';
  }
}

// Image preview
document.getElementById('studentCardInput').addEventListener('change', function(event) {
  var reader = new FileReader();
  reader.onload = function(e) {
    var preview = document.getElementById('studentCardPreview');
    preview.src = e.target.result;
    preview.style.display = 'block';
  };
  reader.readAsDataURL(event.target.files[0]);
});

// Modal logic
const displayBtn = document.getElementById('displayProfileBtn');
const modal = document.getElementById('profileModal');
const closeModalBtn = document.getElementById('closeModal');

displayBtn.addEventListener('click', () => {
  modal.style.display = 'flex';
});
closeModalBtn.addEventListener('click', () => {
  modal.style.display = 'none';
});
window.addEventListener('click', (e) => {
  if (e.target === modal) {
    modal.style.display = 'none';
  }
});
</script>

</body>
</html>
<?php $conn->close(); ?>
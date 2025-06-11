<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "mypetakom");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT
            mb.student_id,
            e.event_id,
            e.event_title,
            CONCAT(c.committee_role, ' in ', e.event_level, ' Level') AS role_display,
            m.merit_score
        FROM committee c
        JOIN member mb ON c.member_id = mb.member_id
        JOIN event e ON c.event_id = e.event_id
        LEFT JOIN merit m ON m.merit_description = CONCAT(c.committee_role, ' in ', e.event_level, ' Level')
        WHERE e.merit_status = 'Approved' AND mb.student_id = ?
        ORDER BY e.event_title ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Merit Awarded</title>
    <link rel="stylesheet" href="STYLE4/meritAwarded.css">
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
            <a href="#">View Event</a>
        </div>

        <div class="menu-title" onclick="toggleMenu('attendance')">ATTENDANCE</div>
        <div class="dropdown-content" id="attendance">
            <a href="#">Key In Attendance</a>
            <a href="#">View Attendance</a>
        </div>

        <div class="menu-title" onclick="toggleMenu('merit')">MERIT</div>
        <div class="dropdown-content" id="merit">
            <a href="meritApplication.php">Merit Application</a>
            <a href="meritAwarded.php">Merit Summary</a>
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
            <a href="#">Profile</a>
            <a href="#">Calendar</a>
            <a href="#">Report</a>
            <a href="logout.php">Log Out</a>
        </div>
    </div>
</div>

<main class="main-content">
    <div class="top-controls">
        <a href="student_dashboard.php" class="back-btn">BACK</a>
        <a href="merit_qr.php" class="create-btn" target="_blank">QR Code</a>
    </div>

    <h2>MERIT AWARDED</h2>

    <div class="container">
        <table>
            <tr class="header-row">
                <th>Student ID</th>
                <th>Event ID</th>
                <th>Event Title</th>
                <th>Role</th>
                <th>Merit Score</th>
            </tr>

            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['student_id']) ?></td>
                        <td><?= htmlspecialchars($row['event_id']) ?></td>
                        <td><?= htmlspecialchars($row['event_title']) ?></td>
                        <td><?= htmlspecialchars($row['role_display']) ?></td>
                        <td><?= htmlspecialchars($row['merit_score']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">No merit awarded yet.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</main>

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

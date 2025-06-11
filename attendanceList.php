<?php
session_start();

// Allow only Staff
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Staff (Petakom Advisor)') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "mypetakom");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle bulk update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'], $_POST['new_verification'])) {
    $studentId = $_POST['student_id'];
    $newVerification = $_POST['new_verification'];

    $stmt = $conn->prepare("
        UPDATE attendance 
        SET location_verification = ? 
        WHERE student_id = ?
    ");
    $stmt->bind_param("ss", $newVerification, $studentId);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('All attendance records updated for student $studentId.'); window.location.href='attendanceList.php';</script>";
    exit();
}

// Get all attendance data
$query = "
    SELECT 
        a.attendance_id, 
        a.attendanceslot_id,
        s.student_id,
        e.event_title,
        a.checkin_time,
        a.location_verification
    FROM attendance a
    JOIN student s ON a.student_id = s.student_id
    JOIN attendance_slot slot ON a.attendanceslot_id = slot.attendanceslot_id
    JOIN event e ON slot.event_id = e.event_id
    ORDER BY s.student_id, a.attendance_id DESC
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Update Attendance Verification</title>
  <style>
    table {
      border-collapse: collapse;
      margin-bottom: 20px;
      width: 100%;
    }
    table, th, td {
      border: 1px solid #ccc;
    }
    th {
      background-color: #0074D9;
      color: white;
    }
    td, th {
      padding: 8px;
      text-align: center;
    }
    .verified { background-color: #e0ffe0; }
    .not-verified { background-color: #ffe0e0; }
    form.student-form {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
    }
    .student-header {
      background: #f5f5f5;
      padding: 10px;
      border-left: 5px solid #0074D9;
    }
    button.back-btn {
      margin-bottom: 20px;
      padding: 8px 15px;
      background-color: #0074D9;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    button.back-btn:hover {
      background-color: #005fa3;
    }
  </style>
</head>
<body>
<button class="back-btn" onclick="window.location.href='attendaceSlot.php'">Back</button>
<h2>Change Attendance Location Verification Status</h2>
<?php
// Group data by student
$result->data_seek(0);
$groupedData = [];
while ($row = $result->fetch_assoc()) {
    $groupedData[$row['student_id']][] = $row;
}

if (empty($groupedData)): ?>
  <p>No attendance records found.</p>
<?php else: ?>

<?php foreach ($groupedData as $studentId => $records): ?>
  <div class="student-header">
    <strong>Student ID: <?= htmlspecialchars($studentId) ?></strong>
    <form method="POST" class="student-form" onsubmit="return confirm('Update all records for this student?');">
      <input type="hidden" name="student_id" value="<?= htmlspecialchars($studentId) ?>">
      <select name="new_verification" required>
        <option value="">--Select Verification--</option>
        <option value="Verified">Verified</option>
        <option value="Not Verified">Not Verified</option>
      </select>
      <button type="submit">Update All</button>
    </form>
  </div>

  <table>
    <thead>
      <tr>
        <th>Attendance ID</th>
        <th>Slot ID</th>
        <th>Event Title</th>
        <th>Check-in Time</th>
        <th>Location Verification</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($records as $row): 
        $class = $row['location_verification'] === 'Verified' ? 'verified' : 'not-verified';
      ?>
      <tr class="<?= $class ?>">
        <td><?= htmlspecialchars($row['attendance_id']) ?></td>
        <td><?= htmlspecialchars($row['attendanceslot_id']) ?></td>
        <td><?= htmlspecialchars($row['event_title']) ?></td>
        <td><?= htmlspecialchars($row['checkin_time']) ?></td>
        <td><?= htmlspecialchars($row['location_verification']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endforeach; ?>

<?php endif; ?>

</body>
</html>








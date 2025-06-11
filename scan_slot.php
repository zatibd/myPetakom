<?php
$conn = mysqli_connect("localhost", "root", "", "mypetakom");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Step 1: Make sure QRCode is provided
$QRCode = $_GET['QRCode'] ?? "";  // Use null coalescing to avoid warning


$sql = "SELECT a.attendanceslot_id, a.event_id, e.event_title
        FROM attendance_slot a
        JOIN event e ON a.event_id = e.event_id
        ORDER BY a.attendanceslot_id ASC";

$result = $conn->query($sql);
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'] ?? '';
    $user_password = $_POST['user_password'] ?? '';

    // TODO: Add actual verification logic here
    $message = "You have submitted your attendance!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Slots - MyPetakom</title>
    <meta name="description" content="Student attendance page for Petakom events">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('image/fkom.png') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            font-size: 26px;
            color: #2b4d71;
            margin-bottom: 25px;
        }

        .slot-item {
            background: #ffffff;
            border-radius: 10px;
            padding: 20px;
            width: 400px;
            margin-bottom: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .section-box {
            background: #ffffff;
            border: 1px solid #ccc;
            padding: 25px;
            border-radius: 10px;
            width: 400px;
            margin-top: 30px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .section-box h3 {
            color: blue;
            margin-bottom: 10px;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #aaa;
            box-sizing: border-box;
        }

        .verify-button {
            margin-top: 20px;
            background-color: #2b4d71;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        .verify-button:hover {
            background-color: #22405a;
        }

        .success-message {
            color: green;
            font-weight: bold;
        }

        .note {
            font-size: 0.9rem;
            color: #555;
            margin-top: 5px;
        }
    </style>
</head>
<body>

    <h1>Attendance Slot List - MyPetakom</h1>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="slot-item">
                <p><strong>Slot ID:</strong> <?= htmlspecialchars($row['attendanceslot_id']) ?></p>
                <p><strong>Event ID:</strong> <?= htmlspecialchars($row['event_id']) ?></p>
                <p><strong>Event Title:</strong> <?= htmlspecialchars($row['event_title']) ?></p>
				 
                <p><strong id="time-<?= $row['attendanceslot_id'] ?>">Current Malaysia Time: Loading...</strong></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No attendance slots found.</p>
    <?php endif; ?>

    <div class="section-box">
        <h3>Verify your attendance:</h3>

        <?php if (!empty($message)): ?>
            <p class="success-message"><?= htmlspecialchars($message) ?></p>
        <?php else: ?>
            <p class="note">Please enter your student ID and password</p>
            <form method="post">
                <label for="studentID">Student ID:</label>
                <input type="text" id="studentID" name="student_id" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="user_password" required>

                <button type="submit" class="verify-button">VERIFY</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        function updateMalaysiaTime() {
            const now = new Date();

            const malaysiaTime = new Intl.DateTimeFormat('en-GB', {
                timeZone: 'Asia/Kuala_Lumpur',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour12: false
            }).format(now);

            document.querySelectorAll('[id^="time-"]').forEach(div => {
                div.innerHTML = "Current Malaysia Time: " + malaysiaTime;
            });
        }

        updateMalaysiaTime();
        setInterval(updateMalaysiaTime, 1000);
    </script>

</body>
</html>

<?php $conn->close(); ?>















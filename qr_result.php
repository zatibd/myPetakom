<?php
if (!isset($_GET['sid'])) {
    die("Invalid request.");
}

$student_id = $_GET['sid'];
$conn = new mysqli("localhost", "root", "", "mypetakom");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("
    SELECT 
        s.student_id,
        u.user_name,
        s.student_program,
        SUM(m.merit_score) AS total_merit
    FROM committee c
    JOIN member mb ON c.member_id = mb.member_id
    JOIN student s ON mb.student_id = s.student_id
    JOIN user u ON s.student_id = u.user_id
    JOIN event e ON c.event_id = e.event_id
    JOIN merit m ON m.merit_description = CONCAT(c.committee_role, ' in ', e.event_level, ' Level')
    WHERE e.merit_status = 'Approved' AND s.student_id = ?
    GROUP BY s.student_id
");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("
    SELECT 
        e.event_title,
        e.event_date,
        CONCAT(c.committee_role, ' in ', e.event_level, ' Level') AS role_display,
        m.merit_score
    FROM committee c
    JOIN member mb ON c.member_id = mb.member_id
    JOIN event e ON c.event_id = e.event_id
    JOIN merit m ON m.merit_description = CONCAT(c.committee_role, ' in ', e.event_level, ' Level')
    WHERE e.merit_status = 'Approved' AND mb.student_id = ?
    ORDER BY e.event_title ASC
");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$event_results = $stmt->get_result();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Merit Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f8fb;
            padding: 30px;
        }

        .container-custom {
            max-width: 900px;
            margin: auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        .logo {
            display: block;
            margin: 0 auto 20px;
            max-width: 150px;
        }

        .print-btn {
            margin-top: 30px;
        }

        @media print {
            .print-btn {
                display: none;
            }

            .container-custom {
                box-shadow: none;
                border: 1px solid #aaa;
            }
        }
    </style>
</head>
<body>
    <div class="container container-custom">
        <img src="IMAGES/LogoPetakom.png" alt="Petakom Logo" class="logo">

        <h2 class="text-center mb-4">Student Merit Details</h2>

        <?php if ($student): ?>
            <table class="table table-bordered">
                <tbody>
                    <tr><th scope="row">Name</th><td><?= htmlspecialchars($student['user_name']) ?></td></tr>
                    <tr><th scope="row">Student ID</th><td><?= htmlspecialchars($student['student_id']) ?></td></tr>
                    <tr><th scope="row">Programme</th><td><?= htmlspecialchars($student['student_program']) ?></td></tr>
                    <tr><th scope="row">Total Merits</th><td><?= htmlspecialchars($student['total_merit']) ?></td></tr>
                </tbody>
            </table>

            <h4 class="mt-5">Event Merit Breakdown</h4>
            <table class="table table-striped table-hover table-bordered mt-3">
                <thead class="table-primary">
                    <tr>
                        <th>Event Title</th>
                        <th>Event Date</th>
                        <th>Role</th>
                        <th>Merit Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $event_results->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['event_title']) ?></td>
                        <td><?= htmlspecialchars(date("d M Y", strtotime($row['event_date']))) ?></td>
                        <td><?= htmlspecialchars($row['role_display']) ?></td>
                        <td><?= htmlspecialchars($row['merit_score']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="text-center">
                <button onclick="window.print()" class="btn btn-primary print-btn">Print as PDF</button>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center">
                No merit information found for this student.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

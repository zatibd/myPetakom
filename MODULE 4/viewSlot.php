<?php
// Connect to the database
$conn = mysqli_connect("localhost", "root", "", "mypetakom");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Correct table name variable
$tableName = "attendance_slot";

// Prepare SQL statement to fetch all attendance slots
$sql = "SELECT 
            attendanceslot_id, 
            event_id, 
            slot_time, 
            ST_X(slot_geolocation) AS latitude, 
            ST_Y(slot_geolocation) AS longitude, 
            attendanceslot_qr AS qr_code_file
        FROM $tableName";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error in query: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Attendance Slots</title>
    <style>
        table {
            border-collapse: collapse;
            width: 90%;
            margin: 20px auto;
        }
        th, td {
            border: 1px solid #444;
            padding: 8px 12px;
        }
        th {
            background-color: #eee;
        }
        .container {
            width: 95%;
            margin: auto;
            text-align: center;
        }
        button {
            margin: 15px;
            padding: 8px 12px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container">
   <button type="button" onclick="window.location.href='attendaceSlot.php'">Back</button><br><br>
    <h1>Attendance Slots</h1>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>AttendanceSlot ID</th>
                    <th>Event ID</th>
                    <th>Time</th>
                    <th>Location (Lat,Long)</th>
                    <th>QR Code</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['attendanceslot_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['event_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['slot_time']); ?></td>
                        <td>
                            <?php 
                                // Show latitude and longitude with 6 decimals
                                echo number_format($row['latitude'], 6) . ", " . number_format($row['longitude'], 6); 
                            ?>
                        </td>
                        <td>
                            <?php if (!empty($row['qr_code_file'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($row['qr_code_file']); ?>" alt="QR Code" width="100">
                            <?php else: ?>
                                No QR
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No attendance slots found.</p>
    <?php endif; ?>

</div>
</body>
</html>

<?php
// Close the connection
mysqli_close($conn);
?>






<?php
$conn = new mysqli("localhost", "root", "", "mypetakom");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'];
    $student_ids = $_POST['student_ids'];
    $positions = $_POST['positions'];

	// Clear old committee (this works for both new or existing events)
    $conn->query("DELETE FROM committee WHERE event_id = '$event_id'");

    for ($i = 0; $i < count($student_ids); $i++) {
        $student_id = $conn->real_escape_string($student_ids[$i]);
        $position = $conn->real_escape_string($positions[$i]);

        // Ensure student exists in user table
        $check_user = $conn->query("SELECT * FROM user WHERE user_id = '$student_id'");
        if ($check_user->num_rows === 0) {
            continue; // skip invalid user
        }

        // Get member_id from member table based on student_id
        $result_member = $conn->query("SELECT member_id FROM member WHERE student_id = '$student_id'");
        if ($result_member->num_rows === 0) {
            continue; // skip if no corresponding member entry
        }

        $member = $result_member->fetch_assoc();
        $member_id = $member['member_id'];

        // Insert into committee table
        $conn->query("INSERT INTO committee (member_id, event_id, committee_role)
                      VALUES ('$member_id', '$event_id', '$position')");
    }

    echo "<script>alert('Committee Added Successfully'); window.location.href='assign_event.php';</script>";
} else {
    echo "Invalid access.";
}
?>
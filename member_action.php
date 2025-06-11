<?php
$conn = new mysqli("localhost", "root", "", "mypetakom");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $memberID = $_POST["memberID"];
    $action = $_POST["action"];

    $statusMap = [
        "accept" => "approved",
        "reject" => "rejected"
    ];

    if (array_key_exists($action, $statusMap)) {
        $newStatus = $statusMap[$action];

        // Check if the member exists
        $checkStmt = $conn->prepare("SELECT member_id FROM member WHERE member_id = ?");
        $checkStmt->bind_param("i", $memberID);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows === 0) {
            echo "Error: Member not found";
            $checkStmt->close();
            $conn->close();
            exit();
        }
        $checkStmt->close();

        // Update member_approval and member_status
        $stmt = $conn->prepare("UPDATE member SET member_approval = ?, member_status = ? WHERE member_id = ?");
        $stmt->bind_param("ssi", $newStatus, $newStatus, $memberID);

        if ($stmt->execute()) {
            echo $newStatus;
        } else {
            echo "Database error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "invalid action";
    }
}

$conn->close();
?>
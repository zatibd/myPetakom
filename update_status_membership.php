<?php
$conn = new mysqli("localhost", "root", "", "mypetakom");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $memberID = $_POST["memberID"]; // ID from frontend (should be 'member_id' in DB)
    $action = $_POST["action"];

    // Map of actions to status
    $statusMap = [
        "accept" => "approved",
        "reject" => "rejected"
    ];

    // Check if the action is valid
    if (array_key_exists($action, $statusMap)) {
        $newStatus = $statusMap[$action];

        // Use prepared statement to update both status columns
        $stmt = $conn->prepare("UPDATE member SET member_status = ?, member_approval = ? WHERE member_id = ?");
        if (!$stmt) {
            echo "Prepare failed: " . $conn->error;
            exit;
        }

        $stmt->bind_param("ssi", $newStatus, $newStatus, $memberID);

        if ($stmt->execute()) {
            echo $newStatus; // Send status back to JavaScript
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

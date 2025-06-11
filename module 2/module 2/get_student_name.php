<?php
$conn = new mysqli("localhost", "root", "", "mypetakom");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id = $conn->real_escape_string($_POST['student_id']);

    $query = "SELECT user_name FROM user WHERE user_id = '$student_id'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(["success" => true, "name" => $row['user_name']]);
    } else {
        echo json_encode(["success" => false]);
    }
}
?>
<?php
session_start();
$conn = new mysqli("localhost", "root", "", "mypetakom");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (
    isset($_POST['student_profile_action']) &&
    isset($_FILES['student_card']) &&
    $_FILES['student_card']['error'] === UPLOAD_ERR_OK
) {
    $user_id = $_SESSION['user_id'];
    $image_tmp_path = $_FILES['student_card']['tmp_name'];
    $image_type = $_FILES['student_card']['type'];

    // Validate file is an image
    if (strpos($image_type, 'image') === false) {
        $_SESSION['message'] = "Uploaded file is not a valid image.";
        header("Location: student_profile.php");
        exit();
    }

    // Read the image content
    $image_data = file_get_contents($image_tmp_path);

    // Check if record exists
    $check_sql = "SELECT 1 FROM member WHERE student_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // UPDATE existing row
        $sql = "UPDATE member SET student_card = ?, member_approval = NULL WHERE student_id = ?";
        $stmt = $conn->prepare($sql);
        $null = NULL;
        $stmt->bind_param("bs", $null, $user_id);
        $stmt->send_long_data(0, $image_data);
    } else {
        // INSERT new row
        $sql = "INSERT INTO member (student_id, student_card, member_approval) VALUES (?, ?, NULL)";
        $stmt = $conn->prepare($sql);
        $null = NULL;
        $stmt->bind_param("sb", $user_id, $null);
        $stmt->send_long_data(1, $image_data);
    }

    if ($stmt->execute()) {
        $_SESSION['message'] = "Student card uploaded successfully.";
    } else {
        $_SESSION['message'] = "Upload failed: " . $stmt->error;
    }

    $stmt->close();
    header("Location: student_profile.php");
    exit();
}
?>

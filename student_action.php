<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "mypetakom");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Restrict access to only logged-in students
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$user_sql = "SELECT * FROM user WHERE user_id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("s", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Fetch program and student card image
$program = "Not Available";
$student_card_path = null;
$student_sql = "SELECT student_program, student_card FROM student WHERE student_id = ?";
$student_stmt = $conn->prepare($student_sql);
$student_stmt->bind_param("s", $user_id);
$student_stmt->execute();
$student_result = $student_stmt->get_result();

if ($student_result->num_rows === 1) {
    $program_row = $student_result->fetch_assoc();
    $program = $program_row['student_program'];
    $student_card_path = $program_row['student_card'];
}

// Handle form submission and file upload
if (isset($_POST['save_profile']) && isset($_FILES['student_card']) && $_FILES['student_card']['error'] == 0) {
    $uploadDir = 'uploads/';
    $fileName = uniqid() . '_' . basename($_FILES['student_card']['name']);
    $uploadFile = $uploadDir . $fileName;

    // Create folder if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Move uploaded file
    if (move_uploaded_file($_FILES['student_card']['tmp_name'], $uploadFile)) {
        // Save the file path to DB
        $update_sql = "UPDATE student SET student_card = ? WHERE student_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ss", $uploadFile, $user_id);
        $update_stmt->execute();

        // Refresh the page to show the image
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error uploading file.";
    }
}
?>

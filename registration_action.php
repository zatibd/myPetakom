<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$db = "mypetakom";

// Connect to the database
$data = mysqli_connect($host, $user, $password, $db);
if ($data === false) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $user_id = $_POST['user_id'];
    $user_password = $_POST['user_password'];
    $user_name = $_POST['user_name'];
    $user_email = $_POST['user_email'];
    $user_phone = $_POST['user_phone'];
    $user_role = $_POST['user_role'];
    $user_program = isset($_POST['user_program']) ? $_POST['user_program'] : null;

    // Hash the password
    $hashed_password = md5($user_password);

    // Insert into user table
    $stmt = $data->prepare("INSERT INTO user (user_id, user_password, user_name, user_email, user_phone, user_role) VALUES (?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        die("User insert prepare failed: " . $data->error);
    }

    $stmt->bind_param("ssssss", $user_id, $hashed_password, $user_name, $user_email, $user_phone, $user_role);

    if ($stmt->execute()) {
        // Insert into student table
        if (strtolower($user_role) === "student" && !empty($user_program)) {
            $stmt2 = $data->prepare("INSERT INTO student (student_id, student_program) VALUES (?, ?)");

            if (!$stmt2) {
                die("Student insert prepare failed: " . $data->error);
            }

            $stmt2->bind_param("ss", $user_id, $user_program);
            $stmt2->execute();
            $stmt2->close();
        }

        // Insert into staff table
        elseif (stripos($user_role, "staff") !== false) {
            $stmt3 = $data->prepare("INSERT INTO staff (staff_id, staff_role) VALUES (?, ?)");

            if (!$stmt3) {
                die("Staff insert prepare failed: " . $data->error);
            }

            $stmt3->bind_param("ss", $user_id, $user_role);
            $stmt3->execute();
            $stmt3->close();
        }

        $stmt->close();
        mysqli_close($data);

        // Redirect to login
        header("Location: login.php");
        exit();
    } else {
        echo "Error inserting user record: " . $stmt->error;
    }
}
?>

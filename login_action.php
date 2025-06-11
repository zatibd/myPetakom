<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "mypetakom", 3306);

// Check if database connection failed
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $password = $_POST['user_password'];
    $user_role = $_POST['user_role'];

    // Hash the entered password using MD5
    $hashed_password = md5($password);

    // Validate user credentials
    $query = "SELECT user_id, user_name, user_password, user_role FROM user WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Check password
        if ($hashed_password === $user['user_password']) {
            // Check if user role matches
            if ($user_role === $user['user_role']) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['user_name'];
                $_SESSION['user_role'] = $user['user_role'];

                // Redirect based on user role
                switch ($user_role) {
                    case 'Student':
                        header('Location: student_dashboard.php');
                        break;
                    case 'Staff (Petakom Advisor)':
                        header('Location: staff_dashboard.php');
                        break;
                    case 'Staff (Administrator)':
                        header('Location: administrator_dashboard.php');
                        break;
                }
                exit();
            } else {
                // Role doesn't match
                header("Location: login.php?error=role");
                exit();
            }
        } else {
            // Password incorrect
            header("Location: login.php?error=invalid");
            exit();
        }
    } else {
        // User not found
        header("Location: login.php?error=invalid");
        exit();
    }
}
?>

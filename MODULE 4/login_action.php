<?php
session_start();

// Assuming you have a function to validate user credentials
function validate_user($username, $password) {
    // Replace with your actual validation logic
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
	$hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $user_type = $_POST['user_type'];
	

    if (validate_user($username, $password)) {
        // Store user type in session
        $_SESSION['user_type'] = $user_type;

        // Redirect based on user type
        switch ($user_type) {
            case 'staff':
                header('Location: staff_dashboard.php');
                break;
            case 'student':
                header('Location: student_dashboard.php');
                break;
            case 'administrator':
                header('Location: administrator_dashboard.php');
                break;
            default:
                // Handle unexpected user type
                header('Location: login.php?error=invalid_user_type');
                break;
        }
        exit();
    } else {
        // Handle invalid credentials
        header('Location: login.php?error=invalid_credentials');
        exit();
    }
}
?>

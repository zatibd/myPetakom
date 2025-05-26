<?php
session_start();
$error = '';

if (isset($_GET['error']) && $_GET['error'] === 'invalid') {
    $error = 'Invalid user ID or password. Please try again.';
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="STYLE/login.css">
</head>

<body style="background-image: url('IMAGES/bg-tech.jpg'); background-size: cover; background-position: center;">
    <div class="container">
        <!-- Left side with logo, description, and action buttons -->
        <div class="left-side">
            <img src="IMAGES/LogoPetakom.png" alt="Logo">
            <p class="description">Log in to access your account</p>
            <div class="button-container">
                <button onclick="window.location.href='login.php'">Log In</button>
                <button onclick="window.location.href='registration.php'">Sign Up</button>
            </div>
        </div>

        <!-- Right side with form -->
        <div class="right-side">
            <h1>Login</h1>

            <?php if ($error): ?>
                <p class="error-message"><?php echo $error; ?></p>
            <?php endif; ?>

            <form action="login_action.php" method="POST">
                <label for="user_id">User ID</label>
                <input type="text" id="user_id" name="user_id" placeholder="Enter ID card number" required>

                <label for="user_password">Password</label>
                <input type="password" id="user_password" name="user_password" placeholder="Enter Password" required>

                <label for="user_role">User Role</label>
                <select id="user_role" name="user_role" required>
                    <option value="Student">Student</option>
                    <option value="Staff (Administrator)">Staff (Administrator)</option>
                    <option value="Staff (Petakom Advisor)">Staff (Petakom Advisor)</option>
                </select>

                <button type="submit">Login</button>
            </form>
        </div>
    </div>
</body>
</html>

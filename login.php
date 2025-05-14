<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="STYLE/style.css">
</head>
<body style="background-image: url('IMAGES/bg-tech.jpg'); background-size: cover; background-position: center;">
    <div class="login-container">
        <div class="login-header">
            <img src="IMAGES/LogoPetakom.png" alt="University Logo" class="logo">
            <h2>User Login</h2>
        </div>

        <form action="login_action.php" method="POST" class="login-form">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Username" required>
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Password" required>
            </div>

            <div class="input-group">
                <label for="user-type">User Type</label>
                <select id="user-type" name="user_type" required>
                    <option value="staff">Staff</option>
                    <option value="student">Student</option>
                    <option value="administrator">Administrator</option>
                </select>
            </div>

            <button type="submit" class="login-button">Login</button>
     

        <div class="login-options">
            <div class="links">
                <a href="#">Register Membership</a>
                <span>|</span>
                <a href="#">Forgot password?</a>
            </div>
        </div>
 
        <footer>
            <p>Universiti Malaysia Pahang Al Sultan Abdullah (UMPSA)</p>
        </footer>
    </div>
	 </form>
</body>
</html>

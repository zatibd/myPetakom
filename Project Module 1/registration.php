<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Registration</title>
    <link rel="stylesheet" href="STYLE/registration.css">
</head>

<body style="background-image: url('IMAGES/bg-tech.jpg'); background-size: cover; background-position: center;">
    <div class="container">
        <!-- Left side with logo and info -->
        <div class="left-side">
            <img src="IMAGES/LogoPetakom.png" alt="Logo">
            <p class="description">Join us and be part of a committee of MyPetakom. Sign up today!</p>
            <div class="button-container">
                <button onclick="window.location.href='login.php'">Log In</button>
                <button onclick="window.location.href='registration.php'">Sign Up</button>
            </div>
        </div>

        <!-- Right side with form -->
        <div class="right-side">
            <h1>Account Registration</h1>
            <form action="registration_action.php" method="POST">
                <label for="user_id">User ID</label>
                <input type="text" id="user_id" name="user_id" required>

                <label for="user_name">Name</label>
                <input type="text" id="user_name" name="user_name" required>

                <label for="user_email">Email Address</label>
                <input type="email" id="user_email" name="user_email" required>

                <label for="user_phone">Phone Number</label>
                <input type="tel" id="user_phone" name="user_phone" required>

                <label for="user_password">Password</label>
                <input type="password" id="user_password" name="user_password" required>

                <label for="user_role">User Role</label>
                <select id="user_role" name="user_role" required>
                    <option value="">-- Select Role --</option>
                    <option value="Student">Student</option>
                    <option value="Staff (Administrator)">Staff (Administrator)</option>
                    <option value="Staff (Petakom Advisor)">Staff (Petakom Advisor)</option>
                </select>

                <!-- Student program selection -->
                <div id="program-selection" style="display: none;">
                    <label for="user_program">Student Program</label>
                    <select id="user_program" name="user_program">
                        <option value="">-- Select Program --</option>
                        <option value="BCY">BCY - Bachelor of Computer Science (Cyber Security)</option>
                        <option value="BCS">BCS - Bachelor of Computer Science (Software Engineering)</option>
                        <option value="BCM">BCM - Bachelor of Computer Science (Multimedia Software)</option>
                        <option value="BCN">BCN - Bachelor of Computer Science (Computer Systems & Networking)</option>
                    </select>
                </div>

                <button type="submit">Register</button>
            </form>
        </div>
    </div>

    <script>
        const roleSelect = document.getElementById('user_role');
        const programDiv = document.getElementById('program-selection');

        roleSelect.addEventListener('change', () => {
            if (roleSelect.value.toLowerCase() === 'student') {
                programDiv.style.display = 'block';
                document.getElementById('user_program').setAttribute('required', 'required');
            } else {
                programDiv.style.display = 'none';
                document.getElementById('user_program').removeAttribute('required');
            }
        });
    </script>
</body>
</html>

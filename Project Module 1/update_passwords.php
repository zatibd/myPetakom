<?php
include('config.php'); // Include the database configuration

// Fetch all users from the user_login table
$stmt = $pdo->query("SELECT * FROM user_login");
$users = $stmt->fetchAll();

foreach ($users as $user) {
    // Hash the user's password
    $hashed_password = password_hash($user['user_password'], PASSWORD_DEFAULT);

    // Update the password in the database with the hashed password
    $update_stmt = $pdo->prepare("UPDATE user_login SET user_password = :hashed_password WHERE user_id = :user_id");
    $update_stmt->execute([
        ':hashed_password' => $hashed_password,
        ':user_id' => $user['user_id']
    ]);

    echo "Password for user ID {$user['user_id']} updated successfully.<br>";
}

echo "Password update complete.";
?>

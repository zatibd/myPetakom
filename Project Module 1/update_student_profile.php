<?php
session_start();  // Start the session to access user data
include("student_action.php"); // Include any necessary actions

// Check if the form is submitted
if (isset($_POST['save_profile'])) {
    $user_id = $_POST['user_id']; // Retrieve the student ID
    $user_name = $_POST['user_name'];
    $user_email = $_POST['user_email'];
    $program = $_POST['program'];
    $user_phone = $_POST['user_phone'];
    $user_password = $_POST['user_password'];
    $user_role = $_POST['user_role'];

    // Handle student card image upload
    if ($_FILES['student_card']['name']) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["student_card"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the file is an image
        $check = getimagesize($_FILES["student_card"]["tmp_name"]);
        if ($check === false) {
            echo "File is not an image.";
            $uploadOk = 0;
        }

        // Check file size (limit to 5MB)
        if ($_FILES["student_card"]["size"] > 5000000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow specific file formats (jpg, jpeg, png)
        if ($imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "png") {
            echo "Sorry, only JPG, JPEG, & PNG files are allowed.";
            $uploadOk = 0;
        }

        // Check if upload is OK
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
        } else {
            // Upload the image
            if (move_uploaded_file($_FILES["student_card"]["tmp_name"], $target_file)) {
                echo "The file " . htmlspecialchars(basename($_FILES["student_card"]["name"])) . " has been uploaded.";
                $student_card_path = $target_file;  // Save the file path
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }

    // Update the student's profile in the database
    $query = "UPDATE students SET user_name = '$user_name', user_email = '$user_email', program = '$program', 
              user_phone = '$user_phone', user_password = '$user_password', user_role = '$user_role'";

    // If a student card was uploaded, include it in the query
    if (isset($student_card_path)) {
        $query .= ", student_card = '$student_card_path'"; 
    }

    $query .= " WHERE user_id = '$user_id'";

    if (mysqli_query($link, $query)) {
        echo "Profile updated successfully.";
    } else {
        echo "Error updating profile: " . mysqli_error($link);
    }
}
?>

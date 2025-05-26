<?php
// Connect to the database server
$link = mysqli_connect("localhost", "root", "") or die(mysqli_connect_error());

// Select the database
mysqli_select_db($link, "mypetakom") or die(mysqli_error($link)); // Change db name accordingly

// Get the ID from the URL
if (!isset($_GET['id'])) {
    die("Missing attendance slot ID.");
}

$idURL = $_GET['id'];

// SQL query to delete the record
$query = "DELETE FROM user WHERE user_id = '$idURL'";

// Execute the delete query
$result = mysqli_query($link, $query);

// Redirect or show alert if successful
if ($result) {
    echo "<script type='text/javascript'> alert('Record deleted successfully.'); window.location='member.php'; </script>";
} else {
    echo "Error deleting record: " . mysqli_error($link);
}
?>
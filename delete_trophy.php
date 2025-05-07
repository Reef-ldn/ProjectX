<!-- This script handles deleting trophies on the edit profile page -->

<?php
session_start();
//Make sure the user is logged in
if (!isset($_SESSION['user_id'])) {
  die("Unauthorised access");
}

//Connect to the database
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Database connection failed: " . $conn->connect_error);
}

//Variables
$trophyId = (int) $_POST['trophy_id'];    //Trophy ID
$userId = $_SESSION['user_id'];     //UserID is the session

//Remove the trophy from the database
$conn->query("DELETE FROM trophies WHERE id = '$trophyId' AND user_id = '$userId'");

//Refresh the edit_profile page
header("Location: edit_profile.php");
exit;
?>

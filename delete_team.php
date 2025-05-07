<!-- This script handles deleting previous teams on the edit profile page -->

<?php
session_start();

//Make sure the user is logged in
if (!isset($_SESSION['user_id'])) {
  die("Unauthorised access");
}

//Connect to the database
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

//If the user requests to delete, do it, if not, do nothing
if (isset($_GET['team_id'])) {
  //Vraiables
  $teamId = (int) $_GET['team_id']; //Get the Team ID of the team they want to delete
  $userId = $_SESSION['user_id'];   //UserID of the currently logged in user
  
  //Query to remove the team from the 'previous_teams' table in the database
  //but only if it belongs to the logged in user
  $conn->query("DELETE FROM previous_teams WHERE id = $teamId AND user_id = $userId");

  //refresh the edit_profile page
  header("Location: edit_profile.php");
  exit;
}

?>

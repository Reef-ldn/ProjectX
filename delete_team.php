<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  die("Unauthorised access");
}

$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}


if (isset($_GET['team_id'])) {
  $teamId = (int) $_GET['team_id'];
  $userId = $_SESSION['user_id'];
  $conn->query("DELETE FROM previous_teams WHERE id = $teamId AND user_id = $userId");
  header("Location: edit_profile.php");
  exit;
}

?>

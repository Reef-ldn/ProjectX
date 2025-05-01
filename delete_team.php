<?php
session_start();
if (!isset($_SESSION['user_id'])) die("Unauthorized");

$conn = new mysqli("localhost", "root", "", "projectx_db");
$team_id = (int) $_POST['team_id'];
$user_id = $_SESSION['user_id'];

$conn->query("DELETE FROM previous_teams WHERE id = '$team_id' AND user_id = '$user_id'");
header("Location: edit_profile.php");
exit;
?>

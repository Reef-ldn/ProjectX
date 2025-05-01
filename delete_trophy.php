<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  die("Unauthorised access");
}

$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Database connection failed: " . $conn->connect_error);
}

$trophyId = (int) $_POST['trophy_id'];
$userId = $_SESSION['user_id'];

$conn->query("DELETE FROM trophies WHERE id = '$trophyId' AND user_id = '$userId'");

header("Location: edit_profile.php");
exit;
?>

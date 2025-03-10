<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  die("You must be logged in to like a post.");
}

$userID = $_SESSION['user_id'];
$postID = $_GET['post_id'] ?? 0;
$action = $_GET['action'] ?? '';

$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if ($action === 'like') {
  // Insert row if user hasn't liked yet
  $checkSql = "SELECT * FROM likes WHERE user_id='$userID' AND post_id='$postID'";
  $checkRes = $conn->query($checkSql);
  if ($checkRes->num_rows == 0) {
    $insertSql = "INSERT INTO likes (user_id, post_id, created_at)
                  VALUES ('$userID','$postID',NOW())";
    $conn->query($insertSql);
  }
} elseif ($action === 'unlike') {
  // Remove row if it exists
  $deleteSql = "DELETE FROM likes WHERE user_id='$userID' AND post_id='$postID'";
  $conn->query($deleteSql);
}

$conn->close();
header("Location: feed.php");
exit;

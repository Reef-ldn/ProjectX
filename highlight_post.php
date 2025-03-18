<?php
session_start();
if(!isset($_SESSION['user_id'])) {
  die("Please log in first!");
}
$loggedUser = $_SESSION['user_id'];

$postID = $_GET['post_id'] ?? 0;
$action = $_GET['action'] ?? '';

$conn = new mysqli("localhost","root","","projectx_db");
if($conn->connect_error){
  die("Connection failed:" . $conn->connect_error);
}

// Check post owner
$checkSql = "SELECT user_id FROM posts WHERE id='$postID'";
$checkRes = $conn->query($checkSql);
$row = $checkRes->fetch_assoc();
$postOwner = $row['user_id'] ?? 0;

// Only let the owner highlight or unhighlight
if($postOwner != $loggedUser){
  die("You don’t own this post!");
}

if($action === 'add'){
  // is_highlight=1
  $updateSql = "UPDATE posts SET is_highlight=1 WHERE id='$postID'";
  $conn->query($updateSql);
} elseif($action === 'remove'){
  // is_highlight=0
  $updateSql = "UPDATE posts SET is_highlight=0 WHERE id='$postID'";
  $conn->query($updateSql);
}

$conn->close();

// redirect back to feed or profile
header("Location: feed.php");
exit;
?>

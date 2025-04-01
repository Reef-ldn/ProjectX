<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  die("You must be logged in to delete a post.");
}

$loggedInUserId = $_SESSION['user_id'];
$postId = (int) $_GET['post_id'];

// Connect to DB
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Database connection failed: " . $conn->connect_error);
}

// Check if the post belongs to the logged-in user
$checkSql = "SELECT * FROM posts WHERE id = '$postId' AND user_id = '$loggedInUserId'";
$result = $conn->query($checkSql);

if ($result && $result->num_rows === 1) {
  // Delete post
  $deleteSql = "DELETE FROM posts WHERE id = '$postId'";
  if ($conn->query($deleteSql)) {
    header("Location: profile.php?user_id=$loggedInUserId&message=Post+Deleted");
    exit;
  } else {
    echo "Error deleting post: " . $conn->error;
  }
} else {
  echo "You are not authorised to delete this post.";
}

$conn->close();
?>

<!-- This page handles deleting posts -->

<?php
session_start();

//Make sure the user is logged in
if (!isset($_SESSION['user_id'])) {
  die("You must be logged in to delete a post.");
}

//Variables
$loggedInUserId = $_SESSION['user_id'];   //Logged in user's id
$postId = (int) $_GET['post_id'];       //Id of the post the user wants to delete

// Connect to DB
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Database connection failed: " . $conn->connect_error);
}

// Check if the post belongs to the logged-in user
$checkSql = "SELECT * FROM posts WHERE id = '$postId' AND user_id = '$loggedInUserId'";
$result = $conn->query($checkSql);

//If the post does belong to the user
if ($result && $result->num_rows === 1) {

   // Delete all comments on this post from the comments table
   $conn->query("DELETE FROM comments WHERE post_id = '$postId'");
   //  delete all likes on this post from the likes table
   $conn->query("DELETE FROM likes WHERE post_id = '$postId'");

  // Delete post from the posts table
  $deleteSql = "DELETE FROM posts WHERE id = '$postId'";
  if ($conn->query($deleteSql)) {
    header("Location: profile.php?user_id=$loggedInUserId&deleted=1");
    exit;
  } else {
    echo "Error deleting post: " . $conn->error;
  }
} else {
  echo "You are not authorised to delete this post.";
}

$conn->close();
?>

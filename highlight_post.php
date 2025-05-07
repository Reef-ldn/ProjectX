<!-- Script to handle highlights on posts -->

<?php
session_start();

//Make sure the user is logged in
if (!isset($_SESSION['user_id'])) {
  die("Please log in first!");
}

//Variables
$loggedUser = $_SESSION['user_id'];   //Set the loggedin user as the session 
$postID = $_GET['post_id'] ?? 0;    //Post ID
$action = $_GET['action'] ?? '';      //Action (add or remove)

//Connect to the database
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Connection failed:" . $conn->connect_error);
}

// Check the post belongs to the logged in user
$checkSql = "SELECT user_id FROM posts WHERE id='$postID'";
$checkRes = $conn->query($checkSql);
$row = $checkRes->fetch_assoc();
$postOwner = $row['user_id'] ?? 0;

// Only let the owner highlight or unhighlight
if ($postOwner != $loggedUser) {
  die("You don’t own this post!");
}

//Update the database to set or unset the "highlight" flag
if ($action === 'add') {
  // Mark the post as highlighted (is_highlight = 1)
  $updateSql = "UPDATE posts SET is_highlight=1 WHERE id='$postID'";
  $conn->query($updateSql);
} elseif ($action === 'remove') {
  // Remove the highlight from the post (is_highlight = 0)
  $updateSql = "UPDATE posts SET is_highlight=0 WHERE id='$postID'";
  $conn->query($updateSql);
}
$conn->close();

// redirect back to where teh user came from
$redirectBack = $_SERVER['HTTP_REFERER'] ?? 'feed.php';
header("Location: $redirectBack");
exit;
?>
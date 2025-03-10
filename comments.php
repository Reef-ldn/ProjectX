<!-- This script allows the user to comment on videos-->

<?php
session_start();
//Ensures the user is logged in before allowing them to comment
if (!isset($_SESSION['user_id'])) {
  die("You must be logged in to comment!");    //Kills the session if they're not
}

//Get the user's ID from the session
$user_id = $_SESSION['user_id'];

//Getting the post_id and comment_text from the database (through POST)
$post_id = $_POST['post_id'];
$comment_text = $_POST['comment_text'];
//$user_id = $_POST['user_id'];


//Connect to the db
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Failed to connect to the database: " . $conn->connect_error);  //Kills the session
}

//Insert into the comments table in the db
$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment_text, created_at)
         VALUES (?, ?, ? , NOW())");   //Use prepare and placeholders to allow the user to type apostrophes

//there's 2 ints (vid_id and user id) and 1 string ?(comment_text), so the format string is iis (i = int, s = string)
$stmt->bind_param("iis", $post_id, $user_id, $comment_text);

//execute statement
if ($stmt->execute()) {    //queries the database
  echo "Comment posted successfully! ";
} else {
  echo "Error, could not post your comment: " . $stmt->error;
}

$stmt->close();    //close statement
$conn->close();     //done communicating with the db

//redirect back to the feed
header("Location: feed.php");
exit;
?>
<?php
session_start();
//Make sure the user is logged in
if (!isset($_SESSION['user_id'])) {
  die("You must be logged in to like a post.");
}

//Variables
$userID = $_SESSION['user_id'];     //User ID
$postID = $_GET['post_id'] ?? 0;    //Post ID
$action = $_GET['action'] ?? '';    //Like or Unlike

//Connect to the database
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

//If the post is being liked
if ($action === 'like') {
  //Query likes table to check if the user has liked already
  $checkSql = "SELECT * FROM likes WHERE user_id='$userID' AND post_id='$postID'";
  $checkRes = $conn->query($checkSql);

  //If the user hasn't liked already
  if ($checkRes->num_rows == 0) {
    //Insert like row into the database
    $insertSql = "INSERT INTO likes (user_id, post_id, created_at) VALUES ('$userID','$postID',NOW())";
    $conn->query($insertSql);
  }
  //If the user has liked already
} elseif ($action === 'unlike') {
  // Remove the like row from the database
  $deleteSql = "DELETE FROM likes WHERE user_id='$userID' AND post_id='$postID'";
  $conn->query($deleteSql);
}

$conn->close();

//Return JSON response
$redirectBack = $_SERVER['HTTP_REFERER'] ?? 'feed.php';

// Append a flag 
$glue = strpos($redirectBack, '?') !== false ? '&' : '?';
$redirectBack .= "{$glue}liked=1";

//This sends a success response back to the Javascript(AJAX)
header('Content-Type: application/json');
echo json_encode([
  'status' => 'success',
  'liked' => ($action === 'like'), //Tells JS if the post is now liked or unliked
  'post_id' => $postID
]);
exit; //End script


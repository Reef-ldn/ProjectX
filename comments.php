<?php
// This script allows the user to comment on videos
session_start();
header('Content-Type: application/json');

//Ensures the user is logged in before allowing them to comment
if (!isset($_SESSION['user_id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Not logged in']);   //Kills the session if they're not
  exit;
}

//Get the user's ID from the session
$user_id = $_SESSION['user_id'];

//Getting the post_id and comment_text from the database (through POST)
$post_id = $_POST['post_id'] ?? 0;
$comment_text = $_POST['comment_text'] ?? '';
//$user_id = $_POST['user_id'];

if (empty($comment_text)) {
  echo json_encode(['status' => 'error', 'message' => 'Comment cannot be empty']);
  exit;
}

//Connect to the db
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  echo json_encode(['status' => 'error', 'message' => 'DB connection failed']); //kills the session
  exit;
}

//Insert into the comments table in the db
$insertSql = "INSERT INTO comments (user_id, post_id, comment_text, created_at)
              VALUES ('$user_id', '$post_id', '$comment_text', NOW())";

if ($conn->query($insertSql)) {
  // Get the commenter’s username
  $userRes = $conn->query("SELECT username FROM users WHERE id = '$user_id'");
  $username = $userRes->fetch_assoc()['username'] ?? 'unknown';

  echo json_encode([
    'status' => 'success',
    'username' => $username,
    'comment_text' => $comment_text,
    'created_at' => date('Y-m-d H:i')
  ]);
} else {
  echo json_encode(['status' => 'error', 'message' => 'Comment insert failed']);
}

//$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment_text, created_at)
//         VALUES (?, ?, ? , NOW())");   //Use prepare and placeholders to allow the user to type apostrophes

//there's 2 ints (vid_id and user id) and 1 string ?(comment_text), so the format string is iis (i = int, s = string)
//$stmt->bind_param("iis", $post_id, $user_id, $comment_text);

//execute statement
//if ($stmt->execute()) {    //queries the database
//  echo "Comment posted successfully! ";
//} else {
//  echo "Error, could not post your comment: " . $stmt->error;
//}

//$stmt->close();    //close statement
$conn->close();     //done communicating with the db
exit;

//redirect back to the feed
// $redirectBack = $_SERVER['HTTP_REFERER'] ?? 'feed.php';
// header("Location: $redirectBack");
// exit;

?>
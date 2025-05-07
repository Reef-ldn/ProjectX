<?php
session_start();
header('Content-Type: application/json'); //JSON

//Make sure the user is logged in
if (!isset($_SESSION['user_id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
  exit;
}

//Variables
$user_id = $_SESSION['user_id'];      //User ID
$post_id = $_POST['post_id'] ?? 0;    //Post ID
$reply_text = $_POST['reply_text'] ?? '';   //The reply text content
$parent_id = $_POST['parent_id'] ?? null;   //The parent of the comment

//Make sure the reply is not null
if (empty($reply_text)) {
  echo json_encode(['status' => 'error', 'message' => 'Reply cannot be empty']);
  exit;
}

//Connect to the db
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
  exit;
}

//Prepared statement to insert the reply into the comment table
$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment_text, created_at, parent_id) 
                                      VALUES (?, ?, ?, ?, NOW())");
//postID = int, userID = int, reply_text = string, parentID = int
$stmt->bind_param("iisi", $post_id, $user_id, $reply_text, $parent_id);

//Respond with success if it worked
if ($stmt->execute()) {
  echo json_encode(['status' => 'success']);
} else {    //If not respond with an error
  echo json_encode(['status' => 'error', 'message' => 'Failed to insert reply']);
}

$stmt->close();
$conn->close();
exit;

?>
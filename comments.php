<?php
session_start();
header('Content-Type: application/json'); //Tells the browser we're returning JSON

//Ensures the user is logged in before allowing them to comment
if (!isset($_SESSION['user_id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Not logged in']);   //Kills the session if they're not
  exit;
}

//Get the user's ID from the session
$user_id = $_SESSION['user_id'];
//Getting the post_id and comment_text from the form submission(through POST)
$post_id = $_POST['post_id'] ?? 0;
$comment_text = $_POST['comment_text'] ?? '';

//If the user submits an empty comment, it's rejected
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

//Using a prepared statement to insert comments safely into the DB (Prevents SQL injection and apostrophe issues)
$stmt = $conn->prepare("
  INSERT INTO comments (user_id, post_id, comment_text, created_at)
  VALUES (?, ?, ?, NOW())");
/*
This prepared statement automatically escapes speical characters like apostrophes
and it also protects the database from SQL Injection attacks 
*/

// "iis" stands for int, int, string, which is all the types of the values we're binding
$stmt->bind_param("iis", $user_id, $post_id, $comment_text);

// Execute the query and handle the result
if ($stmt->execute()) {
  // Get the username of the person who commented
  $userRes = $conn->query("SELECT username FROM users WHERE id = '$user_id'");
  $username = $userRes->fetch_assoc()['username'] ?? 'unknown';
  // Check if the request is AJAX
  $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
  //If the response was sent through AJAX, send a JSON response
  if ($isAjax) {
    echo json_encode(['status' => 'success', 'redirect' => "view_post.php?post_id=$post_id"]);
  } else { 
    //If the request came from a regular form submission, redirect the user back to the post page
    header("Location: view_post.php?post_id=$post_id");
  }
  exit;
} else {  
  echo json_encode(['status' => 'error', 'message' => 'Failed to save comment']);
}

// // Check if the request was sent through JavaScript (AJAX)
// if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
//     strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
//   // Return JSON for AJAX
//   echo json_encode([
//     'status' => 'success',
//     'username' => $username,
//     'comment_text' => $comment_text,
//     'created_at' => date('Y-m-d H:i')
//   ]);
// } else {
//   // If it's a regular form submission (not AJAX), redirect to view_post.php
//   header("Location: view_post.php?post_id=" . $post_id);
// }
// } else {
//   echo json_encode(['status' => 'error', 'message' => 'Comment insert failed']);
// }



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



$stmt->close();    //close statement
$conn->close();     //done communicating with the db

//redirect back to the feed
// $redirectBack = $_SERVER['HTTP_REFERER'] ?? 'feed.php';
// header("Location: $redirectBack");
// exit;

?>
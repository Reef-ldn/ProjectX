<!--This script handles sending posts from user to user-->
<?php
session_start();

//Connect to the db
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

//Variables
$sender_id = $_SESSION['user_id'] ?? 0; //The logged in user's ID
$recipient_id = (int) $_POST['recipient_id'];   //recipient's ID
$post_id = (int) $_POST['post_id'];   //The ID of the post being sent

//If all of these align, send the post
if ($sender_id && $recipient_id && $post_id) {
  //Query to send the post as a message, inserts into the messages table (prepared stmt)
  $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content, shared_post_id, created_at)
                          VALUES (?, ?, NULL, ?, NOW())");
  $stmt->bind_param("iii", $sender_id, $recipient_id, $post_id);

  //Once sent, redirect back to the feed
  if ($stmt->execute()) {
    header("Location: feed.php?shared=success");
    exit;
  } else {  //If the message wasn't sent
    echo "Failed to send post.";
  }

  $stmt->close();
} else {  //completely failed
  echo "Missing data.";
}

$conn->close();
?>
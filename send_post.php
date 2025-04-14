<!--This script handles sending posts from user to user-->
<?php
session_start();
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sender_id = $_SESSION['user_id'] ?? 0;
$recipient_id = (int) $_POST['recipient_id'];
$post_id = (int) $_POST['post_id'];


if ($sender_id && $recipient_id && $post_id) {
  $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content, shared_post_id, created_at)
                          VALUES (?, ?, NULL, ?, NOW())");
  $stmt->bind_param("iii", $sender_id, $recipient_id, $post_id);

  if ($stmt->execute()) {
    header("Location: feed.php?shared=success");
    exit;
  } else {
    echo "Failed to send post.";
  }

  $stmt->close();
} else {
  echo "Missing data.";
}

$conn->close();
?>

<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
  exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'] ?? 0;
$reply_text = $_POST['reply_text'] ?? '';
$parent_id = $_POST['parent_id'] ?? null;

if (empty($reply_text)) {
  echo json_encode(['status' => 'error', 'message' => 'Reply cannot be empty']);
  exit;
}

$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
  exit;
}

$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment_text, created_at, parent_id) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("iisi", $post_id, $user_id, $reply_text, $parent_id);


if ($stmt->execute()) {
  echo json_encode(['status' => 'success']);
} else {
  echo json_encode(['status' => 'error', 'message' => 'Failed to insert reply']);
}

$stmt->close();
$conn->close();
exit;

?>
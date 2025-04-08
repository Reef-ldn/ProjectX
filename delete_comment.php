<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
  exit;
}

$commentID = $_POST['comment_id'] ?? 0;
$userID = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
  exit;
}

// Only delete if the user owns the comment
$sql = "DELETE FROM comments WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $commentID, $userID);
$stmt->execute();

if ($stmt->affected_rows > 0) {
  echo json_encode(['status' => 'success']);
} else {
  echo json_encode(['status' => 'error', 'message' => 'Unauthorized or not found']);
}
$conn->close();
?>


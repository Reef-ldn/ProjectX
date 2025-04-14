<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "projectx_db");

if ($conn->connect_error) {
  echo json_encode(['status' => 'error']);
  exit;
}

$postID = (int) ($_GET['post_id'] ?? 0);
$sql = "SELECT COUNT(*) AS like_count FROM likes WHERE post_id = $postID";
$res = $conn->query($sql);

if ($res && $row = $res->fetch_assoc()) {
  echo json_encode(['status' => 'success', 'like_count' => $row['like_count']]);
} else {
  echo json_encode(['status' => 'error']);
}

$conn->close();

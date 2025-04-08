<?php
session_start();
if (!isset($_SESSION['user_id'])) exit;

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'] ?? 0;
$reply_text = $_POST['reply_text'] ?? '';
$parent_id = $_POST['parent_id'] ?? null;

$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) exit;

$stmt = $conn->prepare("INSERT INTO comments (user_id, post_id, comment_text, created_at, parent_id)
                        VALUES (?, ?, ?, NOW(), ?)");
$stmt->bind_param("iisi", $user_id, $post_id, $reply_text, $parent_id);
$stmt->execute();

echo json_encode(['status' => 'success']);

?>
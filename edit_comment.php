<?php
session_start();
if (!isset($_SESSION['user_id'])) exit;

$commentID = $_POST['comment_id'] ?? 0;
$newText = $_POST['comment_text'] ?? '';
$userID = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) exit;

$stmt = $conn->prepare("UPDATE comments SET comment_text = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("sii", $newText, $commentID, $userID);
$stmt->execute();

echo json_encode(['status' => 'success']);
?>
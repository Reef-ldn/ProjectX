<?php
session_start();

//Make sure the user is logged in
if (!isset($_SESSION['user_id'])) exit;

//Variables
$commentID = $_POST['comment_id'] ?? 0;     //CommentID
$newText = $_POST['comment_text'] ?? '';    //The New Text
$userID = $_SESSION['user_id'];             //UserID

//Connect to the db
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) exit;

//Insert the new comment into the comments table (prepare statement)
$stmt = $conn->prepare("UPDATE comments SET comment_text = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("sii", $newText, $commentID, $userID);
$stmt->execute();

//Confirm the edit was successful
echo json_encode(['status' => 'success']);
?>
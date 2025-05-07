<?php
session_start();

//Make sure the user is logged in
if (!isset($_SESSION['user_id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
  exit;
}

//Variables
$commentID = $_POST['comment_id'] ?? 0;   //Comment ID that the user wants to delete
$userID = $_SESSION['user_id'];     //The user's ID 

//Connect to the database
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
  exit;
}

// Only delete if the user owns the comment
$sql = "DELETE FROM comments WHERE id = ? AND user_id = ?"; //Query to delete the comment
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $commentID, $userID);
$stmt->execute();

//If the comment delete was successful
if ($stmt->affected_rows > 0) {
  echo json_encode(['status' => 'success']);    //Show success
} else {    //Show error
  echo json_encode(['status' => 'error', 'message' => 'Unauthorised or not found']);
}
$conn->close();
?>


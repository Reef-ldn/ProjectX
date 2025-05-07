<?php
header('Content-Type: application/json'); //JSON since we're sending data back to javascriptt

//Connect to the db
$conn = new mysqli("localhost", "root", "", "projectx_db");
//Make sure the database is actually connected
if ($conn->connect_error) {
  echo json_encode(['status' => 'error']);    //Return an error if connection fails
  exit;
}

$postID = (int) ($_GET['post_id'] ?? 0);  //Get the Post ID from the URL
//Query to get the like_count from the likes table where the post ID is this post
$sql = "SELECT COUNT(*) AS like_count FROM likes WHERE post_id = $postID";
$res = $conn->query($sql);

//If the query worked, return the number of likes as JSON
if ($res && $row = $res->fetch_assoc()) {
  echo json_encode(['status' => 'success', 'like_count' => $row['like_count']]);
} else {
  //If the query failed, show an error
  echo json_encode(['status' => 'error']);
}

$conn->close();

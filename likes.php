<!-- This Scipt allows the user to like a video-->

<?php
session_start();

//Ensure the user is logged in before allowing them to like content
if (!isset($_SESSION['user_id'])) {
  die("You must be logged in to like a video!");    //Kills the session if they're not
}

//Variables
$post_id = $_GET['post_id'] ?? 0;     //Post ID
$user_id = $_SESSION['user_id'];    //User ID

//Connect to the DB
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Failed to connect to the database: " . $conn->connect_error);  //Kills the session
}

//Insert a new row in the 'likes' table
$sql = "INSERT INTO likes (user_id, post_id, created_at) VALUES ('$user_id' , '$post_id' , NOW() )";
if ($conn->query($sql) === TRUE) {    //If the like is successful
  echo "Liked successfully!";     //For testing only, won't be shown to users
} else {
  echo "Error: " . $conn->error;      //if the like isn't successful, show an error
}
$conn->close();   //Done communicating with the db

//redirect back to where they came from
$redirectBack = $_SERVER['HTTP_REFERER'] ?? 'feed.php';
header("Location: $redirectBack");
exit;

?>
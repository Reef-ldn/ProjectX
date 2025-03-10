<!--This page handles following user functionality-->
<?php
session_start();
//Check if the user is logged in before allowing them to follow a user.
if (!isset($_SESSION['user_id'])) {
  die("You must be logged in to follow a user.");   //If they're not logged in, kill the sesssion
}

//Get the session ID and get the followed user id
$follower_id = $_SESSION['user_id'];
$followed_id = $_GET['followed_id'] ?? 0;
$action = $_GET['action'] ?? '';

//Connect to the db
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Failed to connect to the database: " . $conn->connect_error);
}


//If the user wants to 'follow' 
if ($action == 'follow') {
  //Check if they already follow that user (matching row in the follows table)
  $sqlCheck = "SELECT * FROM follows 
                  WHERE follower_id='$follower_id'
                  AND followed_id='$followed_id' ";
  $res = $conn->query($sqlCheck);

  //If they don't follow this user, insert the follow
  if ($res->num_rows == 0) {
    $insertSql = "INSERT INTO follows (follower_id, followed_id, created_at)
          VALUES ('$follower_id', '$followed_id', NOW() )";
    $conn->query($insertSql);
  }
}

//If the user wants to 'unfollow'
else if ($action == 'unfollow') {
  //delete row
  $deleteSql = "DELETE FROM follows
                  WHERE follower_id ='$follower_id'
                  AND followed_id = '$followed_id' ";
  $conn->query($deleteSql);
}

//redirect back to proiile page
header("Location: profile.php?user_id=$followed_id");
exit;


?>
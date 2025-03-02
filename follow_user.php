<!--This page handles following user functionality-->

<?php
  session_start();
  //Check if the user is logged in before allowing them to follow a user.
  if(!isset($_SESSION['user_id'])) {
    die("You must be logged in to follow a user.");   //If they're not logged in, kill the sesssion
  }

  //Get the session ID and get the followed user id
  $follower_id = $_SESSION['user_id'];
  $followed_id = $_GET['followed_id'] ?? 0;

  //Connect to the db
  $conn = new mysqli("localhost", "root", "", "projectx_db");
  if($conn->connect_error) {
    die("Failed to connect to the database: " . $conn->connect_error);
  }

  //Insert the new follow into the 'follows' table
  //First check if the user already follows the user, if they are, skip.
  $sql = "SELECT * FROM follows WHERE follower_id=? AND followed_id=?
          AND INSERT INTO follows (follower_id, followed_id, created at)
          VALUES ('$follower_id', '$followed_id', NOW() )";
          $conn->query($sql);

  //redirect back to proiile page
  header("Location: profile.php?user_id=$followed_id");
  exit;

  
?>

<!--To see 'who follows me' = SELECT followed_id FROM follows WHERE follower_id='follower_id'-->
<!--To see 'who I follow' = SELECT follower_id FROM follows WHERE followed_id='$my_id'--> 
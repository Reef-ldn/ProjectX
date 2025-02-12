<!-- This file allows the user to like a video--> 

<?php
 session_start();

 //Ensure the user is logged in before allowing them to like content
 if(!isset($_SESSION['user_id'])) {
  die("You must be logged in to like a video!");    //Kills the session if they're not
 }



 //Getting the video's ID
 $video_id = $_GET['video_id'] ?? 0;   

 //Connect to the DB
 $user_id = $_SESSION['user_id'];
 $conn = new mysqli("localhost", "root", "" , "projectx_db");
 if($conn->connect_error) {
   die("Failed to connect to the database: " . $conn->connect_error);  //Kills the session
 }

 //Insert a new row in the 'likes' table
 $sql = "INSERT INTO likes (user_id, video_id, created_at)
        VALUES ('$user_id' , '$video_id' , NOW() )" ; 
 if($conn->query($sql) === TRUE ) {    //If the like is successful
   echo "Liked successfully!";
 } else {    
   echo "Error: " . $conn->error;      //if the like isn't successful
 }
 $conn->close();   //Done communicating with the db

 //redirect back to the feed
 header("Location: feed.php");
 exit;
?>
<!-- This script allows the user to comment on videos--> 
 
<?php
 session_start();
 //Ensures the user is logged in before allowing them to comment
 if(!isset($_SESSION['user_id'])) {
  die("You must be logged in to comment!");    //Kills the session if they're not
 }

 //Get the user's ID from the session
 $user_id = $_SESSION['user_id'];

 //Getting the video_id and comment_text from the database (through POST)
 $video_id = $_POST['video_id'];
 $comment_text = $_POST['comment_text'];
 //$user_id = $_POST['user_id'];


 //Connect to the db
 $conn = new mysqli("localhost", "root", "" , "projectx_db");
 if($conn->connect_error) {
  die("Failed to connect to the database: " . $conn->connect_error);  //Kills the session
}

 //Insert into the comments table in the db
 $sql = "INSERT INTO comments (video_id, user_id, comment_text, created_at)
         VALUES ('$video_id' , '$user_id' , '$comment_text' , NOW() )" ;   

 if($conn->query($sql) === TRUE) {    //queries the database
   echo "Comment posted successfully! " ;
 } else {
   echo "Error, could not post your comment: " . $conn->error; 
 }
 $conn->close();     //done communicating with the db

 //redirect back to the feed
 header("Location: feed.php");
 exit;
?>
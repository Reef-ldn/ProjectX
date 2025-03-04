<!-- This script handles direct messaging between users and allows users to send messsages to each other-->
 <?php
  session_start();

  //Check if the user is logged in before allowing them to send a message.
  if(!isset($_SESSION['user_id'])) {
    die("You must be logged in.");   //If they're not logged in, kill the sesssion
  }

  //Get the session ID, receiver id and the content
  $sender_id = $_SESSION['user_id'];          //The ID of the user that sent the text

  //Get data from the form
  $receiver_id = $_SESSION['receiver_id'];    //The ID of the user that received the text
  $content = $_SESSION['content'];            //The content of the text

  //Connect to the db
  $conn = new mysqli("localhost", "root", "", "projectx_db");
  if($conn->connect_error) {
    die("Failed to connect to the database: " . $conn->connect_error);
  }
  
  //Insert the new message into the db in the 'messages' table
  $sql = "INSERT INTO messages (sender_id, receiver_id, content, created_at)
          VALUES ('$sender_id' , '$receiver_id', '$content', NOW() )";
  $conn->query($sql);
  
  //Redirect back to the inbox
  header("Location: inbox.php");
  exit; 

 ?>
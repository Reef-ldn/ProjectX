<!-- This allows the user to upload a video of their highlights--> 
 <?php
 session_start();
 //Ensures the user is logged in before allowing them to upload content
 if(!isset($_SESSION['user_id'])) {
  die("Please log in first!");    //Kills the session if they're not
 }
 ?>




 <!DOCTYPE html>
 <html>

  <head>
    <title>Video Upload</title>
  </head>
  
  <body>
    <h1>Upload a Video<h1> 
    
    <!--Form to allow users to upload a video -->
    <form action = "upload_video.php" method="POST" enctype="multipart/form-data">
      <label> Video Title: </label> <br>
      <input type = "text" name = "title" required> <br><br>

      <!--Only allow files of type video-->
      <label>Select Video:</label> <br>
      <input type = "file" name = "myvideo" accept="video/*" required> <br><br>

      <button type = "submit" name="submit">Upload</button>
    </form>

  </body>

</html>




<?php
  if(isset($_POST['submit'])){      //When 'submit' is pressed
    //Get data from the form
    $videoTitle = $_POST['title'];       
    $playerID =  $_SESSION['user_id']; // the logged-in player's user ID

    //Check if there is a file
    //Basically checks what the user inputted and store their video files in a temporary folder
    if(isset($_FILES['myvideo']) && $_FILES['myvideo']['error'] == 0) {   
      $videoOriginalName = $_FILES['myvideo']['name'];       //Original File name
      $videoTmp = $_FILES['myvideo']['tmp_name'];    //Temporary location on the server
      $uploadPath = "uploads/" . $videoOriginalName;        //Where the video is being put

      //Moves the file from the temp folder to my uploads folder
      if(!move_uploaded_file($videoTmp,$uploadPath)) {
        die("Error moving the uploaded file.");
      }

      //Store a record in the 'videos' database table
      $conn = new mysqli("localhost", "root", "", "projectx_db");
      if($conn->connect_error){
        die("Failed to connect to the database: " . $conn->connect_error);
      }

      //Insert into the DB
      $sql = "INSERT INTO videos (user_id, video_path, title, created_at) 
              VALUES ('$playerID', '$uploadPath' , '$videoTitle', NOW())";   
      //Uploads who posted the video, what the vid address is and when it was posted
      if($conn->query($sql) === TRUE ) {
        echo "Video Uploaded Successfully!";
      } else {
        echo "Database Error: " . $conn->error;
      }

      $conn->close();
    } else {
      echo "No file selected or an upload error occured!";
    }

  }
?>
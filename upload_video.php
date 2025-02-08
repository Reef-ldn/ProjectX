<!-- This allows the user to upload a video--> 
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
    <form action = "upload_video.php" method="POST" entype="multipart/form-data">
      <label>Video Title:</label> <br>
      <input type = "text" name = "title" required> <br><br>

      <!--Only allow files of type video-->
      <label>Select Video:</label> <br>
      <input type = "file" name = "myvideo" accept="video/*" required> <br><br>

      <button type = "submit" name="subit">Upload</button>
    </form>

  </body>

</html>




<?php
  if(isset($_POST['submit'])){      
    //Get data from the form
    $title = $_POST['title'];
    $user_id =  $_SESSION['user_id']; // the logged-in user's ID

    //Check if there is a file
    if(isset($_FILES['myvideo']) && S_FILES['myvideo']['error']==0){
      $videoName = $_FILES['myvideo']['name'];       //Original File name
      $videoTemp = $_FILES['myvideo']['temp_name'];  //Temporary location on the server
      $destination = "uploads/" . $videoName;        //Where the video is being put

      //Move the file from the temp to uploads/ folder
      if(!move_uploaded_file($videoTemp,$destination)) {
        die("Error moving the uploaded file.");
      }

      //Store a record in the 'videos' table
      $conn = new mysqli("localhost", "root", "", "projectx_db");
      if($conn->connect_error){
        die("Connection Failed: " . $conn->connect_error);
      }

      //Insert into the DB
      $SQL = "INSERT INTO videos (user_id, video_path, title, created_at) 
              VALUES ('$user_id', '$destination' , '$title', NOW())";
      if($conn->query($sql) === TRUE ) {
        echo "Video Uploaded Successfully!";
      } else {
        echo "Database Error: " .$conn->error;
      }
      $conn->close();
      } else{
        echo "No file seleted or an upload error occured!";
      }

  }
?>
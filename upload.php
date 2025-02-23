<!-- This page handles multi media uploads (Text, Videos and Images)-->

<?php
 session_start();
 //Ensures the user is logged in before allowing them to upload content
 if(!isset($_SESSION['user_id'])) {
  die("Please log in first!");    //Kills the session if they're not
 }
 ?>


 <!--Front End-->
<!DOCTYPE html>
 <html>

  <head>
    <title>Create a Post</title>
  </head>
  
  <body>
    <h1>Upload Content</h1> 
    
    <!--Form to allow users to upload media -->
    <form action = "upload.php" method="POST" enctype="multipart/form-data">

      <!--Choose a Post Type-->
      <label> Type of Post: </label> <br>
      <select name = "post_type" required>
        <option value = "text">Text</option>       <!--Text Posts-->
        <option value = "image">Image</option>     <!--Image Posts-->
        <option value = "video">Video</option>     <!--Video Posts-->
      </select> <br> <br>

      <!--Title field-->
      <label>Title (Optional): </label><br>
      <input type = "text" name="title" placeholder="A short title..."/> <br><br>


      <!--For Text Posts/captions-->
      <label> Text Content (For Text Posts or Captions): </label>   <br>
      <textarea name = "text_content"   placeholder= "Write Something..."></textarea>     <br><br>

      <!--For Image/Video Uploads-->
      <label>Upload File (For Images or Video): </label> <br>
      <input type = "file" name = "media_file" accept="image/*,video/*" > <br><br>     

      <button type = "submit" name="submit_post">Post</button>
    </form>

  </body>

</html>

<!--Backend to handle this post  -->
<?php
if(isset($_POST['submit_post'])) {    //if the post button is pressed.
  $user_id = $_SESSION['user_id'];    

  //Get the type of post that the user selected
  $post_type = $_POST['post_type'];
  //Get the text content
  $text_content = $_POST['text_content'];
  //Get the post title (can be null)
  $title = $_POST['title'];

  //Connect to the DB
  $conn = new mysqli("localhost", "root", "", "projectx_db");
  if($conn->connect_error){
    die("Failed to connect to the database: " . $conn->connect_error);
  }


  //Store the file path if the user uploaded an image/video (This step is skipped if the user uploaded a text file)
  $file_path = null;      //defailt is null for text posts

  //Check if the user uploaded a file (only if the post_type if an image/vid)
  if(($post_type == "image" || $post_type == "video")   //if the post is an image or a video, we expect an uploaded fiile
      && isset($_FILES['media_file']) 
      && $_FILES['media_file']['error'] == 0 ) {        //also check for errors

        //Move the file to the 'uploads' subfolder
        $originalName = $_FILES['media_file']['name'];   //Original File name
        $destination = "uploads/" . $originalName;       //Where the video is being put   //I might come back to this later to rename if collisions occur

        //Moves the file from the temp folder to my uploads folder
        if(!move_uploaded_file($_FILES['media_file']['tmp_name'], $destination)) {
          die("Error moving the uploaded file.");
        }

        $file_path = $destination;
      }

  //Insert the data into the 'posts' table in the DB

  //Prepared statements are used to handle apostrophees in text_content
  $stmt = $conn->prepare("INSERT INTO posts 
                          (user_id, post_type, title, file_path, text_content, created_at)
                          VALUES (?, ?, ?, ?, ?, now())");

  //user_id = int (i)
  //post _type = string (s)
  //title = string (s)
  //file_path = string or null (s) - Pass a string if not null
  //text_content = string(s)
  $stmt->bind_param("issss", $user_id, $post_type, $title, $file_path, $text_content); //"isss" represents the above
  if($stmt->execute()) {          //if the statement is executed successfully
    echo "Post uploaded successfully!";
  } else {
    echo "Error uploading your post: " . $stmt->error;
  }

  $stmt->close();
  $conn->close();

  //redirect to the feed
  header("Location: feed.php");
  exit;

}

?>
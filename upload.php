<!-- This page handles multi media uploads (Text, Videos and Images)-->

<?php
 session_start();
 //Ensures the user is logged in before allowing them to upload content
 if(!isset($_SESSION['user_id'])) {
  die("Please log in first!");    //Kills the session if they're not
 }
 ?>

<!--Backend to handle postS  -->
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

<!--Front-end-->
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Upload Content</title>

  <!-- Bootstrap CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!--  Icons -->
  <script src="https://kit.fontawesome.com/22c727220d.js" crossorigin="anonymous"></script>

  <style>
    body {
      background-color: #f4f6f9;
    }

    .upload-container {
      max-width: 600px;
      margin: 60px auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
    }

    .form-label {
      font-weight: 500;
    }

    .preview-box {
      margin-top: 15px;
      max-width: 100%;
      max-height: 300px;
    }
  </style>
</head>

<body>

  <div class="upload-container">
    <h2 class="text-center mb-4">Create a Post</h2>

    <form action="upload.php" method="POST" enctype="multipart/form-data">
      <!-- Post Type -->
      <div class="mb-3">
        <label class="form-label">Type of Post</label>
        <select name="post_type" class="form-select" id="postType" required>
          <option value="text">Text</option>
          <option value="image">Image</option>
          <option value="video">Video</option>
        </select>
      </div>

      <!-- Title -->
      <div class="mb-3">
        <label class="form-label">Title (Optional)</label>
        <input type="text" name="title" class="form-control" placeholder="Enter a short title">
      </div>

      <!-- Text Content -->
      <div class="mb-3">
        <label class="form-label">Text Content</label>
        <textarea name="text_content" class="form-control" rows="4" placeholder="Write something..."></textarea>
      </div>

      <!-- Media Upload -->
      <div class="mb-3">
        <label class="form-label">Upload File (for Image or Video)</label>
        <input type="file" name="media_file" class="form-control" id="mediaInput" accept="image/*,video/*">
        <div class="preview-box mt-2" id="mediaPreview"></div>
      </div>

      <!-- Submit -->
      <div class="d-grid">
        <button type="submit" name="submit_post" class="btn btn-success btn-lg">Post</button>
      </div>
    </form>
  </div>

  <script>
    const mediaInput = document.getElementById('mediaInput');
    const preview = document.getElementById('mediaPreview');

    mediaInput.addEventListener('change', function () {
      const file = this.files[0];
      preview.innerHTML = '';

      if (file) {
        const fileType = file.type;
        const reader = new FileReader();

        reader.onload = function (e) {
          if (fileType.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'img-fluid rounded';
            preview.appendChild(img);
          } else if (fileType.startsWith('video/')) {
            const vid = document.createElement('video');
            vid.src = e.target.result;
            vid.controls = true;
            vid.className = 'img-fluid rounded';
            preview.appendChild(vid);
          }
        };

        reader.readAsDataURL(file);
      }
    });
  </script>

</body>
</html>

<!-- This page handles multi media uploads (Text, Videos and Images)-->

<?php
session_start();
//Ensures the user is logged in before allowing them to upload content
if (!isset($_SESSION['user_id'])) {
  die("Please log in first!");    //Kills the session if they're not
}
?>

<!--Backend to handle post  -->
<?php
//if the post button is pressed from the upload form.
if (isset($_POST['submit_post'])) {

  //Get data from the form
  $user_id = $_SESSION['user_id'];  //User ID
  $post_type = $_POST['post_type'];   //Get the type of post that the user selected
  $text_content = $_POST['text_content'];   //Get the text content
  $title = $_POST['title'];   //Get the post title (Not visible in the feed and can be null)

  //Connect to the DB
  $conn = new mysqli("localhost", "root", "", "projectx_db");
  if ($conn->connect_error) {
    die("Failed to connect to the database: " . $conn->connect_error);
  }

  //Prepare for media upload if it's an image/video (This step is skipped if the user uploaded a text file)
  $file_path = null;      //defailt is null for text posts

  //Check if the user uploaded a file (only if the post_type is an image/vid)
  if (
    ($post_type == "image" || $post_type == "video")   //if the post is an image or a video, we expect an uploaded file
    && isset($_FILES['media_file'])
    && $_FILES['media_file']['error'] == 0
  ) {        //also check for errors

    // Define allowed file types using MIME for security
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm', 'video/quicktime'];

    // Get the MIME type of the uploaded file
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $_FILES['media_file']['tmp_name']);  //Check the upload's type
    finfo_close($finfo);

    // Check if the MIME type is allowed
    if (!in_array($mimeType, $allowedMimeTypes)) {
      die("Invalid file type uploaded."); //if it's not allowed, don't allow it
    }

    //Move the file to the 'uploads' subfolder
    $originalName = $_FILES['media_file']['name'];   //Original File name
    $destination = "uploads/" . $originalName;       //Where the video is being put   
    // //I might come back to this later to rename if collisions occur

    //Moves the file from the temp folder to my uploads folder
    if (!move_uploaded_file($_FILES['media_file']['tmp_name'], $destination)) {
      die("Error moving the uploaded file.");
    }
    //Define destination variable
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

  //if the statement is executed successfully
  if ($stmt->execute()) {
    echo "Post uploaded successfully!";
  } else {  //Error occured
    echo "Error uploading your post: " . $stmt->error;
  }

  $stmt->close();
  $conn->close();

  //redirect to the feed - post should be at the top
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

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Icons -->
  <script src="https://kit.fontawesome.com/22c727220d.js" crossorigin="anonymous"></script>

  <style>
    /* Container */
    .upload-container {
      max-width: 600px;
      margin: 60px auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
      transition: 0.3s ease;
    }

    /* Dark theme */
    .dark-mode .upload-container {
      background: #1f1f1f;
      color: white;
    }

    /* Upload Header */
    .form-label {
      font-weight: 500;
    }

    /* Preview box */
    .preview-box {
      margin-top: 15px;
      max-width: 100%;
      max-height: 300px;
    }

    /* Preview drop area */
    #drop-area {
      border: 2px dashed #ccc;
      padding: 20px;
      text-align: center;
      cursor: pointer;
      transition: border 0.3s ease;
    }

    #drop-area.dragging {
      border-color: #009e42;
      background-color: #f0fff3;
    }
  </style>
</head>

<body>
  <!-- Toggle for Dark Mode (removed but left in code due to time constraints)-->
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <title>Create a Post</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons  -->
    <script src="https://kit.fontawesome.com/22c727220d.js" crossorigin="anonymous"></script>

    <!--Navbar stylesheet-->
    <link rel="stylesheet" href="/ProjectX/css/navbar.css">


    <style>
      body {
        background-image: url('/ProjectX/uploads/people-soccer-stadium.jpg');
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
        background-attachment: fixed;
        color: white;
      }

      /* Blur Background */
      .bg-blur-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        backdrop-filter: blur(4px);
        background-color: rgba(0, 0, 0, 0.4);
        z-index: 1;
      }

      /* Container that holds the form */
      .upload-container {
        position: relative;
        z-index: 2;
        max-width: 600px;
        margin: 100px auto;
        background-color: rgba(30, 30, 30, 0.85);
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 0 15px rgba(0, 255, 100, 0.2);
        color: white;
      }

      /* Form input Fields */
      .upload-container .form-control {
        background-color: rgba(30, 30, 30, 0.85);
        color: white;
        border: 1px solid #444;
      }

      .upload-container .form-control::placeholder,
      .upload-container textarea::placeholder {
        color: #aaa;
        opacity: 1;
      }

      .upload-container .form-control:focus {
        border-color: #0f0;
        background-color: rgba(30, 30, 30, 0.85);
        ;
        color: white;
        box-shadow: none;
      }

      .upload-container select.form-select {
        background-color: rgba(30, 30, 30, 0.85);
        ;
        color: white;
        border: 1px solid #444;
      }

      .upload-container select.form-select option[disabled] {
        color: #aaa;
      }

      .upload-container select.form-select:focus {
        border-color: #0f0;
        background-color: #111;
        color: white;
        box-shadow: none;
      }

      /* drag & drop box */
      #drop-area {
        border: 2px dashed #ccc;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: border 0.3s ease;
      }

      /* Change appearance when a file is being dragged over */
      #drop-area.dragging {
        border-color: #009e42;
        background-color: #f0fff3;
      }

      /* Where the image or video preview will be shown */
      .preview-box {
        margin-top: 15px;
        max-height: 300px;
      }

      .preview-box img,
      .preview-box video {
        max-width: 100%;
        border-radius: 8px;
      }
    </style>
  </head>

  <body>
    <div class="bg-blur-overlay"></div> <!--Background-->

    <!--Nav Bar-->
    <?php
    $currentPage = 'upload';
    include 'navbar.php'; ?>

    <!-- Main Upload Form -->
    <div class="upload-container">
      <h2 class="text-center mb-4">Create a Post</h2>

      <!-- Form to send data to upload.php -->
      <form id="uploadForm" action="upload.php" method="POST" enctype="multipart/form-data">

        <!-- Choose post type (text, image, video) -->
        <div class="mb-3">
          <label class="form-label">Type of Post</label>
          <select name="post_type" class="form-select" id="postType" required>
            <option value="text">Text</option>
            <option value="image">Image</option>
            <option value="video">Video</option>
          </select>
        </div>

        <!-- Optional title input -->
        <div class="mb-3">
          <label class="form-label">Title (Optional)</label>
          <input type="text" name="title" class="form-control" placeholder="Enter a short title">
        </div>

        <!-- Text content (caption or text-only post) -->
        <div class="mb-3">
          <label class="form-label">Text Content</label>
          <textarea name="text_content" class="form-control" rows="4" placeholder="Write something..."></textarea>
        </div>

        <!-- Drag & Drop Upload Section -->
        <div class="mb-3">
          <label class="form-label">Upload File (Image/Video)</label>

          <!-- Drop area for drag-and-drop upload -->
          <div id="drop-area">
            <p><i class="fas fa-upload"></i> Drag & Drop file here or click to select (JPEG/PNG for images)</p>

            <!-- Hidden file input (used when clicking drop area) -->
            <input type="file" name="media_file" id="mediaInput" class="form-control d-none" accept="image/*,video/*">
          </div>

          <!-- Shows a preview of uploaded media -->
          <div class="preview-box" id="mediaPreview"></div>
        </div>

        <!-- Submit button -->
        <div class="d-grid">
          <button type="submit" name="submit_post" class="btn btn-success btn-lg">Post</button>
        </div>
      </form>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!--Script to handle the drag and drop area-->
    <script>
      // Grab key elements from the page
      const dropArea = document.getElementById('drop-area');
      const mediaInput = document.getElementById('mediaInput');
      const preview = document.getElementById('mediaPreview');

      // When user clicks the drop area, open the file selector
      dropArea.addEventListener('click', () => mediaInput.click());

      // When a file is dragged over the drop area 
      ['dragenter', 'dragover'].forEach(event => {
        dropArea.addEventListener(event, e => {   //Listen for the file being dragged over
          e.preventDefault(); //Stop original action
          e.stopPropagation();
          dropArea.classList.add('dragging');   //Show that it's being dragged over
        });
      });

      // When dragging leaves or file is dropped
      ['dragleave', 'drop'].forEach(event => {
        dropArea.addEventListener(event, e => {   //Listen for the file leaving or being dropped
          e.preventDefault();
          e.stopPropagation();
          dropArea.classList.remove('dragging');  //Stop the drag action
        });
      });

      // If a file is dropped, store it in the file input
      dropArea.addEventListener('drop', e => {
        mediaInput.files = e.dataTransfer.files;
        showPreview(mediaInput.files[0]);   //Show preview 
      });

      // If a file is selected manually, show preview
      mediaInput.addEventListener('change', () => {
        if (mediaInput.files[0]) {
          showPreview(mediaInput.files[0]);   //Show preview
        }
      });

      // Show preview for image or video
      function showPreview(file) {
        preview.innerHTML = ''; // Clear old preview
        const reader = new FileReader();    //Read new

        // When file is read, display it
        reader.onload = e => {
          //If file is an iamge
          if (file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = e.target.result;
            preview.appendChild(img);   //Preview image
            //If file is a video
          } else if (file.type.startsWith('video/')) {
            const video = document.createElement('video');
            video.src = e.target.result;
            video.controls = true;
            preview.appendChild(video); //Preview Video
          }
        };

        reader.readAsDataURL(file); // Read the file
      }
    </script>
  </body>

  </html>
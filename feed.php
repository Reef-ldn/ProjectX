<!-- This page displays a feed of all the uploaded content from users-->

<?php
session_start();  //Check the user is logged in;

//connect to the db
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Failed to connect to the database: " . $conn->connect_error);
}




//Fetch all videos from newest to oldest (LIFO)
$sql = "SELECT p.id, p.post_type, p.file_path, p.text_content, p.created_at, u.username,
          (SELECT COUNT(*) FROM likes l where l.post_id = p.id) AS like_count
          from posts p
          JOIN users u ON p.user_id = u.id 
          ORDER BY p.created_at DESC"
;
//The "JOIN" gives the foreigner key of the user's name from the user's table
//The select count is a sub query of likes and acts as a like counter.
//for each row in 'posts', the 'likes' table is also checked to see how many rows there is 
// for posts with that id and  keeps a count of it
//This count result is the 'like_count'

$result = $conn->query($sql);
?>



<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Feed</title>

  <!--Bootstrap CSS (CDN)-->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

  <!--Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />

  <script src="https://kit.fontawesome.com/22c727220d.js" crossorigin="anonymous"></script>

</head>

<body>

  <!--Navbar start-->
  <nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark"> <!--Dark Background-->
    <div class="container-fluid">
      <!--Left - Logo + Project Name-->
      <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="/docs/5.3/assets/brand/bootstrap-logo.svg" alt="Logo" width="30" height="24" class="me-2">
        Next XI
      </a>

      <!--Toggler for small screens-->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span> <!--Toggler Icon-->
      </button>

      <!--Collapsible Div for the nav links and user dropdown-->
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <!--Middle:Nav Links (centered using mx-auto)-->
        <ul class="navbar-nav mx-auto mb-2 mb-lg-0">

          <!--Nav Links-->
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#">Feed</a> <!--Current Page-->
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Upload</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Settings</a>
          </li>

        </ul>

        <!--Right Side: Search + Profile Pic Dropdown-->
        <div class="d-flex align-items-center">
          <!--Search Bar-->
          <form class="d-flex me-3" role="search">
            <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
            <button class="btn btn-outline-light" type="submit">Search</button>
          </form>

          <!--Profile Pic Dropdown-->
          <div class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button"
              data-bs-toggle="dropdown" aria-expanded="false">
              <!-- The user’s profile pic -->
              <img src="https://via.placeholder.com/32" alt="Profile" width="32" height="32" class="rounded-circle">
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <!-- "dropdown-menu-end" to align the menu to the right side -->
              <li><a class="dropdown-item" href="#">My Profile</a></li>
              <li><a class="dropdown-item" href="#">Settings</a></li>
              <li><a class="dropdown-item" href="#">Help/Support</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item" href="#">Log Out</a></li>
            </ul>
          </div>

        </div> <!--end d-flex align-items-center-->
      </div> <!--End collaps-->
    </div> <!--End container-fluid-->
  </nav>
  <!--Navbar End-->


  <!--Main Content Area-->
  <div class="container pt-5 mt-5">
    <div class="row">
      <!--Left or Center Column: 8/12 columns-->
      <div class="col-md-8">

        <!--Feed logic (php), each post in a card-->
        <?php
        if ($result->num_rows > 0) {
          //Display each post - Read each line
          while ($row = $result->fetch_assoc()) {
            ?>
            <div class="card mb-4">
              <!--Card Body-->
              <div class="card-body">

                <!--Top Part: user pic + username + 3-dot hamburger on the right-->
                <div class="d-flex justify-content-between align-items-center mb-2">

                  <!--Left side: User profile pic+name+ @username + time-->
                  <div class="d-flex align-items-center">
                    <!--User's Profile Pic-->
                    <img src="https://via.placeholder.com/40" alt="Profile" width="40" height="40"
                      class="rounded-circle me-2">
                    <div>
                      <!--User account name-->
                      <strong><?php echo $row['username']; ?></strong>
                      <!-- user's @ handle -->
                      <span class="text-muted">@<?php echo strtolower($row['username']); ?></span><br>
                      <!-- time posted -->
                      <small class="text-muted">
                        Posted on <?php echo date('d M, y H:i', strtotime($row['created_at'])); ?>
                      </small>
                    </div>
                  </div>

                  <!-- Right: 3-dot dropdown menu -->
                  <div class="dropdown">
                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="bi bi-three-dots"></i> <!-- Using a bootstrap icon -->
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li><a class="dropdown-item" href="#">Save Post</a></li>
                      <li><a class="dropdown-item" href="#">Report</a></li>
                      <li><a class="dropdown-item" href="#">Follow/Unfollow</a></li>
                      <li><a class="dropdown-item" href="#">View Profile</a></li>
                      <li>
                        <hr class="dropdown-divider">
                      </li>
                      <li><a class="dropdown-item" href="#">Cancel</a></li>
                    </ul>
                  </div>
                </div> <!-- end d-flex justify-content-between -->

                <!-- Middle: the actual post content (image/video/text) -->
                <div style="max-width: 800px;">
                  <div class="mb-3">
                    <?php if ($row['post_type'] == "image"): ?>
                      <img src="<?php echo $row['file_path']; ?>" class="img-fluid" alt="Post Image">
                    <?php elseif ($row['post_type'] == "video"): ?>
                      <video class="img-fluid" controls>
                        <source src="<?php echo $row['file_path']; ?>" type="video/mp4">
                        Your browser does not support the video tag.
                      </video>
                    <?php elseif ($row['post_type'] == "text"): ?>
                      <p><?php echo $row['text_content']; ?></p>
                    <?php endif; ?>
                  </div>
                </div>

                <!-- Buttons row (like, comment, share) -->
                <div class="d-flex align-items-center mb-2">
                  <!-- Like Heart Icon -->
                  <button class="btn btn-link text-decoration-none me-3">
                    <i class="bi bi-heart"></i> <!-- outline heart, becomes "bi-heart-fill" when liked -->
                  </button>
                  <!-- Comment icon -->
                  <button class="btn btn-link text-decoration-none me-3">
                    <i class="bi bi-chat-right-dots"></i>
                  </button>
                  <!--Share Icon-->
                  <button class="btn btn-link text-decoration-none me-3">
                    <i class="bi bi-send"></i> </button>
                </div>


                <!-- Like count -->
                <p class="mb-1"><strong><?php echo $row['like_count']; ?> likes</strong></p>

                <!-- Caption -->
                <?php if (!empty($row['text_content']) && $row['post_type'] != 'text'): ?>
                  <p>
                    <strong><?php echo strtolower($row['username']); ?> </strong>
                    <?php echo $row['text_content']; ?>
                  </p>
                <?php endif; ?>

                <!-- Comments Section -->
                <hr>
                <div class="mb-2">
                  <!-- fetch comments and loop-->
                  <small class="text-muted">Comments go here...</small>
                </div>
                <form class="d-flex">
                  <input class="form-control me-2" type="text" placeholder="Add a comment...">
                  <button class="btn btn-sm btn-primary">Post</button>
                </form>

              </div> <!-- end card-body -->
            </div> <!-- end card mb-4 -->

            <?php
          } // end while
        } else {
          echo "<p>No posts found in feed.</p>";
        }
        ?>
      </div> <!-- end col-md-8 -->

    </div>


  </div>

  </div>



  <?php
  if ($result->num_rows > 0) {
    //Display each post - Read each line
    while ($row = $result->fetch_assoc()) {
      //creates the video box
      echo "<div style = 'border:1px solid #ccc;
                margin-bottom:10px;
                padding:10px;'>";

      //Show the title (If there is one)
      if (!empty($row['title'])) {
        echo "<h3>" . $row['title'] . "</h3>";
      }

      echo "<p>Uploaded by: " . $row['username'] . " at " . $row['created_at'] . "</p>";

      //If it's a text post
      if ($row['post_type'] == "text") {
        echo "<p>" . $row['text_content'] . "</p>";
      }
      //if it's an image post
      else if ($row['post_type'] == "image") {
        echo "<p>" . $row['text_content'] . "</p>"; //uses text_content as a caption (2 birds with 1 stone)
        echo "<img src='" . $row['file_path'] . "' width='400'/>";
      }
      //if it's a video
      else if ($row['post_type'] == "video") {
        echo "<p>" . $row['text_content'] . "</p>"; //uses text_content as a caption
        echo "<video width='400' controls> 
                <source src = ' " . $row['file_path'] . " ' type = 'video/mp4' >
                Your browser does not support the video tag.
                </video> ";
      }

      echo "</div>";


      //link to view the profile
      //<a href="profile.php?user_id=<?php echo$row['id'];
  
      //Displaying the Like count
      echo "<p>Likes: " . $row['like_count'] . "</p>"; //Show the like count next to the video (How many likes are in the 'like_count' section of that likes column)
  
      //Like Button 
      echo "<a href='likes.php?post_id=" . $row['id'] . "'>Like</a>";    //Clicking this button will call the 'likes.php' script
  
      //The Comments Form
      echo "<form action = 'comments.php'    method = 'POST'>";
      echo "<input type='hidden' name='post_id'  value='" . $row['id'] . "' /> ";
      echo "<textarea name = 'comment_text'   placeholder ='Write a comment here...' > </textarea> <br>";
      echo "<button type = 'submit' > Comment </button> ";
      echo "</form>";

      //Displaying the commentts
      $postID = $row['id']; //The current post
      $commentSql = "SELECT c.comment_text, c.created_at, u.username
                        FROM comments c
                        JOIN users u ON c.user_id = u.id
                        WHERE c.post_id = '$postID'
                        ORDER BY c.created_at ASC";

      $commentResult = $conn->query($commentSql);

      while ($cRow = $commentResult->fetch_assoc()) {
        echo "<p> <b>" . $cRow['username'] . ":</b> " . $cRow['comment_text'] .
          " <i>(" . $cRow['created_at'] . ")</i></p>";
      }

    }
  } else {
    echo "Feed is Empty.";
  }
  $conn->close();
  ?>
  </div>
  <!--Bootstrap JavaScript-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

</body>

</html>
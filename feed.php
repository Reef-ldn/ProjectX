<!-- This page displays a feed of all the uploaded content from users-->

<?php
session_start();  //Check the user is logged in;

//connect to the db
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Failed to connect to the database: " . $conn->connect_error);
}




//Fetch all videos from newest to oldest (LIFO)
$sql = "SELECT p.id AS postID, p.post_type, p.file_path, p.text_content, p.created_at, p.is_highlight, u.id AS user_owner_id, u.username, u.name, u.profile_pic,
          (SELECT COUNT(*) FROM likes l where l.post_id = p.id) AS like_count,
          (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
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
      <a class="navbar-brand d-flex align-items-center" href="feed.php">
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
            <a class="nav-link active" aria-current="page" href="feed.php">Feed</a> <!--Current Page-->
          </li>
          <li class="nav-item">
            <a class="nav-link" href="upload.php">Upload</a>
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
              <li><a class="dropdown-item" href="logout.php" form action="logout.php">Log Out</a></li>
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
      <!--Left or Center Column: 6/12 columns-->
      <div class="offset-md-2 col-md-7">

        <!--Feed logic (php), each post in a card-->
        <?php
        if ($result && $result->num_rows > 0) {
          //Display each post - Read each line
          while ($row = $result->fetch_assoc()) {
            // Variables
            $postID = $row['postID'];
            $userID = $_SESSION['user_id'] ?? 0;
            $loggedUserID = $_SESSION['user_id'] ?? 0;
            $postOwnerID = $row['user_owner_id'];

            $alreadyLiked = false;
            $alreadyFollows = false;

            if ($userID > 0) {
              // Check if this user already liked
              $likeCheckSql = "SELECT * FROM likes WHERE post_id='$postID' AND user_id='$userID'";
              $likeCheckResult = $conn->query($likeCheckSql);
              $alreadyLiked = ($likeCheckResult->num_rows > 0);
            }

            if ($loggedUserID > 0) {
              //check if the user already follows the user
              $checkFollowSql = "SELECT * FROM follows WHERE follower_id='$loggedUserID' AND followed_id='$postOwnerID'";
              $followRes = $conn->query($checkFollowSql);
              $alreadyFollows = ($followRes->num_rows > 0);
            }

            // fetch comments
            $commentSql = "SELECT c.comment_text, c.created_at, u.username
                         FROM comments c
                         JOIN users u ON c.user_id = u.id
                         WHERE c.post_id = '$postID'
                         ORDER BY c.created_at ASC
                         LIMIT 2";
            $commentRes = $conn->query($commentSql);
            ?>
            <div class="card mb-4">
              <!--Card Body-->
              <div class="card-body">

                <!--Top Part: user pic + username + 3-dot hamburger on the right-->
                <div class="d-flex justify-content-between align-items-center mb-2">

                  <!--Left side: User profile pic+name+ @username + time-->
                  <div class="d-flex align-items-center">
                    <!--User's Profile Pic-->
                    <img src="<?php echo $row['profile_pic']; ?>" alt="Profile" width="40" height="40"
                      class="rounded-circle me-2">
                    <div>
                      <!--User account name-->
                      <strong><?php echo $row['name']; ?></strong>
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
                      <!--Only show this if the PostOwner is the logged in user-->
                      <?php if ($postOwnerID == $loggedUserID): ?>
                        <?php if ($row['is_highlight'] == 1): ?> <!--If it's a highlight-->
                          <li><a class="dropdown-item" href="highlight_post.php?post_id=<?php echo $postID; ?>&action=remove">Remove from Highlights</a></li>
                        <?php else: ?>
                          <!--If it's not highlighted already-->
                          <li><a class="dropdown-item" href="highlight_post.php?post_id=<?php echo $postID; ?>&action=add">Add to Highlights</a></li>
                        <?php endif; ?>
                      <?php endif; ?>
                      <li><a class="dropdown-item" href="#">Report</a></li>
                      <!--Only show follow/unfollow if the postowner isnt the same as the logged in user-->
                      <?php if ($postOwnerID != $loggedUserID): ?>
                        <?php
                        if ($alreadyFollows) {
                          echo '<li><a class="dropdown-item" href="follow_user.php?followed_id=' . $postOwnerID . '&action=unfollow">Unfollow</a></li>';
                        } else {
                          echo '<li><a class="dropdown-item" href="follow_user.php?followed_id=' . $postOwnerID . '&action=follow">Follow</a></li>';
                        }
                        ?>
                      <?php endif; ?>
                      <!--<li><a class="dropdown-item" href="#">Follow/Unfollow</a></li>-->
                      <li><a class="dropdown-item" href="profile.php?user_id=<?php echo $postOwnerID; ?>">View Profile</a>
                      </li>
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
                      <div style="max-width: 300px; max-height: 350px; margin: 0 auto; overflow: hidden;">
                        <video style="object-fit: contain; width: 100%; height: 100%;" controls>
                          <source src="<?php echo $row['file_path']; ?>" type="video/mp4">
                          Your browser does not support the video tag.
                        </video>

                      </div>


                    <?php elseif ($row['post_type'] == "text"): ?>
                      <p><?php echo $row['text_content']; ?></p>
                    <?php endif; ?>
                  </div>
                </div>

                <!-- Buttons row (like, comment, share) -->
                <div class="d-flex align-items-center mb-2">

                  <!-- Like Heart Icon -->
                  <?php
                  if ($alreadyLiked) {
                    // filled heart
                    echo '<a href="toggle_like.php?post_id=' . $postID . '&action=unlike" 
                         class="btn btn-link me-3 text-danger">
                         <i class="bi bi-heart-fill"></i>
                       </a>';
                  } else {
                    // outline heart
                    echo '<a href="toggle_like.php?post_id=' . $postID . '&action=like" 
                         class="btn btn-link me-3">
                         <i class="bi bi-heart"></i>
                       </a>';
                  }
                  ?>

                  <!-- Comment icon -->
                  <button class="btn btn-link text-decoration-none me-3">
                    <a href="view_comments.php?post_id=<?php echo $postID; ?>.">
                      <i class="bi bi-chat-right-dots"></i>
                    </a>
                  </button>

                  <!--Share Icon-->
                  <button class="btn btn-link text-decoration-none me-3">
                    <i class="bi bi-send"></i> </button>
                </div>

                <!-- Like count -->
                <?php
                $likeCount = $row['like_count'];
                if ($likeCount == 1) {
                  echo "<p><strong>1 like</strong></p>";
                } else {
                  echo "<p><strong>{$likeCount} likes</strong></p>";
                }
                ?>

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
                  <?php
                  if ($commentRes && $commentRes->num_rows > 0) {
                    while ($cRow = $commentRes->fetch_assoc()) {
                      echo '<p><b>' . $cRow['username'] . ':</b> ' . $cRow['comment_text'] . ' <i>(' . $cRow['created_at'] . ')</i></p>';
                    }
                  } else {
                    echo '<small class="text-muted">No comments yet.</small><br><br>';
                  }


                  //Only display 2 comments and hide the rest under a "View all comments" hyperlink
                  $commentCount = $row['comment_count'];
                  if ($commentCount > 2) {
                    echo '<a href="view_comments.php?post_id=' . $postID . '">View all ' . $commentCount . ' comments</a>';
                  }

                  ?>
                  <!--<small class="text-muted">Comments go here...</small>-->
                </div>
                <!--Comments Form-->
                <form class="d-flex" action="comments.php" method="POST">
                  <input type="hidden" name="post_id" value="<?php echo $postID; ?>">
                  <input class="form-control me-2" type="text" name="comment_text" placeholder="Add a comment...">
                  <button class="btn btn-sm btn-primary" type="submit">Comment</button>
                </form>

              </div> <!-- end card-body -->
            </div> <!-- end card mb-4 -->

            <?php
          } // end while
        } else {
          echo "<p>No posts found in feed.</p>";
        }
        $conn->close();
        ?>
      </div> <!-- end col-md-7 -->


      <!-- Right Column: col-md-4 for "Trending" or anything else -->
      <div class="col-md-3">
        <div class="bg-light p-3 mb-5">
          <h5>Trending</h5>
          <p>Trending posts / recommended users</p>
        </div>
        <div class="bg-light p-3 mb-4">
          <h5>People you may know</h5>
          <p>User 1</p>
          <p>User 2</p>
          <p>User 3</p>
        </div>
        <div class="bg-light p-3">
          <h5>Another Section</h5>
          <p>Some additional widget or ads, etc.</p>
        </div>
      </div> <!-- end col-md-4 -->
    </div> <!-- end row -->
  </div> <!-- end container -->

  <!--Bootstrap JavaScript-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

</body>

</html>
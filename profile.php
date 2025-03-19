<!--This page handles displaying the users profile -->

<!--Backend to handle the profile data-->
<?php
session_start();
//The user's account that we are on (user_id taken from the url)
$profileUserId = (int) $_GET['user_id'] ?? 0;

//The user that is currently logged in
$loggedUserId = (int) $_SESSION['user_id'] ?? 0;

//connect to the db
$conn = new mysqli("localhost", "root", "", "projectx_db");    //connect to db
if ($conn->connect_error) {      //check connection
  die("Failed to connect to the database: " . $conn->connect_error);
}

//fetch from the 'users' table
$userSql = "SELECT username, user_type, profile_pic, banner_pic FROM users WHERE id = '$profileUserId'";
$userResult = $conn->query($userSql);
if ($userResult->num_rows == 0) {         //If no user has this ID, no user is found.
  die("No user found.");
}
$userRow = $userResult->fetch_assoc();

//The user's profile picture
$profilePic = $userRow['profile_pic'] ?? 'uploads/profile_pics/default_profile_pic.jpg';
$bannerPic = $userRow['banner_pic'] ?? 'uploads/profile_pics/default_banner.jpg';

//If the user type is 'player', fetch from the 'players' table too
if ($userRow['user_type'] == 'player') {
  //Select all players from the players table with the same user ID
  $plSql = "SELECT * FROM players WHERE user_id = '$profileUserId' ";
  $plResult = $conn->query($plSql);   //store this info in a result variable
  if ($plResult && $plResult->num_rows > 0) {
    $plData = $plResult->fetch_assoc();
  } else {
    $plData = null;
  }
}

//Check if the logged in user is following the user that we are viewing
$sqlCheck = "SELECT * FROM follows
                WHERE follower_id = '$loggedUserId'
                AND followed_id = '$profileUserId' ";
$checkResult = $conn->query($sqlCheck);
$isFollowing = ($checkResult->num_rows > 0); //This is true if the user is already following them


//Followers Count (How many people follow this user)
$sqlFollowers = "SELECT COUNT(*) AS followers_count
                    FROM follows
                    WHERE followed_id = '$profileUserId' ";
$resFollowers = $conn->query($sqlFollowers);
$rowFollowers = $resFollowers->fetch_assoc();
$followersCount = $rowFollowers['followers_count'];

//Following Count (How many people this user follows)
$sqlFollowing = "SELECT COUNT(*) AS following_count
                   FROM follows
                   WHERE follower_id = '$profileUserId' ";
$resFollowing = $conn->query($sqlFollowing);
$rowFollowing = $resFollowing->fetch_assoc();
$followingCount = $rowFollowing['following_count'];

//Post Count to be displayed at the top
$user_id = $profileUserId;
$sqlPosts = "SELECT COUNT(*) AS totalPosts 
             FROM posts 
             WHERE user_id = '$user_id'";
$resPosts = $conn->query($sqlPosts);
if ($resPosts && $rowPosts = $resPosts->fetch_assoc()) {
  $postCount = $rowPosts['totalPosts'];
} else {
  $postCount = 0;
}

//Like COunt
$sqlLikes = "
  SELECT COUNT(*) AS totalLikes
  FROM likes l
  JOIN posts p ON l.post_id = p.id
  WHERE p.user_id = '$user_id'
";
$resLikes = $conn->query($sqlLikes);
if ($resLikes && $rowLikes = $resLikes->fetch_assoc()) {
  $likeCount = $rowLikes['totalLikes'];
} else {
  $likeCount = 0;
}

// The user's chosen pics or fallback
$profilePic = $userRow['profile_pic'] ?? 'uploads/profile_pics/default_profile_pic.jpg';
$bannerPic = $userRow['banner_pic'] ?? 'uploads/profile_pics/default_banner.jpg';




$user_id = $profileUserId; // the user whose profile we are viewing

// Fetch all posts
$sqlAllPosts = "SELECT 
  p.id AS postID,
  p.post_type,
  p.file_path,
  p.text_content,
  p.created_at,
  p.is_highlight,
  u.id AS user_owner_id,
  u.username,
  u.name,
  (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
  (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
FROM posts p
JOIN users u ON p.user_id = u.id
WHERE p.user_id = '$profileUserId'  /* only that user's posts */
ORDER BY p.created_at DESC";
$resAllPosts = $conn->query($sqlAllPosts);

// Fetch media ( images and videos)
$sqlMedia = "SELECT * FROM posts
         WHERE user_id = '$user_id'
          AND post_type IN ('image','video')
         ORDER BY created_at DESC";
$resMedia = $conn->query($sqlMedia);

//Fetch "highlights"
$sqlHighlights = " SELECT 
    p.id AS postID,
    p.post_type,
    p.file_path,
    p.text_content,
    p.created_at,
    p.is_highlight,
    (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
    (SELECT COUNT(*) FROM comments cc WHERE cc.post_id = p.id) AS comment_count,
    u.id AS user_owner_id,
    u.username,
    u.name,
    u.profile_pic
  FROM posts p
  JOIN users u ON p.user_id = u.id
  WHERE p.user_id = '$profileUserId'
    AND p.is_highlight = 1
  ORDER BY p.created_at DESC";
$resHighlights = $conn->query($sqlHighlights);

//. Fetch likes (posts the user Liked)
$sqlLikes = "SELECT p.id AS postID,
    p.post_type,
    p.file_path,
    p.text_content,
    p.created_at,
    u.id AS user_owner_id,
    u.username,
    u.name,
    u.profile_pic,
    (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
    (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
              FROM likes l
              JOIN posts p ON l.post_id = p.id
              JOIN users u ON p.user_id = u.id
              WHERE l.user_id = '$user_id'
              ORDER BY p.created_at DESC
            ";
$resLikesTab = $conn->query($sqlLikes);

?>

<!--Front-end to display the profile-->
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Profile</title>

  <!--Bootstrap CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

  <!--Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <style>
    .profile-pic {
      width: 120px;
      height: 120px;
      border-style: solid;
      border-width: 4px;
      border-radius: 50%;
      border-color: #009e42;
      object-fit: cover;
    }

    .handle {
      font-size: 22px;
      opacity: 70%;
    }

    .current-team {
      font-size: 19px;
      padding-left: 18px;
    }

    .position {
      font-size: 16px;
      font-weight: 600;
      padding-left: 38px;

    }

    /*  black box in the top-right (posts, likes, followers, following) */
    .info-box {
      display: flex;
      justify-content: space-evenly;
      align-items: center;
      background-color: rgba(0, 0, 0, 0.8);
      color: #fff;
      padding: 10px 15px;
      border-radius: 8px;
      margin-top: 15px;
    }

    .info-box .info-stat {
      flex: 1;
      text-align: center;
      /*padding: 0px 0px 10px 15px;*/
      padding: 0px 30px 0px 30px;
    }

    /*The Actual numbers */
    .info-box h5 {
      margin: 0 0 1px 0;
      font-size: 30px;
      font-weight: 400;
    }

    /*Small Text in black box */
    .info-box small {
      font-size: 15px;
      font-weight: 100;
    }

    /*  green rows */
    .stats-row {
      background-color: rgb(4, 145, 63);
      color: #fff;
      padding: 12px;
      margin-bottom: 8px;
      border-radius: 8px;
    }

    .green-stats-h1 {
      text-align: center;
      margin-bottom: 5px;

    }

    /* Divider */
    hr.solid {
      border-top: 3px solid;
      border-style: solid;
    }

    .stats-row .stats-label {
      font-size: 15px;
      opacity: 0.8;
    }

    /*  styling for the sub-nav tabs */
    .sub-nav-tabs {
      background-color: #444;
      border-radius: 8px;
      padding: 10px;
      margin-bottom: 10px;
    }

    .sub-nav-tabs .nav-link {
      color: #fff;
      margin: 0 5px;
    }

    .sub-nav-tabs .nav-link.active {
      background-color: #009e42;
      border: none;
    }

    .tab-pane h4 {
      margin-top: 0;
      margin-bottom: 0.5rem;
    }

    .tab-pane h4 {
      margin-top: 0;
      margin-bottom: 0.5rem;
    }

    .tab-highlights {
      margin-top: -20;
    }
  </style>
</head>


<div class="bg-light">

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

  <!--Main Body Container-->
  <div class="container mt-5">

    <!-- Row #1: Profile Pic + Name/Handle/Team/Position-->
    <div class="row align-items-center mb-2">
      <!-- Left col: big round pic -->
      <div class="col-auto text-center">
        <img src="<?php echo $profilePic; ?>" alt="Profile Picture" class="profile-pic">
      </div>

      <!-- Middle col: user info -->
      <div class="col">
        <!-- Display user’s name -->
        <h2 class="mb-1">
          <?php echo $userRow['name'] ?? 'Player Name'; ?>

          <!-- The user's handle -->
          <small class="handle">@<?php echo strtolower($userRow['username'] ?? 'player'); ?></small>
          <br>
        </h2>

        <!-- The player's current team -->
        <small class="current-team"><?php echo $plData['current_team'] ?? 'No Team'; ?></small>
        <br>
        <!-- The player's preferred position -->
        <small class="position"><?php echo $plData['preferred_position'] ?? 'Position'; ?></small>
      </div>

      <!-- Right col: black info box + follow button  -->
      <div class="col-auto d-flex flex-column align-items-end ">

        <!-- Black Info box -->
        <div class="info-box mb-3">
          <div class="info-stat">
            <h5><?php echo $postCount; ?></h5>
            <small>Posts</small>
          </div>

          <div class="info-stat">
            <h5><?php echo $followersCount; ?></h5>
            <small>Followers</small>
          </div>
          <div class="info-stat">
            <h5><?php echo $followingCount; ?></h5>
            <small>Following</small>
          </div>
          <div class="info-stat">
            <h5><?php echo $likeCount; ?></h5>
            <small>Likes</small>
          </div>
        </div>

        <!-- Follow button -->
        <?php if ($loggedUserId != $profileUserId): ?>
          <?php if ($isFollowing): ?>
            <a href="follow_user.php?followed_id=<?php echo $profileUserId; ?>&action=unfollow"
              class="btn btn-outline-danger btn-lg">
              Unfollow
            </a>
          <?php else: ?>
            <a href="follow_user.php?followed_id=<?php echo $profileUserId; ?>&action=follow"
              class="btn btn-success btn-lg">
              Follow
            </a>
          <?php endif; ?>
        <?php else: ?>
          <!-- If it's the same user as the one logged in, "Edit Profile" -->
          <a href="edit_profile.php" class="btn btn-primary btn-lg">Edit Profile</a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Green Stats Row 1-->
    <div class="row stats-row text-center mb-3 mt-3">

      <!--Height-->
      <div class="col-4 col-md-3">
        <h4><?php echo ($plData['height'] ?? 0) . 'cm'; ?></h4>
        <div class="stats-label">Height</div>
      </div>
      <!--Weight-->
      <div class="col-4 col-md-3">
        <h4><?php echo ($plData['weight'] ?? 0) . 'kg'; ?></h4>
        <div class="stats-label">Weight</div>
      </div>
      <!--Preferred Foot-->
      <div class="col-4 col-md-3">
        <h4><?php echo ($plData['preferred_foot'] ?? 'Right'); ?></h4>
        <div class="stats-label">Foot</div>
      </div>
      <!--Country-->
      <div class="col-4 col-md-3">
        <h4><?php echo ($plData['country'] ?? 'England'); ?></h4>
        <div class="stats-label">Country</div>
      </div>
    </div>

    <!-- Green Stats Row 2 -->
    <div class="row stats-row text-center">
      <h3 class="green-stats-h1">
        Stats:
      </h3>
      <hr class="solid"> <!--Divider-->
      <!--Matches-->
      <div class="col-6 col-md-2">
        <h4><?php echo $plData['matches'] ?? '0'; ?></h4>
        <div class="stats-label">Matches</div>
      </div>
      <!--G/A-->
      <div class="col-6 col-md-2">
        <h4><?php echo $plData['goals_assists'] ?? '0'; ?></h4>
        <div class="stats-label">G/A</div>
      </div>
      <!--Goals-->
      <div class="col-6 col-md-2">
        <h4><?php echo $plData['goals'] ?? '0'; ?></h4>
        <div class="stats-label">Goals</div>
      </div>
      <!--Assists-->
      <div class="col-6 col-md-2">
        <h4><?php echo $plData['assists'] ?? '0'; ?></h4>
        <div class="stats-label">Assists</div>
      </div>
      <!--MOTM-->
      <div class="col-6 col-md-2">
        <h4><?php echo $plData['motm'] ?? '0'; ?></h4>
        <div class="stats-label">MOTM</div>
      </div>
      <!--POTM-->
      <div class="col-6 col-md-2">
        <h4><?php echo $plData['potm'] ?? '0'; ?></h4>
        <div class="stats-label">POTM</div>
      </div>
    </div>

    <!-- Sub-Nav for Profile Options -->
    <div class="sub-nav-tabs mt-3 ">
      <ul class="nav nav-pills">
        <!--Posts-->
        <li class="nav-item"> <a class="nav-link active" data-bs-toggle="tab" href="#tab-posts">Posts</a></li>
        <!--Media-->
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-media">Media</a></li>
        <!--Highlights-->
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-highlights">Highlights</a></li>
        <!--Reposts-->
        <!-- <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-reposts">Reposts</a></li> -->
        <!--Likes-->
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-likes">Likes</a></li>
      </ul>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
      <!-- Posts Tab -->
      <div class="tab-pane fade show active" id="tab-posts">
        <h4 class="mt=0 mb-1">Posts</h4>
        <?php
        if ($resAllPosts && $resAllPosts->num_rows > 0) {
          while ($row = $resAllPosts->fetch_assoc()) {
            // parse needed variables
            $postID = $row['postID'];
            $postOwnerID = $row['user_owner_id'];
            $likeCount = $row['like_count'];
            $commentCount = $row['comment_count'];

            $postType = $row['post_type'];
            $filePath = $row['file_path'];
            $userID = $_SESSION['user_id'] ?? 0;
            $loggedUserID = $_SESSION['user_id'] ?? 0;

            $likeCount = $row['like_count'];
            $commentCount = $row['comment_count'];

            // For user info:
            $ownerName = $row['name'] ?? 'Unknown';
            $ownerUsername = $row['username'] ?? 'user';
            $ownerPic = $row['profile_pic'] ?? 'uploads/profile_pics/default_profile_pic.jpg';
            $postCreated = $row['created_at'];

            // check if current (logged-in) user liked/follows
            $alreadyLiked = false;
            $alreadyFollows = false;

            // if logged in
            if ($loggedUserId > 0) {
              // check like
              $likeCheckSql = "SELECT * FROM likes WHERE post_id='$postID' AND user_id='$loggedUserId'";
              $likeCheckRes = $conn->query($likeCheckSql);
              $alreadyLiked = ($likeCheckRes->num_rows > 0);

              // check follow
              if ($postOwnerID != $loggedUserId) {
                $followCheckSql = "SELECT * FROM follows WHERE follower_id='$loggedUserId' AND followed_id='$postOwnerID'";
                $followCheckRes = $conn->query($followCheckSql);
                $alreadyFollows = ($followCheckRes->num_rows > 0);
              }
            }
            ?>

            <!--  same card HTML as main feed: -->
            <div class="card mb-4">
              <div class="card-body">
                <!-- top row: user info + 3 dots -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <div class="d-flex align-items-center">
                    <img src="https://via.placeholder.com/40" alt="Profile" width="40" height="40"
                      class="rounded-circle me-2">
                    <div>
                      <strong><?php echo $row['username']; ?></strong>
                      <span class="text-muted">@<?php echo strtolower($row['username']); ?></span><br>
                      <small class="text-muted">
                        Posted on <?php echo date('d M, y H:i', strtotime($row['created_at'])); ?>
                      </small>
                    </div>
                  </div>

                  <!--3 dots dropdown-->
                  <div class="dropdown">
                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="bi bi-three-dots"></i> <!-- Using a bootstrap icon -->
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li><a class="dropdown-item" href="#">Save Post</a></li>
                      <!--Only show this if the PostOwner is the logged in user-->
                      <?php if ($postOwnerID == $loggedUserID): ?>
                        <?php if ($row['is_highlight'] == 1): ?> <!--If it's a highlight-->
                          <li><a class="dropdown-item"
                              href="highlight_post.php?post_id=<?php echo $postID; ?>&action=remove">Remove from
                              Highlights</a>
                          </li>
                        <?php else: ?>
                          <!--If it's not highlighted already-->
                          <li><a class="dropdown-item" href="highlight_post.php?post_id=<?php echo $postID; ?>&action=add">Add
                              to
                              Highlights</a></li>
                        <?php endif; ?>
                      <?php endif; ?>

                      <li>
                        <hr class="dropdown-divider">
                      </li>
                      <li><a class="dropdown-item" href="#">Cancel</a></li>
                    </ul>
                  </div>
                </div> <!-- end d-flex justify-content-between -->

                <!-- Middle: post content -->
                <div style="max-width: 800px;" class="mb-3">
                  <?php if ($row['post_type'] === 'image'): ?>
                    <img src="<?php echo $row['file_path']; ?>" class="img-fluid" alt="Post Image">
                  <?php elseif ($row['post_type'] === 'video'): ?>
                    <div style="max-width: 300px; max-height: 350px; margin: 0 auto; overflow: hidden;">
                      <video style="object-fit: contain; width: 100%; height: 100%;" controls>
                        <source src="<?php echo $row['file_path']; ?>" type="video/mp4">
                      </video>
                    </div>
                  <?php elseif ($row['post_type'] === 'text'): ?>
                    <p><?php echo $row['text_content']; ?></p>
                  <?php endif; ?>
                </div>

                <!-- Buttons row -->
                <div class="d-flex align-items-center mb-2">
                  <?php if ($alreadyLiked): ?>
                    <a href="toggle_like.php?post_id=<?php echo $postID; ?>&action=unlike"
                      class="btn btn-link me-3 text-danger">
                      <i class="bi bi-heart-fill"></i>
                    </a>
                  <?php else: ?>
                    <a href="toggle_like.php?post_id=<?php echo $postID; ?>&action=like" class="btn btn-link me-3">
                      <i class="bi bi-heart"></i>
                    </a>
                  <?php endif; ?>

                  <!-- Comment icon (view all comments page) -->
                  <button class="btn btn-link text-decoration-none me-3">
                    <a href="view_comments.php?post_id=<?php echo $postID; ?>">
                      <i class="bi bi-chat-right-dots"></i>
                    </a>
                  </button>

                  <!-- Share Icon, if you want it -->
                  <button class="btn btn-link text-decoration-none me-3">
                    <i class="bi bi-send"></i>
                  </button>
                </div>

                <!-- Like Count -->
                <?php if ($likeCount == 1): ?>
                  <p><strong>1 like</strong></p>
                <?php else: ?>
                  <p><strong><?php echo $likeCount; ?> likes</strong></p>
                <?php endif; ?>

                <!-- If there's a text_content that is a caption (for images/videos) -->
                <?php if (!empty($row['text_content']) && $row['post_type'] != 'text'): ?>
                  <p>
                    <strong><?php echo strtolower($row['username']); ?> </strong>
                    <?php echo $row['text_content']; ?>
                  </p>
                <?php endif; ?>

                <!-- Comments area (show 2 comments, link to see more if $commentCount>2) -->
                <hr>
                <div class="mb-2">
                  <?php
                  // fetch the first 2 comments
                  $commentSql = "SELECT c.comment_text, c.created_at, u.username
                             FROM comments c
                             JOIN users u ON c.user_id = u.id
                             WHERE c.post_id = '$postID'
                             ORDER BY c.created_at ASC
                             LIMIT 2";
                  $commentRes = $conn->query($commentSql);

                  if ($commentRes && $commentRes->num_rows > 0) {
                    while ($cRow = $commentRes->fetch_assoc()) {
                      echo '<p><b>' . $cRow['username'] . ':</b> ' . $cRow['comment_text'] . ' <i>(' .
                        $cRow['created_at'] . ')</i></p>';
                    }
                  } else {
                    echo '<small class="text-muted">No comments yet.</small><br><br>';
                  }

                  if ($commentCount > 2) {
                    echo '<a href="view_comments.php?post_id=' . $postID . '">View all ' . $commentCount . ' comments</a>';
                  }
                  ?>
                </div>

                <!-- Add a new comment form -->
                <form class="d-flex" action="comments.php" method="POST">
                  <input type="hidden" name="post_id" value="<?php echo $postID; ?>">
                  <input class="form-control me-2" type="text" name="comment_text" placeholder="Add a comment...">
                  <button class="btn btn-sm btn-primary" type="submit">Comment</button>
                </form>
              </div><!-- end card-body -->
            </div><!-- end card -->

            <?php
          } // end while
        } else {
          echo "<p>No posts found.</p>";
        }
        ?>
      </div>

      <!-- Media Tab -->
      <div class="tab-pane fade" id="tab-media">
        <h4>Media</h4>
        <p>All image/video posts, etc.</p>

      </div>

      <!-- Highlights Tab -->
      <div class="tab-pane fade" id="tab-highlights">
        <h4 class="mt-0 mb-1">Highlights</h4>
        <?php
        if ($resHighlights && $resHighlights->num_rows > 0) {
          while ($hrow = $resHighlights->fetch_assoc()) {
            // Show a feed- card for each highlight
            $postID = $hrow['postID'];
            $postType = $hrow['post_type'];
            $filePath = $hrow['file_path'];
            $postOwnerID = $hrow['user_owner_id'];
            $userID = $_SESSION['user_id'] ?? 0;
            $loggedUserID = $_SESSION['user_id'] ?? 0;

            $alreadyLiked = false;
            $alreadyFollows = false;

            $likeCount = $hrow['like_count'];
            $commentCount = $hrow['comment_count'];

            // For user info:
            $ownerName = $hrow['name'] ?? 'Unknown';
            $ownerUsername = $hrow['username'] ?? 'user';
            $ownerPic = $hrow['profile_pic'] ?? 'uploads/profile_pics/default_profile_pic.jpg';
            $postCreated = $hrow['created_at'];

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
                    <img src="<?php echo $hrow['profile_pic']; ?>" alt="Profile" width="40" height="40"
                      class="rounded-circle me-2">
                    <div>
                      <!--User account name-->
                      <strong><?php echo $hrow['name']; ?></strong>
                      <!-- user's @ handle -->
                      <span class="text-muted">@<?php echo strtolower($hrow['username']); ?></span><br>
                      <!-- time posted -->
                      <small class="text-muted">
                        Posted on <?php echo date('d M, y H:i', strtotime($hrow['created_at'])); ?>
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
                        <?php if ($hrow['is_highlight'] == 1): ?> <!--If it's a highlight-->
                          <li><a class="dropdown-item"
                              href="highlight_post.php?post_id=<?php echo $postID; ?>&action=remove">Remove from Highlights</a>
                          </li>
                        <?php else: ?>
                          <!--If it's not highlighted already-->
                          <li><a class="dropdown-item" href="highlight_post.php?post_id=<?php echo $postID; ?>&action=add">Add
                              to
                              Highlights</a></li>
                        <?php endif; ?>
                      <?php endif; ?>

                      <li>
                        <hr class="dropdown-divider">
                      </li>
                      <li><a class="dropdown-item" href="#">Cancel</a></li>
                    </ul>
                  </div>
                </div> <!-- end d-flex justify-content-between -->

                <!-- Middle: the actual post content (image/video/text) -->
                <div style="max-width: 800px;" class="mb-3">
                  <div class="mb-3">
                    <?php if ($hrow['post_type'] == "image"): ?>
                      <img src="<?php echo $hrow['file_path']; ?>" class="img-fluid" alt="Post Image">
                    <?php elseif ($hrow['post_type'] == "video"): ?>
                      <div style="max-width: 300px; max-height: 350px; margin: 0 auto; overflow: hidden;">
                        <video style="object-fit: contain; width: 100%; height: auto;" controls>
                          <source src="<?php echo $hrow['file_path']; ?>" type="video/mp4">
                          Your browser does not support the video tag.
                        </video>
                      </div>

                    <?php elseif ($hrow['post_type'] == "text"): ?>
                      <p><?php echo $hrow['text_content']; ?></p>
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
                $likeCount = $hrow['like_count'];
                if ($likeCount == 1) {
                  echo "<p><strong>1 like</strong></p>";
                } else {
                  echo "<p><strong>{$likeCount} likes</strong></p>";
                }
                ?>

                <!-- Caption -->
                <?php if (!empty($hrow['text_content']) && $hrow['post_type'] != 'text'): ?>
                  <p>
                    <strong><?php echo strtolower($hrow['username']); ?> </strong>
                    <?php echo $hrow['text_content']; ?>
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
                  $commentCount = $hrow['comment_count'];
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
        }

        ?>
      </div>
    </div>

    <!-- Reposts Tab -->
    <!-- <div class="tab-pane fade" id="tab-reposts">
        <h4>Reposts</h4>
        <p>All reposts here.</p>
      </div> -->
    <!-- Likes Tab -->
    <div class="tab-pane fade" id="tab-likes">
      <h4 class="mt-0 mb-1">Likes</h4>
      <p>All liked posts here...</p>

      <?php 
      if ($resLikesTab && $resLikesTab->num_rows > 0){
         while ($row = $resLikesTab->fetch_assoc()){
          
          // Show a feed- card for each highlight
            $postID = $row['postID'] ?? 0;
            $postType = $row['post_type'] ?? 'text';
            $filePath = $row['file_path']?? '';
            $postOwnerID = $row['user_owner_id']??0;
            $userID = $_SESSION['user_id'] ?? 0;
            $loggedUserID = $_SESSION['user_id'] ?? 0;

            $alreadyLiked = false;
            $alreadyFollows = false;

            $likeCount = $row['like_count']??0;
            $commentCount = $row['comment_count']??0;

            // For user info:
            $ownerName = $row['name'] ?? 'Unknown';
            $ownerUsername = $row['username'] ?? 'user';
            $ownerPic = $row['profile_pic'] ?? 'uploads/profile_pics/default_profile_pic.jpg';
            $postCreated = $row['created_at']?? '1970-01-01 00:00:00';

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
                          <li><a class="dropdown-item"
                              href="highlight_post.php?post_id=<?php echo $postID; ?>&action=remove">Remove from Highlights</a>
                          </li>
                        <?php else: ?>
                          <!--If it's not highlighted already-->
                          <li><a class="dropdown-item" href="highlight_post.php?post_id=<?php echo $postID; ?>&action=add">Add
                              to
                              Highlights</a></li>
                        <?php endif; ?>
                      <?php endif; ?>

                      <li>
                        <hr class="dropdown-divider">
                      </li>
                      <li><a class="dropdown-item" href="#">Cancel</a></li>
                    </ul>
                  </div>
                </div> <!-- end d-flex justify-content-between -->

                <!-- Middle: the actual post content (image/video/text) -->
                <div style="max-width: 800px;" class="mb-3">
                  <div class="mb-3">
                    <?php if ($row['post_type'] == "image"): ?>
                      <img src="<?php echo $row['file_path']; ?>" class="img-fluid" alt="Post Image">
                    <?php elseif ($row['post_type'] == "video"): ?>
                      <div style="max-width: 300px; max-height: 350px; margin: 0 auto; overflow: hidden;">
                        <video style="object-fit: contain; width: 100%; height: auto;" controls>
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
        }
        ?>

    </div>
  </div>


</div>
</div><!-- container -->


<!--Bootstrap JavaScript-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
  integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
  </script>


</body>

</html>
<?php
$conn->close();
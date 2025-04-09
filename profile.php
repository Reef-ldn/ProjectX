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
$profilePic = !empty($userRow['profile_pic'])
  ? $userRow['profile_pic']
  : 'uploads/profile_pics/Footballer_shooting_b&w.jpg';
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
$profilePic = !empty($userRow['profile_pic'])
  ? $userRow['profile_pic']
  : 'uploads/profile_pics/Footballer_shooting_b&w.jpg';
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
  u.profile_pic,
  (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
  (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
FROM posts p
JOIN users u ON p.user_id = u.id
WHERE p.user_id = '$profileUserId'  /* only that user's posts */
ORDER BY p.created_at DESC";
$resAllPosts = $conn->query($sqlAllPosts);

// Fetch media ( images and videos)
$sqlMedia = "SELECT 
    p.id AS postID,
    p.post_type,
    p.file_path,
    p.text_content,
    p.created_at,
    p.is_highlight,
    u.id AS user_owner_id,
    u.username,
    u.name,
    u.profile_pic,
    (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
    (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
  FROM posts p
  JOIN users u ON p.user_id = u.id
  WHERE p.user_id = '$user_id'  
    AND p.post_type IN ('image','video')
  ORDER BY p.created_at DESC";
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
    p.is_highlight,
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
                AND p.is_highlight = 1
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
    body {
      background-image: url('/ProjectX/uploads/people-soccer-stadium.jpg');
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center;
      background-attachment: fixed;
      /*  no scrolling */
      margin: 0;
      padding: 0;
      position: relative;
      min-height: 100vh;
      overflow-x: hidden;

      /* color: rgba(240,240,240,1); */

    }

    .bg-blur-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      backdrop-filter: blur(4px);
      /* blur effect */
      background-color: rgba(0, 0, 0, 0.3);
      /*  dark tint */
      z-index: 1;
    }

    .main-content {
      position: relative;
      z-index: 2;
    }

    .profile-pic {
      width: 120px;
      height: 120px;
      border-style: solid;
      border-width: 4px;
      border-radius: 50%;
      border-color: #009e42;
      object-fit: cover;
    }

    .name {
      color: white;

    }

    .handle {
      font-size: 22px;
      opacity: 80%;
      color: white;
    }

    .current-team {
      font-size: 19px;
      padding-left: 18px;
      color: white;

    }

    .position {
      font-size: 18px;
      font-weight: 600;
      padding-left: 38px;
      color: white;

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
      margin-top: 22px;
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
      background-color: rgba(3, 145, 63, 0.85);
      color: #fff;
      padding: 12px;
      margin-bottom: 8px;
      border-radius: 8px;
    }

    .green-stats-h1 {
      text-align: center;
      margin-bottom: 5px;
      opacity: 100%;


    }

    /* Divider */
    hr.solid {
      border-top: 3px solid;
      border-style: solid;
    }

    .stats-row .stats-label {
      font-size: 15px;
      opacity: 1;
    }

    /*  styling for the sub-nav tabs */
    .sub-nav-tabs {
      background-color: rgba(30, 30, 30, 0.95);
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

    .card {
      background-color: rgba(30, 30, 30, 0.95);
      color: rgba(240, 240, 240, 1);
    }

    .custom-muted {
      color: white;
      opacity: 0.8;
    }


    .btn-outline-primary {
      color: rgb(32, 145, 32);
      border-color: rgb(36, 156, 20)
    }

    .btn-outline-primary:hover {
      color: #fff;
      background-color: rgb(3, 145, 63);
      border-color: rgba(255, 255, 255, 0.73);
    }

    .btn-outline-primary.focus,
    .btn-outline-primary:focus {
      box-shadow: 0 0 0 .2rem rgba(0, 255, 55, 0.5)
    }

    .btn-primary {
      background-color: #038e63;
    }

    .btn-primary:hover {
      background-color: rgba(189, 160, 0, 0.72);
      opacity: 80%;
    }


    .btn-danger {
      opacity: 0.9;
    }

    .btn-link {
      color: #038e63;
    }

    .bi-chat-right-dots,
    .bi-send {
      color: #038e63;
    }

    .right-bar {
      background-color: rgba(30, 30, 30, 0.8);
      color: rgba(240, 240, 240, 1);
    }
  </style>
</head>

<body>
  <div class="bg-blur-overlay"></div>
  <div class="main-content">

    <!--Navbar start-->
    <nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-black"> <!--Dark Background-->
      <div class="container-fluid">
        <!--Left - Logo + Project Name-->
        <a class="navbar-brand d-flex align-items-center" href="feed.php">
          <img src="\ProjectX\uploads\Logo\Next XI Logo.png" alt="Logo" width="35" height="35" class="me-2">
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
              <a class="nav-link" aria-current="page" href="feed.php">Feed</a> <!--Current Page-->
            </li>
            <li class="nav-item">
              <a class="nav-link" href="upload.php">Upload</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" href="profile.php?user_id=<?php echo $loggedUserId; ?>#">My Profile</a>
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
                <img src="<?php echo $profilePic; ?>" alt="Profile" width="32" height="32" class="rounded-circle">
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <!-- "dropdown-menu-end" to align the menu to the right side -->
                <li><a class="dropdown-item" href="profile.php?user_id=<?php echo $loggedUserId; ?>">My Profile</a></li>
                <li><a class="dropdown-item" href="#">Settings</a></li>
                <li><a class="dropdown-item" href="#">Help/Support</a></li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="logout.php">Log Out</a></li>
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
          <h2 class="name mb-1">
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

          <!-- Follow + message button -->
          <?php if ($loggedUserId != $profileUserId): ?>
            <div class="d-flex align-items-center gap-2">
              <a href="conversation.php?other_id=<?php echo $profileUserId; ?>" class="btn btn-primary btn-lg me-2">
                Message
              </a>

              <?php if ($isFollowing): ?>
                <a href="follow_user.php?followed_id=<?php echo $profileUserId; ?>&action=unfollow"
                  class="btn btn-danger btn-lg">
                  Unfollow
                </a>
              <?php else: ?>
                <a href="follow_user.php?followed_id=<?php echo $profileUserId; ?>&action=follow"
                  class="btn btn-success btn-lg">
                  Follow
                </a>
              <?php endif; ?>
            </div>
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
        <ul class="nav nav-pills justify-content-center">
          <!--Posts-->
          <li class="nav-item me-5"> <a class="nav-link active" data-bs-toggle="tab" href="#tab-posts">Posts</a></li>
          <!--Media-->
          <li class="nav-item me-5"><a class="nav-link" data-bs-toggle="tab" href="#tab-media">Media</a></li>
          <!--Highlights-->
          <li class="nav-item me-5"><a class="nav-link" data-bs-toggle="tab" href="#tab-highlights">Highlights</a></li>
          <!--Reposts-->
          <!-- <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-reposts">Reposts</a></li> -->
          <!--Likes-->
          <li class="nav-item me-5"><a class="nav-link" data-bs-toggle="tab" href="#tab-likes">Likes</a></li>
        </ul>
      </div>
      <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
        <div class="alert alert-success alert-dismissible fade show mt-10" role="alert">
          ✅ Post deleted successfully.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <!-- Tab Content -->
      <div class="tab-content">
        <!-- Posts Tab -->
        <div class="tab-pane fade show active" id="tab-posts">
          <div class="container">
            <div class="row">
              <!--Left or Center Column: 6/12 columns-->
              <div class="offset-md-1 col-md-7">

                <!--<h4 class="mt=0 mb-1">Posts</h4>-->
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
                    $ownerPic = !empty($row['profile_pic']) ? $userRow['profile_pic'] : 'uploads/profile_pics/Footballer_shooting_b&w.jpg';
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
                            <img src="<?php echo $profilePic; ?>" alt="Profile" width="40" height="40"
                              class="rounded-circle me-2">
                            <div>
                              <strong><?php echo $row['name']; ?></strong>
                              <span class="custom-muted">@<?php echo strtolower($row['username']); ?></span><br>
                              <small class="custom-muted">
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
                                  <li><a class="dropdown-item"
                                      href="highlight_post.php?post_id=<?php echo $postID; ?>&action=add">Add
                                      to
                                      Highlights</a></li>
                                <?php endif; ?>
                                <!--DELETE POST-->
                                <li><a class="dropdown-item text-danger" href="delete_post.php?post_id=<?php echo $postID; ?>"
                                    onclick="return confirm('Are you sure you want to delete this post?');">Delete Post</a>
                                </li>
                                </li>
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
                            <video class="w-100" style="max-height: 400px;" controls>
                              <source src="<?php echo $row['file_path']; ?>" type="video/mp4">
                              Your browser does not support the video tag.
                            </video>

                          <?php elseif ($row['post_type'] === 'text'): ?>
                            <p><?php echo $row['text_content']; ?></p>
                          <?php endif; ?>
                        </div>

                        <!-- Buttons row -->
                        <div class="d-flex align-items-center mb-2">
                          <!-- Like Heart Icon -->
                          <a href="#" class="btn btn-link me-3 toggle-like" data-post-id="<?php echo $postID; ?>"
                            data-liked="<?php echo $alreadyLiked ? '1' : '0'; ?>">
                            <i class="bi <?php echo $alreadyLiked ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                          </a>

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
                            echo '<small class="custom-muted">No comments yet.</small><br><br>';
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
              <!-- Right Column: col-md-4 for "Previous Teams" current league -->
              <div class="col-md-3 ms-3">
                <div class="right-bar p-3">
                  <h5>Previous Teams</h5>
                  <p>Team 1 <small>(2000/01 - 2002/03)</small></p>
                  <p>Team 2 <small>(2000/01 - 2002/03)</small></p>
                  <p>Team 3 <small>(2000/01 - 2002/03)</small></p>
                </div>
                <div class="right-bar p-3">
                  <h5>Trophies</h5>
                  <p>Trophy 1 <small>(2003/01)</small></p>
                  <p>Trophy 2 <small>(2003/01)</small></p>
                  <p>Trophy 3 <small>(2003/01)</small></p>
                </div>
                <div class="right-bar p-3">
                  <h5>People You May Know</h5>
                  <p>User 1 <small>@handle</small></p>
                  <p>User 2 <small>@handle</small></p>
                  <p>User 3 <small>@handle</small></p>
                </div>
              </div> <!-- end col-md-4 -->
            </div> <!-- end row -->
          </div>

        </div>


        <!-- Media Tab -->
        <div class="tab-pane fade" id="tab-media">
          <div class="container">
            <div class="row">
              <!--Left or Center Column: 6/12 columns-->
              <div class="offset-md-1 col-md-7">

                <?php
                if ($resMedia && $resMedia->num_rows > 0) {
                  while ($row = $resMedia->fetch_assoc()) {
                    $postID = $row['postID'];
                    $postType = $row['post_type'];
                    $filePath = $row['file_path'];
                    $postOwnerID = $row['user_owner_id'];
                    $userID = $_SESSION['user_id'] ?? 0;
                    $loggedUserID = $_SESSION['user_id'] ?? 0;

                    $likeCount = $row['like_count'] ?? 0;
                    $commentCount = $row['comment_count'] ?? 0;

                    $ownerName = $row['name'] ?? 'Unknown';
                    $ownerUsername = $row['username'] ?? 'user';
                    $ownerPic = !empty($row['profile_pic']) ? $row['profile_pic'] : 'uploads/profile_pics/Footballer_shooting_b&w.jpg';
                    $postCreated = $row['created_at'] ?? '1970-01-01 00:00:00';

                    // Check if the logged-in user has liked this post
                    $alreadyLiked = false;
                    if ($userID > 0) {
                      $likeCheckSql = "SELECT * FROM likes WHERE post_id='$postID' AND user_id='$userID'";
                      $likeCheckResult = $conn->query($likeCheckSql);
                      $alreadyLiked = ($likeCheckResult->num_rows > 0);
                    }


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

                    ?>

                    <!-- Post Card -->
                    <div class="card mb-4">
                      <!--Card Body-->
                      <div class="card-body">

                        <!--Top Part: user pic + username + 3-dot hamburger on the right-->
                        <div class="d-flex justify-content-between align-items-center mb-2">

                          <!--Left side: User profile pic+name+ @username + time-->
                          <div class="d-flex align-items-center">
                            <!--User's Profile Pic-->
                            <img src="<?php echo $ownerPic ?>" alt="Profile" width="40" height="40"
                              class="rounded-circle me-2">
                            <div>
                              <!--User account name-->
                              <strong><?php echo $row['name']; ?></strong>
                              <!-- user's @ handle -->
                              <span class="custom-muted">@<?php echo strtolower($row['username']); ?></span><br>
                              <!-- time posted -->
                              <small class="custom-muted">
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
                                      href="highlight_post.php?post_id=<?php echo $postID; ?>&action=remove">Remove from
                                      Highlights</a>
                                  </li>
                                <?php else: ?>
                                  <!--If it's not highlighted already-->
                                  <li><a class="dropdown-item"
                                      href="highlight_post.php?post_id=<?php echo $postID; ?>&action=add">Add
                                      to
                                      Highlights</a></li>
                                <?php endif; ?>
                                <!--DELETE POST-->
                                <li><a class="dropdown-item text-danger" href="delete_post.php?post_id=<?php echo $postID; ?>"
                                    onclick="return confirm('Are you sure you want to delete this post?');">Delete Post</a>
                                </li>
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
                              <video class="w-100" style="max-height: 400px;" controls>
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
                        <a href="#" class="btn btn-link me-3 toggle-like" data-post-id="<?php echo $postID; ?>"
                          data-liked="<?php echo $alreadyLiked ? '1' : '0'; ?>">
                          <i class="bi <?php echo $alreadyLiked ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                        </a>

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
                          echo '<small class="custom-muted">No comments yet.</small><br><br>';
                        }


                        //Only display 2 comments and hide the rest under a "View all comments" hyperlink
                        $commentCount = $row['comment_count'];
                        if ($commentCount > 2) {
                          echo '<a href="view_comments.php?post_id=' . $postID . '">View all ' . $commentCount . ' comments</a>';
                        }

                        ?>
                        <!--<small class="custom-muted">Comments go here...</small>-->
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

            <!-- Right Column: col-md-4 for "Previous Teams" current league -->
            <div class="col-md-3 ms-3">
              <div class="bg-light p-3">
                <h5>Previous Teams</h5>
                <p>Team 1 <small>(2000/01 - 2002/03)</small></p>
                <p>Team 2 <small>(2000/01 - 2002/03)</small></p>
                <p>Team 3 <small>(2000/01 - 2002/03)</small></p>
              </div>
              <div class="bg-light p-3">
                <h5>Trophies</h5>
                <p>Trophy 1 <small>(2003/01)</small></p>
                <p>Trophy 2 <small>(2003/01)</small></p>
                <p>Trophy 3 <small>(2003/01)</small></p>
              </div>
              <div class="bg-light p-3">
                <h5>People You May Know</h5>
                <p>User 1 <small>@handle</small></p>
                <p>User 2 <small>@handle</small></p>
                <p>User 3 <small>@handle</small></p>
              </div>
            </div> <!-- end col-md-4 -->
          </div> <!-- end row -->
        </div>


        <!-- Highlights Tab -->
        <div class="tab-pane fade" id="tab-highlights">
          <div class="container">
            <div class="row">
              <!--Left or Center Column: 6/12 columns-->
              <div class="offset-md-1 col-md-7">

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
                    $ownerPic = !empty($hrow['profile_pic'])
                      ? $userRow['profile_pic']
                      : 'uploads/profile_pics/Footballer_shooting_b&w.jpg';
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
                              <span class="custom-muted">@<?php echo strtolower($hrow['username']); ?></span><br>
                              <!-- time posted -->
                              <small class="custom-muted">
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
                                      href="highlight_post.php?post_id=<?php echo $postID; ?>&action=remove">Remove from
                                      Highlights</a>
                                  </li>
                                <?php else: ?>
                                  <!--If it's not highlighted already-->
                                  <li><a class="dropdown-item"
                                      href="highlight_post.php?post_id=<?php echo $postID; ?>&action=add">Add
                                      to
                                      Highlights</a></li>
                                <?php endif; ?>
                                <!--DELETE POST-->
                                <li><a class="dropdown-item text-danger" href="delete_post.php?post_id=<?php echo $postID; ?>"
                                    onclick="return confirm('Are you sure you want to delete this post?');">Delete Post</a>
                                </li>
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
                              <video class="w-100" style="max-height: 400px;" controls>
                                <source src="<?php echo $row['file_path']; ?>" type="video/mp4">
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
                        <a href="#" class="btn btn-link me-3 toggle-like" data-post-id="<?php echo $postID; ?>"
                          data-liked="<?php echo $alreadyLiked ? '1' : '0'; ?>">
                          <i class="bi <?php echo $alreadyLiked ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                        </a>

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
                          echo '<small class="custom-muted">No comments yet.</small><br><br>';
                        }


                        //Only display 2 comments and hide the rest under a "View all comments" hyperlink
                        $commentCount = $hrow['comment_count'];
                        if ($commentCount > 2) {
                          echo '<a href="view_comments.php?post_id=' . $postID . '">View all ' . $commentCount . ' comments</a>';
                        }

                        ?>
                        <!--<small class="custom-muted">Comments go here...</small>-->
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
            <!-- Right Column: col-md-4 for "Previous Teams" current league -->
            <div class="col-md-3 ms-3">
              <div class="bg-light p-3">
                <h5>Previous Teams</h5>
                <p>Team 1 <small>(2000/01 - 2002/03)</small></p>
                <p>Team 2 <small>(2000/01 - 2002/03)</small></p>
                <p>Team 3 <small>(2000/01 - 2002/03)</small></p>
              </div>
              <div class="bg-light p-3">
                <h5>Trophies</h5>
                <p>Trophy 1 <small>(2003/01)</small></p>
                <p>Trophy 2 <small>(2003/01)</small></p>
                <p>Trophy 3 <small>(2003/01)</small></p>
              </div>
              <div class="bg-light p-3">
                <h5>People You May Know</h5>
                <p>User 1 <small>@handle</small></p>
                <p>User 2 <small>@handle</small></p>
                <p>User 3 <small>@handle</small></p>
              </div>
            </div> <!-- end col-md-4 -->
          </div> <!-- end row -->
        </div>
      </div>

      <!-- Reposts Tab -->
      <!-- <div class="tab-pane fade" id="tab-reposts">
        <h4>Reposts</h4>
        <p>All reposts here.</p>
      </div> -->

      <!-- Likes Tab -->
      <div class="tab-pane fade" id="tab-likes">
        <div class="container">
          <div class="row">
            <!--Left or Center Column: 6/12 columns-->
            <div class="offset-md-1 col-md-7">

              <?php
              if ($resLikesTab && $resLikesTab->num_rows > 0) {
                while ($row = $resLikesTab->fetch_assoc()) {

                  // Show a feed- card for each highlight
                  $postID = $row['postID'] ?? 0;
                  $postType = $row['post_type'] ?? 'text';
                  $filePath = $row['file_path'] ?? '';
                  $postOwnerID = $row['user_owner_id'] ?? 0;
                  $userID = $_SESSION['user_id'] ?? 0;
                  $loggedUserID = $_SESSION['user_id'] ?? 0;

                  $alreadyLiked = false;
                  $alreadyFollows = false;

                  $likeCount = $row['like_count'] ?? 0;
                  $commentCount = $row['comment_count'] ?? 0;

                  // For user info:
                  $ownerName = $row['name'] ?? 'Unknown';
                  $ownerUsername = $row['username'] ?? 'user';
                  $ownerPic = !empty($row['profile_pic']) ? $userRow['profile_pic'] : 'uploads/profile_pics/Footballer_shooting_b&w.jpg';
                  $postCreated = $row['created_at'] ?? '1970-01-01 00:00:00';

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
                            <span class="custom-muted">@<?php echo strtolower($row['username']); ?></span><br>
                            <!-- time posted -->
                            <small class="custom-muted">
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
                                    href="highlight_post.php?post_id=<?php echo $postID; ?>&action=remove">Remove from
                                    Highlights</a>
                                </li>
                              <?php else: ?>
                                <!--If it's not highlighted already-->
                                <li><a class="dropdown-item"
                                    href="highlight_post.php?post_id=<?php echo $postID; ?>&action=add">Add
                                    to
                                    Highlights</a></li>
                              <?php endif; ?>
                              <!--DELETE POST-->
                              <li><a class="dropdown-item text-danger" href="delete_post.php?post_id=<?php echo $postID; ?>"
                                  onclick="return confirm('Are you sure you want to delete this post?');">Delete Post</a></li>
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
                        <a href="#" class="btn btn-link me-3 toggle-like" data-post-id="<?php echo $postID; ?>"
                          data-liked="<?php echo $alreadyLiked ? '1' : '0'; ?>">
                          <i class="bi <?php echo $alreadyLiked ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                        </a>

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
                          echo '<small class="custom-muted">No comments yet.</small><br><br>';
                        }


                        //Only display 2 comments and hide the rest under a "View all comments" hyperlink
                        $commentCount = $row['comment_count'];
                        if ($commentCount > 2) {
                          echo '<a href="view_comments.php?post_id=' . $postID . '">View all ' . $commentCount . ' comments</a>';
                        }

                        ?>
                        <!--<small class="custom-muted">Comments go here...</small>-->
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
            <!-- Right Column: col-md-4 for "Previous Teams" current league -->
            <div class="col-md-3 ms-3">
              <div class="bg-light p-3">
                <h5>Previous Teams</h5>
                <p>Team 1 <small>(2000/01 - 2002/03)</small></p>
                <p>Team 2 <small>(2000/01 - 2002/03)</small></p>
                <p>Team 3 <small>(2000/01 - 2002/03)</small></p>
              </div>
              <div class="bg-light p-3">
                <h5>Trophies</h5>
                <p>Trophy 1 <small>(2003/01)</small></p>
                <p>Trophy 2 <small>(2003/01)</small></p>
                <p>Trophy 3 <small>(2003/01)</small></p>
              </div>
              <div class="bg-light p-3">
                <h5>People You May Know</h5>
                <p>User 1 <small>@handle</small></p>
                <p>User 2 <small>@handle</small></p>
                <p>User 3 <small>@handle</small></p>
              </div>
            </div> <!-- end col-md-4 -->
          </div> <!-- end row -->
        </div>
      </div>

    </div><!-- container -->


  </div>

  <!--Bootstrap JavaScript-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

  <!--Script to auto refresh the page after deleting a post-->
  <script>
    // Check if deletion just happened
    const urlParams = new URLSearchParams(window.location.search);
    const wasDeleted = urlParams.get('deleted') === '1';
    const wasLiked = urlParams.get('liked') === '1';

    if (wasDeleted || wasLiked) {
      setTimeout(() => {
        // Remove the success banner from the URL
        urlParams.delete('deleted');
        urlParams.delete('liked');

        const newUrl = `${window.location.pathname}?${urlParams.toString()}`;
        window.history.replaceState(null, '', newUrl);

        // Reload the current tab content (Bootstrap tabs)
        const activeTab = document.querySelector('.sub-nav-tabs .nav-link.active');
        if (activeTab) {
          activeTab.click(); // re-triggers the tab to reload content if it's dynamic
        } else {
          location.reload(); // fallback: full reload
        }

      }, 1200); // Wait 1.2 seconds before refreshing
    }
  </script>
  <!--Script to handle likes in place without refreshing the whole page-->
  <script>
    document.querySelectorAll('.toggle-like').forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.preventDefault();

        const postID = this.dataset.postId;
        const alreadyLiked = this.dataset.liked === '1';
        const action = alreadyLiked ? 'unlike' : 'like';
        const icon = this.querySelector('i');
        const url = `toggle_like.php?post_id=${postID}&action=${action}`;

        fetch(url)
          .then(res => res.json())
          .then(data => {
            if (data.status === 'success') {
              // Toggle the icon style
              if (data.liked) {
                icon.classList.remove('bi-heart');
                icon.classList.add('bi-heart-fill', 'text-danger');
                btn.dataset.liked = '1';
              } else {
                icon.classList.remove('bi-heart-fill', 'text-danger');
                icon.classList.add('bi-heart');
                btn.dataset.liked = '0';
              }

              // Update like count
              const countP = btn.closest('.card-body').querySelector('p strong');
              let countText = countP.innerText;
              let currentCount = parseInt(countText) || 0;

              const newCount = data.liked ? currentCount + 1 : currentCount - 1;
              countP.innerText = `${newCount} like${newCount !== 1 ? 's' : ''}`;
            }
          });
      });
    });
  </script>


</body>

</html>
<?php
$conn->close();
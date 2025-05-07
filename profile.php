<!--This page handles displaying the users profile -->

<!--Backend to handle the profile data-->
<?php

session_start();
//The user's account that we are on (user_id taken from the url)
$profileUserId = (int) $_GET['user_id'] ?? 0;
//The user that is currently logged in
$loggedUserId = (int) $_SESSION['user_id'] ?? null;
$loggedIn = isset($_SESSION['user_id']);

//connect to the db
$conn = new mysqli("localhost", "root", "", "projectx_db");    //connect to db
if ($conn->connect_error) {      //check connection
  die("Failed to connect to the database: " . $conn->connect_error);
}

//fetch basic user info from the 'users' table 
$userSql = "SELECT username, name, user_type, profile_pic, banner_pic FROM users WHERE id = '$profileUserId'";
$userResult = $conn->query($userSql);
if ($userResult->num_rows == 0) {         //If no user has this ID, no user is found.
  die("No user found.");
}
$userRow = $userResult->fetch_assoc();

//The user's profile picture
$profilePic = !empty($row['profile_pic']) ? $userRow['profile_pic'] : 'uploads/profile_pics/Footballer_shooting_b&w.jpg';

//If the user type is 'player', fetch from the 'players' table too
if ($userRow['user_type'] == 'player') {
  //Select all players from the players table with the same user ID
  $plSql = "SELECT * FROM players WHERE user_id = '$profileUserId' ";
  $plResult = $conn->query($plSql);   //store this info in a result variable
  //If any player-specific stats found, fetch, otherwise skip
  if ($plResult && $plResult->num_rows > 0) {
    $plData = $plResult->fetch_assoc();
  } else {
    $plData = null;
  }

  //If the user type is a manager, fetch from the 'managers' table
} elseif ($userRow['user_type'] == 'manager') {
  $mgrSql = "SELECT * FROM managers WHERE user_id = '$profileUserId'";
  $mgrResult = $conn->query($mgrSql);
  $mgrData = $mgrResult && $mgrResult->num_rows > 0 ? $mgrResult->fetch_assoc() : null;

  //if the user type is a scout, fetch from the 'scout' table
} elseif ($userRow['user_type'] == 'scout') {
  $scoutSql = "SELECT * FROM scouts WHERE user_id = '$profileUserId'";
  $scoutResult = $conn->query($scoutSql);
  $scoutData = $scoutResult && $scoutResult->num_rows > 0 ? $scoutResult->fetch_assoc() : null;
}

//Network Black Box Handling
//Check if the logged in user is following the user that we are viewing
$sqlCheck = "SELECT * FROM follows WHERE follower_id = '$loggedUserId' AND followed_id = '$profileUserId' ";
$checkResult = $conn->query($sqlCheck);
$isFollowing = ($checkResult->num_rows > 0); //This is true if the user is already following them

//Followers Count (How many people follow this user)
$sqlFollowers = "SELECT COUNT(*) AS followers_count FROM follows WHERE followed_id = '$profileUserId' ";
$resFollowers = $conn->query($sqlFollowers);
$rowFollowers = $resFollowers->fetch_assoc();
$followersCount = $rowFollowers['followers_count'];

//Following Count (How many people this user follows)
$sqlFollowing = "SELECT COUNT(*) AS following_count FROM follows WHERE follower_id = '$profileUserId' ";
$resFollowing = $conn->query($sqlFollowing);
$rowFollowing = $resFollowing->fetch_assoc();
$followingCount = $rowFollowing['following_count'];

//Set the user's display name
$displayName = !empty($userRow['name']) ? $userRow['name'] : "Player " . $profileUserId;
$user_id = $profileUserId; // the user whose profile we are viewing

//Query to get the post count
$sqlPosts = "SELECT COUNT(*) AS totalPosts FROM posts WHERE user_id = '$user_id'";
$resPosts = $conn->query($sqlPosts);
if ($resPosts && $rowPosts = $resPosts->fetch_assoc()) {
  $postCount = $rowPosts['totalPosts'];
} else {
  $postCount = 0;
}

//Query to get the like count
$sqlLikes = "SELECT COUNT(*) AS totalLikes FROM likes l
                    JOIN posts p ON l.post_id = p.id 
                    WHERE p.user_id = '$user_id' ";
$resLikes = $conn->query($sqlLikes);
if ($resLikes && $rowLikes = $resLikes->fetch_assoc()) {
  $likeCount = $rowLikes['totalLikes'];
} else {
  $likeCount = 0; //Like count starts art 0 by default
}

// The user's chosen profile picture
$profilePic = !empty($userRow['profile_pic']) ? $userRow['profile_pic'] : 'uploads/profile_pics/Footballer_shooting_b&w.jpg';
// $bannerPic = $userRow['banner_pic'] ?? 'uploads/profile_pics/default_banner.jpg';

// Fetch all posts
$sqlAllPosts = "SELECT p.id 
                        AS postID, p.post_type, p.file_path, p.text_content, p.created_at, p.is_highlight, u.id 
                        AS user_owner_id, u.username, u.name, u.profile_pic,
                (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
                (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
                 FROM posts p
                 JOIN users u ON p.user_id = u.id
                 WHERE p.user_id = '$profileUserId'  /* only that user's posts */
                 ORDER BY p.created_at DESC"; //Order in reverse chronological order.
$resAllPosts = $conn->query($sqlAllPosts);

// Fetch media ( images and videos) - Same query as above just excluding text posts
$sqlMedia = "SELECT p.id AS postID, p.post_type, p.file_path, p.text_content, p.created_at, p.is_highlight, u.id 
                    AS user_owner_id, u.username, u.name, u.profile_pic,
             (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
             (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
             FROM posts p
             JOIN users u ON p.user_id = u.id /*Join the users table with the posts table */
             WHERE p.user_id = '$user_id' AND p.post_type IN ('image','video') /*Only images and videos */
             ORDER BY p.created_at DESC";
$resMedia = $conn->query($sqlMedia);

//Fetch Highlights - All posts in the post table which the user has set is_highlight on
$sqlHighlights = " SELECT p.id AS postID, p.post_type, p.file_path, p.text_content, p.created_at, p.is_highlight,
                   (SELECT COUNT(*) FROM likes l 
                          WHERE l.post_id = p.id) AS like_count,
                   (SELECT COUNT(*) FROM comments cc 
                          WHERE cc.post_id = p.id) AS comment_count, u.id AS user_owner_id, u.username, u.name, u.profile_pic
                    FROM posts p
                    JOIN users u ON p.user_id = u.id  /*Join the users table with the posts table */
                    WHERE p.user_id = '$profileUserId' AND p.is_highlight = 1 /*Is set as highlighted by the user */
                    ORDER BY p.created_at DESC";
$resHighlights = $conn->query($sqlHighlights);

//Fetch likes (posts the user has Liked)
$sqlLikes = "SELECT p.id AS postID, p.post_type, p.file_path,p.text_content, p.created_at, p.is_highlight, u.id 
                    AS user_owner_id, u.username, u.name, u.profile_pic,
            (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
            (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
            FROM likes l
            JOIN posts p ON l.post_id = p.id  /*Join the post table and the like table*/
            JOIN users u ON p.user_id = u.id
            WHERE l.user_id = '$user_id'  /*All posts that the user has liked*/
            ORDER BY p.created_at DESC";
$resLikesTab = $conn->query($sqlLikes);

//Logic for previous teams
$teamsSql = "SELECT team_name, start_year, end_year FROM previous_teams WHERE user_id = '$profileUserId' 
                    ORDER BY start_year DESC";
$teamsResult = $conn->query($teamsSql);
$teams = []; //Stored in an array
//Loop through
if ($teamsResult && $teamsResult->num_rows > 0) {
  while ($row = $teamsResult->fetch_assoc()) {
    $teams[] = $row;
  }
}

//Logic for trophies (Same logic as above but for trophies)
$trophiesSql = "SELECT trophy_name, year_awarded FROM trophies WHERE user_id = '$profileUserId' 
                       ORDER BY year_awarded DESC";
$trophiesResult = $conn->query($trophiesSql);
$trophies = []; //store in an array
//Loop through
if ($trophiesResult && $trophiesResult->num_rows > 0) {
  while ($row = $trophiesResult->fetch_assoc()) {
    $trophies[] = $row;
  }
}

//Logic for people you amy know
$peopleSql = "SELECT u.id, u.username, u.name, u.profile_pic FROM users u
                     JOIN players p ON u.id = p.user_id /*Join players table with the users table*/
                     WHERE u.id != '$loggedUserId' AND u.id 
                     /*Select all users that the logged in user doesnt follow from their mutual followers*/
                     NOT IN (SELECT followed_id FROM follows WHERE follower_id = '$loggedUserId')
                     LIMIT 3"; /*Only show 3*/
$people = []; //store in an array
$peopleResult = $conn->query($peopleSql);
//Loop Through
if ($peopleResult && $peopleResult->num_rows > 0) {
  while ($row = $peopleResult->fetch_assoc()) {
    $people[] = $row;
  }
}

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

  <!--Navbar stylesheet-->
  <link rel="stylesheet" href="/ProjectX/css/navbar.css">

  <style>
    body {
      background-image: url('/ProjectX/uploads/people-soccer-stadium.jpg');
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center;
      background-attachment: fixed;
      margin: 0;
      padding: 0;
      position: relative;
      min-height: 100vh;
      overflow-x: hidden;
    }

    /*Background*/
    .bg-blur-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      backdrop-filter: blur(4px);
      background-color: rgba(0, 0, 0, 0.3);
      z-index: 1;
    }

    /*Centralise Content*/
    .main-content {
      position: relative;
      z-index: 2;
    }

    /*User Info*/
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

    /*  black box in the top-right of the page (posts, likes, followers, following) */
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

    /*Black Box - info container*/
    .info-box .info-stat {
      flex: 1;
      text-align: center;
      padding: 0px 30px 0px 30px;
    }

    /*Black Box - numbers */
    .info-box h5 {
      margin: 0 0 1px 0;
      font-size: 30px;
      font-weight: 400;
    }

    /* small Text in black box under the numbers */
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

    /*Freen Rows Title*/
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

    /*Green Row - Stat numbers*/
    .stats-row .stats-label {
      font-size: 15px;
      opacity: 1;
    }

    /* Sub-nav tabs */
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

    /*Currently Selected Tab*/
    .sub-nav-tabs .nav-link.active {
      background-color: #009e42;
      border: none;
    }

    .tab-pane h4 {
      margin-top: 0;
      margin-bottom: 0.5rem;
    }

    .tab-highlights {
      margin-top: -20;
    }

    /*Post Card*/
    .card {
      background-color: rgba(30, 30, 30, 0.95);
      color: rgba(240, 240, 240, 1);
    }

    /*Small text in post card*/
    .custom-muted {
      color: white;
      opacity: 0.8;
    }

    /*Outline Primary Buttons*/
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

    /*Filled Priamry Buttons*/
    .btn-primary {
      background-color: #038e63;
    }

    .btn-primary:hover {
      background-color: rgba(189, 160, 0, 0.72);
      opacity: 80%;
    }

    /*Red Buttons*/
    .btn-danger {
      opacity: 0.9;
    }

    .btn-link {
      color: #038e63;
    }

    /*3 dots dropdown*/
    .bi-chat-right-dots,
    .bi-send {
      color: #038e63;
    }

    /*Side Panel*/
    .right-bar {
      background-color: rgba(30, 30, 30, 0.9);
      color: rgba(240, 240, 240, 1);
      border-radius: 10px;
      margin-bottom: 8px;
    }

    .right-bar-wrapper {
      position: sticky;
      top: 70px;
    }

    .bi-three-dots {
      color: #fff;
    }
  </style>
</head>

<body>
  <div class="bg-blur-overlay"></div> <!--Background-->

  <!--Main Container-->
  <div class="main-content">

    <!--Nav Bar-->
    <?php
    // $currentPage = 'profile';
    include 'navbar.php'; ?>

    <!--If the user is logged in-->
    <?php if ($loggedIn) {
      // Get user's profile pic from DB
      $loggedInPic = 'uploads/profile_pics/Footballer_shooting_b&w.jpg';
      $query = $conn->query("SELECT profile_pic FROM users WHERE id = $loggedUserId");
      //If there is a profile pic, display it
      if ($query && $query->num_rows > 0) {
        $profilePicData = $query->fetch_assoc();
        if (!empty($profilePicData['profile_pic'])) {
          $loggedInPic = $profilePicData['profile_pic'];
        }
      }
    }
    ?>


    <!--Main Body Container to centralise eveyrhting-->
    <div class="container mt-5">

      <!-- Profile Details at the top-->
      <!--Row 1  -->
      <div class="row align-items-center mb-2">
        <!-- Profile Pic -->
        <div class="col-auto text-center"> <img src="<?php echo $profilePic; ?>" alt="Profile Picture"
            class="profile-pic"></div>

        <!-- top - user info -->
        <div class="col">
          <h2 class="name mb-1"><?php echo $displayName; ?></h2> <!-- Display user’s name -->
          <!-- The user's handle -->
          <small class="handle">@<?php echo strtolower($userRow['username'] ?? 'player'); ?></small>
          <br>
          </h2>

          <!-- Display current team and position -->
          <small class="current-team"> <!--Current Team-->
            <?= $plData['current_team'] ?? $mgrData['current_team'] ?? $scoutData['current_team'] ?? 'No Team'; ?>
          </small>
          <br>
          <small class="position"> <!--Position-->
            <?= ucfirst($userRow['user_type']) ?>
          </small>

          <!-- The player's preferred position and user type -->
          <small class="position">
            <?php
            //Adjust the preferred position based on the user type
            switch ($userRow['user_type']) {
              case 'player':
                echo $plData['preferred_position'] ?? 'Position'; //Players see their position
                break;
              //Everyone else sees nothing as it's shown somewhere else
              case 'manager':
                echo ' ';
                break;
              case 'scout':
                echo ' ';
                break;
              case 'fan':
                echo ' ';
                break;
              default:
                echo 'User';
            }
            ?>
          </small>
        </div>

        <!-- Right side - Black Info Box and follow button  -->
        <div class="col-auto d-flex flex-column align-items-end ">

          <!-- Black Info box -->
          <div class="info-box mb-3">
            <!--Post Count-->
            <div class="info-stat">
              <h5><?php echo $postCount; ?></h5>
              <small>Posts</small>
            </div>
            <!--Follower COunt-->
            <div class="info-stat">
              <h5><?php echo $followersCount; ?></h5>
              <small>Followers</small>
            </div>
            <!--Following COunt-->
            <div class="info-stat">
              <h5><?php echo $followingCount; ?></h5>
              <small>Following</small>
            </div>
            <!--Like COunt-->
            <div class="info-stat">
              <h5><?php echo $likeCount; ?></h5>
              <small>Likes</small>
            </div>
          </div>

          <!-- Follow and message button -->
          <?php if ($loggedUserId != $profileUserId): ?> <!--Only show if the logged in user isn't view own profile-->
            <div class="d-flex align-items-center gap-2">
              <a href="conversation.php?other_id=<?php echo $profileUserId; ?>" class="btn btn-primary btn-lg me-2">
                Message</a>
              <!--If the logged in user is following them already, show unfollow, if not, follow-->
              <?php if ($isFollowing): ?>
                <a href="follow_user.php?followed_id=<?php echo $profileUserId; ?>&action=unfollow"
                  class="btn btn-danger btn-lg">
                  Unfollow</a>
              <?php else: ?>
                <a href="follow_user.php?followed_id=<?php echo $profileUserId; ?>&action=follow"
                  class="btn btn-success btn-lg">
                  Follow</a>
              <?php endif; ?>
            </div>
          <?php else: ?> <!--If the logged in user is viewing their own profile-->
            <!-- Show "Edit Profile" -->
            <a href="edit_profile.php" class="btn btn-primary btn-lg">Edit Profile</a>
          <?php endif; ?>
        </div>
      </div>

      <!--If the user is a fan, show nothing-->
      <?php if ($userRow['user_type'] != 'fan'): ?>

        <!--If the user is of type player, show player relayed stats-->
        <?php if ($userRow['user_type'] == 'player' && $plData): ?>
          <!-- Green Stats Row 1 - For Players-->
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
          <!-- Green Stats Row 2 - for players -->
          <div class="row stats-row text-center">
            <h3 class="green-stats-h1">Stats:</h3>
            <hr class="solid"> <!--Divider-->
            <!-- Matches -->
            <div class="col-6 col-md-2">
              <h4><?php echo $plData['appearances'] ?? '0'; ?></h4>
              <div class="stats-label">Matches</div>
            </div>
            <!-- G/A -->
            <div class="col-6 col-md-2">
              <!--Matches is just the goals + assists added together-->
              <h4><?php echo isset($plData['goals'], $plData['assists']) ? $plData['goals'] + $plData['assists'] : '0'; ?>
              </h4>
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

          <!--If the user is of type Manager, Show manager related stats-->
        <?php elseif ($userRow['user_type'] == 'manager' && $mgrData): ?>
          <!-- Manager-specific stats -->
          <!--Green row 1: Manager info-->
          <div class="row stats-row text-center justify-content-center mb-3 mt-3">
            <!--Matches Managed-->
            <div class="col-6 col-md-3">
              <h4><?= $mgrData['matches_managed'] ?? '0' ?></h4>
              <div class="stats-label">Matches</div>
            </div>
            <!--Spoken Lanaguage-->
            <div class="col-6 col-md-3">
              <h4><?= !empty($mgrData['spoken_language']) ? $mgrData['spoken_language'] : 'Not Set' ?></h4>
              <div class="stats-label">Language</div>
            </div>
            <!--Country-->
            <div class="col-6 col-md-3">
              <h4><?= !empty($mgrData['country']) ? $mgrData['country'] : 'Not Set' ?></h4>
              <div class="stats-label">Country</div>
            </div>
          </div>
          <!--Green stats row 2 -for manager info-->
          <div class="row stats-row text-center justify-content-center">
            <h3 class="green-stats-h1">Manager Information:</h3>
            <hr class="solid"> <!--Divider-->
            <!--Manager of the Month-->
            <div class="col-6 col-md-3">
              <h4><?= $mgrData['motm'] ?? '0' ?></h4>
              <div class="stats-label">MOTM</div>
            </div>
            <!--Manager of the year-->
            <div class="col-6 col-md-3">
              <h4><?= $mgrData['moty'] ?? '0' ?></h4>
              <div class="stats-label">MOTY</div>
            </div>
            <!--Clean Sheets-->
            <div class="col-6 col-md-3">
              <h4><?= $mgrData['clean sheets'] ?? '0' ?></h4>
              <div class="stats-label">Clean Sheets</div>
            </div>

          </div>

          <!--If the user type is scout-->
        <?php elseif ($userRow['user_type'] == 'scout' && $scoutData): ?>
          <!-- Green Stats Row - Scout -->
          <div class="row stats-row text-center justify-content-center mb-3 mt-3">
            <!--Current Team-->
            <div class="col-6 col-md-3">
              <h4><?= !empty($scoutData['current_team']) ? $scoutData['current_team'] : 'No Team' ?></h4>
              <div class="stats-label">Team</div>
            </div>
            <!--Years Scouted For-->
            <div class="col-6 col-md-3">
              <h4><?= $scoutData['duration'] ?? '0' ?> y</h4>
              <div class="stats-label">Experience</div>
            </div>
            <!--Spoken Langauge-->
            <div class="col-6 col-md-3">
              <h4><?= !empty($scoutData['spoken_language']) ? $scoutData['spoken_language'] : 'Not Set' ?></h4>
              <div class="stats-label">Language</div>
            </div>
            <!--Country-->
            <div class="col-6 col-md-3">
              <h4><?= !empty($scoutData['country']) ? $scoutData['country'] : 'Not Set' ?></h4>
              <div class="stats-label">Country</div>
            </div>
          </div>

        <?php else: ?> <<!--No stats found , aka they're a fan-->
            <p class="text-light">No data available for this profile.</p>
          <?php endif; ?>

          <!-- Sub-Nav for Profile Options -->
          <div class="sub-nav-tabs mt-3 ">
            <ul class="nav nav-pills justify-content-center">
              <!--Posts-->
              <li class="nav-item me-5"> <a class="nav-link active" data-bs-toggle="tab" href="#tab-posts">Posts</a></li>
              <!--Media-->
              <li class="nav-item me-5"><a class="nav-link" data-bs-toggle="tab" href="#tab-media">Media</a></li>
              <!--Highlights-->
              <li class="nav-item me-5"><a class="nav-link" data-bs-toggle="tab" href="#tab-highlights">Highlights</a>
              </li>
              <!--Reposts-->
              <!-- <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-reposts">Reposts</a></li> -->
              <!--Likes-->
              <li class="nav-item me-5"><a class="nav-link" data-bs-toggle="tab" href="#tab-likes">Likes</a></li>
            </ul>
          </div>
          <!--If a post is deleted, show a success banner-->
          <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
            <div class="alert alert-success alert-dismissible fade show mt-10" role="alert">
              ✅ Post deleted successfully.
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>
        <?php endif; ?>

        <!-- Tab Content -->
        <div class="tab-content">

          <!-- Posts Tab -->
          <div class="tab-pane fade show active" id="tab-posts">
            <div class="container"> <!--Container-->
              <div class="row">
                <!--Main Post Section-->
                <div class="offset-md-1 col-md-7">
                  <?php
                  //Loop through posts
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

                      // For user info:
                      $ownerName = $row['name'] ?? 'Unknown';
                      $ownerUsername = $row['username'] ?? 'user';
                      $ownerPic = !empty($row['profile_pic']) ? $row['profile_pic'] : 'uploads/profile_pics/Footballer_shooting_b&w.jpg';
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
                          <!-- top row -  user info and 3 dots -->
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
                                <i class="bi bi-three-dots"></i>
                              </button>
                              <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#">Save Post</a></li> <!--Not functional-->
                                <!--Only show this if the PostOwner is the logged in user-->
                                <?php if ($postOwnerID == $loggedUserID): ?>
                                  <?php if ($row['is_highlight'] == 1): ?> <!--If it's a highlight, let user remove-->
                                    <li><a class="dropdown-item"
                                        href="highlight_post.php?post_id=<?php echo $postID; ?>&action=remove">
                                        Remove from Highlights</a> </li>
                                  <?php else: ?> <!--If it's not highlighted already, let user add-->
                                    <li><a class="dropdown-item"
                                        href="highlight_post.php?post_id=<?php echo $postID; ?>&action=add">
                                        Add to Highlights</a></li>
                                  <?php endif; ?>
                                  <!--Delete post-->
                                  <li><a class="dropdown-item text-danger"
                                      href="delete_post.php?post_id=<?php echo $postID; ?>"
                                      onclick="return confirm('Are you sure you want to delete this post?');">Delete Post</a>
                                  </li>
                                <?php endif; ?>
                                <li>
                                  <hr class="dropdown-divider">
                                </li> <!--Divider-->
                                <li><a class="dropdown-item" href="#">Cancel</a></li>
                              </ul>
                            </div>
                          </div>

                          <!-- Middle row - actual post content -->
                          <div style="max-width: 800px;" class="mb-3">
                            <!--If the post is an image-->
                            <?php if ($row['post_type'] === 'image'): ?>
                              <img src="<?php echo $row['file_path']; ?>" class="img-fluid" alt="Post Image">
                              <!--Video-->
                            <?php elseif ($row['post_type'] === 'video'): ?>
                              <video class="w-100" style="max-height: 400px;" controls>
                                <source src="<?php echo $row['file_path']; ?>" type="video/mp4">
                                Your browser does not support the video tag.
                              </video>
                              <!--Text-->
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
                            <!-- Comment icon (Redirects to View Post page) -->
                            <button class="btn btn-link text-decoration-none me-3">
                              <a href="view_post.php?post_id=<?php echo $postID; ?>">
                                <i class="bi bi-chat-right-dots"></i>
                              </a>
                            </button>
                            <!-- Share Icon - Opens Modal-->
                            <button class="btn btn-link text-decoration-none me-3">
                              <i class="bi bi-send"></i>
                            </button>
                          </div>

                          <!-- Like Count -->
                          <?php if ($likeCount == 1): ?> <!--If only 1 person like, show singualr-->
                            <p><strong>1 like</strong></p>
                          <?php else: ?>
                            <p><strong><?php echo $likeCount; ?> likes</strong></p> <!--Plural-->
                          <?php endif; ?>

                          <!-- If there's a caption (for images/videos) -->
                          <?php if (!empty($row['text_content']) && $row['post_type'] != 'text'): ?>
                            <p>
                              <strong><?php echo strtolower($row['username']); ?> </strong>
                              <?php echo $row['text_content']; ?>
                            </p>
                          <?php endif; ?>

                          <!-- Comments (only show 2 comments, link to see more if $commentCount>2) -->
                          <hr>
                          <div class="mb-2">
                            <?php
                            // fetch the first 2 comments
                            $commentSql = "SELECT c.comment_text, c.created_at, u.username FROM comments c
                                                JOIN users u ON c.user_id = u.id WHERE c.post_id = '$postID'
                                                 ORDER BY c.created_at ASC
                                                 LIMIT 2";
                            $commentRes = $conn->query($commentSql);

                            if ($commentRes && $commentRes->num_rows > 0) {
                              while ($cRow = $commentRes->fetch_assoc()) {
                                //Show the user's info that commented
                                echo '<p><b>' . $cRow['username'] . ':</b> ' . $cRow['comment_text'] . ' <i>(' .
                                  $cRow['created_at'] . ')</i></p>';
                              }
                            } else {  //If no comments exist
                              echo '<small class="custom-muted">No comments yet.</small><br><br>';
                            }
                            //If there are more than 2 comments, show a hyperlink to redirec them to view_post.php
                            if ($commentCount > 2) {
                              echo '<a href="view_post.php?post_id=' . $postID . '">View all ' . $commentCount . ' comments</a>';
                            }
                            ?>
                          </div>

                          <!-- Add a new comment form -->
                          <form class="d-flex" action="comments.php" method="POST">
                            <input type="hidden" name="post_id" value="<?php echo $postID; ?>">
                            <input class="form-control me-2" type="text" name="comment_text" placeholder="Add a comment...">
                            <button class="btn btn-sm btn-primary" type="submit">Comment</button>
                          </form>
                        </div>
                      </div>

                      <?php
                    } // end while
                  } else {
                    echo "<p>No posts found.</p>";
                  }
                  ?>
                </div>
                <!-- Side Bar (Prev teams, pymk and trophies) -->
                <div class="col-md-3 ms-3">
                  <div class="right-bar-wrapper">

                    <!--Side Bar - Previous Teams-->
                    <div class="right-bar p-3">
                      <h5>Previous Teams</h5>
                      <?php if (!empty($teams)): ?>
                        <?php foreach ($teams as $team): ?>
                          <p><?= htmlspecialchars($team['team_name']) ?>
                            <small>(<?= $team['start_year'] ?>/01 - <?= $team['end_year'] ?>/03)</small>
                          </p>
                        <?php endforeach; ?>
                      <?php else: ?> <!--No teams found-->
                        <p>No previous teams found.</p>
                      <?php endif; ?>
                    </div>

                    <!--Side Bar - Trophies-->
                    <div class="right-bar p-3">
                      <h5>Trophies</h5>
                      <?php if (!empty($trophies)): ?>
                        <?php foreach ($trophies as $trophy): ?>
                          <p><?= htmlspecialchars($trophy['trophy_name']) ?>
                            <?php
                            $startYear = (int) $trophy['year_awarded'];
                            $endYear = $startYear + 1;
                            ?>
                            <small>(<?= $startYear ?>/<?= substr($endYear, -2) ?>)</small>
                          </p>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </div>

                    <!--Side Bar - People you may know-->
                    <div class="right-bar p-3">
                      <h5>People You May Know</h5>
                      <?php foreach ($people as $person): ?> <!--Loop through-->
                        <?php
                        $personPic = !empty($person['profile_pic']) ? $person['profile_pic']
                          : 'uploads/profile_pics/Footballer_shooting_b&w.jpg';
                        ?>
                        <div class="d-flex align-items-center mb-2">
                          <img src="<?= $personPic ?>" alt="Profile" width="40" height="40" class="rounded-circle me-2">
                          <div>
                            <p class="mb-0"><?= htmlspecialchars($person['name']) ?></p>
                            <small>@<?= htmlspecialchars($person['username']) ?></small>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>

                  </div>
                </div> <!-- Side Bar -->
              </div> <!--  row -->
            </div> <!--Container-->
          </div> <!--Post Tab-->


          <!-- Media Tab -->
          <div class="tab-pane fade" id="tab-media">
            <div class="container">
              <div class="row">
                <!--Main content areaa-->
                <div class="offset-md-1 col-md-7">

                  <?php
                  if ($resMedia && $resMedia->num_rows > 0) {
                    while ($row = $resMedia->fetch_assoc()) {
                      //Get variables needed
                      $postID = $row['postID'];
                      $postType = $row['post_type'];
                      $filePath = $row['file_path'];
                      $postOwnerID = $row['user_owner_id'];
                      $userID = $_SESSION['user_id'] ?? 0;
                      $loggedUserID = $_SESSION['user_id'] ?? 0;
                      //The Counts
                      $likeCount = $row['like_count'] ?? 0;
                      $commentCount = $row['comment_count'] ?? 0;
                      //Owner info
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
                      // Check if this user already liked
                      if ($userID > 0) {
                        $likeCheckSql = "SELECT * FROM likes WHERE post_id='$postID' AND user_id='$userID'";
                        $likeCheckResult = $conn->query($likeCheckSql);
                        $alreadyLiked = ($likeCheckResult->num_rows > 0);
                      }
                      //check if the user already follows the user
                      if ($loggedUserID > 0) {
                        $checkFollowSql = "SELECT * FROM follows WHERE follower_id='$loggedUserID' AND followed_id='$postOwnerID'";
                        $followRes = $conn->query($checkFollowSql);
                        $alreadyFollows = ($followRes->num_rows > 0);
                      }
                      ?>

                      <!-- Post Card -->
                      <div class="card mb-4">
                        <!--Card Body-->
                        <div class="card-body">

                          <!--Top Row - profile pic, username and 3 dots-->
                          <div class="d-flex justify-content-between align-items-center mb-2">

                            <!--Left -  User profile pic, name, handle and time-->
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

                            <!-- Right -  3-dot dropdown menu -->
                            <div class="dropdown">
                              <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots"></i>
                              </button>
                              <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#">Save Post</a></li> <!--Not Functional-->
                                <!--Only show this if the PostOwner is the logged in user-->
                                <?php if ($postOwnerID == $loggedUserID): ?>
                                  <!--If it's a highlight, let them remove-->
                                  <?php if ($row['is_highlight'] == 1): ?>
                                    <li><a class="dropdown-item"
                                        href="highlight_post.php?post_id=<?php echo $postID; ?>&action=remove">Remove from
                                        Highlights</a> </li>
                                  <?php else: ?>
                                    <!--If it's not highlighted already, let them add-->
                                    <li><a class="dropdown-item"
                                        href="highlight_post.php?post_id=<?php echo $postID; ?>&action=add">Add to
                                        Highlights</a></li>
                                  <?php endif; ?>
                                  <!--Delete Post-->
                                  <li><a class="dropdown-item text-danger"
                                      href="delete_post.php?post_id=<?php echo $postID; ?>"
                                      onclick="return confirm('Are you sure you want to delete this post?');">Delete Post</a>
                                  </li>
                                <?php endif; ?>
                                <li>
                                  <hr class="dropdown-divider"> <!--Divider-->
                                </li>
                                <li><a class="dropdown-item" href="#">Cancel</a></li>
                              </ul>
                            </div>
                          </div>

                          <!-- Middle - the actual post content (image/video/text) -->
                          <div style="max-width: 800px;" class="mb-3">
                            <!--Image vid or text-->
                            <?php if ($row['post_type'] == "image"): ?>
                              <img src="<?php echo $row['file_path']; ?>" class="img-fluid" alt="Post Image">
                            <?php elseif ($row['post_type'] == "video"): ?>
                              <video class="w-100" style="max-height: 400px;" controls>
                                <source src="<?php echo $row['file_path']; ?>" type="video/mp4">
                                Your browser does not support the video tag.
                              </video>
                            <?php elseif ($row['post_type'] == "text"): ?>
                              <p><?php echo $row['text_content']; ?></p>
                            <?php endif; ?>
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
                              <a href="view_post.php?post_id=<?php echo $postID; ?>.">
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
                            // query to fetch the first 2 comments
                            $commentSql = "SELECT c.comment_text, c.created_at, u.username FROM comments c
                                    JOIN users u ON c.user_id = u.id WHERE c.post_id = '$postID'
                                    ORDER BY c.created_at ASC
                                    LIMIT 2"; /*Only show 2*/
                            $commentRes = $conn->query($commentSql);
                            //Loop
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
                              echo '<a href="view_post.php?post_id=' . $postID . '">View all ' . $commentCount . ' comments</a>';
                            }
                            ?>
                          </div>

                          <!--Comments Form-->
                          <form class="d-flex" action="comments.php" method="POST">
                            <input type="hidden" name="post_id" value="<?php echo $postID; ?>">
                            <input class="form-control me-2" type="text" name="comment_text" placeholder="Add a comment...">
                            <button class="btn btn-sm btn-primary" type="submit">Comment</button>
                          </form>

                        </div> <!-- end card-body -->
                      </div>

                      <?php
                    } // end while
                  }
                  ?>

                </div>

                <!-- Side Bar (prev teams, pymk, troph) -->
                <div class="col-md-3 ms-3">
                  <div class="right-bar-wrapper">
                    <!--Prev Teams-->
                    <div class="right-bar p-3">
                      <h5>Previous Teams</h5>
                      <?php if (!empty($teams)): ?>
                        <?php foreach ($teams as $team): ?>
                          <p><?= htmlspecialchars($team['team_name']) ?>
                            <small>(<?= $team['start_year'] ?>/01 - <?= $team['end_year'] ?>/03)</small>
                          </p>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <p>No previous teams found.</p>
                      <?php endif; ?>
                    </div>
                    <!--Trophies-->
                    <div class="right-bar p-3">
                      <h5>Trophies</h5>
                      <?php if (!empty($trophies)): ?>
                        <?php foreach ($trophies as $trophy): ?>
                          <p><?= htmlspecialchars($trophy['trophy_name']) ?>
                            <?php
                            $startYear = (int) $trophy['year_awarded'];
                            $endYear = $startYear + 1;
                            ?>
                            <small>(<?= $startYear ?>/<?= substr($endYear, -2) ?>)</small>
                          </p>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </div>
                    <!--PYMK-->
                    <div class="right-bar p-3">
                      <h5>People You May Know</h5>
                      <?php foreach ($people as $person): ?>
                        <?php
                        $personPic = !empty($person['profile_pic'])
                          ? $person['profile_pic']
                          : 'uploads/profile_pics/Footballer_shooting_b&w.jpg';
                        ?>
                        <div class="d-flex align-items-center mb-2">
                          <img src="<?= $personPic ?>" alt="Profile" width="40" height="40" class="rounded-circle me-2">
                          <div>
                            <p class="mb-0"><?= htmlspecialchars($person['name']) ?></p>
                            <small>@<?= htmlspecialchars($person['username']) ?></small>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>

                  </div>
                </div> <!--Side Bar-->

              </div>
            </div>
          </div> <!--Media-->


          <!-- Highlights Tab -->
          <div class="tab-pane fade" id="tab-highlights">
            <div class="container">
              <div class="row">
                <!--Actual Post Content-->
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
                      $ownerPic = !empty($hrow['profile_pic']) ? $userRow['profile_pic'] : 'uploads/profile_pics/Footballer_shooting_b&w.jpg';
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
                      $commentSql = "SELECT c.comment_text, c.created_at, u.username FROM comments c
                         JOIN users u ON c.user_id = u.id WHERE c.post_id = '$postID'
                         ORDER BY c.created_at ASC LIMIT 2";
                      $commentRes = $conn->query($commentSql);
                      ?>

                      <div class="card mb-4">
                        <!--Card Body-->
                        <div class="card-body">

                          <!-- Top -  pic, username, 3-dot -->
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <!--Left -  profile pic, name, handle, time-->
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

                            <!-- Right -  3-dot dropdown menu -->
                            <div class="dropdown">
                              <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots"></i>
                              </button>
                              <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#">Save Post</a></li> <!--Not functional-->
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
                                  <li><a class="dropdown-item text-danger"
                                      href="delete_post.php?post_id=<?php echo $postID; ?>"
                                      onclick="return confirm('Are you sure you want to delete this post?');">Delete Post</a>
                                  </li>
                                <?php endif; ?>
                                <li>
                                  <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="#">Cancel</a></li>
                              </ul>
                            </div>
                          </div>

                          <!-- the actual post content (image/video/text) -->
                          <div style="max-width: 800px;" class="mb-3">
                            <div class="mb-3">
                              <?php if ($hrow['post_type'] == "image"): ?>
                                <img src="<?php echo $hrow['file_path']; ?>" class="img-fluid" alt="Post Image">
                              <?php elseif ($hrow['post_type'] == "video"): ?>
                                <video class="w-100" style="max-height: 400px;" controls>
                                  <source src="<?php echo $hrow['file_path']; ?>" type="video/mp4">
                                  Your browser does not support the video tag.
                                </video>
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
                              <a href="view_post.php?post_id=<?php echo $postID; ?>.">
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
                              echo '<a href="view_post.php?post_id=' . $postID . '">View all ' . $commentCount . ' comments</a>';
                            }
                            ?>
                          </div>
                          <!--Comments Form-->
                          <form class="d-flex" action="comments.php" method="POST">
                            <input type="hidden" name="post_id" value="<?php echo $postID; ?>">
                            <input class="form-control me-2" type="text" name="comment_text" placeholder="Add a comment...">
                            <button class="btn btn-sm btn-primary" type="submit">Comment</button>
                          </form>
                        </div>
                      </div>

                      <?php
                    } // end while
                  }

                  ?>
                </div>
                <!-- Side bar - PYMK, prev teams, troph -->
                <div class="col-md-3 ms-3">
                  <div class="right-bar-wrapper">
                    <div class="right-bar p-3">
                      <!--Prev Teams-->
                      <h5>Previous Teams</h5>
                      <?php if (!empty($teams)): ?>
                        <?php foreach ($teams as $team): ?>
                          <p><?= htmlspecialchars($team['team_name']) ?>
                            <small>(<?= $team['start_year'] ?>/01 - <?= $team['end_year'] ?>/03)</small>
                          </p>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <p>No previous teams found.</p>
                      <?php endif; ?>
                    </div>
                    <!--Trophies-->
                    <div class="right-bar p-3">
                      <h5>Trophies</h5>
                      <?php if (!empty($trophies)): ?>
                        <?php foreach ($trophies as $trophy): ?>
                          <p><?= htmlspecialchars($trophy['trophy_name']) ?>
                            <?php
                            $startYear = (int) $trophy['year_awarded'];
                            $endYear = $startYear + 1;
                            ?>
                            <small>(<?= $startYear ?>/<?= substr($endYear, -2) ?>)</small>
                          </p>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </div>
                    <!--PYMK-->
                    <div class="right-bar p-3">
                      <h5>People You May Know</h5>
                      <?php foreach ($people as $person): ?>
                        <?php $personPic = !empty($person['profile_pic']) ? $person['profile_pic'] : 'uploads/profile_pics/Footballer_shooting_b&w.jpg'; ?>
                        <div class="d-flex align-items-center mb-2">
                          <img src="<?= $personPic ?>" alt="Profile" width="40" height="40" class="rounded-circle me-2">
                          <div>
                            <p class="mb-0"><?= htmlspecialchars($person['name']) ?></p>
                            <small>@<?= htmlspecialchars($person['username']) ?></small>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div> <!-- Side bar-->

              </div>
            </div>
          </div> <!--Highlights-->

          <!-- Reposts Tab -->
          <!-- <div class="tab-pane fade" id="tab-reposts">
        <h4>Reposts</h4>
        <p>All reposts here.</p>
      </div> -->

          <!-- Likes Tab -->
          <div class="tab-pane fade" id="tab-likes">
            <div class="container">
              <div class="row">
                <!--Main Post Area-->
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
                      $ownerPic = !empty($row['profile_pic']) ? $row['profile_pic'] : 'uploads/profile_pics/Footballer_shooting_b&w.jpg';
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
                      $commentSql = "SELECT c.comment_text, c.created_at, u.username FROM comments c
                         JOIN users u ON c.user_id = u.id WHERE c.post_id = '$postID'
                         ORDER BY c.created_at ASC LIMIT 2";
                      $commentRes = $conn->query($commentSql);
                      ?>
                      <div class="card mb-4">
                        <!--Card Body-->
                        <div class="card-body">

                          <!--Top-->
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <!--Left - pic, name, handle, time-->
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
                            <!-- Right 3-dot dropdown menu -->
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
                                  <!--Delete post-->
                                  <li><a class="dropdown-item text-danger"
                                      href="delete_post.php?post_id=<?php echo $postID; ?>"
                                      onclick="return confirm('Are you sure you want to delete this post?');">Delete Post</a>
                                  </li>
                                <?php endif; ?>
                                <li>
                                  <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="#">Cancel</a></li>
                              </ul>
                            </div>
                          </div>

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
                              <a href="view_post.php?post_id=<?php echo $postID; ?>.">
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
                              echo '<a href="view_post.php?post_id=' . $postID . '">View all ' . $commentCount . ' comments</a>';
                            }
                            ?>
                          </div>

                          <!--Comments Form-->
                          <form class="d-flex" action="comments.php" method="POST">
                            <input type="hidden" name="post_id" value="<?php echo $postID; ?>">
                            <input class="form-control me-2" type="text" name="comment_text" placeholder="Add a comment...">
                            <button class="btn btn-sm btn-primary" type="submit">Comment</button>
                          </form>
                        </div>
                      </div>
                      <?php
                    } // end while
                  }
                  ?>
                </div>
                <!-- Side bar - PYMK, troph, prev teams -->
                <div class="col-md-3 ms-3">
                  <div class="right-bar-wrapper">
                    <!--Prev teams-->
                    <div class="right-bar p-3">
                      <h5>Previous Teams</h5>
                      <?php if (!empty($teams)): ?>
                        <?php foreach ($teams as $team): ?>
                          <p><?= htmlspecialchars($team['team_name']) ?>
                            <small>(<?= $team['start_year'] ?>/01 - <?= $team['end_year'] ?>/03)</small>
                          </p>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <p>No previous teams found.</p>
                      <?php endif; ?>
                    </div>
                    <!--Troph-->
                    <div class="right-bar p-3">
                      <h5>Trophies</h5>
                      <?php if (!empty($trophies)): ?>
                        <?php foreach ($trophies as $trophy): ?>
                          <p><?= htmlspecialchars($trophy['trophy_name']) ?>
                            <?php
                            $startYear = (int) $trophy['year_awarded'];
                            $endYear = $startYear + 1;
                            ?>
                            <small>(<?= $startYear ?>/<?= substr($endYear, -2) ?>)</small>
                          </p>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </div>
                    <!--PYMK-->
                    <div class="right-bar p-3">
                      <h5>People You May Know</h5>
                      <?php foreach ($people as $person): ?>
                        <?php $personPic = !empty($person['profile_pic']) ? $person['profile_pic'] : 'uploads/profile_pics/Footballer_shooting_b&w.jpg'; ?>
                        <div class="d-flex align-items-center mb-2">
                          <img src="<?= $personPic ?>" alt="Profile" width="40" height="40" class="rounded-circle me-2">
                          <div>
                            <p class="mb-0"><?= htmlspecialchars($person['name']) ?></p>
                            <small>@<?= htmlspecialchars($person['username']) ?></small>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>

                  </div>
                </div> <!--Side Bar-->
              </div>
            </div>
          </div> <!--Likes-->

        </div><!-- Tab Content -->
    </div>

    <!--Bootstrap JavaScript-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
      </script>

    <!--Script to auto refresh the page after deleting or liking a post-->
    <script>
      // Check if deletion just happened
      const urlParams = new URLSearchParams(window.location.search);
      //Check for query parameters that indicate a delete or like just happened
      const wasDeleted = urlParams.get('deleted') === '1';
      const wasLiked = urlParams.get('liked') === '1';

      //If a post was just deleted or liked
      if (wasDeleted || wasLiked) {
        setTimeout(() => {
          // Remove the success banner from the URL
          urlParams.delete('deleted');
          urlParams.delete('liked');
          const newUrl = `${window.location.pathname}?${urlParams.toString()}`;
          window.history.replaceState(null, '', newUrl);

          // Reload the current tab content 
          const activeTab = document.querySelector('.sub-nav-tabs .nav-link.active');
          if (activeTab) {
            activeTab.click(); // re-triggers the tab to reload content
          } else {
            location.reload(); // fallback: reload the page
          }
        }, 1200); // Wait 1.2 seconds before refreshing
      }
    </script>

    <!--Script to handle likes without refreshing the whole page (AJAX)-->
    <script>
      document.querySelectorAll('.toggle-like').forEach(btn => {
        btn.addEventListener('click', function (e) {
          e.preventDefault(); //Prevent the normal link behaviour (refresh to the top)

          const postID = this.dataset.postId; //Get the Post ID from the data attribute
          const alreadyLiked = this.dataset.liked === '1';
          const action = alreadyLiked ? 'unlike' : 'like';  //Decide the action type
          const icon = this.querySelector('i'); //Get the icon element inside the button
          const url = `toggle_like.php?post_id=${postID}&action=${action}`; //Endpoint for toggle

          //Send a request to the server using fetch
          fetch(url)
            .then(res => res.json())  //Parse JSON response
            .then(data => {
              if (data.status === 'success') {
                // Update the like icon based on the like state
                if (data.liked) { //When Liked
                  icon.classList.remove('bi-heart');
                  icon.classList.add('bi-heart-fill', 'text-danger');
                  btn.dataset.liked = '1';  //Mark as liked
                } else {  //When Unliked
                  icon.classList.remove('bi-heart-fill', 'text-danger');
                  icon.classList.add('bi-heart');
                  btn.dataset.liked = '0';  //Mark as unliked
                }

                // Update the like count shown in the post card
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

    <?php
    $conn->close();
    ?>

</body>

</html>
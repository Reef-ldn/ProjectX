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

    .stats-row .stats-label {
      font-size: 15px;
      opacity: 0.8;
    }

    /*  styling for the sub-nav tabs */
    .sub-nav-tabs {
      background-color: #444;
      border-radius: 8px;
      padding: 10px;
      margin-bottom: 20px;
    }

    .sub-nav-tabs .nav-link {
      color: #fff;
      margin: 0 5px;
    }

    .sub-nav-tabs .nav-link.active {
      background-color: #009e42;
      border: none;
    }
  </style>
</head>


<body class="bg-light">

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
    <div class="row align-items-center mb-4">
      <!-- Left col: big round pic -->
      <div class="col-auto text-center">
        <img src="<?php echo $profilePic; ?>" alt="Profile Picture" class="profile-pic">
      </div>

      <!-- Middle col: user info -->
      <div class="col">
        <!-- Display user’s name -->
        <h2 class="mb-1">
          <?php echo $userRow['username'] ?? 'Player Name'; ?>

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
    <div class="row stats-row mb-3 text-center">
      <!--Height-->
      <div class="col-6 col-md-3">
        <h4><?php echo ($plData['height'] ?? 0) . 'cm'; ?></h4>
        <div class="stats-label">Height</div>
      </div>
      <!--Weight-->
      <div class="col-6 col-md-3">
        <h4><?php echo ($plData['weight'] ?? 0) . 'kg'; ?></h4>
        <div class="stats-label">Weight</div>
      </div>
      <!--Preferred Foot-->
      <div class="col-6 col-md-3">
        <h4><?php echo ($plData['preferred_foot'] ?? 'Right'); ?></h4>
        <div class="stats-label">Foot</div>
      </div>
      <!--Country-->
      <div class="col-6 col-md-3">
        <h4><?php echo ($plData['country'] ?? 'England'); ?></h4>
        <div class="stats-label">Country</div>
      </div>
    </div>

    <!-- Green Stats Row 2 -->
    <div class="row stats-row mb-3 text-center">
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
    <div class="sub-nav-tabs mb-3">
      <ul class="nav nav-pills">
        <!--Posts-->
        <li class="nav-item"> <a class="nav-link active" data-bs-toggle="tab" href="#tab-posts">Posts</a></li>
        <!--Media-->
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-media">Media</a></li>
        <!--Highlights-->
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-highlights">Highlights</a></li>
        <!--Reposts-->
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-reposts">Reposts</a></li>
        <!--Likes-->
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-likes">Likes</a></li>
      </ul>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
      <!-- Posts Tab -->
      <div class="tab-pane fade show active" id="tab-posts">
        <h4>Posts</h4>
        <!-- Show the user's posts -->
        <div class="card mb-3">
          <div class="card-body">
            <p>Example user post content here...</p>
            <!-- Maybe an image or video, etc. -->
          </div>
        </div>
      </div>
      <!-- Media Tab -->
      <div class="tab-pane fade" id="tab-media">
        <h4>Media</h4>
        <p>All image/video posts, etc.</p>
      </div>
      <!-- Highlights Tab -->
      <div class="tab-pane fade" id="tab-highlights">
        <h4>Highlights</h4>
        <p>All highlight posts or data here.</p>
      </div>
      <!-- Reposts Tab -->
      <div class="tab-pane fade" id="tab-reposts">
        <h4>Reposts</h4>
        <p>All reposts here.</p>
      </div>
      <!-- Likes Tab -->
      <div class="tab-pane fade" id="tab-likes">
        <h4>Likes</h4>
        <p>All liked posts here...</p>
      </div>
    </div>


    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home"
          type="button" role="tab" aria-controls="pills-home" aria-selected="true">Nexts</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile"
          type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Media</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-contact"
          type="button" role="tab" aria-controls="pills-contact" aria-selected="false">Highlights</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-contact"
          type="button" role="tab" aria-controls="pills-contact" aria-selected="false">Likes</button>
      </li>

    </ul>
    <div class="tab-content" id="pills-tabContent">
      <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab"
        tabindex="0">...</div>
      <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab" tabindex="0">
        ...</div>
      <div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab" tabindex="0">
        ...</div>
      <div class="tab-pane fade" id="pills-disabled" role="tabpanel" aria-labelledby="pills-disabled-tab" tabindex="0">
        ...</div>
    </div>
  </div>
  </div><!-- /container -->








  <br><br><BR><br><br>

  <h1 class="mb-3">Profile Page</h1>

  <!--Form to allow a user to upload a profile picture and banner of their choice-->
  <h2>Upload Profile & Banner</h2>
  <form action="upload_profile_pic.php" method="POST" enctype="multipart/form-data">
    <div>
      <label>Profile Picture:</label>
      <input type="file" name="profile_pic" accept="image/*">
    </div>
    <div>
      <label>Banner Image:</label>
      <input type="file" name="banner_pic" accept="image/*">
    </div>
    <button type="submit" name="submit">Confirm</button>
  </form>

  <!--Top Section for username-->
  <div class="card p-3">
    <p>Username: <?php echo $userRow['username']; ?> </p> <!--Display the username-->
    <p>Type: <?php echo $userRow['user_type']; ?> </p> <!--Display the user type-->

    <!--Container to move elements to the right of the page-->
    <div class="d-flex flex-row-reverse">

      <!--Display the amount of Followers and followings-->
      <?php echo "<p>Following: $followingCount</p>"; ?>
      <?php echo "<p>Followers: $followersCount</p>"; ?>

    </div>


    <!--Display a 'Follow User' Button, if the profile being viewed is not the same as the logged in user -->
    <?php if ($loggedUserId != $profileUserId) {
      //show the follow or unfollow Button
      if ($isFollowing) {
        //Show an "unfollow" link
        echo "<a href = 'follow_user.php?followed_id=$profileUserId&action=unfollow'>Unfollow</a> ";
      } else {
        //Show a "Follow" link
        echo "<a href = 'follow_user.php?followed_id=$profileUserId&action=follow'>Follow</a> ";
      }
    }
    ?>
  </div>




  <!--Display a 'send Message' form if the profile being viewd is not the same as the logged in user-->
  <?php if ($loggedUserId != $profileUserId): ?>
    <!--Form to allow users to send a message-->

    <h3>Message This User:</h3>
    <form action="send_message.php" method="POST">
      <!-- hidden input to specify who is being messaged-->
      <input type="hidden" name="receiver_id" value="<?php echo $profileUserId; ?>">

      <label> Your Message: </label> <br>
      <textarea name="content" rows="3" cols="40"></textarea> <br><br>

      <button type="submit">Send</button>
    </form>

  <?php endif; ?>

  <!--Display a 'send Message' form if the profile being viewd is not the same as the logged in user-->
  <?php if ($loggedUserId != $profileUserId): ?>
    <!--Form to allow users to send a message-->

    <h3>Message This User:</h3>
    <a href="conversation.php?other_id=<?php echo $profileUserId; ?>">Send Private Message</a>

  <?php endif; ?>




  <!--If the user is a player, display player related details-->
  <?php if ($userRow['user_type'] == 'player'): ?>
    <h2>Player Info</h2>

    <!--Display the player's info-->
    <?php if ($plData): ?>


      <?php if ($_SESSION['user_id'] == $profileUserId && $userRow['user_type'] == 'player'): ?>
        <a href='edit_profile.php'>Edit Profile</a>
      <?php endif; ?>

      <!--If there is a row in 'players' with this info -->
      <p>Height: <?php echo $plData['height']; ?> cm </p> <!-- height-->
      <p>Weight: <?php echo $plData['weight']; ?> kg </p> <!-- weight-->
      <p>Preferred Position: <?php echo $plData['preferred_position']; ?> </p> <!-- Preferred Position-->
      <p>Preferred Foot: <?php echo $plData['preferred_foot']; ?> </p> <!-- preferred foot-->
      <p>Goals: <?php echo $plData['goals']; ?> </p> <!-- goals-->
      <p>Assists: <?php echo $plData['assists']; ?> </p> <!-- assists-->
      <p>MOTM: <?php echo $plData['motm']; ?> </p> <!-- motm-->
      <p>POTM: <?php echo $plData['potm']; ?> </p> <!-- potm-->
      <p>Awards: <?php echo $plData['awards']; ?> </p> <!-- awards-->
      <p>Country: <?php echo $plData['country']; ?> </p> <!-- country-->
      <p>Current League: <?php echo $plData['current_league']; ?> </p> <!-- current league-->
      <p>Current Team: <?php echo $plData['current_team']; ?> </p> <!-- current team-->

      <!--If the user has a profile pic uploaded, display this too-->
      <?php if (!empty($plData['profile_pic'])): ?>
        <img src="<?php echo $plData['profile_pic']; ?>" width="200" />
      <?php endif; ?>


    <?php else: ?>
      <p> No information was found for user <?php echo $profileUserId; ?> . </p>
    <?php endif; ?>
  <?php endif; ?>

  </div>

  <!--Bootstrap JavaScript-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>



</body>

</html>
<?php
$conn->close();
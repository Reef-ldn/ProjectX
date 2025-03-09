<!--This page handles displaying the users profile -->

<!--Backend to handle the profile data-->
<?php
session_start();
//The user's account that we are on (user_id taken from the url)
$profileUserId = $_GET['user_id'] ?? 0;

//The user that is currently logged in
$loggedUserId = $_SESSION['user_id'] ?? 0;

//connect to the db
$conn = new mysqli("localhost", "root", "", "projectx_db");    //connect to db
if ($conn->connect_error) {      //check connection
  die("Failed to connect to the database: " . $conn->connect_error);
}

//fetch from the 'users' table
$userSql = "SELECT username, user_type FROM users WHERE id = '$profileUserId' ";
$userResult = $conn->query($userSql);
if ($userResult->num_rows == 0) {         //If no user has this ID, no user is found.
  die("No user found.");
}
$userRow = $userResult->fetch_assoc();

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
</head>

<body>

  <!--Navbar start-->
  <nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark" > <!--Dark Background-->
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
  <div class="container mt-5 pt-4">
    <h1 class="mb-3">Profile Page</h1>

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
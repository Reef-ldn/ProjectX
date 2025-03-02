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
  if($conn->connect_error) {      //check connection
    die("Failed to connect to the database: " . $conn->connect_error);
  }

  //fetch from the 'users' table
  $userSql = "SELECT username, user_type FROM users WHERE id = '$profileUserId' " ;
  $userResult = $conn->query($userSql);
  if($userResult->num_rows == 0 ) {         //If no user has this ID, no user is found.
    die("No user found.");
  }
  $userRow = $userResult->fetch_assoc();

  //If the user type is 'player', fetch from the 'players' table too
  if($userRow['user_type'] == 'player') {
    //Select all players from the players table with the same user ID
    $plSql = "SELECT * FROM players WHERE user_id = '$profileUserId' "; 
    $plResult = $conn->query($plSql);   //store this info in a result variable
    if($plResult && $plResult->num_rows>0){
      $plData = $plResult->fetch_assoc();
    } else{
      $plData = null;
    }
  }

  //Check if the logged in user is following the user that we are viewing
  $sqlCheck = "SELECT * FROM follows
                WHERE follower_id = '$loggedUserId'
                AND followed_id = '$profileUserId' ";
  $checkResult = $conn->query($sqlCheck);
  $isFollowing = ($checkResult->num_rows > 0); //This is true if the user is already following them


  
?>

<!--Front-end to display the profile-->
<!DOCTYPE html>
  <html>

    <head>
      <title>Profile</title>
    </head>
  
    <body>
      <h1>Profile Page</h1> 

      <p>Username: <?php echo $userRow['username']; ?> </p>   <!--Display the username-->
      <p>Type: <?php echo $userRow['user_type']; ?> </p>      <!--Display the user type-->


      <?php echo "<p>DEBUG: Logged in user = $loggedUserId,  Viewing profile user = $profileUserId</p>"; ?>

      <!--Display a 'Follow User' Button, if the profile being viewed if not the same as the logged in user-->
      <?php if($loggedUserId != $profileUserId) { 
        //show the follow or unfollow Button
        if($isFollowing) {
          //Show an "unfollow" link
          echo "<a href = 'follow_user.php?followed_id=$profileUserId&action=unfollow'>Unfollow</a> ";
        } else {
          //Show a "Follow" link
            echo "<a href = 'follow_user.php?followed_id=$profileUserId&action=follow'>Follow</a> ";
        }
      }
      ?>


      <!--If the user is a player, display player related details-->
      <?php if($userRow['user_type'] == 'player'): ?>
        <h2>Player Info</h2>

        <!--Display the player's info-->
        <?php if($plData):   ?>

          
          <?php if($_SESSION['user_id'] == $profileUserId && $userRow['user_type'] == 'player'): ?>
            <a href = 'edit_profile.php'>Edit Profile</a>
          <?php endif; ?>
      
          <!--If there is a row in 'players' with this info -->
          <p>Height: <?php echo $plData['height']; ?> cm </p>                         <!-- height-->
          <p>Weight: <?php echo $plData['weight']; ?> kg </p>                         <!-- weight-->
          <p>Preferred Position: <?php echo $plData['preferred_position']; ?> </p>    <!-- Preferred Position-->
          <p>Preferred Foot: <?php echo $plData['preferred_foot']; ?> </p>            <!-- preferred foot-->
          <p>Goals: <?php echo $plData['goals']; ?> </p>                              <!-- goals-->
          <p>Assists: <?php echo $plData['assists']; ?>  </p>                         <!-- assists-->
          <p>MOTM: <?php echo $plData['motm']; ?>  </p>                             <!-- motm-->
          <p>POTM: <?php echo $plData['potm']; ?>  </p>                             <!-- potm-->
          <p>Awards: <?php echo $plData['awards']; ?>  </p>                         <!-- awards-->
          <p>Country: <?php echo $plData['country']; ?>  </p>                       <!-- country-->
          <p>Current League: <?php echo $plData['current_league']; ?>  </p>         <!-- current league-->
          <p>Current Team: <?php echo $plData['current_team']; ?>  </p>             <!-- current team-->
          
          <!--If the user has a profile pic uploaded, display this too
          <?php if(!empty($plData['profile_pic'])): ?>
            <img src="<?php echo $plData['profile_pic']; ?>" width="200" />
          <?php endif; ?>
          

        <?php else: ?>    
          <p> No information was found for user <?php echo $profileUserId; ?> .  </p>
        <?php endif; ?>
      <?php endif; ?>
    </body>

</html>
<?php
$conn->close();
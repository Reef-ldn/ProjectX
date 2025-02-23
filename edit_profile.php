<!--This script allows users to handle editing profiles-->

<!--Backend-->
<?php
  session_start();  //Check the user is logged in;

  //Ensures the user is logged in before allowing them to edit their profile
  if(!isset($_SESSION['user_id'])) {
    die("Please log in to edit your profile!");    //Kills the session if they're not
  }
  $user_id = $_SESSION['user_id'];

  //connect to the db
  $conn = new mysqli("localhost", "root", "", "projectx_db");
  if($conn->connect_error) {
    die("Failed to connect to the database: " . $conn->connect_error);
  }

  //Check the user's user type using the 'users' table
  $userSql = "SELECT user_type FROM users WHERE id = '$user_id' ";
  $userResult = $conn->query($userSql);
  if($userResult->num_rows == 0) {        //if no users exist.
    die("No user found with the ID $user_id.");
  }
  $userRow = $userResult->fetch_assoc();
  //If the user isn't a player, deny them edit permissions.
  if($userRow['user_type'] != 'player') {
    die("You are not a player, so you can not edit the player profile info.");
  }

  //If the user is a player, load their info (from the 'players' row)
  $playerSql = "SELECT * FROM players where user_id='$user_id' ";
  $playerResult = $conn->query($playerSql);
  $playerData = $playerResult->fetch_assoc();   //if there's no row, this may be null

  //If the form is submitted, overwrite previous data
  if(isset($_POST['update_profile'])){
    //Read the new data from the form
    $newHeight = $_POST['height'];
    $newWeight = $_POST['weight'];
    $newAge = $_POST['age'];
    $newPosition = $_POST['preferred_position'];
    $newFoot = $_POST['preferred_foot'];
    $newGoals = $_POST['goals'];
    $newAssists = $_POST['assists'];
    $newMotm = $_POST['motm'];
    $newPotm = $_POST['potm'];
    $newTeam = $_POST['current_team'];
    $newLeague = $_POST['current_league'];
    $newAwards = $_POST['awards'];
    $newCountry = $_POST['country'];
    
    //Update and overwrite
    $updateSql = "UPDATE players
                  SET height = '$newHeight',
                      weight = '$newWeight',
                      age = '$newAge',
                      preferred_position = '$newPosition',
                      preferred_foot = '$newFoot',
                      goals = '$newGoals',
                      assists = '$newAssists',
                      motm = '$newMotm',
                      potm = '$newPotm',
                      current_team = '$newTeam',
                      current_league = '$newLeague',
                      awards = '$newAwards',
                      country = '$newCountry' ";
      $conn-> query($updateSql);

      //Reload the page to show the updated values
      header("Location: edit_profile.php");
      exit;
  }

?>

<!--Front-end-->
<!DOCTYPE html>
 <html>

  <head>
    <title>Edit Profile</title>
  </head>
  
  <body>
    <h1>Edit Player Profile</h1>

    <?php if($playerData): ?>
      <form action="edit_profile.php" method="POST">
        <!--Height-->
        <label>Height (cm): </label> <br>
        <input type = "number" name = "height" value="<?php echo $playerData['height']; ?>" > <br><br>

        <!--Weight-->
        <label>Weight (kg): </label> <br>
        <input type = "number" name = "weight" value="<?php echo $playerData['weight']; ?>" > <br><br>

        <!--Age-->
        <label>Age: </label> <br>
        <input type = "number" name = "age" value="<?php echo $playerData['age']; ?>" > <br><br>

        <!--Position-->
        <label>Preferred Position: </label> <br>
        <input type = "text" name = "preferred_position" value="<?php echo $playerData['preferred_position']; ?>" > <br><br>

        <!--Foot-->
        <label>Preferred Foot: </label> <br>
        <input type = "text" name = "preferred_foot" value="<?php echo $playerData['preferred_foot']; ?>" > <br><br>

        <!--Goals-->
        <label>Goals: </label> <br>
        <input type = "number" name = "goals" value="<?php echo $playerData['goals']; ?>" > <br><br>

        <!--Assists-->
        <label>Assists: </label> <br>
        <input type = "number" name = "assists" value="<?php echo $playerData['assists']; ?>" > <br><br>

        <!--MOTM-->
        <label>MOTM: </label> <br>
        <input type = "number" name = "motm" value="<?php echo $playerData['motm']; ?>" > <br><br>

        <!--POTM-->
        <label> POTM: </label> <br>
        <input type = "number" name = "potm" value="<?php echo $playerData['potm']; ?>" > <br><br>

        <!--Team-->
        <label>Current Team: </label> <br>
        <input type = "text" name = "current_team" value="<?php echo $playerData['current_team']; ?>" > <br><br>

        <!--League-->
        <label>Current League: </label> <br>
        <input type = "text" name = "current_league" value="<?php echo $playerData['current_league']; ?>" > <br><br>

        <!--Awards-->
        <label>Awards: </label> <br>
        <input type = "text" name = "awards" value="<?php echo $playerData['awards']; ?>" > <br><br>

        <!--Country-->
        <label>Country: </label> <br>
        <input type = "text" name = "country" value="<?php echo $playerData['country']; ?>" > <br><br>

        <button type = "submit" name = "update_profile"> Save Changes</button> 
    </form>

    <?php else: ?>
      <p> No player record found. Please contact an admin or create a player profile. </p>
      <?php endif; ?>
  </body>

</html>
    
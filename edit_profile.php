<!--This script allows users to handle editing profiles-->

<!--Backend-->
<?php
session_start();  //Check the user is logged in;

//Ensures the user is logged in before allowing them to edit their profile
if (!isset($_SESSION['user_id'])) {
  die("Please log in to edit your profile!");    //Kills the session if they're not
}
$user_id = $_SESSION['user_id'];

//connect to the db
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Failed to connect to the database: " . $conn->connect_error);
}

//Check the user's user type using the 'users' table
$userSql = "SELECT username, name, user_type 
            FROM users 
            WHERE id = '$user_id'";
$userResult = $conn->query($userSql);
if ($userResult->num_rows == 0) {        //if no users exist.
  die("No user found with the ID $user_id.");
}
$userRow = $userResult->fetch_assoc();
$username = $userRow['username'];
$name = $userRow['name'];

//If the user isn't a player, deny them edit permissions.
if ($userRow['user_type'] != 'player') {
  die("You are not a player, so you can not edit the player profile info.");
}

//If the user is a player, load their info (from the 'players' row)
$playerSql = "SELECT * FROM players 
              where user_id='$user_id' ";
$playerResult = $conn->query($playerSql);
$playerData = $playerResult->fetch_assoc();   //if there's no row, this may be null

// Fetch previous teams
$prevTeamsSql = "SELECT * FROM previous_teams 
                 WHERE user_id = '$user_id' 
                 ORDER BY start_year DESC";
$prevTeamsResult = $conn->query($prevTeamsSql);

// Fetch trophies
$trophiesSql = "SELECT * 
                FROM trophies 
                WHERE user_id = '$user_id' 
                ORDER BY year_awarded DESC";
$trophiesResult = $conn->query($trophiesSql);


//If the form is submitted, overwrite previous data
if (isset($_POST['update_profile'])) {
  //Read the new data from the form
  $newName = $_POST['name'];
  $newUsername = $_POST['username'];
  $newHeight = $_POST['height'];
  $newWeight = $_POST['weight'];
  $newAge = $_POST['age'];
  $newMatches = $_POST['appearances'];
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

  $conn->query("UPDATE users 
                       SET name = '$newName', username = '$newUsername' 
                       WHERE id = '$user_id'");


  //Update and overwrite
  $updateSql = "UPDATE players
                  SET height = '$newHeight',
                      weight = '$newWeight',
                      age = '$newAge',
                      appearances = '$newMatches',
                      preferred_position = '$newPosition',
                      preferred_foot = '$newFoot',
                      goals = '$newGoals',
                      assists = '$newAssists',
                      motm = '$newMotm',
                      potm = '$newPotm',
                      current_team = '$newTeam',
                      current_league = '$newLeague',
                      awards = '$newAwards',
                      country = '$newCountry' 
                  WHERE user_id = '$user_id' ";
  $conn->query($updateSql);

  // Add a new previous team if data is provided
  if (!empty($_POST['new_team_name']) && !empty($_POST['new_team_start']) && !empty($_POST['new_team_end'])) {
    $teamName = $conn->real_escape_string($_POST['new_team_name']);
    $startYear = (int) $_POST['new_team_start'];
    $endYear = (int) $_POST['new_team_end'];

    $conn->query("INSERT INTO previous_teams (user_id, team_name, start_year, end_year)
                VALUES ('$user_id', '$teamName', '$startYear', '$endYear')");
  }

  // Add a new trophy if data is provided
  if (!empty($_POST['new_trophy_name']) && !empty($_POST['new_trophy_year'])) {
    $trophyName = $conn->real_escape_string($_POST['new_trophy_name']);
    $yearAwarded = (int) $_POST['new_trophy_year'];

    $conn->query("INSERT INTO trophies (user_id, trophy_name, year_awarded)
                VALUES ('$user_id', '$trophyName', '$yearAwarded')");
  }


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

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <!--Navbar stylesheet-->
  <link rel="stylesheet" href="/ProjectX/css/navbar.css">

  <style>
    body {
      background-image: url('/ProjectX/uploads/people-soccer-stadium.jpg');
      background-size: cover;
      background-repeat: no-repeat;
      background-attachment: fixed;
      background-position: center;
      color: white;
      margin: 0;
      padding: 0;
      height: 100vh;
      overflow: hidden;
    }

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

    /* Main wrapper holds everything and is scrollable */
    .main-content-wrapper {
      position: relative;
      z-index: 2;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .scroll-container {
      width: 90%;
      max-width: 1000px;
      height: 90vh;
      background-color: rgba(30, 30, 30, 0.9);
      border-radius: 16px;
      box-shadow: 0 0 20px rgba(0, 255, 100, 0.15);
      padding: 30px;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    .row.flex-grow-1 {
      height: 100%;
      overflow: hidden;
      flex-grow: 1;
      overflow-y: auto;
    }

    .col-md-7 {
      overflow-y: auto;
      max-height: 100%;
      padding-right: 15px;
    }

    /* Sticky inside container */
    .sticky-top-inside {
      position: sticky;
      top: 0;
      background-color: rgba(0, 0, 0, 0.85);
      padding: 10px;
      z-index: 3;
    }

    .sticky-bottom-inside {
      position: sticky;
      bottom: 0;
      background-color: rgba(0, 0, 0, 0.85);
      padding: 10px;
      text-align: center;
      z-index: 3;
    }

    .form-control {
      background-color: #1e1e1e;
      color: white;
      border: 1px solid #009e42;
    }

    .form-control:focus {
      border-color: #00c95b;
      box-shadow: 0 0 0 0.2rem rgba(0, 255, 100, 0.25);
    }

    .btn-success {
      background-color: #009e42;
      border: none;
    }

    .btn-success:hover {
      background-color: #00c95b;
    }

    img#imagePreview {
      max-width: 100%;
      max-height: 200px;
      display: none;
      margin-top: 10px;
    }

    /* Stick Save Changes inside scrollable left form */
    .left-form-wrapper {
      position: relative;
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    /* Sticky full-width bar */
    .action-bar {
      position: sticky;
      bottom: 0;
      width: 100%;
      padding: 10px 15px;
      z-index: 10;
      display: flex;
      justify-content: space-between;
      gap: 10px;
      border-top: 1px solid #009e42;
    }

    /* Add bottom padding so fields don't get hidden under bar */
    .form-scroll {
      overflow-y: auto;
      flex-grow: 1;
      padding-right: 10px;
      padding-bottom: 100px;
      /* reserve space for sticky bar */
    }
    
  </style>

</head>

<body>
  <div class="bg-blur-overlay"></div>


  <div class="main-content-wrapper">

    <!--Nav Bar-->
    <?php
    // $currentPage = 'profile';
    include 'navbar.php'; ?>

    <div class="scroll-container d-flex flex-column mt-5">

      <!-- Header -->
      <div class="text-center mb-3">
        <h2 class="text-success">Edit Player Profile</h2>
      </div>


      <!-- Flexbox for Left/Right sides -->
      <div class="row flex-grow-1 gx-5 overflow-auto" style="min-height: 0;">

        <!-- Left Form: Scrollable -->
        <div class="col-md-7">
          <?php if ($playerData): ?>
            <form id="editForm" action="edit_profile.php" method="POST" class="d-flex flex-column h-100">
              <!--  fields  -->
              <div class="form-scroll">
                <div class="mb-3">

                  <label class="form-label">Name</label>
                  <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" class="form-control">
                </div>
                <div class="mb-3">
                  <label class="form-label">Username</label>
                  <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" class="form-control">
                </div>

                <div class="mb-3">

                  <label class="form-label">Height (cm)</label>
                  <input type="number" name="height" value="<?= htmlspecialchars($playerData['height']) ?>"
                    class="form-control">
                </div>
                <div class="mb-3">
                  <label class="form-label">Weight (kg)</label>
                  <input type="number" name="weight" value="<?= htmlspecialchars($playerData['weight']) ?>"
                    class="form-control">
                </div>
                <div class="mb-3">
                  <label class="form-label">Age</label>
                  <input type="number" name="age" value="<?= htmlspecialchars($playerData['age']) ?>"
                    class="form-control">
                </div>
                <div class="mb-3">
                  <label class="form-label">Matches Played</label>
                  <input type="number" name="appearances" value="<?= htmlspecialchars($playerData['appearances']) ?>"
                    class="form-control">

                </div>


                <!-- Dropdown for Position -->
                <div class="mb-3">
                  <label class="form-label">Preferred Position</label>
                  <select name="preferred_position" class="form-control">
                    <?php
                    $positions = ["Striker", "Center Forward", "Left Winger", "Right Winger", "Attacking Midfielder", "Central Midfielder", "Defensive Midfielder", "Left Back", "Left Wing Back", "Center Back", "Right Back", "Right Wing Back", "Goalkeeper"];
                    foreach ($positions as $pos) {
                      $selected = ($playerData['preferred_position'] == $pos) ? 'selected' : '';
                      echo "<option value=\"$pos\" $selected>$pos</option>";
                    }
                    ?>
                  </select>
                </div>

                <!-- Dropdown for Foot -->
                <div class="mb-3">
                  <label class="form-label">Preferred Foot</label>
                  <select name="preferred_foot" class="form-control">
                    <?php
                    $feet = ["Right", "Left", "Both"];
                    foreach ($feet as $foot) {
                      $selected = ($playerData['preferred_foot'] == $foot) ? 'selected' : '';
                      echo "<option value=\"$foot\" $selected>$foot</option>";
                    }
                    ?>
                  </select>
                </div>

                <!-- More Inputs -->
                <?php
                $fields = [
                  'goals' => 'Goals',
                  'assists' => 'Assists',
                  'motm' => 'Man of the Match',
                  'potm' => 'Player of the Month',
                  'current_team' => 'Current Team',
                  'current_league' => 'Current League',
                  'awards' => 'Awards',
                  'country' => 'Country'
                ];
                foreach ($fields as $key => $label):
                  ?>
                  <div class="mb-3">
                    <label class="form-label"><?= $label ?></label>
                    <input type="<?= is_numeric($playerData[$key]) ? 'number' : 'text'; ?>" name="<?= $key ?>"
                      value="<?= htmlspecialchars($playerData[$key]) ?>" class="form-control">
                  </div>
                <?php endforeach; ?>

                <hr>
                <!--Previous Teams-->
                <h5 class="text-success">Previous Teams</h5>
                <?php if ($prevTeamsResult && $prevTeamsResult->num_rows > 0): ?>
                  <?php while ($team = $prevTeamsResult->fetch_assoc()): ?>
                    <div class="mb-2 d-flex align-items-center">
                      <input type="text" name="prev_teams_existing[]" class="form-control me-2"
                        value="<?= htmlspecialchars($team['team_name']) ?>" readonly>
                      <a href="delete_team.php?team_id=<?= $team['id'] ?>" onclick="return confirm('Delete this team?');"
                        class="btn btn-sm btn-danger">Delete</a>

                    </div>
                  <?php endwhile; ?>

                <?php else: ?>
                  <small class="text-muted">No teams added yet.</small>
                <?php endif; ?>

                <!-- New team input -->
                <div class="mb-3">
                  <label class="form-label">Add New Team</label>
                  <input type="text" name="new_team_name" class="form-control mb-1" placeholder="Team Name">
                  <input type="number" name="new_team_start" class="form-control mb-1" placeholder="Start Year">
                  <input type="number" name="new_team_end" class="form-control" placeholder="End Year">
                </div>

                <hr>
                <!--Trophies-->
                <h5 class="text-success">Trophies</h5>
                <?php if ($trophiesResult && $trophiesResult->num_rows > 0): ?>
                  <?php while ($trophy = $trophiesResult->fetch_assoc()): ?>
                    <div class="mb-2 d-flex align-items-center">
                      <div class="flex-grow-1">
                        <input type="text" class="form-control mb-1" value="<?= htmlspecialchars($trophy['trophy_name']) ?>"
                          readonly>
                        <small>Year: <?= $trophy['year_awarded'] ?></small>
                      </div>
                      <form action="delete_trophy.php" method="POST" class="ms-2"
                        onsubmit="return confirm('Delete this trophy?');">
                        <input type="hidden" name="trophy_id" value="<?= $trophy['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                      </form>
                    </div>
                  <?php endwhile; ?>

                <?php else: ?>
                  <small class="text-muted">No trophies added yet.</small>
                <?php endif; ?>

                <!-- New trophy input -->
                <div class="mb-3">
                  <label class="form-label">Add New Trophy</label>
                  <input type="text" name="new_trophy_name" class="form-control mb-1" placeholder="Trophy Name">
                  <input type="number" name="new_trophy_year" class="form-control" placeholder="Year Awarded">
                </div>

              </div><!--Form fields-->



            </form>
          <?php else: ?>
            <p>No player data found.</p>
          <?php endif; ?>
        </div>




        <!-- Right side ( Upload Profile Pic ) -->
        <div class="col-md-5 d-flex flex-column justify-content-start">
          <form action="upload_profile_pic.php" method="POST" enctype="multipart/form-data">
            <label class="form-label">Upload Profile Picture:</label>
            <input type="file" name="profile_pic" accept="image/*" class="form-control" onchange="previewImage(event)">
            <img id="imagePreview" src="#" alt="Preview" style="max-height: 200px; margin-top: 10px; display: none;">
            <button type="submit" name="submit" class="btn btn-success mt-3 w-100">Upload</button>
          </form>
        </div>
      </div>

      <!-- Sticky full-width bottom action bar -->
      <div class="action-bar">
        <a href="profile.php?user_id=<?= $user_id ?>" class="btn btn-outline-light w-50">← Back to Profile</a>
        <button form="editForm" type="submit" name="update_profile" class="btn btn-success w-50">Save
          Changes</button>
      </div>

    </div>

  </div>





  <script>
    function previewImage(event) {
      const reader = new FileReader();
      reader.onload = () => {
        const output = document.getElementById('imagePreview');
        output.src = reader.result;
        output.style.display = 'block';
      };
      reader.readAsDataURL(event.target.files[0]);
    }
  </script>
</body>


</html>
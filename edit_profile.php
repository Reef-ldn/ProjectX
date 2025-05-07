<!--This Page allows users to handle editing their profiles-->

<!--Backend-->
<?php
session_start();  //Check the user is logged in;

//Ensures the user is logged in before allowing them to edit their profile
if (!isset($_SESSION['user_id'])) {
  die("Please log in to edit your profile!");    //Kills the session if they're not
}
$user_id = $_SESSION['user_id'];  //Set the user ID as the session ID

//connect to the db
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Failed to connect to the database: " . $conn->connect_error);
}

//Check the user's id and type using the 'users' table
$userSql = "SELECT username, name, user_type FROM users WHERE id = '$user_id'";
$userResult = $conn->query($userSql);
if ($userResult->num_rows == 0) {        //if no users exist.
  die("No user found with the ID $user_id.");
}
$userRow = $userResult->fetch_assoc();  //Store as userRow

//Variables
$username = $userRow['username'];   //Get the username
$name = $userRow['name']; //Get the uers's name
$userType = $userRow['user_type']; //Get the user type


//if the user is a fan, deny them access to the edit.
if (!in_array($userRow['user_type'], ['player', 'manager', 'scout'])) {
  die("Profile edits are not allowed on this profile type.");
}

//If the user is a manager, get their details from the 'managers' table
if ($userType === 'manager') {
  $mgrSql = "SELECT * FROM managers WHERE user_id = '$user_id'";  //fetch their details
  $mgrResult = $conn->query($mgrSql);
  $mgrData = ($mgrResult && $mgrResult->num_rows > 0) ? $mgrResult->fetch_assoc() : null; //If there's no details, this will be null

  //if the user is a scout, get their details from the 'scouts' table
} elseif ($userType === 'scout') {
  $scoutSql = "SELECT * FROM scouts WHERE user_id = '$user_id'";  //fetch their details
  $scoutResult = $conn->query($scoutSql);
  $scoutData = ($scoutResult && $scoutResult->num_rows > 0) ? $scoutResult->fetch_assoc() : null; //if there's none, this will be null
}

//If the user is a player, load their info (from the 'players' row)
$playerSql = "SELECT * FROM players where user_id='$user_id' "; //fetch their details
$playerResult = $conn->query($playerSql);
$playerData = $playerResult->fetch_assoc();   //if there's no row, this may be null

// Fetch previous teams
$prevTeamsSql = "SELECT * FROM previous_teams WHERE user_id = '$user_id' 
                          ORDER BY start_year DESC"; //Order by most recent first
$prevTeamsResult = $conn->query($prevTeamsSql);

// Fetch trophies
$trophiesSql = "SELECT * FROM trophies WHERE user_id = '$user_id' 
                ORDER BY year_awarded DESC";  //Order by most recent first
$trophiesResult = $conn->query($trophiesSql);

//If the form is submitted, overwrite previous data
if (isset($_POST['update_profile'])) {
  //Name and username variables
  $newName = $_POST['name'];
  $newUsername = $_POST['username'];

  //Update the name and username for all account types
  $conn->query("UPDATE users SET name = '$newName', username = '$newUsername' WHERE id = '$user_id'");

  //Handle form submission - If the user is a player, POST their form submission into the db
  if ($userType === 'player') {
    //Reads from the form
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

    //Update and overwrite in the database
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

    // If a previous team is provided, add a new field in that table
    if (!empty($_POST['new_team_name']) && !empty($_POST['new_team_start']) && !empty($_POST['new_team_end'])) {
      $teamName = $conn->real_escape_string($_POST['new_team_name']); //Team name
      $startYear = (int) $_POST['new_team_start'];    //When they joined the team
      $endYear = (int) $_POST['new_team_end'];    //When they lett
      //Insert into the previous teams table
      $conn->query("INSERT INTO previous_teams (user_id, team_name, start_year, end_year)
                VALUES ('$user_id', '$teamName', '$startYear', '$endYear')");
    }

    // Add a new trophy if data is provided
    if (!empty($_POST['new_trophy_name']) && !empty($_POST['new_trophy_year'])) {
      $trophyName = $conn->real_escape_string($_POST['new_trophy_name']); //name of trophy
      $yearAwarded = (int) $_POST['new_trophy_year']; //When they got it
      //Insert into the previous teams table
      $conn->query("INSERT INTO trophies (user_id, trophy_name, year_awarded)
                VALUES ('$user_id', '$trophyName', '$yearAwarded')");
    }
    //If the user is a manager, get their details from the form and do the same thing.
  } elseif ($userType === 'manager') {
    // Fetch the info they submitted into the form and set these to variables
    $newTeam = $_POST['current_team'];
    $newLeague = $_POST['current_league'];
    $newLang = $_POST['spoken_language'];
    $newMatches = $_POST['matches_managed'];
    $newAge = $_POST['age'];
    $newCountry = $_POST['country'];
    $newMotm = $_POST['motm'];
    $newMoty = $_POST['moty'];
    $newCS = $_POST['clean_sheets'];
    $newAwards = $_POST['awards'];

    //Update the database with these variables and overwrite what's there currently
    $conn->query("UPDATE managers 
                            SET current_team = '$newTeam',
                                current_league = '$newLeague',
                                spoken_language = '$newLang',
                                matches_managed = '$newMatches',
                                age = '$newAge',
                                country = '$newCountry',
                                motm = '$newMotm',
                                moty = '$newMoty',
                                `clean sheets` = '$newCS',
                                awards = '$newAwards'
                          WHERE user_id = '$user_id'");

    //If the user is a scout, get what that user submtitted into the form
  } elseif ($userType === 'scout') {
    // Fetch what they submitted
    $newTeam = $_POST['current_team'];
    $newLeague = $_POST['current_league'];
    $newLang = $_POST['spoken_language'];
    $newCountry = $_POST['country'];
    $newPrevTeams = $_POST['previous_teams'];
    $newDuration = $_POST['duration'];
    $newAchievements = $_POST['achievements'];

    //Update the scouts table
    $conn->query("UPDATE scouts 
                            SET current_team = '$newTeam',
                                current_league = '$newLeague',
                                spoken_language = '$newLang',
                                Country = '$newCountry',
                                previous_teams = '$newPrevTeams',
                                duration = '$newDuration',
                                achievements = '$newAchievements'
                          WHERE user_id = '$user_id'");
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

    /*Background Blur*/
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

    /* Main wrapper */
    .main-content-wrapper {
      position: relative;
      z-index: 2;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    /*The scrollable container (left)*/
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

    /*Form input fields*/
    .form-control {
      background-color: #1e1e1e;
      color: white;
      border: 1px solid #009e42;
    }

    .form-control:focus {
      border-color: #00c95b;
      box-shadow: 0 0 0 0.2rem rgba(0, 255, 100, 0.25);
    }

    /*Success Buttons*/
    .btn-success {
      background-color: #009e42;
      border: none;
    }

    .btn-success:hover {
      background-color: #00c95b;
    }

    /*Upload Preview*/
    img#imagePreview {
      max-width: 100%;
      max-height: 200px;
      display: none;
      margin-top: 10px;
    }

    /* Left form wrapper */
    .left-form-wrapper {
      position: relative;
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    /* Action bar */
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

    /* Adds padding to the bottom so fields don't get hidden under bar */
    .form-scroll {
      overflow-y: auto;
      flex-grow: 1;
      padding-right: 10px;
      padding-bottom: 100px;
    }
  </style>

</head>

<body>
  <div class="bg-blur-overlay"></div> <!--Background-->

  <!--Main wrapper-->
  <div class="main-content-wrapper">

    <!--Nav Bar-->
    <?php
    // $currentPage = 'profile';
    include 'navbar.php'; ?>

    <!--Scrollable Container-->
    <div class="scroll-container d-flex flex-column mt-5">

      <!-- Header -->
      <div class="text-center mb-3">
        <h2 class="text-success">Edit Player Profile</h2>
      </div>

      <!-- Container -->
      <div class="row flex-grow-1 gx-5 overflow-auto" style="min-height: 0;">

        <!-- Left form (Change Stats) -->
        <div class="col-md-7">
          <!--If the user is a player, show these fields-->
          <?php if ($userType === 'player' && $playerData): ?>
            <form id="editForm" action="edit_profile.php" method="POST" class="d-flex flex-column h-100">
              <!--  PLayer fields  -->
              <div class="form-scroll">
                <!--Name-->
                <div class="mb-3">
                  <label class="form-label">Name</label>
                  <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" class="form-control">
                </div>
                <!--Username-->
                <div class="mb-3">
                  <label class="form-label">Username</label>
                  <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" class="form-control">
                </div>
                <!--Height-->
                <div class="mb-3">
                  <label class="form-label">Height (cm)</label>
                  <input type="number" name="height" value="<?= htmlspecialchars($playerData['height']) ?>"
                    class="form-control">
                </div>
                <!--Weight-->
                <div class="mb-3">
                  <label class="form-label">Weight (kg)</label>
                  <input type="number" name="weight" value="<?= htmlspecialchars($playerData['weight']) ?>"
                    class="form-control">
                </div>
                <!--Age-->
                <div class="mb-3">
                  <label class="form-label">Age</label>
                  <input type="number" name="age" value="<?= htmlspecialchars($playerData['age']) ?>"
                    class="form-control">
                </div>
                <!--Matched Played-->
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
                <!-- The rest of the Inputs -->
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
                foreach ($fields as $key => $label):  //Loop through each to show on profile
                  ?>
                  <!--Show in the form-->
                  <div class="mb-3">
                    <label class="form-label"><?= $label ?></label>
                    <input type="<?= is_numeric($playerData[$key]) ? 'number' : 'text'; ?>" name="<?= $key ?>"
                      value="<?= htmlspecialchars($playerData[$key]) ?>" class="form-control">
                  </div>
                <?php endforeach; ?>

                <hr>
                <!--Previous Teams-->
                <h5 class="text-success">Previous Teams</h5>
                <!--Check if any exist already in the db-->
                <?php if ($prevTeamsResult && $prevTeamsResult->num_rows > 0): ?>
                  <?php while ($team = $prevTeamsResult->fetch_assoc()): ?>
                    <!--Display-->
                    <div class="mb-2 d-flex align-items-center">
                      <input type="text" name="prev_teams_existing[]" class="form-control me-2"
                        value="<?= htmlspecialchars($team['team_name']) ?>" readonly>
                      <!--Delete previous team-->
                      <a href="delete_team.php?team_id=<?= $team['id'] ?>" onclick="return confirm('Delete this team?');"
                        class="btn btn-sm btn-danger">Delete</a>
                    </div>
                  <?php endwhile; ?>
                <?php else: ?> <!--No teams found-->
                  <small class="text-muted">No teams added yet.</small>
                <?php endif; ?>
                <!-- New previous team input -->
                <div class="mb-3">
                  <label class="form-label">Add New Team</label>
                  <input type="text" name="new_team_name" class="form-control mb-1" placeholder="Team Name">
                  <input type="number" name="new_team_start" class="form-control mb-1" placeholder="Start Year">
                  <input type="number" name="new_team_end" class="form-control" placeholder="End Year">
                </div>
                <hr>
                <!--Trophies-->
                <h5 class="text-success">Trophies</h5>
                <!--Check if ther's a row in the trophies table-->
                <?php if ($trophiesResult && $trophiesResult->num_rows > 0): ?>
                  <?php while ($trophy = $trophiesResult->fetch_assoc()): ?>
                    <div class="mb-2 d-flex align-items-center">
                      <!--Display-->
                      <div class="flex-grow-1">
                        <input type="text" class="form-control mb-1" value="<?= htmlspecialchars($trophy['trophy_name']) ?>"
                          readonly>
                        <small>Year: <?= $trophy['year_awarded'] ?></small>
                      </div>
                      <!--Delete trophy-->
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

              <!--If the user is a manager, show these fields-->
            <?php elseif ($userType === 'manager' && $mgrData): ?>
              <form id="editForm" action="edit_profile.php" method="POST" class="d-flex flex-column h-100">
                <!--Manager Fields-->
                <div class="form-scroll">
                  <!--Name-->
                  <div class="mb-3"><label>Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" class="form-control">
                  </div>
                  <!--Username-->
                  <div class="mb-3"><label>Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" class="form-control">
                  </div>
                  <!--Current Team-->
                  <div class="mb-3"><label>Current Team</label>
                    <input type="text" name="current_team" value="<?= $mgrData['current_team'] ?>" class="form-control">
                  </div>
                  <!--League-->
                  <div class="mb-3"><label>League</label>
                    <input type="text" name="current_league" value="<?= $mgrData['current_league'] ?>"
                      class="form-control">
                  </div>
                  <!-- Lanaguage -->
                  <div class="mb-3"><label>Language</label>
                    <input type="text" name="spoken_language" value="<?= $mgrData['spoken_language'] ?>"
                      class="form-control">
                  </div>
                  <!-- Age -->
                  <div class="mb-3"><label>Age</label>
                    <input type="number" name="age" value="<?= $mgrData['age'] ?>" class="form-control">
                  </div>
                  <!-- Country -->
                  <div class="mb-3"><label>Country</label>
                    <input type="text" name="country" value="<?= $mgrData['country'] ?>" class="form-control">
                  </div>
                  <!-- Matched Managed -->
                  <div class="mb-3"><label>Matches Managed</label>
                    <input type="number" name="matches_managed" value="<?= $mgrData['matches_managed'] ?>"
                      class="form-control">
                  </div>
                  <!-- Manager of the month -->
                  <div class="mb-3"><label>MOTM</label>
                    <input type="number" name="motm" value="<?= $mgrData['motm'] ?>" class="form-control">
                  </div>
                  <!-- Mnager of the year -->
                  <div class="mb-3"><label>MOTY</label>
                    <input type="number" name="moty" value="<?= $mgrData['moty'] ?>" class="form-control">
                  </div>
                  <!-- Clean Sheets -->
                  <div class="mb-3"><label>Clean Sheets</label>
                    <input type="number" name="clean_sheets" value="<?= $mgrData['clean sheets'] ?>" class="form-control">
                  </div>
                  <!-- Awards -->
                  <div class="mb-3"><label>Awards</label>
                    <textarea name="awards" class="form-control"><?= $mgrData['awards'] ?></textarea>
                  </div>
                </div>
              </form>

              <!-- If the user is a scout, show these fields -->
            <?php elseif ($userType === 'scout' && $scoutData): ?>
              <form id="editForm" action="edit_profile.php" method="POST" class="d-flex flex-column h-100">
                <!-- Scout Fields -->
                <div class="form-scroll">
                  <!-- Name -->
                  <div class="mb-3"><label>Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" class="form-control">
                  </div>
                  <!-- Username -->
                  <div class="mb-3"><label>Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" class="form-control">
                  </div>
                  <!-- Current Team -->
                  <div class="mb-3"><label>Current Team</label>
                    <input type="text" name="current_team" value="<?= $scoutData['current_team'] ?>" class="form-control">
                  </div>
                  <!-- League -->
                  <div class="mb-3"><label>League</label>
                    <input type="text" name="current_league" value="<?= $scoutData['current_league'] ?>"
                      class="form-control">
                  </div>
                  <!-- Language -->
                  <div class="mb-3"><label>Language</label>
                    <input type="text" name="spoken_language" value="<?= $scoutData['spoken_language'] ?>"
                      class="form-control">
                  </div>
                  <!-- Country -->
                  <div class="mb-3"><label>Country</label>
                    <input type="text" name="country" value="<?= $scoutData['Country'] ?>" class="form-control">
                  </div>
                  <!-- Prev Teams -->
                  <div class="mb-3"><label>Previous Teams</label>
                    <input type="text" name="previous_teams" value="<?= $scoutData['previous_teams'] ?>"
                      class="form-control">
                  </div>
                  <!-- How long they've been a scout -->
                  <div class="mb-3"><label>Duration (Years)</label>
                    <input type="number" name="duration" value="<?= $scoutData['duration'] ?>" class="form-control">
                  </div>
                  <!-- Achievements -->
                  <div class="mb-3"><label>Achievements</label>
                    <textarea name="achievements" class="form-control"><?= $scoutData['achievements'] ?></textarea>
                  </div>
                </div>
              </form>

            <?php else: ?> <!--No user type table found-->
              <p>No data found.</p>
            <?php endif; ?>

        </div> <!--Left Side-->

        <!-- Right Form ( Upload Profile Pic ) -->
        <div class="col-md-5 d-flex flex-column justify-content-start">
          <form action="upload_profile_pic.php" method="POST" enctype="multipart/form-data">
            <label class="form-label">Upload Profile Picture:</label>
            <input type="file" name="profile_pic" accept="image/*" class="form-control" onchange="previewImage(event)">
            <img id="imagePreview" src="#" alt="Preview" style="max-height: 200px; margin-top: 10px; display: none;">
            <button type="submit" name="submit" class="btn btn-success mt-3 w-100">Upload</button>
          </form>
        </div>
      </div>

      <!-- action bar - Redirect back to profile page -->
      <div class="action-bar">
        <a href="profile.php?user_id=<?= $user_id ?>" class="btn btn-outline-light w-50">← Back to Profile</a>
        <button form="editForm" type="submit" name="update_profile" class="btn btn-success w-50">Save
          Changes</button>
      </div>

    </div> <!--Container-->
  </div>

  <!--JavaScript Script to preview the profile pic image -->
  <script>
    function previewImage(event) {  //Listen for an image being uploaded, trigger script when a file is uploaded
      const reader = new FileReader();  //read the image file
      reader.onload = () => {
        const output = document.getElementById('imagePreview');   //Get the imagePreview element
        output.src = reader.result;     //Set the image's src as the result from file reader
        output.style.display = 'block';     //Show the image in a block
      };
      reader.readAsDataURL(event.target.files[0]);
    }
  </script>
</body>


</html>
<!-- This page handles Registration using a form that Creates an account-->

<!--Backend-->
<?php
//Checks if the form has been submitted through the submit button
if (isset($_POST['submit'])) {

  //Gets the user inputted data from the form (reads it and parses into PHP variables)
  $username = $_POST['username'];
  $email = $_POST['email'];
  $password = $_POST['password'];
  $user_type = $_POST['user_type'];   //Gets the user's account type


  //Connects the mySQL database
  $conn = new mysqli("localhost", "root", "", "projectx_db");
  //Checks connection to the database
  if ($conn->connect_error) {
    die("Failed to connect to the database: " . $conn->connect_error);    //stops everything if there's an error
  }

  //Hashing the password for security
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  //Inserts the new user into the 'users' table as a column
  $sql = "INSERT INTO users (username, email, password, created_at, user_type) 
          VALUES ('$username', '$email', '$hashed_password', NOW(), '$user_type')";
  $result = $conn->query($sql);

  //If the user was created successfully, insert into respective role table
  if ($result == TRUE) {
    $newUserId = $conn->insert_id;    //the newly created user's id

    //If the user_type is 'player', insert a row in the 'players' table in the db
    if ($user_type == 'player') {
      $playerSql = "INSERT INTO players (user_id, height, weight, age, goals, assists)
              VALUES ('$newUserId', 0 ,0 , 0, 0, 0)";
      $conn->query($playerSql);
    }

    //If the user_type is 'scout', insert a row in the 'scouts' table in the db
    if ($user_type == 'scout') {
      $scoutSql = "INSERT INTO scouts (user_id)
              VALUES ('$newUserId')";
      $conn->query($scoutSql);
    }

    //If the user_type is 'manager', insert a row in the 'managers' table in the db
    if ($user_type == 'manager') {
      $managerSql = "INSERT INTO managers (user_id, age)
              VALUES ('$newUserId', 0)";
      $conn->query($managerSql);
    }

    //Gives Feedback to the user (Not shown due to redirect)
    echo "Registration Successful!";

    //redirects to login page.
    header("Location: login.php");
    exit;
  } else {
    echo "Error: " . $conn->error; //Show a db error if the connection fails
  }

  //Closes the db connection
  $conn->close();

}

?>

<!--Front End-->
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Sign Up</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!--Navbar stylesheet-->
  <link rel="stylesheet" href="/ProjectX/css/navbar.css">


  <style>
    body {
      background-image: url('/ProjectX/uploads/people-soccer-stadium.jpg');
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center;
      background-attachment: fixed;
      color: white;
    }

    /*Blurred Background*/
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

    /*Container*/
    .register-box {
      position: relative;
      z-index: 2;
      max-width: 450px;
      margin: 90px auto;
      background-color: rgba(30, 30, 30, 0.88);
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0, 255, 100, 0.2);
    }

    /*Input fields and dropdowns*/
    .form-control,
    .form-select {
      background-color: #111;
      color: white;
      border: 1px solid #444;
    }
    .form-control::placeholder {
      color: #aaa;
    }
    .form-control:focus,
    .form-select:focus {
      border-color: #00ff88;
      background-color: #111;
      color: white;
      box-shadow: none;
    }

    /*Success Buttons*/
    .btn-success {
      background-color: #009e42;
      border-color: #009e42;
    }
    .btn-success:hover {
      background-color: #00c55b;
    }

    /*Error message - not displayed*/
    .error-message {
      color: #ff5f5f;
      text-align: center;
      margin-top: 15px;
    }

    /*Login Text (Header) and smaller login text*/
    .login-text {
      text-align: center;
      margin-top: 20px;
    }
    .login-text a {
      color: #00ff88;
      text-decoration: none;
      font-weight: bold;
    }
    .login-text a:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>
  <!--Blurred bg-->
  <div class="bg-blur-overlay"></div>

  <!--Nav Bar-->
  <?php
  // $currentPage = ' ';
  include 'navbar.php'; ?>

  <!--Container-->
  <div class="register-box">
    <h1 class="text-center mb-4">Sign Up</h1>
    
    <!--The Registration Form-->
    <form method="POST" action="register.php">
      <div class="mb-3">
        <!--Username Input-->
        <label>Username</label>
        <input type="text" name="username" class="form-control" placeholder="Choose a username" required>
      </div>

      <!--Email input-->
      <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" placeholder="Enter email" required>
      </div>

      <!--Password Input-->
      <div class="mb-3">
        <label>Password</label>
        <input type="password" name="password" class="form-control" placeholder="Enter password" required>
      </div>

      <!--Select account type (Players, Scount, Manager, or fan)-->
      <div class="mb-4">
        <label>Account Type</label>
        <select name="user_type" class="form-select" required> <!--Dropdown-->
          <option value="">Choose...</option>
          <option value="player">Player</option>
          <option value="manager">Manager</option>
          <option value="scout">Scout</option>
          <option value="fan">Fan</option>
        </select>
      </div>

      <!--Submit button-->
      <div class="d-grid">
        <button type="submit" name="submit" class="btn btn-success">Create Account</button>
      </div>

      <!--Error message-->
      <?php if (!empty($error)): ?>
        <div class="error-message mt-3"><?php echo $error; ?></div>
      <?php endif; ?>
    </form>

    <!--Hyperlink to log in if the user already has an existing account-->
    <div class="login-text">
      Already have an account?
      <a href="login.php">Log In</a>
    </div>
  </div>
</body>

</html>
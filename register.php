<!-- Registration Form - Creates an account-->

<?php
if(isset($_POST['submit'])) {       //checks if the form was submitted through the submit button
  //Gets the user inputted data from the form (reads it and parses into PHP variables)
  $username = $_POST['username'];
  $email = $_POST['email'];
  $password = $_POST['password'];

  //Get the user's account type
  $user_type = $_POST['user_type'];

  //Connects the mySQL database
  $conn = new mysqli("localhost", "root", "", "projectx_db");
  //Checks connection to the database
  if($conn->connect_error){
    die("Failed to connect to the database: " . $conn->connect_error);    //stops everything if there's an error
  }

  //Hashing the password
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  //Inserts the new user into the 'users' table as a column
  //$user_type = 'player';    //for now this will be set to player for the sake of the MVP
  $sql = 
  "INSERT INTO users (username, email, password, created_at, user_type)
  VALUES ('$username', '$email', '$hashed_password', NOW(), '$user_type')";
  $result = $conn->query($sql); 

  //check if the insertion worked
  if($result == TRUE) {
    //grab the new user id
    $newUserId = $conn->insert_id;    //the newly created user's id

     //If the user_type is 'player', insert a row in the 'players' table in the db
     if($user_type == 'player') {
      $playerSql = "INSERT INTO players (user_id, height, weight, age, goals, assists)
              VALUES ('$newUserId', 0 ,0 , 0, 0, 0)";
      $conn->query($playerSql);
     }

     //If the user_type is 'scout', insert a row in the 'scouts' table in the db
     if($user_type == 'scout') {
      $scoutSql = "INSERT INTO scouts (user_id)
              VALUES ('$newUserId')";
      $conn->query($scoutSql);
     }


     //If the user_type is 'manager', insert a row in the 'managers' table in the db
     if($user_type == 'manager') {
      $managerSql = "INSERT INTO managers (user_id, age)
              VALUES ('$newUserId', 0)";
      $conn->query($managerSql);
     }


      //Gives Feedback to the user
      //if($conn->query($sql) === TRUE){    // "if the sql command works then...."
      echo "Registration Successful!";
    } else {
        echo "Error: " . $conn->error;
    }


  $conn->close();   //closes the database connection
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign Up</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-image: url('/ProjectX/uploads/people-soccer-stadium.jpg');
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center;
      background-attachment: fixed;
      color: white;
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

    .register-box {
      position: relative;
      z-index: 2;
      max-width: 450px;
      margin: 90px auto;
      background-color: rgba(30, 30, 30, 0.88);
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0,255,100,0.2);
    }

    .form-control, .form-select {
      background-color: #111;
      color: white;
      border: 1px solid #444;
    }

    .form-control::placeholder {
      color: #aaa;
    }

    .form-control:focus, .form-select:focus {
      border-color: #00ff88;
      background-color: #111;
      color: white;
      box-shadow: none;
    }

    .btn-success {
      background-color: #009e42;
      border-color: #009e42;
    }

    .btn-success:hover {
      background-color: #00c55b;
    }

    .error-message {
      color: #ff5f5f;
      text-align: center;
      margin-top: 15px;
    }

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
  <div class="bg-blur-overlay"></div>
  <div class="register-box">
    <h1 class="text-center mb-4">Sign Up</h1>
    <form method="POST" action="register.php">
      <div class="mb-3">
        <label>Username</label>
        <input type="text" name="username" class="form-control" placeholder="Choose a username" required>
      </div>

      <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" placeholder="Enter email" required>
      </div>

      <div class="mb-3">
        <label>Password</label>
        <input type="password" name="password" class="form-control" placeholder="Enter password" required>
      </div>

      <div class="mb-4">
        <label>Account Type</label>
        <select name="user_type" class="form-select" required>
          <option value="">Choose...</option>
          <option value="player">Player</option>
          <option value="manager">Manager</option>
          <option value="scout">Scout</option>
          <option value="fan">Fan</option>
        </select>
      </div>

      <div class="d-grid">
        <button type="submit" name="submit" class="btn btn-success">Create Account</button>
      </div>

      <?php if (!empty($error)): ?>
        <div class="error-message mt-3"><?php echo $error; ?></div>
      <?php endif; ?>
    </form>

    <div class="login-text">
      Already have an account?
      <a href="login.php">Log In</a>
    </div>
  </div>
</body>
</html>

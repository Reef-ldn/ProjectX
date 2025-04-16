
<?php
session_start();  //Session to track which user is logged in

if(isset($_POST['submit'])) {   //checks if the login button was pressed
  //gets the data from the form
  $email = $_POST['email'];
  $password = $_POST['password'];

  //Connects the db
  $conn = new mysqli("localhost", "root", "", "projectx_db");
  //Checks the db is actually connected
  if($conn->connect_error) {
    die("Failed to connect to the database: " . $conn->connect_error);
  }

  //Finds the user by email
  $sql = "SELECT * FROM users WHERE email='$email'";  //checks if there's a row in the users table with the entered email
  $result = $conn->query($sql);   //if yes, stores that info in $result 

  if($result->num_rows> 0) {    //if the result is > 0, at least one user has that email
    $row = $result->fetch_assoc();
    //Checks the password hash
    if(password_verify($password, $row['password'])) {
      //password is correct - session variables set
      $_SESSION['user_id'] = $row['id'];
      $_SESSION['username'] = $row['username'];
      echo "Logged in Successfully!";
      //Redirection to homepage 
    } else {
      echo "Wrong password!";
    }  
  } else {
      echo "No account with that email!";
    }

  $conn->close();
}
/*Session just means to remember the user's ID in the background 
so the next time they visit the page, they will be remembered*/
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Log In</title>
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

    .login-box {
      position: relative;
      z-index: 2;
      max-width: 400px;
      margin: 100px auto;
      background-color: rgba(30, 30, 30, 0.85);
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0,255,100,0.2);
    }

    .form-control {
      background-color: #111;
      color: white;
      border: 1px solid #444;
    }

    .form-control::placeholder {
      color: #aaa;
    }

    .form-control:focus {
      border-color: #0f0;
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

    .signup-text {
      text-align: center;
      margin-top: 20px;
    }

    .signup-text a {
      color: #00ff88;
      text-decoration: none;
      font-weight: bold;
    }

    .signup-text a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="bg-blur-overlay"></div>
  <div class="login-box">
    <h1 class="text-center mb-4">Log In</h1>
    <form method="POST" action="login.php">
      <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" placeholder="Enter email" required>
      </div>

      <div class="mb-3">
        <label>Password</label>
        <input type="password" name="password" class="form-control" placeholder="Enter password" required>
      </div>

      <div class="d-grid">
        <button type="submit" name="submit" class="btn btn-success">Log In</button>
      </div>

      <?php if (!empty($error)): ?>
        <div class="error-message mt-3"><?php echo $error; ?></div>
      <?php endif; ?>
    </form>

    <div class="signup-text">
      Don't have an account?
      <a href="register.php">Sign Up</a>
    </div>
  </div>
</body>
</html>
<!--Simple Log In Form- Logs a user in -->
<form action = "login.php" method = "POST">
  <h1>Log in</h1>
  Email = <input type = "email" name = "email">   <br>
  Password = <input type = "password" name = "password">   <br>
  <button type = "submit" name = "submit">Log In</button>
</form>

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
    die("Connection failed: " . $conn->connect_error);
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
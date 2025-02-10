<!--Simple Registration Form - Creates an account-->
<form action = "register.php" method = "POST">
  <h1>Sign Up</h1>
  Username: <input type = "text" name = "username">  <br>
  Email: <input type = "email" name = "email">   <br>
  Password: <input type = "password" name="password">    <br>
  <button type="submit" name="submit">Register</button>
</form>

<?php
if(isset($_POST['submit'])) {       //checks if the form was submitted through the submit button
  //Gets the user inputted data from the form (reads it and parses into PHP variables)
  $username = $_POST['username'];
  $email = $_POST['email'];
  $password = $_POST['password'];

  //Connects the mySQL database
  $conn = new mysqli("localhost", "root", "", "projectx_db");
  //Checks connection to the database
  if($conn->connect_error){
    die("Failed to connect to the database: " . $conn->connect_error);    //stops everything if there's an error
  }

  //Hashing the password
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  //Inserts the new user into the 'users' table as a column
  $sql = 
  "INSERT INTO users (username, email, password, created_at)
  VALUES ('$username', '$email', '$hashed_password', NOW())";
  
  //Gives Feedback to the user
  if($conn->query($sql) === TRUE){    // "if the sql command works then...."
    echo "Registration Successful!";
  } else {
    echo "Error: " . $conn->error;
  }

  $conn->close();   //closes the database connection
}

?>

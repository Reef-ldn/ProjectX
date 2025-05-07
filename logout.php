<!--This page handles logging the user out-->

<?php
/*Basically clears the session so the next time the user logs in, 
they're not recognised.*/

session_start();
//Destroy all session data - logs the user out
session_destroy(); //clears the session

echo "You have logged out"; //Not visible due to redirect

//redirects to login page.
header("Location: login.php");
exit;
?>
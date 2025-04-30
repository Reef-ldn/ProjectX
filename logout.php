<?php
/*Basically clears the session so the next time the user logs in, 
they're not recognised.*/
session_start();
session_destroy(); //clears the session
echo "You have logged out";
header("Location: login.php"); exit;

?>
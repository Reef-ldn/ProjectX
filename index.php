<?php
//Checks if the user is logged in
//If they're not logged in, they can't see the page
session_start();
if(!isset($_SESSION['user_id'])) {
  echo "Not Signed In.";
  exit;   //kicks the user off the page
}


echo "Hello Aspiring Footballers!";
?>

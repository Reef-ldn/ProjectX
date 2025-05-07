<!-- This script allows users to upload profile pictures -->

<?php
session_start();
//Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
  die("Please log in!");
}
$userID = $_SESSION['user_id']; //Set userID as the session ID

// Connect to DB
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Connection fail: " . $conn->connect_error);
}

//Once the submit button is pushed
if (isset($_POST['submit'])) {
  $profilePicPath = null; //Profile Pic doesn't have a path at first

  //Once the user sets their picture and there is no error
  if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
    // create a folder "uploads/profile_pics"
    $filename = $_FILES['profile_pic']['name']; //Original file name
    $tempPath = $_FILES['profile_pic']['tmp_name']; //Set a temporary location for the file
    $destination = "uploads/profile_pics/" . $filename;   //actual destination - Where the profile picture will be saved

    move_uploaded_file($tempPath, $destination);  //Move file from temp location to actual location
    $profilePicPath = $destination; // store this path in the DB
  }

  //Handle banner if uploaded (Removed functionality)
  // $bannerPicPath = null;
  // if(isset($_FILES['banner_pic']) && $_FILES['banner_pic']['error'] == 0){
  //   $filename = $_FILES['banner_pic']['name'];
  //   $tempPath = $_FILES['banner_pic']['tmp_name'];
  //   $destination = "uploads/banner_pics/".$filename;
  //   move_uploaded_file($tempPath, $destination);
  //   $bannerPicPath = $destination;
  // }

  //Update query to store the new image path in the database
  $updates = [];
  if ($profilePicPath) {
    $updates[] = "profile_pic='$profilePicPath'";
  }
  // if($bannerPicPath){
  //   $updates[] = "banner_pic='$bannerPicPath'";
  // }

  //Only update if there's something new
  if (!empty($updates)) {
    $sqlUpdate = "UPDATE users SET " . implode(",", $updates) . " WHERE id='$userID'";
    $conn->query($sqlUpdate);
  }
  // redirect back to profile page
  header("Location: profile.php?user_id=$userID");
  exit;
}
?>
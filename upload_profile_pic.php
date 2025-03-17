<?php
session_start();
if(!isset($_SESSION['user_id'])){
  die("Please log in!");
}
$userID = $_SESSION['user_id'];

// Connect to DB
$conn = new mysqli("localhost","root","","projectx_db");
if($conn->connect_error){
  die("Connection fail: ".$conn->connect_error);
}

if(isset($_POST['submit'])){
  //Handle profile pic if uploaded
  $profilePicPath = null;
  if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0){
    // create a folder "uploads/profile_pics"
    $filename = $_FILES['profile_pic']['name'];
    $tempPath = $_FILES['profile_pic']['tmp_name'];
    $destination = "uploads/profile_pics/".$filename;
    move_uploaded_file($tempPath, $destination);
    $profilePicPath = $destination; // store this in DB
  }

  //Handle banner pic if uploaded
  $bannerPicPath = null;
  if(isset($_FILES['banner_pic']) && $_FILES['banner_pic']['error'] == 0){
    $filename = $_FILES['banner_pic']['name'];
    $tempPath = $_FILES['banner_pic']['tmp_name'];
    $destination = "uploads/banner_pics/".$filename;
    move_uploaded_file($tempPath, $destination);
    $bannerPicPath = $destination;
  }

  // 3) Update the DB with these paths
  $updates = [];
  if($profilePicPath){
    $updates[] = "profile_pic='$profilePicPath'";
  }
  if($bannerPicPath){
    $updates[] = "banner_pic='$bannerPicPath'";
  }
  if(!empty($updates)){
    $sqlUpdate = "UPDATE users SET ".implode(",", $updates)." WHERE id='$userID'";
    $conn->query($sqlUpdate);
  }
  // redirect back to profile page
  header("Location: profile.php?user_id=$userID");
  exit;
}
?>

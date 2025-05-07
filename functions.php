<!--Function that applies a global profile picture palceholder icon to all pages-->
<?php
function getProfilePic($picPath)
{
  $defaultPath = 'uploads/profile_pics/Footballer_shooting_b&w.jpg';
  return (!empty($picPath) && file_exists($picPath)) ? $picPath : $defaultPath;
}
?>
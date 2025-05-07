<!--This file is the global navbar for all pages across the system. -->


<!--Navbar stylesheet-->
<!-- <link rel="stylesheet" href="/ProjectX/css/navbar.css"> -->

<?php
//The user that is currently logged in
$loggedUserId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
$loggedIn = isset($_SESSION['user_id']);


//connect to the db
if (!isset($conn)) {
  $conn = new mysqli("localhost", "root", "", "projectx_db");    //connect to db
  if ($conn->connect_error) {      //check connection
    die("Failed to connect to the database: " . $conn->connect_error);
  }
}
?>

<!-- If the user is logged in, show the Nav Bar -->
<?php if ($loggedIn) {

  // Get user's profile pic  from DB
  $loggedInPic = 'uploads/profile_pics/Footballer_shooting_b&w.jpg';
  $query = $conn->query("SELECT profile_pic FROM users WHERE id = $loggedUserId");
  if ($query && $query->num_rows > 0) {
    $profilePicData = $query->fetch_assoc();
    if (!empty($profilePicData['profile_pic'])) {
      $loggedInPic = $profilePicData['profile_pic'];
    }
  }
} ?>

<!--Navbar Start-->
<nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-black"> <!--black Background-->
  <div class="container-fluid">
    <!--logo and name-->
    <a class="navbar-brand d-flex align-items-center" href="feed.php">
      <img src="\ProjectX\uploads\Logo\Next XI Logo.png" alt="Logo" width="35" height="35" class="me-2">
      Next XI
    </a>

    <!--Toggler for small screens-->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
      aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span> <!--Toggler Icon-->
    </button>

    <!--Collapsible div for the nav links and user dropdown-->
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <!--  Nav Links section -->
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0">

        <!--Nav Links-->
        <?php if ($loggedIn): ?>
          <li class="nav-item">
            <a class="nav-link <?php echo ($currentPage === 'feed') ? 'active' : ''; ?>" href="feed.php">Feed</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo ($currentPage === 'upload') ? 'active' : ''; ?>" href="upload.php">Upload</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo ($currentPage === 'inbox') ? 'active' : ''; ?>" href="inbox.php">Messages</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo ($currentPage === 'profile') ? 'active' : ''; ?>"
              href="profile.php?user_id=<?php echo $loggedUserId; ?>">My Profile</a>
          </li>

        <?php endif; ?>
      </ul>

      <!--Right section - Search bar, profile pic dropdown OR the login/signup buttons-->
      <div class="d-flex align-items-center">
        <!--Search Bar-->
        <form class="d-flex me-4 mb-0" role="search" action="search.php" method="GET">
          <input class="form-control me-2" type="search" name="q" placeholder="Search users or posts"
            aria-label="Search">
          <button class="btn btn-outline-light" type="submit">Search</button>
        </form>

        <!-- if the user is logged in -->
        <?php if ($loggedIn): ?>
          <!--Profile Pic Dropdown-->
          <div class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              <!-- The user’s profile pic -->
              <img src="<?php echo $loggedInPic; ?>" alt="Profile" width="40" height="40" class="rounded-circle"
                style="border: 2px solid #009e42;">
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <!-- "dropdown-menu-end" to align the menu to the right side -->
              <li><a class="dropdown-item" href="profile.php?user_id=<?php echo $loggedUserId; ?>">My Profile</a></li>
              <li><a class="dropdown-item" href="#">Settings</a></li>
              <li><a class="dropdown-item" href="#">Help/Support</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item" href="logout.php">Log Out</a></li>
            </ul>
          </div>
        <?php else: ?>
          <!-- If not logged in -->
          <div class="d-flex align-items-center">
            <a href="login.php" class="btn btn-outline-light me-2">Log In</a>
            <a href="register.php" class="btn btn-success">Sign Up</a>
          </div>

        <?php endif; ?>

      </div>
    </div>
  </div>
</nav>
<!--Navbar End-->
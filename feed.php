<!-- This page displays a feed of all the uploaded content from users-->

<!--Backend-->
<?php
session_start();  //Check the user is logged in;

//connect to the db
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Failed to connect to the database: " . $conn->connect_error);
}

//Store the ID of the user that's logged in (if available)
$loggedUserId = $_SESSION['user_id'] ?? null;
$loggedIn = isset($loggedUserId); //boolean to check the login status


//Logic for the People You May Know Section
$peopleYouMayKnow = [];

//If the user is logged in
if ($loggedIn) {
  // Get all IDs of users the currently logged in user follows
  $followingSql = "SELECT followed_id FROM follows WHERE follower_id = $loggedUserId";
  $followingRes = $conn->query($followingSql);  //Store

  $followingIds = [];   //Store in an array for better handling
  while ($row = $followingRes->fetch_assoc()) {   //If any are found
    $followingIds[] = $row['followed_id'];
  }

  // If the user follows people, find mutuals that aren't followed yet
  if (!empty($followingIds)) {
    $ids = implode(",", $followingIds);

    //Create the mutual followers sql query (Finds users that the user does not follow yet)
    $mutualSql = "SELECT u.id, u.username, u.name, u.profile_pic, COUNT(*) as mutual_count
                        FROM follows f1
                        JOIN follows f2 ON f1.followed_id = f2.follower_id
                        JOIN users u ON f2.followed_id = u.id
                        WHERE f1.follower_id = $loggedUserId
                        AND f2.followed_id != $loggedUserId
                        AND f2.followed_id NOT IN (
                  SELECT followed_id FROM follows WHERE follower_id = $loggedUserId)
                      GROUP BY f2.followed_id
                      ORDER BY mutual_count DESC
                  LIMIT 5";
    //Store the result as a variable
    $mutualRes = $conn->query($mutualSql);
    //If a result is found, store it in the array
    if ($mutualRes && $mutualRes->num_rows > 0) {
      while ($row = $mutualRes->fetch_assoc()) {
        $peopleYouMayKnow[] = $row;
      }
    }
  }
}

//Fetch all videos from newest to oldest (LIFO)
$sql = "SELECT p.id AS postID, p.post_type, p.file_path, p.text_content, p.created_at, p.is_highlight, u.id 
        AS user_owner_id, u.username, u.name, u.profile_pic,
          (SELECT COUNT(*) FROM likes l where l.post_id = p.id) AS like_count,
          (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
          from posts p
          JOIN users u ON p.user_id = u.id 
          ORDER BY p.created_at DESC";
$result = $conn->query($sql); //Fetch all posts
//The "JOIN" gives the foreigner key of the user's name from the user's table
//The select count is a sub query of likes and acts as a like counter.
//for each row in 'posts', the 'likes' table is also checked to see how many rows there is 
// for posts with that id and  keeps a count of it
//This count result is the 'like_count'
?>

<!--Front End-->
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Feed</title>

  <!--Bootstrap CSS (CDN)-->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

  <!--Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />

  <!--Navbar stylesheet-->
  <link rel="stylesheet" href="/ProjectX/css/navbar.css">


  <script src="https://kit.fontawesome.com/22c727220d.js" crossorigin="anonymous"></script>

  <style>
    body {
      background-image: url('/ProjectX/uploads/Trophy_wallpaper_cropped.jpg');
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center;
      background-attachment: fixed;
      margin: 0;
      padding: 0;
      position: relative;
      min-height: 100vh;
      overflow-x: hidden;
      /* color: rgba(240,240,240,1); */
    }

    .bg-blur-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      backdrop-filter: blur(5px);
      background-color: rgba(0, 0, 0, 0.4);
      z-index: 1;
    }

    /*Position the posts in the center*/
    .main-content {
      position: relative;
      z-index: 2;
    }

    /*The cards for all posts*/
    .card {
      background-color: rgba(30, 30, 30, 0.90);
      color: rgba(240, 240, 240, 0.95);
    }

    /* Small text on cards */
    .custom-muted {
      color: white;
      opacity: 0.7;
    }

    /*3 dots in the corner*/
    .bi-three-dots {
      color: rgba(255, 255, 255, 0.9)
    }

    /*Primary buttons*/
    .btn-primary {
      background-color: #038e63;
    }

    /*Comment interaction styling*/
    .bi-chat-right-dots,
    .bi-send,
    .btn-green {
      color: #038e63;
      opacity: 1;
    }

    /*Side bar*/
    .right-bar {
      background-color: rgba(30, 30, 30, 0.94);
      color: rgba(240, 240, 240, 1);
      border-radius: 10px;
      margin-bottom: 15px;
    }

    .right-bar-wrapper {
      position: sticky;
      top: 70px;
    }
  </style>


</head>

<body>

  <div class="bg-blur-overlay"></div> <!--Background-->

  <!-- Container-->
  <div class="main-content ">

    <!--Nav Bar-->
    <?php
    $currentPage = 'feed';
    include 'navbar.php'; ?>



    <!--Main Content Area-->
    <div class="container pt-4 mt-5">

      <div class="row">

        <!--Post sent popup (Doesn't work as intended-->
        <?php if (isset($_GET['shared']) && $_GET['shared'] === 'success'): ?>
          <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            ✅ Post sent successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <!--Left half of the container (posts)-->
        <div class="offset-md-2 col-md-7">

          <!--Feed logic-->
          <?php
          //if any posts are found
          if ($result && $result->num_rows > 0) {
            //Display each post
            while ($row = $result->fetch_assoc()) {
              // Variables
              $postID = $row['postID'];
              $userID = $_SESSION['user_id'] ?? 0;
              $loggedUserID = $_SESSION['user_id'] ?? 0;
              $postOwnerID = $row['user_owner_id'];

              //If no profle picture exists, use the default image
              $ownerPic = !empty($row['profile_pic']) ? $row['profile_pic'] : 'uploads/profile_pics/Footballer_shooting_b&w.jpg';

              //Check the the user already liked this post
              $alreadyLiked = false;
              if ($userID > 0) {
                $likeCheckSql = "SELECT * FROM likes WHERE post_id='$postID' AND user_id='$userID'";
                $likeCheckResult = $conn->query($likeCheckSql);
                $alreadyLiked = ($likeCheckResult->num_rows > 0);
              }

              //check if the user that's logged in already follows the post owner
              $alreadyFollows = false;
              if ($loggedUserID > 0) {
                $checkFollowSql = "SELECT * FROM follows WHERE follower_id='$loggedUserID' AND followed_id='$postOwnerID'";
                $followRes = $conn->query($checkFollowSql);
                $alreadyFollows = ($followRes->num_rows > 0);
              }

              // fetch the most recent comments for this post
              $commentCount = $row['comment_count'];
              $commentSql = "SELECT c.comment_text, c.created_at, u.username
                                    FROM comments c
                                    JOIN users u ON c.user_id = u.id
                                    WHERE c.post_id = '$postID'
                             ORDER BY c.created_at ASC
                             LIMIT 2"; //only show 2 comments on this page
              $commentRes = $conn->query($commentSql);
              ?>

              <!--Card container-->
              <div class="card mb-4">
                <!--Card Body-->
                <div class="card-body">

                  <!--top row of the card (user details and the dropdown)-->
                  <div class="d-flex justify-content-between align-items-center mb-2">

                    <!--Left side of the deets (username, name and profile pic)-->
                    <div class="d-flex align-items-center">
                      <!--User's Profile Pic-->
                      <img src="<?php echo $ownerPic ?>" alt="Profile" width="40" height="40" class="rounded-circle me-2">
                      <div>
                        <!--User account name-->
                        <strong><?php echo $row['name']; ?></strong>
                        <!-- user's @ handle -->
                        <span class="custom-muted">@<?php echo strtolower($row['username']); ?></span><br>
                        <!-- time posted -->
                        <small class="custom-muted">
                          Posted on <?php echo date('d M, y H:i', strtotime($row['created_at'])); ?>
                        </small>
                      </div>
                    </div>

                    <!-- 3-dot dropdown menu -->
                    <div class="dropdown">
                      <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots"></i> <!-- Using a bootstrap icon -->
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">Save Post</a></li> <!--Not functional-->

                        <!--Highlight functionality-->
                        <?php if ($postOwnerID == $loggedUserID): ?>
                          <!--Only show this if the PostOwner is the logged in user-->
                          <?php if ($row['is_highlight'] == 1): ?> <!--If it's a highlight-->
                            <li><a class="dropdown-item" href="highlight_post.php?post_id=<?php echo $postID; ?>&action=remove">
                                Remove from Highlights</a> <!--Remove the highlight -->
                            </li>
                          <?php else: ?>
                            <!--If it's not one of their highlights already-->
                            <li><a class="dropdown-item" href="highlight_post.php?post_id=<?php echo $postID; ?>&action=add">Add
                                to Highlights</a></li> <!--Add to highlights-->
                          <?php endif; ?>
                        <?php endif; ?>

                        <!--Reporting Functionality-->
                        <li><a class="dropdown-item" href="#">Report</a></li> <!--Not functional-->

                        <!--Follow / Unfollow -->
                        <!--Only show if the postowner isnt the same as the logged in user-->
                        <?php if ($postOwnerID != $loggedUserID): ?>
                          <?php
                          if ($alreadyFollows) {
                            //Unfollow button
                            echo '<li><a class="dropdown-item" href="follow_user.php?followed_id=' . $postOwnerID . '&action=unfollow">Unfollow</a></li>';
                          } else {
                            //Follow Button
                            echo '<li><a class="dropdown-item" href="follow_user.php?followed_id=' . $postOwnerID . '&action=follow">Follow</a></li>';
                          }
                          ?>
                        <?php endif; ?>

                        <!--View Profile-->
                        <li><a class="dropdown-item" href="profile.php?user_id=<?php echo $postOwnerID; ?>">View Profile</a>
                        </li>

                        <!--Delete Post (Only if they're the post owner)-->
                        <?php if ($postOwnerID == $loggedUserID): ?>
                          <li><a class="dropdown-item text-danger" href="delete_post.php?post_id=<?php echo $postID; ?>"
                              onclick="return confirm('Are you sure you want to delete this post?');">Delete Post</a></li>
                          <li>
                          <?php endif; ?>
                          <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="#">Cancel</a></li>
                      </ul>
                    </div>
                  </div>

                  <!--Actual Post being dispalyed (image/video/text) -->
                  <div style="max-width: 800px;">
                    <div class="mb-3">
                      <!--If an image-->
                      <?php if ($row['post_type'] == "image"): ?>
                        <img src="<?php echo $row['file_path']; ?>" class="img-fluid" alt="Post Image">
                        <!--If a video-->
                      <?php elseif ($row['post_type'] == "video"): ?>
                        <video class="w-100" style="max-height: 400px;" controls>
                          <source src="<?php echo $row['file_path']; ?>" type="video/mp4">
                          Your browser does not support the video tag.
                        </video>
                        <!--If it's plain-text-->
                      <?php elseif ($row['post_type'] == "text"): ?>
                        <p><?php echo $row['text_content']; ?></p>
                      <?php endif; ?>
                    </div>
                  </div>

                  <!-- Buttons (like, comment, share) -->
                  <div class="d-flex align-items-center mb-2">

                    <!-- Like Heart Icon -->
                    <a href="#" class="btn btn-green toggle-like me-3" data-post-id="<?php echo $postID; ?>"
                      data-liked="<?php echo $alreadyLiked ? '1' : '0'; ?>">
                      <i class="bi <?php echo $alreadyLiked ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                    </a>

                    <!-- Comment icon -->
                    <button class="btn btn-link text-decoration-none me-3">
                      <a href="view_post.php?post_id=<?php echo $postID; ?>"> <!--Redirect to the view_post page-->
                        <i class="bi bi-chat-right-dots"></i>
                      </a>
                    </button>

                    <!--Share Icon-->
                    <button class="btn btn-link text-decoration-none me-3 share-btn" data-bs-toggle="modal"
                      data-bs-target="#shareModal" data-post-id="<?php echo $postID; ?>"> <!--Open the modal-->
                      <i class="bi bi-send"></i> </button>
                  </div>

                  <!-- Like count -->
                  <?php
                  $likeCount = $row['like_count']; //Echo the like count
                  if ($likeCount == 1) {
                    echo '<p><span id="like-count-' . $postID . '"><strong>1 like</strong></span></p>';
                  } else {
                    echo '<p><span id="like-count-' . $postID . '"><strong>' . $likeCount . ' likes</strong></span></p>';
                  }
                  ?>

                  <!-- Caption -->
                  <?php if (!empty($row['text_content']) && $row['post_type'] != 'text'): ?> <!--Get from the post table-->
                    <p>
                      <strong><?php echo strtolower($row['username']); ?> </strong>
                      <?php echo $row['text_content']; ?>
                    </p>
                  <?php endif; ?>

                  <!-- Comments Section -->
                  <hr>
                  <div class="mb-2 comment-section">

                    <!-- fetch comments and loop-->
                    <?php
                    //CHeck if any comments exist
                    if ($commentRes && $commentRes->num_rows > 0) {
                      while ($cRow = $commentRes->fetch_assoc()) {  //if they do, fetch
                        //Dispaly the comment
                        echo '<p><b>' . $cRow['username'] . ':</b> ' . $cRow['comment_text'] . ' <i>(' . $cRow['created_at'] . ')</i></p>';
                      }
                    } else {  //If there are no comments
                      echo '<small class="custom-muted">No comments yet.</small><br><br>';
                    }

                    //Only display 2 comments and hide the rest under a "View all comments" hyperlink
                    if ($commentCount > 2) {
                      echo '<a href="view_post.php?post_id=' . $postID . '">View all ' . $commentCount . ' comments</a>';
                    }
                    ?>
                  </div>

                  <!--Comments Form-->
                  <form class="d-flex comment-form" action="comments.php" method="POST">
                    <input type="hidden" name="post_id" value="<?php echo $postID; ?>">
                    <input class="form-control me-2" type="text" name="comment_text" placeholder="Add a comment...">
                    <button class="btn btn-sm btn-primary" type="submit">Comment</button>
                  </form>

                </div>
              </div>

              <?php
            } // end while
          } else {
            echo "<p>No posts found in feed.</p>";  //If no posts are found
          }

          ?>
        </div>


        <!-- Side Bar Container-->
        <div class="col-md-3">
          <div class="right-bar-wrapper">

            <!--Trending Posts-->
            <?php
            // Fetch top 3 trending posts (based on like count)
            $trendingSql = "SELECT p.id AS post_id, p.text_content, p.file_path, p.post_type,
                            u.username, u.name, u.profile_pic,
                            (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count
                            FROM posts p
                            JOIN users u ON p.user_id = u.id
                            ORDER BY like_count DESC
                            LIMIT 3";
            $trendingRes = $conn->query($trendingSql);
            ?>

            <!--Trending container-->
            <div class="right-bar p-3 mb-3">
              <h5>Trending</h5>
              <!--If a result was found for trending posts--, fetch and loop through them-->
              <?php if ($trendingRes && $trendingRes->num_rows > 0): ?>
                <?php while ($trend = $trendingRes->fetch_assoc()): ?>
                  <div class="mb-2">
                    <!--Show the trending post with an option to select it to be taken to the post-->
                    <a href="view_post.php?post_id=<?= $trend['post_id'] ?>" class="text-light text-decoration-none">
                      <!--Poster's account info-->
                      <div class="d-flex align-items-center">
                        <!--Profile Pic-->
                        <img src="<?= $trend['profile_pic'] ?? 'uploads/profile_pics/Footballer_shooting_b&w.jpg' ?>"
                          width="35" height="35" class="rounded-circle me-2">

                        <div>
                          <strong><?= $trend['name'] ?></strong> <br> <!--Name-->
                          <small>@<?= $trend['username'] ?></small> <br> <!--Username-->
                          <small class="text-muted"><?= $trend['like_count'] ?> likes</small>
                          <!--Likes on that post (not displayed)-->
                        </div>
                      </div>
                    </a>
                  </div>

                <?php endwhile; ?>
              <?php else: ?>
                <p>No trending posts yet.</p> <!--If no trending posts exist-->
              <?php endif; ?>
            </div>

            <!--People you may know section-->
            <div class="right-bar p-3 mb-3">
              <h5>People You May Know</h5>
              <!--If the query from before has people in it-->
              <?php if (!empty($peopleYouMayKnow)): ?>
                <!--For each person, make a card and display their details-->
                <?php foreach ($peopleYouMayKnow as $person): ?>
                  <div class="mb-2">
                    <!--User's details-->
                    <a href="profile.php?user_id=<?= $person['id'] ?>" class="text-light text-decoration-none">
                      <div class="d-flex align-items-center">
                        <!--Profile Pic-->
                        <img src="<?= $person['profile_pic'] ?? 'uploads/profile_pics/Footballer_shooting_b&w.jpg' ?>"
                          width="35" height="35" class="rounded-circle me-2">

                        <div>
                          <strong><?= $person['name'] ?></strong><br> <!--Name-->
                          <small>@<?= $person['username'] ?></small><br> <!--Username-->
                          <small class="text-muted"><?= $person['mutual_count'] ?> mutual follower
                            <?= $person['mutual_count'] > 1 ? 's' : '' ?></small>
                          <!--The amount of mutual followers they have(Not displayed)-->
                        </div>
                      </div>
                    </a>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p>No suggestions right now.</p> <!--If no mutual followers are found-->
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Share Post Modal - Pops up when the user clicks the share button -->
  <div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
    <!--Modal Container-->
    <div class="modal-dialog">
      <!--Modal Form that POSTs to send_post.php (Pick which user with a dropdown)-->
      <form id="shareForm" method="POST" action="send_post.php">
        <input type="hidden" name="post_id" id="modalPostId"> <!--Hidden input to store the post ID to be send-->
        <div class="modal-content">
          <!--Modal title and close button-->
          <div class="modal-header">
            <h5 class="modal-title" id="shareModalLabel">Send Post</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <!--Modal Card - Dropdown of users the logged in user is following-->
          <div class="modal-body">
            <p>Select a user to send this post to:</p>
            <div class="form-group">

              <select class="form-control" name="recipient_id" required>
                <?php
                // Query to get the list of people the logged-in user is following
                $loggedId = $_SESSION['user_id'] ?? 0;
                $followSql = "SELECT u.id, u.username 
                            FROM users u
                            JOIN follows f ON f.followed_id = u.id
                            WHERE f.follower_id = '$loggedId'";
                $followRes = $conn->query($followSql);

                //If any users exist, show all the users in the dropdown
                if ($followRes && $followRes->num_rows > 0) {
                  while ($f = $followRes->fetch_assoc()) {
                    echo '<option value="' . $f['id'] . '">' . $f['username'] . '</option>';
                  }
                } else {
                  //If no users exist, tell the user that no suers are available
                  echo '<option disabled>No followers found</option>';
                }
                ?>
              </select>
            </div>
          </div>

          <!--Submit Button-->
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Send</button>
          </div>
        </div>

      </form>
    </div>
  </div>



  <!--JavaScript-->

  <!--Bootstrap JavaScript-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

  <!--Script to inject the post ID into Modal-->
  <script>
    document.querySelectorAll('.share-btn').forEach(button => {
      button.addEventListener('click', function () {
        const postId = this.dataset.postId; //Get the Post ID from the clicked button
        document.getElementById('modalPostId').value = postId;  //Set it inside the modal's hidden input
      });
    });
  </script>

  <!--Script to handle comments without refreshing the page-->
  <script>
    //Loop through every comment form on the page
    document.querySelectorAll('.comment-form').forEach(form => {

      //Listen for when the comment form is submitted
      form.addEventListener('submit', async function (e) {
        e.preventDefault(); //stop the nromal form submission from reloading the page

        //get the post ID (hidden input)
        const postID = this.querySelector('input[name="post_id"]').value;

        //Get the actual comment that was typed in the text box
        const commentInput = this.querySelector('input[name="comment_text"]');
        const commentText = commentInput.value.trim();  //Remove any extra spaces

        //If the comment is empty, do nothing
        if (!commentText) return;

        //Build the form data to send tot he PHP script
        const formData = new FormData();
        formData.append('post_id', postID);
        formData.append('comment_text', commentText);

        try {
          //send the comment to the comment.php class using a POST request
          const res = await fetch('comments.php', {
            method: 'POST',
            body: formData
          });

          const text = await res.text();  //try to read the response as text (in case its not valid JSON)

          let data;
          try {
            //try converting the repsonse into a JSON object
            data = JSON.parse(text);
          } catch (jsonErr) {
            //if the server didn't return valid JSON, show an error in the console
            console.error("Invalid JSON:", text);
            throw new Error("Server returned invalid JSON.");
          }

          //If the comment was saved successfully
          if (data.status === 'success') {
            window.location.href = data.redirect; //redirect tot he post page to see the comment
            // window.location.href = `view_post.php?post_id=${postID}`;
          } else {
            //if not, show a popup alert with an error message
            alert(data.message || 'Failed to add comment.');
          }

        } catch (error) {
          //if tehre was a network or server error
          console.error('Comment error:', error);
          alert('Something went wrong. Please try again.');
        }
      });
    });
  </script>

  <!--Script to handle comments without refreshing the page-->
  <script>
    //Finds all elements with the class "toggle-like" 
    document.querySelectorAll('.toggle-like').forEach(button => {

      //For each butotn, listen for when it's clicked
      button.addEventListener('click', async function (e) {
        e.preventDefault(); //Stop the default link action (reload the page)

        const postId = this.dataset.postId;   //get the post ID from the data-post-id attribute
        //Check if the user has already liked this post (using the data-liked attirbute)
        const liked = this.dataset.liked === '1'; 

        try {
          //Send a request to toggle_lile.php with the correct action (either like or unlike)
          const res = await fetch(`toggle_like.php?post_id=${postId}&action=${liked ? 'unlike' : 'like'}`);
          const data = await res.json();  //convert the JSON response to JavaScript

          //If the like/unlike was successful
          if (data.status === 'success') {
            const icon = this.querySelector('i'); //change the heart icon
            const countEl = document.getElementById(`like-count-${postId}`);  //get the span with the like count

            // Update icon and count visually
            if (data.liked) {
              icon.classList.remove('bi-heart');  //remove not liked icon
              icon.classList.add('bi-heart-fill', 'text-danger');   //add liked icon
              this.dataset.liked = '1'; //update the attribute so it remebers that it's already liked
            } else {
              //If the user unliked the post, change the icon back to unliked
              icon.classList.remove('bi-heart-fill', 'text-danger');  //remove like icon
              icon.classList.add('bi-heart');   //add not liked icon
              this.dataset.liked = '0'; //update the attribute so it remembers it's not liked now
            }

            //Update the like count text
            let currentCount = parseInt(countEl.textContent) || 0;  //Convert text to a number
            countEl.innerHTML = `<strong>${data.liked ? currentCount + 1 : currentCount - 1} like${(data.liked && currentCount === 0) || (currentCount > 1) ? 's' : ''}</strong>`;
          }
        } catch (err) {
          //If anything fails, show an error in the console
          console.error('Error toggling like:', err);
        }
      });
    });
  </script>



  <!--Timeout the pop up message after a few seconds-->
  <script>
    setTimeout(() => {
      const alert = document.querySelector('.alert');
      if (alert) {
        alert.classList.remove('show'); 
        alert.classList.add('fade');
      }
    }, 3000);
  </script>


  <?php
  $conn->close();
  ?>



</body>

</html>
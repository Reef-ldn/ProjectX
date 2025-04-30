<!-- This page displays a feed of all the uploaded content from users-->

<?php

session_start();  //Check the user is logged in;

//connect to the db
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Failed to connect to the database: " . $conn->connect_error);
}

$loggedUserId = $_SESSION['user_id'] ?? null;
$loggedIn = isset($loggedUserId);




//Fetch all videos from newest to oldest (LIFO)
$sql = "SELECT p.id AS postID, p.post_type, p.file_path, p.text_content, p.created_at, p.is_highlight, u.id 
        AS user_owner_id, u.username, u.name, u.profile_pic,
          (SELECT COUNT(*) FROM likes l where l.post_id = p.id) AS like_count,
          (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
          from posts p
          JOIN users u ON p.user_id = u.id 
          ORDER BY p.created_at DESC"
;
//The "JOIN" gives the foreigner key of the user's name from the user's table
//The select count is a sub query of likes and acts as a like counter.
//for each row in 'posts', the 'likes' table is also checked to see how many rows there is 
// for posts with that id and  keeps a count of it
//This count result is the 'like_count'

$result = $conn->query($sql);
?>



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
      /*  no scrolling */
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
      /* blur effect */
      background-color: rgba(0, 0, 0, 0.4);
      /*  dark tint */
      z-index: 1;
    }

    .main-content {
      position: relative;
      z-index: 2;
    }


    .card {
      background-color: rgba(30, 30, 30, 0.90);
      color: rgba(240, 240, 240, 0.95);
    }


    .custom-muted {
      color: white;
      opacity: 0.7;
    }

    .bi-three-dots {
      color: rgba(255, 255, 255, 0.9)
    }

    .btn-primary {
      background-color: #038e63;
    }

    .bi-chat-right-dots,
    .bi-send,
    .btn-green {
      color: #038e63;
      opacity: 1;
    }

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

  <div class="bg-blur-overlay"></div>
  <div class="main-content ">

  <!--Nav Bar-->
    <?php 
    $currentPage = 'feed';
    include 'navbar.php'; ?>
  


    <!--Main Content Area-->
    <div class="container pt-4 mt-5">

      <div class="row">

        <!--Post sent popup-->
        <?php if (isset($_GET['shared']) && $_GET['shared'] === 'success'): ?>
          <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            ✅ Post sent successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <!--Left or Center Column: 6/12 columns-->
        <div class="offset-md-2 col-md-7">

          <!--Feed logic (php), each post in a card-->
          <?php
          if ($result && $result->num_rows > 0) {
            //Display each post - Read each line
            while ($row = $result->fetch_assoc()) {
              // Variables
              $postID = $row['postID'];
              $userID = $_SESSION['user_id'] ?? 0;
              $loggedUserID = $_SESSION['user_id'] ?? 0;
              $postOwnerID = $row['user_owner_id'];

              $ownerPic = !empty($row['profile_pic']) ? $row['profile_pic'] : 'uploads/profile_pics/Footballer_shooting_b&w.jpg';


              $alreadyLiked = false;
              $alreadyFollows = false;

              if ($userID > 0) {
                // Check if this user already liked
                $likeCheckSql = "SELECT * FROM likes WHERE post_id='$postID' AND user_id='$userID'";
                $likeCheckResult = $conn->query($likeCheckSql);
                $alreadyLiked = ($likeCheckResult->num_rows > 0);
              }

              if ($loggedUserID > 0) {
                //check if the user already follows the user
                $checkFollowSql = "SELECT * FROM follows WHERE follower_id='$loggedUserID' AND followed_id='$postOwnerID'";
                $followRes = $conn->query($checkFollowSql);
                $alreadyFollows = ($followRes->num_rows > 0);
              }

              // fetch comments
              $commentCount = $row['comment_count'];
              $commentSql = "SELECT c.comment_text, c.created_at, u.username
                         FROM comments c
                         JOIN users u ON c.user_id = u.id
                         WHERE c.post_id = '$postID'
                         ORDER BY c.created_at ASC
                         LIMIT 2";
              $commentRes = $conn->query($commentSql);
              ?>

              <div class="card mb-4">

                <!--Card Body-->
                <div class="card-body">

                  <!--Top Part: user pic + username + 3-dot hamburger on the right-->
                  <div class="d-flex justify-content-between align-items-center mb-2">

                    <!--Left side: User profile pic+name+ @username + time-->
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

                    <!-- Right: 3-dot dropdown menu -->
                    <div class="dropdown">
                      <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-three-dots"></i> <!-- Using a bootstrap icon -->
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">Save Post</a></li>
                        <!--Only show this if the PostOwner is the logged in user-->
                        <?php if ($postOwnerID == $loggedUserID): ?>
                          <?php if ($row['is_highlight'] == 1): ?> <!--If it's a highlight-->
                            <li><a class="dropdown-item"
                                href="highlight_post.php?post_id=<?php echo $postID; ?>&action=remove">Remove from
                                Highlights</a>
                            </li>
                          <?php else: ?>
                            <!--If it's not highlighted already-->
                            <li><a class="dropdown-item" href="highlight_post.php?post_id=<?php echo $postID; ?>&action=add">Add
                                to Highlights</a></li>
                          <?php endif; ?>

                        <?php endif; ?>
                        <li><a class="dropdown-item" href="#">Report</a></li>
                        <!--Only show follow/unfollow if the postowner isnt the same as the logged in user-->
                        <?php if ($postOwnerID != $loggedUserID): ?>
                          <?php
                          if ($alreadyFollows) {
                            echo '<li><a class="dropdown-item" href="follow_user.php?followed_id=' . $postOwnerID . '&action=unfollow">Unfollow</a></li>';
                          } else {
                            echo '<li><a class="dropdown-item" href="follow_user.php?followed_id=' . $postOwnerID . '&action=follow">Follow</a></li>';
                          }
                          ?>

                        <?php endif; ?>
                        <!--<li><a class="dropdown-item" href="#">Follow/Unfollow</a></li>-->
                        <li><a class="dropdown-item" href="profile.php?user_id=<?php echo $postOwnerID; ?>">View Profile</a>
                        </li>
                        <!--DELETE POST-->
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
                  </div> <!-- end d-flex justify-content-between -->

                  <!-- Middle: the actual post content (image/video/text) -->
                  <div style="max-width: 800px;">
                    <div class="mb-3">
                      <?php if ($row['post_type'] == "image"): ?>
                        <img src="<?php echo $row['file_path']; ?>" class="img-fluid" alt="Post Image">
                      <?php elseif ($row['post_type'] == "video"): ?>
                        <video class="w-100" style="max-height: 400px;" controls>
                          <source src="<?php echo $row['file_path']; ?>" type="video/mp4">
                          Your browser does not support the video tag.
                        </video>



                      <?php elseif ($row['post_type'] == "text"): ?>
                        <p><?php echo $row['text_content']; ?></p>
                      <?php endif; ?>
                    </div>
                  </div>

                  <!-- Buttons row (like, comment, share) -->
                  <div class="d-flex align-items-center mb-2">

                    <!-- Like Heart Icon -->
                    <a href="#" class="btn btn-green toggle-like me-3" data-post-id="<?php echo $postID; ?>"
                      data-liked="<?php echo $alreadyLiked ? '1' : '0'; ?>">
                      <i class="bi <?php echo $alreadyLiked ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                    </a>

                    <!-- Comment icon -->
                    <button class="btn btn-link text-decoration-none me-3">
                      <a href="view_post.php?post_id=<?php echo $postID; ?>">
                        <i class="bi bi-chat-right-dots"></i>
                      </a>
                    </button>

                    <!--Share Icon-->
                    <button class="btn btn-link text-decoration-none me-3 share-btn" data-bs-toggle="modal"
                      data-bs-target="#shareModal" data-post-id="<?php echo $postID; ?>">
                      <i class="bi bi-send"></i> </button>
                  </div>

                  <!-- Like count -->
                  <?php
                  $likeCount = $row['like_count'];
                  if ($likeCount == 1) {
                    echo '<p><span id="like-count-' . $postID . '"><strong>1 like</strong></span></p>';
                  } else {
                    echo '<p><span id="like-count-' . $postID . '"><strong>' . $likeCount . ' likes</strong></span></p>';
                  }
                  ?>


                  <!-- Caption -->
                  <?php if (!empty($row['text_content']) && $row['post_type'] != 'text'): ?>
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


                    if ($commentRes && $commentRes->num_rows > 0) {
                      while ($cRow = $commentRes->fetch_assoc()) {
                        echo '<p><b>' . $cRow['username'] . ':</b> ' . $cRow['comment_text'] . ' <i>(' . $cRow['created_at'] . ')</i></p>';
                      }
                    } else {
                      echo '<small class="custom-muted">No comments yet.</small><br><br>';
                    }

                    //Only display 2 comments and hide the rest under a "View all comments" hyperlink
                    if ($commentCount > 2) {
                      echo '<a href="view_post.php?post_id=' . $postID . '">View all ' . $commentCount . ' comments</a>';
                    }

                    ?>

                    <!--<small class="custom-muted">Comments go here...</small>-->
                  </div>
                  <!--Comments Form-->
                  <form class="d-flex comment-form" action="comments.php" method="POST">
                    <input type="hidden" name="post_id" value="<?php echo $postID; ?>">
                    <input class="form-control me-2" type="text" name="comment_text" placeholder="Add a comment...">
                    <button class="btn btn-sm btn-primary" type="submit">Comment</button>
                  </form>

                </div> <!-- end card-body -->
              </div> <!-- end card mb-4 -->

              <?php
            } // end while
          } else {
            echo "<p>No posts found in feed.</p>";
          }

          ?>
        </div> <!-- end col-md-7 -->


        <!-- Right Column: col-md-4 for "Trending" or anything else -->
        <div class="col-md-3">
          <div class="right-bar-wrapper">
            <div class="right-bar p-3 mb-3">
              <h5>Trending</h5>
              <p>Trending posts / recommended users</p>
            </div>
            <div class="right-bar p-3 mb-3">
              <h5>People you may know</h5>
              <p>User 1</p>
              <p>User 2</p>
              <p>User 3</p>
            </div>
            <div class="right-bar p-3">
              <h5>Another Section</h5>
              <p>Some additional widget or ads, etc.</p>
            </div>
          </div> <!-- end col-md-4 -->
        </div> <!-- end row -->

      </div>
    </div> <!-- end container -->

  </div>

  <!-- Share Post Modal -->
  <div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form id="shareForm" method="POST" action="send_post.php">
        <input type="hidden" name="post_id" id="modalPostId">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="shareModalLabel">Send Post</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">
            <p>Select a user to send this post to:</p>
            <div class="form-group">
              <select class="form-control" name="recipient_id" required>
                <?php
                // Get the list of people the logged-in user is following
                $loggedId = $_SESSION['user_id'] ?? 0;
                $followSql = "SELECT u.id, u.username 
                            FROM users u
                            JOIN follows f ON f.followed_id = u.id
                            WHERE f.follower_id = '$loggedId'";
                $followRes = $conn->query($followSql);

                if ($followRes && $followRes->num_rows > 0) {
                  while ($f = $followRes->fetch_assoc()) {
                    echo '<option value="' . $f['id'] . '">' . $f['username'] . '</option>';
                  }
                } else {
                  echo '<option disabled>No followers found</option>';
                }
                ?>
              </select>
            </div>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Send</button>
          </div>
        </div>
      </form>
    </div>
  </div>



  <!--Bootstrap JavaScript-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

  <!--Script to handle likes without refreshing the page-->
  <script>
    document.querySelectorAll('.comment-form').forEach(form => {
      form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const postID = this.querySelector('input[name="post_id"]').value;
        const commentInput = this.querySelector('input[name="comment_text"]');
        const commentText = commentInput.value.trim();
        if (!commentText) return;

        const formData = new FormData();
        formData.append('post_id', postID);
        formData.append('comment_text', commentText);

        try {
          const res = await fetch('comments.php', {
            method: 'POST',
            body: formData
          });

          const text = await res.text(); // safer than res.json()

          let data;
          try {
            data = JSON.parse(text);
          } catch (jsonErr) {
            console.error("Invalid JSON:", text);
            throw new Error("Server returned invalid JSON.");
          }

          if (data.status === 'success') {
            window.location.href = `view_post.php?post_id=${postID}`;
          } else {
            alert(data.message || 'Failed to add comment.');
          }

        } catch (error) {
          console.error('Comment error:', error);
          alert('Something went wrong. Please try again.');
        }
      });
    });
  </script>

  <script>
    document.querySelectorAll('.toggle-like').forEach(button => {
      button.addEventListener('click', async function (e) {
        e.preventDefault();
        const postId = this.dataset.postId;
        const liked = this.dataset.liked === '1';

        try {
          const res = await fetch(`toggle_like.php?post_id=${postId}&action=${liked ? 'unlike' : 'like'}`);
          const data = await res.json();

          if (data.status === 'success') {
            const icon = this.querySelector('i');
            const countEl = document.getElementById(`like-count-${postId}`);

            // Update icon
            if (data.liked) {
              icon.classList.remove('bi-heart');
              icon.classList.add('bi-heart-fill', 'text-danger');
              this.dataset.liked = '1';
            } else {
              icon.classList.remove('bi-heart-fill', 'text-danger');
              icon.classList.add('bi-heart');
              this.dataset.liked = '0';
            }


            let currentCount = parseInt(countEl.textContent) || 0;
            countEl.innerHTML = `<strong>${data.liked ? currentCount + 1 : currentCount - 1} like${(data.liked && currentCount === 0) || (currentCount > 1) ? 's' : ''}</strong>`;
          }
        } catch (err) {
          console.error('Error toggling like:', err);
        }
      });
    });
  </script>


  <!--Script to inject the post ID into Modal-->
  <script>
    document.querySelectorAll('.share-btn').forEach(button => {
      button.addEventListener('click', function () {
        const postId = this.dataset.postId;
        document.getElementById('modalPostId').value = postId;
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
<?php

//Ensure the user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
  die("Please log in to see comments!");
}

$userID = $_SESSION['user_id'];
$postID = (int) $_GET['post_id']; //id of the post we're viewing

// Connect to DB
$conn = new mysqli("localhost", "root", "", "projectx_db");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}


//Fetch details about the post + the poster's username
$postSql = "SELECT p.*, u.username, u.name, u.profile_pic,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count
            FROM posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.id = $postID";
$postRes = $conn->query($postSql);
$postRow = $postRes->fetch_assoc();

$likeQuery = $conn->query("SELECT * FROM likes WHERE post_id = $postID AND user_id = $userID");
$alreadyLiked = $likeQuery->num_rows > 0;

$countQuery = $conn->query("SELECT COUNT(*) as total FROM likes WHERE post_id = $postID");
$likeCount = $countQuery->fetch_assoc()['total'];


// Fetch all top level comments for this post
$commentSql = "SELECT c.*, u.username, u.name, u.profile_pic, c.created_at
               FROM comments c
               JOIN users u ON c.user_id = u.id
               WHERE c.post_id= $postID AND c.parent_id IS NULL
               ORDER BY c.created_at ASC";
$commentRes = $conn->query($commentSql);

function fetchReplies($conn, $parentID)
{
  $stmt = $conn->prepare("SELECT c.*, u.username , u.name, u.profile_pic
                          FROM comments c 
                          JOIN users u ON c.user_id = u.id 
                          WHERE c.parent_id = ? 
                          ORDER BY c.created_at ASC");
  $stmt->bind_param("i", $parentID);
  $stmt->execute();
  return $stmt->get_result();
}

?>



<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>View Post<?= $postID; ?></title>

  <!--Bootstrap CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <!--Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <!--Global CSS Styling-->
  <link rel="stylesheet" href="css/style.css">

  <!--Navbar stylesheet-->
  <link rel="stylesheet" href="/ProjectX/css/navbar.css">


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

    .main-content {
      position: relative;
      z-index: 2;
    }

    .card {
      background-color: rgba(30, 30, 30, 0.90);
      color: rgba(240, 240, 240, 0.95);
    }

    .comment {
      background-color: rgba(255, 255, 255, 0.1);
      border-radius: 8px;
      padding: 10px;
      margin-bottom: 10px;
    }

    .reply {
      margin-left: 20px;
      margin-top: 5px;
      border-left: 2px solid rgba(255, 255, 255, 0.2);
      padding-left: 15px;
    }

    .post-media {
      max-width: 100%;
      max-height: 400px;
      overflow: hidden;
    }

    .post-media img,
    .post-media video {
      width: 100%;
      height: auto;
      object-fit: contain;
    }


    .comment-section {
      flex: 1;
      max-height: 90vh;
      overflow-y: auto;
      padding-right: 10px;
      background-color: #fff;
      border-left: 1px solid #ddd;
      padding-left: 15px;
    }

    .custom-muted,
    .text-muted,
    .small {
      color: rgba(240, 240, 240, 0.95);
    }

    .comment-box {
      position: relative;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 6px;
      margin-bottom: 10px;
      background-color: white;
    }

    .show-reply-btn {
      font-weight: 600;
      font-size: 0.8rem;
    }

    .reply-box {
      margin-left: 1rem;
      margin-top: 0.5rem;
      padding-left: 0.75rem;
      border-left: 2px solid #ddd;
    }

    .reply-form {
      display: none;
    }

    video,
    img {
      max-width: 100%;
      max-height: 400px;
      object-fit: contain;
    }
  </style>

</head>

<body>
  <div class="bg-blur-overlay"></div>
  <div class="main-content container pt-5 mt-5">

    <!--Nav Bar-->
    <?php
    // $currentPage = 'profile';
    include 'navbar.php'; ?>


    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card p-4">

          <!--  link back to feed -->
          <a href="feed.php" class="btn btn-secondary">Back to Feed</a>
          <br>

          <!-- Display  details about the post -->
          <?php if ($postRow): ?>
            <div class="d-flex align-items-center mb-3">
              <img src="<?= $postRow['profile_pic'] ?? 'uploads/profile_pics/Footballer_shooting_b&w.jpg' ?>" width="40"
                height="40" class="rounded-circle me-2">
              <div>
                <strong><?= $postRow['name'] ?></strong>
                <small class="custom-muted"> @<?= $postRow['username'] ?> <br>
                  Posted <?= date('d M, Y H:i', strtotime($postRow['created_at'])) ?></small>
                <!-- <p><?= htmlspecialchars($postRow['text_content']) ?></p> -->
              </div>
            </div>

            <?php if ($postRow['post_type'] === 'image'): ?>
              <img src="<?= $postRow['file_path'] ?>" class="img-fluid rounded mb-3">
            <?php elseif ($postRow['post_type'] === 'video'): ?>
              <video class="w-100 mb-3" controls>
                <source src="<?= $postRow['file_path'] ?>" type="video/mp4">
                Your browser does not support the video tag.
              </video>
            <?php endif; ?>

            <!-- Like / Comment / Share Buttons -->
            <div class="d-flex align-items-center mb-2">

              <!-- Like Heart Icon -->
              <a href="#" class="btn btn-link p-1 me-1 ms-2 toggle-like" data-post-id="<?= $postID ?>"
                data-liked="<?= $alreadyLiked ? '1' : '0' ?>" id="like-btn-<?= $postID ?>">
                <i class="bi <?= $alreadyLiked ? 'bi-heart-fill text-danger' : 'bi-heart' ?>"></i>
              </a>
              <span id="like-count-<?= $postID ?>"><strong><?= $likeCount ?>
                  <?= $likeCount == 1 ? 'like' : 'likes' ?></strong></span>



              <!-- Share Icon -->
              <button class="btn btn-link text-decoration-none me-3 share-btn" data-bs-toggle="modal"
                data-bs-target="#shareModal" data-post-id="<?= $postID ?>">
                <i class="bi bi-send"></i>
              </button>

            </div>

            <p class="ms-4"><?= htmlspecialchars($postRow['text_content']) ?></p>
            <hr>
            <h5>Comments</h5>

            <!--Leave a comment area-->
            <hr>
            <form action="comments.php" method="POST" class="d-flex align-items-start gap-2">
              <input type="hidden" name="post_id" value="<?= $postID ?>">

              <!-- Comment input -->
              <textarea name="comment_text" class="form-control" placeholder="Comment something..." rows="1"
                style="flex-grow: 1;"></textarea>

              <!-- Submit button -->
              <button type="submit" class="btn btn-primary">Comment</button>
            </form>


            <!--Comment Section-->
            <?php while ($comment = $commentRes->fetch_assoc()): ?>
              <div class="comment-box mt-3 p-3 rounded bg-dark text-light">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-start">
                    <img src="<?= $comment['profile_pic'] ?? 'uploads/profile_pics/Footballer_shooting_b&w.jpg' ?>"
                      width="35" height="35" class="rounded-circle me-2">
                    <div>
                      <a href="profile.php?user_id=<?= $comment['user_id'] ?>" class="text-decoration-none text-light">
                        <strong><?= $comment['name'] ?></strong>
                      </a>
                      <span class="custom-muted">@<?= $comment['username'] ?></span><br>
                      <small class="custom-muted"><?= date('d M, Y H:i', strtotime($comment['created_at'])) ?></small>

                    </div>
                  </div>


                  <!-- Dropdown -->
                  <div class="dropdown">
                    <button class="btn btn-sm text-light" type="button" data-bs-toggle="dropdown">
                      <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <?php if ($comment['user_id'] == $userID): ?>
                        <li><a class="dropdown-item edit-comment" href="#" data-id="<?= $comment['id'] ?>">Edit</a></li>
                        <li><a class="dropdown-item delete-comment text-danger" href="#"
                            data-id="<?= $comment['id'] ?>">Delete</a></li>
                      <?php endif; ?>
                      <li><a class="dropdown-item" href="#">Report</a></li>
                    </ul>
                  </div>
                </div>

                <!-- Comment Content -->
                <p class="comment-text mt-2" data-id="<?= $comment['id'] ?>">
                  <?= htmlspecialchars($comment['comment_text']) ?>
                </p>

                <!-- Replies -->
                <div class="reply-box" id="replies-<?= $comment['id'] ?>">
                  <?php
                  $replies = fetchReplies($conn, $comment['id']);
                  while ($reply = $replies->fetch_assoc()):
                    ?>
                    <div class="reply-box ms-3 mt-2 ps-3 border-start border-secondary">
                      <div class="d-flex justify-content-between align-items-center">
                        <div>
                          <strong><?= $reply['username'] ?></strong>
                          <small class="custom-muted ms-2">(<?= $reply['created_at'] ?>)</small>
                        </div>

                        <?php if ($reply['user_id'] == $userID): ?>
                          <div class="dropdown">
                            <button class="btn btn-sm text-light" type="button" data-bs-toggle="dropdown">
                              <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                              <li><a class="dropdown-item edit-comment" href="#" data-id="<?= $reply['id'] ?>">Edit</a></li>
                              <li><a class="dropdown-item delete-comment text-danger" href="#"
                                  data-id="<?= $reply['id'] ?>">Delete</a></li>
                            </ul>
                          </div>
                        <?php endif; ?>
                      </div>

                      <p class="comment-text mt-1" data-id="<?= $reply['id'] ?>">
                        <?= htmlspecialchars($reply['comment_text']) ?>
                      </p>
                    </div>
                  <?php endwhile; ?>
                </div>

                <!-- Reply Toggle Button -->
                <div class="text-end mt-2">
                  <button class="btn btn-outline-light btn-sm show-reply-btn px-2 py-0"
                    data-id="<?= $comment['id'] ?>">Reply</button>
                </div>

                <!-- Reply Form -->
                <form class="reply-form mt-2 ms-2" data-parent-id="<?= $comment['id'] ?>" style="display: none;">
                  <input type="hidden" name="post_id" value="<?= $postID ?>">
                  <input type="hidden" name="parent_id" value="<?= $comment['id'] ?>">
                  <input type="text" name="reply_text" class="form-control form-control-sm mb-2"
                    placeholder="Write your reply...">
                  <button class="btn btn-sm btn-success ms-2" type="submit">Send</button>
                </form>
              </div>

            <?php endwhile; ?>
          <?php endif; ?>







          <!--  link back to feed -->
          <br>
          <a href="feed.php" class="btn btn-secondary">Back to Feed</a>














          <br>



          <!--Bootstrap JavaScript-->
          <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
            </script>


          <script>
            // Show reply form on click
            document.querySelectorAll('.show-reply-btn').forEach(button => {
              button.addEventListener('click', () => {
                const parentId = button.dataset.id;
                const form = document.querySelector(`.reply-form[data-parent-id="${parentId}"]`);
                form.style.display = form.style.display === 'block' ? 'none' : 'block';
              });
            });

            // Handle reply submission
            document.querySelectorAll('.reply-form').forEach(form => {
              form.addEventListener('submit', async function (e) {
                e.preventDefault();
                const formData = new FormData(this);
                const parentId = this.dataset.parentId;

                const res = await fetch('reply_comment.php', {
                  method: 'POST',
                  body: formData
                });

                const data = await res.json();
                if (data.status === 'success') {
                  const replyBox = document.querySelector(`#replies-${parentId}`);
                  const replyEl = document.createElement('p');
                  replyEl.classList.add('ms-2');
                  replyEl.textContent = 'You: ' + formData.get('reply_text');
                  replyBox.appendChild(replyEl);
                  this.reset();
                  this.style.display = 'none';
                }
              });
            });

            // Edit comment 
            document.querySelectorAll('.edit-comment').forEach(link => {
              link.addEventListener('click', async e => {
                e.preventDefault();
                const id = link.dataset.id;
                const textEl = document.querySelector(`.comment-text[data-id="${id}"]`);
                const oldText = textEl.textContent;
                const newText = prompt("Edit your comment:", oldText);
                if (!newText || newText === oldText) return;

                const res = await fetch('edit_comment.php', {
                  method: 'POST',
                  body: new URLSearchParams({ comment_id: id, comment_text: newText })
                });

                const data = await res.json();
                if (data.status === 'success') textEl.textContent = newText;
              });
            });

            // Delete comment
            document.querySelectorAll('.delete-comment').forEach(link => {
              link.addEventListener('click', async e => {
                e.preventDefault();
                const id = link.dataset.id;
                if (!confirm("Delete this comment?")) return;

                const res = await fetch('delete_comment.php', {
                  method: 'POST',
                  body: new URLSearchParams({ comment_id: id })
                });

                const data = await res.json();
                if (data.status === 'success') {
                  link.closest('.comment-box').remove();
                }
              });
            });

          </script>

          <!--Script to share posts-->
          <script>
            document.querySelectorAll('.share-btn').forEach(button => {
              button.addEventListener('click', function () {
                const postId = this.dataset.postId;
                document.getElementById('modalPostId').value = postId;
              });
            });
          </script>

          <!--Script to allow liking posts dynamically without refreshing the page-->
          <script>
            document.querySelectorAll('.toggle-like').forEach(button => {
              button.addEventListener('click', async (e) => {
                e.preventDefault();

                const postId = button.dataset.postId;
                const isLiked = button.dataset.liked === "1";
                const action = isLiked ? 'unlike' : 'like';
                const icon = button.querySelector('i');

                try {
                  const res = await fetch(`toggle_like.php?post_id=${postId}&action=${action}`);
                  const data = await res.json();

                  if (data.status === 'success') {
                    // Toggle icon
                    if (action === 'like') {
                      icon.classList.remove('bi-heart');
                      icon.classList.add('bi-heart-fill', 'text-danger');
                      button.dataset.liked = "1";
                    } else {
                      icon.classList.remove('bi-heart-fill', 'text-danger');
                      icon.classList.add('bi-heart');
                      button.dataset.liked = "0";
                    }

                    // update like count with another request
                    const countRes = await fetch(`like_count.php?post_id=${postId}`);
                    const countData = await countRes.json();
                    if (countData.status === 'success') {
                      document.getElementById(`like-count-${postId}`).innerHTML = `<strong>${countData.like_count} ${countData.like_count == 1 ? 'like' : 'likes'}</strong>`;
                    }
                  }
                } catch (err) {
                  console.error("Failed to toggle like:", err);
                }
              });
            });
          </script>

        </div>
      </div>
    </div>
  </div>
  <!-- Share Post Modal -->
  <div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form id="shareForm" method="POST" action="send_post.php">
        <input type="hidden" name="post_id" id="modalPostId">
        <div class="modal-content text-dark bg-white">
          <div class="modal-header">
            <h5 class="modal-title" id="shareModalLabel">Send Post</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Select a user to send this post to:</p>
            <div class="form-group">
              <select class="form-control" name="recipient_id" required>
                <!-- Options inserted by PHP -->
                <?php
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




</body>

</html>

<?php
$conn->close();
?>
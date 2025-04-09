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
$postSql = "SELECT p.*, u.username
            FROM posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.id = $postID";
$postRes = $conn->query($postSql);
$postRow = $postRes->fetch_assoc();

// Fetch all top level comments for this post
$commentSql = "SELECT c.*,  u.username
               FROM comments c
               JOIN users u ON c.user_id = u.id
               WHERE c.post_id= $postID AND c.parent_id IS NULL
               ORDER BY c.created_at ASC";
$commentRes = $conn->query($commentSql);

function fetchReplies($conn, $parentID)
{
  $stmt = $conn->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.parent_id = ? ORDER BY c.created_at ASC");
  $stmt->bind_param("i", $parentID);
  $stmt->execute();
  $res = $stmt->get_result();
  return $res;
}

?>



<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Comments for Post #<?= $postID; ?></title>

  <!--Bootstrap CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <!--Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <!--Global CSS Styling-->
  <link rel="stylesheet" href="css/style.css">


  <style>
    body {
      background-color: #f8f9fa;
    }

    .container-main {
      display: flex;
      gap: 20px;
      flex-wrap: nowrap;
    }

    .post-section {
      flex: 2;
      min-width: 0;
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

<body class="container py-4">

  <h2 class="mb-4">Post Details</h2>
  <div class="container-main">
    <!-- Post Content (2/3 of screen) -->
    <div class="post-section">
      <!-- Display  details about the post -->
      <?php if ($postRow): ?>
        <div class="mb-3">
          <h4>Posted by: <?php echo $postRow['username']; ?> on <?php echo $postRow['created_at']; ?></h4>
          <p><?= htmlspecialchars($postRow['text_content']) ?></p>

          <?php if ($postRow['post_type'] === 'image'): ?>
            <img src="<?= $postRow['file_path'] ?>" class="img-fluid" style="max-height: 400px; object-fit: contain;"
              alt="Post Image">
          <?php elseif ($postRow['post_type'] === 'video'): ?>
            <video class="w-100" style="max-height: 400px;" controls>
              <source src="<?= $postRow['file_path'] ?>" type="video/mp4">
              Your browser does not support the video tag.
            </video>

          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <!-- Comment Section (1/3, scrollable) -->
    <div class="comment-section">
      <h4 class="sticky-top bg-white pt-2">All Comments</h4>


      <div class="comment-list" style="overflow-y: auto; max-height: 70vh;">
        <?php if ($commentRes && $commentRes->num_rows > 0): ?>
          <?php while ($comment = $commentRes->fetch_assoc()): ?>
            <div class="comment-box">
              <!-- Header: username and timestamp -->
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <strong><?= $comment['username'] ?></strong>
                  <small class="text-muted">(<?= $comment['created_at'] ?>)</small>
                </div>

                <!-- Dropdown menu (3 dots) -->
                <div class="dropdown">
                  <button class="btn btn-sm text-muted" type="button" data-bs-toggle="dropdown">
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

              <p class="comment-text" data-id="<?= $comment['id'] ?>"><?= htmlspecialchars($comment['comment_text']) ?>
              </p>

              <!-- Replies would go here -->
              <div class="reply-box" id="replies-<?= $comment['id'] ?>">
                <?php
                $replies = fetchReplies($conn, $comment['id']);
                while ($reply = $replies->fetch_assoc()):
                  ?>
                  <div class="reply-box ms-3 mt-1">
                    <strong><?= $reply['username'] ?></strong>
                    <small class="text-muted">(<?= $reply['created_at'] ?>)</small>
                    <p class="mb-1"><?= htmlspecialchars($reply['comment_text']) ?></p>
                  </div>
                <?php endwhile; ?>
              </div>


              <!-- Show/Hide Reply form -->
              <div class="text-end">
                <button class="btn btn-outline-secondary btn-sm show-reply-btn px-2 py-0"
                  data-id="<?= $comment['id'] ?>">Reply</button>
              </div>
              <form class="reply-form mt-2 ms-2" data-parent-id="<?= $comment['id'] ?>">
                <input type="hidden" name="post_id" value="<?= $postID ?>">
                <input type="hidden" name="parent_id" value="<?= $comment['id'] ?>">
                <input type="text" name="reply_text" class="form-control form-control-sm mb-2"
                  placeholder="Write your reply...">
                <button class="btn btn-sm btn-secondary ms-2" type="submit">Send</button>
              </form>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p>No comments yet!</p>
        <?php endif; ?>
      </div>

      <hr class="mt-3">
      <form action="comments.php" method="POST">
        <input type="hidden" name="post_id" value="<?= $postID ?>">
        <textarea name="comment_text" class="form-control mb-2" placeholder="Write a comment..."></textarea>
        <button type="submit" class="btn btn-primary">Post Comment</button>
      </form>
    </div>
  </div>


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

</body>

</html>

<?php
$conn->close();
?>